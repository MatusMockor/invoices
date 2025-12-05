import { UseFormReturn, UseFieldArrayReturn } from "react-hook-form";
import { Plus, Trash2 } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { InvoiceFormData } from "./ClientInformationSection";

interface InvoiceItemsSectionProps {
  form: UseFormReturn<InvoiceFormData>;
  fields: UseFieldArrayReturn<InvoiceFormData, "items", "id">["fields"];
  append: UseFieldArrayReturn<InvoiceFormData, "items", "id">["append"];
  remove: UseFieldArrayReturn<InvoiceFormData, "items", "id">["remove"];
  items: InvoiceFormData["items"];
}

export const InvoiceItemsSection = ({
  form,
  fields,
  append,
  remove,
}: InvoiceItemsSectionProps) => {
  const { register, formState: { errors }, watch, setValue } = form;
  const reverseCharge = watch("reverseCharge");

  return (
    <Card className="p-5">
      <div className="flex justify-between items-center mb-4">
        <h2 className="font-semibold">Položky</h2>
        <Button
          type="button"
          variant="outline"
          size="sm"
          onClick={() => append({ description: "", quantity: 1, price: 0, tax_rate: 20 })}
        >
          <Plus className="h-4 w-4 mr-1" />
          Pridať
        </Button>
      </div>

      {/* Header row - aligned with item rows */}
      <div className="hidden sm:flex gap-3 items-center text-xs font-medium text-muted-foreground border-b pb-2 mb-3 ml-3 mr-3">
        <div className="flex-1 min-w-0">Popis</div>
        <div className="w-20 text-center">Množstvo</div>
        <div className="w-24 text-center">Cena €</div>
        <div className="w-20 text-center">DPH %</div>
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
            <div className="w-20">
              <Select
                value={watch(`items.${index}.tax_rate`)?.toString() || "20"}
                onValueChange={(value) => setValue(`items.${index}.tax_rate`, Number(value))}
              >
                <SelectTrigger className="bg-background">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent className="bg-popover">
                  <SelectItem value="0">0%</SelectItem>
                  <SelectItem value="10">10%</SelectItem>
                  <SelectItem value="20">20%</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div className="w-24 text-right font-semibold flex items-center justify-end">
              {(() => {
                const qty = watch(`items.${index}.quantity`) || 0;
                const price = watch(`items.${index}.price`) || 0;
                const taxRate = watch(`items.${index}.tax_rate`) ?? 20;
                const subtotal = qty * price;
                const total = reverseCharge ? subtotal : subtotal + (subtotal * (taxRate / 100));
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
