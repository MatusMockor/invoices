import { UseFormReturn, UseFieldArrayReturn } from "react-hook-form";
import { Plus, Trash2, Info } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { InvoiceFormData } from "./ClientInformationSection";
import type { VatRate } from "@/types";

/**
 * Slovak VAT rates according to § 27 of VAT Act (zákon o DPH)
 */
const VAT_RATE_OPTIONS: { value: VatRate; label: string }[] = [
  { value: 23, label: '23%' },
  { value: 19, label: '19%' },
  { value: 5, label: '5%' },
  { value: 0, label: '0%' },
];

interface InvoiceItemsSectionProps {
  form: UseFormReturn<InvoiceFormData>;
  fields: UseFieldArrayReturn<InvoiceFormData, "items", "id">["fields"];
  append: UseFieldArrayReturn<InvoiceFormData, "items", "id">["append"];
  remove: UseFieldArrayReturn<InvoiceFormData, "items", "id">["remove"];
  items: InvoiceFormData["items"];
  /** Whether to show VAT rate column (hidden for non-VAT payers) */
  showVatFields?: boolean;
  /** Whether VAT rate can be changed (false for §7a EU reverse charge) */
  isVatRateEditable?: boolean;
  /** Default VAT rate to use for new items */
  defaultVatRate?: VatRate | null;
  /** Informational message about VAT (e.g., for §7a) */
  vatInfoMessage?: string | null;
}

export const InvoiceItemsSection = ({
  form,
  fields,
  append,
  remove,
  showVatFields = true,
  isVatRateEditable = true,
  defaultVatRate = 23,
  vatInfoMessage,
}: InvoiceItemsSectionProps) => {
  const { register, formState: { errors }, watch, setValue } = form;
  const reverseCharge = watch("reverseCharge");

  // Determine the default tax rate for new items
  const newItemTaxRate = showVatFields ? (defaultVatRate ?? 23) : 0;

  return (
    <Card className="p-5">
      <div className="flex justify-between items-center mb-4">
        <h2 className="font-semibold">Položky</h2>
        <Button
          type="button"
          variant="outline"
          size="sm"
          onClick={() => append({ description: "", quantity: 1, price: 0, tax_rate: newItemTaxRate })}
        >
          <Plus className="h-4 w-4 mr-1" />
          Pridať
        </Button>
      </div>

      {/* VAT info message (e.g., for §7a) */}
      {vatInfoMessage && (
        <div className="flex items-center gap-2 p-3 mb-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700">
          <Info className="h-4 w-4 flex-shrink-0" />
          <span>{vatInfoMessage}</span>
        </div>
      )}

      {/* Header row - aligned with item rows */}
      <div className="hidden sm:flex gap-3 items-center text-xs font-medium text-muted-foreground border-b pb-2 mb-3 ml-3 mr-3">
        <div className="flex-1 min-w-0">Popis</div>
        <div className="w-20 text-center">Množstvo</div>
        <div className="w-24 text-center">Cena €</div>
        {showVatFields && <div className="w-20 text-center">DPH %</div>}
        <div className="w-24 text-right pr-2">Celkom</div>
        <div className="w-9"></div>
      </div>

      <div className="space-y-3">
        {fields.map((field, index) => (
          <div key={field.id} className="flex gap-3 items-center p-3 rounded-lg bg-muted/30 border">
            <div className="flex-1 min-w-0">
              <Input
                {...register(`items.${index}.description`)}
                placeholder="Popis položky"
                className="bg-background"
              />
              {errors.items?.[index]?.description && (
                <p className="text-xs text-destructive mt-1">
                  {errors.items[index]?.description?.message}
                </p>
              )}
            </div>
            <div className="w-20">
              <Input
                type="number"
                {...register(`items.${index}.quantity`, { valueAsNumber: true })}
                placeholder="Počet"
                min="1"
                className="bg-background text-center"
              />
            </div>
            <div className="w-24">
              <Input
                type="number"
                step="0.01"
                {...register(`items.${index}.price`, { valueAsNumber: true })}
                placeholder="Cena €"
                min="0"
                className="bg-background"
              />
            </div>
            {showVatFields && (
              <div className="w-20">
                <Select
                  value={watch(`items.${index}.tax_rate`)?.toString() || defaultVatRate?.toString() || "23"}
                  onValueChange={(value) => setValue(`items.${index}.tax_rate`, Number(value))}
                  disabled={!isVatRateEditable}
                >
                  <SelectTrigger className={`bg-background ${!isVatRateEditable ? 'opacity-60 cursor-not-allowed' : ''}`}>
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent className="bg-popover">
                    {VAT_RATE_OPTIONS.map((option) => (
                      <SelectItem key={option.value} value={option.value.toString()}>
                        {option.label}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {!isVatRateEditable && (
                  <p className="text-xs text-muted-foreground mt-1">Zamknuté</p>
                )}
              </div>
            )}
            <div className="w-24 text-right font-semibold flex items-center justify-end">
              {(() => {
                const qty = watch(`items.${index}.quantity`) || 0;
                const price = watch(`items.${index}.price`) || 0;
                const taxRate = showVatFields ? (watch(`items.${index}.tax_rate`) ?? defaultVatRate ?? 23) : 0;
                const subtotal = qty * price;
                const total = reverseCharge || !showVatFields ? subtotal : subtotal + (subtotal * (taxRate / 100));
                return total.toFixed(2);
              })()} €
            </div>
            <div className="w-9 flex items-center justify-center">
              {fields.length > 1 && (
                <Button
                  type="button"
                  variant="ghost"
                  size="icon"
                  onClick={() => remove(index)}
                  className="text-muted-foreground hover:text-destructive"
                >
                  <Trash2 className="h-4 w-4" />
                </Button>
              )}
            </div>
          </div>
        ))}
      </div>
      {errors.items && (
        <p className="text-xs text-destructive mt-2">{errors.items.message}</p>
      )}
    </Card>
  );
};
