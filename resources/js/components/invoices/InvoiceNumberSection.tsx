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
    <div className="space-y-4">
      <div className="space-y-2">
        <Label htmlFor="invoiceNumber">Číslo faktúry *</Label>
        <Input
          id="invoiceNumber"
          {...register("invoiceNumber")}
          placeholder="Napr. 20250001"
          className="font-semibold"
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
