import { UseFormReturn } from "react-hook-form";
import { useMemo } from "react";
import { Calculator } from "lucide-react";
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

  const vatRecapitulation = useMemo(() => {
    if (reverseCharge) return [];

    const recap: { [key: number]: { taxBase: number; vatAmount: number } } = {};

    items.forEach(item => {
      const quantity = item.quantity || 0;
      const price = item.price || 0;
      const taxRate = item.tax_rate ?? 20;
      const subtotal = quantity * price;
      const vatAmount = subtotal * (taxRate / 100);

      if (!recap[taxRate]) {
        recap[taxRate] = { taxBase: 0, vatAmount: 0 };
      }
      recap[taxRate].taxBase += subtotal;
      recap[taxRate].vatAmount += vatAmount;
    });

    return Object.entries(recap).map(([rate, values]) => ({
      rate: Number(rate),
      taxBase: values.taxBase,
      vatAmount: values.vatAmount,
    }));
  }, [items, reverseCharge]);

  return (
    <div className="space-y-6">
      <div className="bg-gradient-to-br from-primary/5 to-accent/5 rounded-xl p-6 border-2 border-primary/20 shadow-elegant-sm">
        <div className="flex items-center gap-3 mb-6">
          <div className="p-2 rounded-lg bg-primary/10">
            <Calculator className="h-5 w-5 text-primary" />
          </div>
          <h3 className="text-lg font-semibold">Rekapitulácia DPH</h3>
        </div>
        <div className="space-y-3">
          {reverseCharge ? (
            <div className="p-3 bg-yellow-50 border border-yellow-200 rounded text-sm">
              ⚠️ Prenos daňovej povinnosti - DPH neúčtované
            </div>
          ) : (
            <>
              {vatRecapitulation.map((recap) => (
                <div key={recap.rate} className="flex justify-between text-sm border-b pb-2">
                  <span>DPH {recap.rate}%:</span>
                  <div className="text-right">
                    <div>Základ: <span className="font-semibold">{recap.taxBase.toFixed(2)} €</span></div>
                    <div>DPH: <span className="font-semibold">{recap.vatAmount.toFixed(2)} €</span></div>
                  </div>
                </div>
              ))}
              <div className="flex justify-between pt-2 font-medium">
                <span>Celkový základ dane:</span>
                <span className="font-bold">{taxBase.toFixed(2)} €</span>
              </div>
              <div className="flex justify-between font-medium">
                <span>Celková DPH:</span>
                <span className="font-bold">{totalVat.toFixed(2)} €</span>
              </div>
            </>
          )}
          <div className="flex justify-between text-2xl font-bold pt-3 border-t-2 text-primary">
            <span>Celkom na úhradu:</span>
            <span>{total.toFixed(2)} €</span>
          </div>
        </div>
      </div>

      {/* Item-by-item breakdown */}
      <div className="bg-card rounded-xl p-6 border shadow-elegant-sm">
        <h4 className="text-md font-semibold mb-4">Rozpis položiek</h4>
        <div className="space-y-2">
          {items.map((item, index) => {
            const quantity = item.quantity || 0;
            const price = item.price || 0;
            const taxRate = item.tax_rate ?? 20;
            const subtotal = quantity * price;
            const vatAmount = reverseCharge ? 0 : subtotal * (taxRate / 100);
            const total = subtotal + vatAmount;

            return (
              <div key={index} className="flex justify-between text-sm p-3 bg-muted/30 rounded-lg">
                <div className="flex-1">
                  <p className="font-medium">{item.description || `Položka ${index + 1}`}</p>
                  <p className="text-xs text-muted-foreground">
                    {quantity} × {price.toFixed(2)} € {!reverseCharge && `(DPH ${taxRate}%)`}
                  </p>
                </div>
                <div className="text-right">
                  <p className="font-semibold">{total.toFixed(2)} €</p>
                  {!reverseCharge && (
                    <p className="text-xs text-muted-foreground">
                      z toho DPH: {vatAmount.toFixed(2)} €
                    </p>
                  )}
                </div>
              </div>
            );
          })}
        </div>
      </div>
    </div>
  );
};
