import { UseFormReturn, UseFieldArrayReturn } from "react-hook-form";
import { Plus, Trash2, Info, Package } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { InvoiceFormData } from "./ClientInformationSection";
import { SectionHeader } from "@/components/ui/SectionHeader";
import { cn } from "@/lib/utils";
import type { VatRate } from "@/types";

/**
 * Slovak VAT rates according to Paragraph 27 of VAT Act (zakon o DPH)
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
  /** Whether VAT rate can be changed (false for Paragraph 7a EU reverse charge) */
  isVatRateEditable?: boolean;
  /** Default VAT rate to use for new items */
  defaultVatRate?: VatRate | null;
  /** Informational message about VAT (e.g., for Paragraph 7a) */
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

  // Calculate item total with VAT
  const calculateItemTotal = (index: number): string => {
    const qty = watch(`items.${index}.quantity`) || 0;
    const price = watch(`items.${index}.price`) || 0;
    const taxRate = showVatFields ? (watch(`items.${index}.tax_rate`) ?? defaultVatRate ?? 23) : 0;
    const subtotal = qty * price;
    const total = reverseCharge || !showVatFields ? subtotal : subtotal + (subtotal * (taxRate / 100));
    return total.toFixed(2);
  };

  // Handle adding new item with scroll to it
  const handleAddItem = () => {
    append({ description: "", quantity: 1, price: 0, tax_rate: newItemTaxRate });
    // Scroll to new item after DOM update
    setTimeout(() => {
      const items = document.querySelectorAll('[data-item-card]');
      items[items.length - 1]?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }, 100);
  };

  return (
    <Card className="p-4 sm:p-5">
      {/* Header with Add Button */}
      <div className="flex justify-between items-center mb-4">
        <SectionHeader icon={Package} title="Polozky" className="mb-0" />
        <Button
          type="button"
          variant="outline"
          size="sm"
          onClick={handleAddItem}
          className="min-h-[44px] sm:min-h-[36px]"
        >
          <Plus className="h-4 w-4 mr-1" />
          <span className="hidden sm:inline">Pridat polozku</span>
          <span className="sm:hidden">Pridat</span>
        </Button>
      </div>

      {/* VAT info message (e.g., for Paragraph 7a) */}
      {vatInfoMessage && (
        <div className="flex items-center gap-2 p-3 mb-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700 dark:bg-blue-950/30 dark:border-blue-800 dark:text-blue-400">
          <Info className="h-4 w-4 flex-shrink-0" />
          <span>{vatInfoMessage}</span>
        </div>
      )}

      {/* Desktop Header Row - hidden on mobile */}
      <div className="hidden md:flex gap-3 items-center px-3 py-2 text-xs font-medium text-muted-foreground border-b mb-2">
        <div className="flex-1 min-w-0">Popis</div>
        <div className="w-20 text-center">Mnozstvo</div>
        <div className="w-24 text-center">Cena EUR</div>
        {showVatFields && <div className="w-20 text-center">DPH %</div>}
        <div className="w-24 text-center">Suma</div>
        <div className="w-11"></div> {/* Space for delete button */}
      </div>

      <div className="space-y-3">
        {fields.map((field, index) => (
          <div
            key={field.id}
            data-item-card
            className="flex flex-col md:flex-row gap-3 md:items-start p-3 rounded-lg bg-muted/30 border"
          >
            {/* Description - full width on mobile, flex-1 on desktop */}
            <div className="flex-1 min-w-0">
              <Label className="text-xs text-muted-foreground md:hidden mb-1 block">Popis</Label>
              <Input
                {...register(`items.${index}.description`)}
                placeholder="Popis polozky"
                className="bg-background"
              />
              {errors.items?.[index]?.description && (
                <p className="text-xs text-destructive mt-1">
                  {errors.items[index]?.description?.message}
                </p>
              )}
            </div>

            {/* Mobile Grid for Numbers */}
            <div className={cn(
              "grid gap-2 md:contents",
              showVatFields ? "grid-cols-4" : "grid-cols-3"
            )}>
              {/* Quantity */}
              <div className="md:w-20">
                <Label className="text-xs text-muted-foreground md:hidden mb-1 block">Mnoz.</Label>
                <Input
                  type="number"
                  {...register(`items.${index}.quantity`, { valueAsNumber: true })}
                  placeholder="1"
                  min="1"
                  className="bg-background text-center"
                />
              </div>

              {/* Price */}
              <div className="md:w-24">
                <Label className="text-xs text-muted-foreground md:hidden mb-1 block">Cena</Label>
                <Input
                  type="number"
                  step="0.01"
                  {...register(`items.${index}.price`, { valueAsNumber: true })}
                  placeholder="0.00"
                  min="0"
                  className="bg-background text-center"
                />
              </div>

              {/* VAT Rate */}
              {showVatFields && (
                <div className="md:w-20">
                  <Label className="text-xs text-muted-foreground md:hidden mb-1 block">DPH</Label>
                  <Select
                    value={watch(`items.${index}.tax_rate`)?.toString() || defaultVatRate?.toString() || "23"}
                    onValueChange={(value) => setValue(`items.${index}.tax_rate`, Number(value))}
                    disabled={!isVatRateEditable}
                  >
                    <SelectTrigger className={cn(
                      "bg-background",
                      !isVatRateEditable && "opacity-60 cursor-not-allowed"
                    )}>
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
                    <p className="text-xs text-muted-foreground mt-1 hidden md:block">Zamknute</p>
                  )}
                </div>
              )}

              {/* Item Sum - shown in grid on mobile */}
              <div className="md:hidden flex flex-col">
                <Label className="text-xs text-muted-foreground mb-1 block">Suma</Label>
                <div className="h-10 flex items-center justify-center font-semibold text-primary">
                  {calculateItemTotal(index)} EUR
                </div>
              </div>
            </div>

            {/* Item Sum - desktop only */}
            <div className="hidden md:flex md:w-24 h-10 items-center justify-center">
              <span className="font-semibold text-primary">
                {calculateItemTotal(index)} EUR
              </span>
            </div>

            {/* Delete Button */}
            <div className="md:w-11 flex items-center justify-end md:justify-center">
              {fields.length > 1 && (
                <Button
                  type="button"
                  variant="ghost"
                  size="icon"
                  onClick={() => remove(index)}
                  className="h-11 w-11 text-muted-foreground hover:text-destructive hover:bg-destructive/10"
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
