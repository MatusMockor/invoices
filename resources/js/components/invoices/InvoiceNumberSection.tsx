import { UseFormReturn } from "react-hook-form";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { InvoiceFormData } from "./ClientInformationSection";

interface InvoiceNumberSectionProps {
  form: UseFormReturn<InvoiceFormData>;
}

export const InvoiceNumberSection = ({ form }: InvoiceNumberSectionProps) => {
  const { register, formState: { errors } } = form;

  return (
    <div className="bg-gradient-card rounded-xl p-6 border-2 border-primary/30 shadow-elegant-sm">
      <h3 className="text-lg font-bold text-primary mb-4 flex items-center gap-2">
        <span className="w-8 h-8 bg-primary text-primary-foreground rounded-full flex items-center justify-center text-sm">1</span>
        Číslo faktúry
      </h3>
      <div className="space-y-2">
        <Label htmlFor="invoiceNumber">Číslo faktúry *</Label>
        <Input
          id="invoiceNumber"
          {...register("invoiceNumber")}
          placeholder="Napr. 20250001"
          className="border-primary/30 text-lg font-semibold"
        />
        {errors.invoiceNumber && (
          <p className="text-sm text-destructive">{errors.invoiceNumber.message}</p>
        )}
        <p className="text-xs text-muted-foreground">
          Číslo faktúry sa automaticky synchronizuje s variabilným symbolom
        </p>
      </div>
    </div>
  );
};
