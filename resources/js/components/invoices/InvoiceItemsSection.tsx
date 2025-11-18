import { UseFormReturn, UseFieldArrayReturn } from "react-hook-form";
import { Plus, Trash2, Receipt } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
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
  items,
}: InvoiceItemsSectionProps) => {
  const { register, formState: { errors }, watch, setValue } = form;
  const reverseCharge = watch("reverseCharge");

  return (
    <div className="bg-gradient-to-br from-background to-primary/5 rounded-xl p-6 border-2 border-primary/20 shadow-elegant-sm">
      <div className="flex justify-between items-center mb-6">
        <div className="flex items-center gap-3">
          <div className="p-2 rounded-lg bg-primary/10">
            <Receipt className="h-5 w-5 text-primary" />
          </div>
          <h3 className="text-lg font-semibold">Položky faktúry</h3>
        </div>
        <Button
          type="button"
          variant="default"
          size="sm"
          onClick={() => append({ description: "", quantity: 1, price: 0, tax_rate: 20 })}
        >
          <Plus className="h-4 w-4 mr-2" />
          Pridať položku
        </Button>
      </div>

      <div className="space-y-4">
        {fields.map((field, index) => (
          <Card key={field.id} className="p-5 bg-background border-primary/20 shadow-sm">
            <div className="grid grid-cols-12 gap-4">
              <div className="col-span-12 lg:col-span-5">
                <Label className="text-sm font-medium">Popis *</Label>
                <Input
                  {...register(`items.${index}.description`)}
                  placeholder="Služba alebo tovar"
                  className="mt-1.5"
                />
                {errors.items?.[index]?.description && (
                  <p className="text-sm text-destructive mt-1">
                    {errors.items[index]?.description?.message}
                  </p>
                )}
              </div>
              <div className="col-span-6 sm:col-span-3 lg:col-span-1">
                <Label className="text-sm font-medium">Počet</Label>
                <Input
                  type="number"
                  {...register(`items.${index}.quantity`, { valueAsNumber: true })}
                  min="1"
                  className="mt-1.5"
                />
              </div>
              <div className="col-span-6 sm:col-span-3 lg:col-span-2">
                <Label className="text-sm font-medium">Cena/ks €</Label>
                <Input
                  type="number"
                  step="0.01"
                  {...register(`items.${index}.price`, { valueAsNumber: true })}
                  min="0"
                  placeholder="0.00"
                  className="mt-1.5"
                />
              </div>
              <div className="col-span-6 sm:col-span-3 lg:col-span-1">
                <Label className="text-sm font-medium">DPH %</Label>
                <Select
                  value={watch(`items.${index}.tax_rate`)?.toString() || "20"}
                  onValueChange={(value) => setValue(`items.${index}.tax_rate`, Number(value))}
                >
                  <SelectTrigger className="mt-1.5">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent className="bg-popover">
                    <SelectItem value="0">0%</SelectItem>
                    <SelectItem value="10">10%</SelectItem>
                    <SelectItem value="20">20%</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div className="col-span-12 sm:col-span-9 lg:col-span-2 flex flex-col justify-end">
                <Label className="text-sm font-medium mb-1.5">
                  {reverseCharge ? "Celkom bez DPH" : "Celkom s DPH"}
                </Label>
                <div className="h-10 flex items-center px-3 font-bold text-lg text-primary bg-primary/10 rounded-md">
                  {(() => {
                    const qty = watch(`items.${index}.quantity`) || 0;
                    const price = watch(`items.${index}.price`) || 0;
                    const taxRate = watch(`items.${index}.tax_rate`) || 20;
                    const subtotal = qty * price;
                    const total = reverseCharge ? subtotal : subtotal + (subtotal * (taxRate / 100));
                    return total.toFixed(2);
                  })()} €
                </div>
              </div>
              {fields.length > 1 && (
                <div className="col-span-12 sm:col-span-3 lg:col-span-1 flex lg:absolute lg:right-5 lg:top-5">
                  <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    onClick={() => remove(index)}
                    className="text-destructive hover:text-destructive hover:bg-destructive/10 w-full lg:w-auto"
                  >
                    <Trash2 className="h-4 w-4 mr-2" />
                    Odstrániť
                  </Button>
                </div>
              )}
            </div>
          </Card>
        ))}
      </div>
      {errors.items && (
        <p className="text-sm text-destructive mt-2">{errors.items.message}</p>
      )}
    </div>
  );
};
