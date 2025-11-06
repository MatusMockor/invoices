import { UseFormReturn, UseFieldArrayReturn } from "react-hook-form";
import { Plus, Trash2 } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
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
  const { register, formState: { errors } } = form;

  const calculateTotal = () => {
    return items.reduce((total, item) => {
      const quantity = item.quantity || 0;
      const price = item.price || 0;
      return total + (quantity * price);
    }, 0);
  };

  return (
    <>
      <div className="bg-gradient-card rounded-xl p-6 border-2 border-primary/30 shadow-elegant-sm">
        <h3 className="text-lg font-bold text-primary mb-4 flex items-center gap-2">
          <span className="w-8 h-8 bg-primary text-primary-foreground rounded-full flex items-center justify-center text-sm">5</span>
          Položky faktúry
        </h3>
        <div className="space-y-4">
          {fields.map((field, index) => (
            <div
              key={field.id}
              className="grid grid-cols-12 gap-3 p-4 bg-card rounded-lg border border-primary/20"
            >
              <div className="col-span-5 space-y-2">
                <Label htmlFor={`description-${index}`}>Popis</Label>
                <Input
                  id={`description-${index}`}
                  {...register(`items.${index}.description`)}
                  placeholder="Webový dizajn"
                  className="border-primary/30"
                />
                {errors.items?.[index]?.description && (
                  <p className="text-sm text-destructive">
                    {errors.items[index]?.description?.message}
                  </p>
                )}
              </div>
              <div className="col-span-2 space-y-2">
                <Label htmlFor={`quantity-${index}`}>Počet</Label>
                <Input
                  id={`quantity-${index}`}
                  type="number"
                  {...register(`items.${index}.quantity`, {
                    valueAsNumber: true,
                  })}
                  placeholder="1"
                  min="1"
                  className="border-primary/30"
                />
                {errors.items?.[index]?.quantity && (
                  <p className="text-sm text-destructive">
                    {errors.items[index]?.quantity?.message}
                  </p>
                )}
              </div>
              <div className="col-span-3 space-y-2">
                <Label htmlFor={`price-${index}`}>Cena/ks (€)</Label>
                <Input
                  id={`price-${index}`}
                  type="number"
                  {...register(`items.${index}.price`, {
                    valueAsNumber: true,
                  })}
                  placeholder="0.00"
                  min="0"
                  step="0.01"
                  className="border-primary/30"
                />
                {errors.items?.[index]?.price && (
                  <p className="text-sm text-destructive">
                    {errors.items[index]?.price?.message}
                  </p>
                )}
              </div>
              <div className="col-span-2 flex items-end">
                {fields.length > 1 && (
                  <Button
                    type="button"
                    variant="outline"
                    size="icon"
                    onClick={() => remove(index)}
                    className="border-destructive/30 text-destructive hover:bg-destructive/10"
                  >
                    <Trash2 className="h-4 w-4" />
                  </Button>
                )}
              </div>
            </div>
          ))}
          <Button
            type="button"
            variant="outline"
            onClick={() => append({ description: "", quantity: 1, price: 0 })}
            className="w-full border-primary/30 text-primary hover:bg-primary/10"
          >
            <Plus className="h-4 w-4 mr-2" />
            Pridať položku
          </Button>
          {errors.items && (
            <p className="text-sm text-destructive">{errors.items.message}</p>
          )}
        </div>
      </div>

      {/* Total calculation display */}
      <div className="flex justify-between items-center pt-6 pb-8 border-t border-border bg-card rounded-xl p-6 shadow-elegant-sm">
        <div className="text-lg font-semibold text-foreground">
          Celkom: <span className="text-2xl text-primary ml-2">€{calculateTotal().toFixed(2)}</span>
        </div>
      </div>
    </>
  );
};
