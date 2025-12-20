import { UseFormReturn } from "react-hook-form";
import { Card } from "@/components/ui/card";
import { InvoiceFormData } from "./ClientInformationSection";

interface InvoiceSummarySectionProps {
  form: UseFormReturn<InvoiceFormData>;
  /** Whether VAT summary fields should be visible (based on supplier VAT payer status) */
  showVatSummary: boolean;
}

export const InvoiceSummarySection = ({ form, showVatSummary }: InvoiceSummarySectionProps) => {
  const { watch } = form;

  // Watch the entire items array to trigger re-renders on any item change
  // This ensures reactivity when quantity, price, or tax_rate changes
  const items = watch("items");
  const reverseCharge = watch("reverseCharge");

  // Calculate tax base (amount without VAT)
  const taxBase = items.reduce((sum, item) => {
    const quantity = item.quantity || 0;
    const price = item.price || 0;
    return sum + (quantity * price);
  }, 0);

  // VAT should only be calculated and shown if:
  // 1. Supplier is a VAT payer (showVatSummary = true)
  // 2. It's not a reverse charge invoice
  const shouldShowVat = showVatSummary && !reverseCharge;

  // Calculate total VAT (only if VAT should be shown)
  const totalVat = shouldShowVat
    ? items.reduce((sum, item) => {
        const quantity = item.quantity || 0;
        const price = item.price || 0;
        const taxRate = item.tax_rate ?? 20;
        const subtotal = quantity * price;
        return sum + (subtotal * (taxRate / 100));
      }, 0)
    : 0;

  // Calculate total (with or without VAT)
  const total = shouldShowVat ? taxBase + totalVat : taxBase;

  // Show side info only when VAT details or reverse charge text should be displayed
  const showSideInfo = shouldShowVat || (showVatSummary && reverseCharge);

  return (
    <Card className="p-4 sm:p-6 bg-gradient-to-r from-primary/5 to-primary/10 border-primary/20 rounded-xl h-full flex flex-col justify-center">
      {showSideInfo ? (
        <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 sm:gap-6">
          <div className="space-y-1.5">
            {shouldShowVat && (
              <>
                <p className="text-sm text-muted-foreground">
                  Zaklad dane: <span className="font-medium text-foreground">{taxBase.toFixed(2)} EUR</span>
                </p>
                <p className="text-sm text-muted-foreground">
                  DPH: <span className="font-medium text-foreground">{totalVat.toFixed(2)} EUR</span>
                </p>
              </>
            )}
            {showVatSummary && reverseCharge && (
              <p className="text-sm text-muted-foreground">Prenos danovej povinnosti</p>
            )}
          </div>
          <div className="text-left sm:text-right">
            <p className="text-sm text-muted-foreground">Celkom</p>
            <p className="text-2xl sm:text-3xl font-bold text-primary">{total.toFixed(2)} EUR</p>
          </div>
        </div>
      ) : (
        <div className="text-center">
          <p className="text-sm text-muted-foreground">Celkom</p>
          <p className="text-2xl sm:text-3xl font-bold text-primary">{total.toFixed(2)} EUR</p>
        </div>
      )}
    </Card>
  );
};
