import { UseFormReturn } from "react-hook-form";
import { useMemo } from "react";
import { Card } from "@/components/ui/card";
import { InvoiceFormData } from "./ClientInformationSection";

interface InvoiceSummarySectionProps {
  form: UseFormReturn<InvoiceFormData>;
  items: InvoiceFormData["items"];
}

export const InvoiceSummarySection = ({ form, items }: InvoiceSummarySectionProps) => {
  const { watch } = form;
  const reverseCharge = watch("reverseCharge");

  const total = useMemo(() => {
    if (reverseCharge) {
      // When reverse charge is enabled, return total without VAT
      return items.reduce((total, item) => {
        const quantity = item.quantity || 0;
        const price = item.price || 0;
        return total + (quantity * price);
      }, 0);
    }

    // Normal calculation with VAT
    return items.reduce((total, item) => {
      const quantity = item.quantity || 0;
      const price = item.price || 0;
      const taxRate = item.tax_rate ?? 20;
      const subtotal = quantity * price;
      const taxAmount = subtotal * (taxRate / 100);
      return total + subtotal + taxAmount;
    }, 0);
  }, [items, reverseCharge]);

  const taxBase = useMemo(() => {
    // Tax base is always amount without VAT
    return items.reduce((sum, item) => {
      const quantity = item.quantity || 0;
      const price = item.price || 0;
      return sum + (quantity * price);
    }, 0);
  }, [items]);

  const totalVat = useMemo(() => {
    if (reverseCharge) return 0;

    return items.reduce((sum, item) => {
      const quantity = item.quantity || 0;
      const price = item.price || 0;
      const taxRate = item.tax_rate ?? 20;
      const subtotal = quantity * price;
      return sum + (subtotal * (taxRate / 100));
    }, 0);
  }, [items, reverseCharge]);

  return (
    <Card className="p-5 bg-primary/5 border-primary/20">
      <div className="flex justify-between items-center">
        <div className="space-y-1">
          {!reverseCharge && (
            <>
              <p className="text-sm text-muted-foreground">
                Základ dane: <span className="font-medium text-foreground">{taxBase.toFixed(2)} €</span>
              </p>
              <p className="text-sm text-muted-foreground">
                DPH: <span className="font-medium text-foreground">{totalVat.toFixed(2)} €</span>
              </p>
            </>
          )}
          {reverseCharge && (
            <p className="text-sm text-muted-foreground">Prenos daňovej povinnosti</p>
          )}
        </div>
        <div className="text-right">
          <p className="text-sm text-muted-foreground">Celkom</p>
          <p className="text-3xl font-bold text-primary">{total.toFixed(2)} €</p>
        </div>
      </div>
    </Card>
  );
};
