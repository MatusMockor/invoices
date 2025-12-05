import { UseFormReturn } from "react-hook-form";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { InvoiceFormData } from "./ClientInformationSection";

interface InvoiceNumberSectionProps {
  form: UseFormReturn<InvoiceFormData>;
}

export const InvoiceNumberSection = ({ form }: InvoiceNumberSectionProps) => {
  const { register, formState: { errors }, watch } = form;

  return (
    <div className="grid grid-cols-2 gap-3">
      <div>
        <Label className="text-xs">Číslo faktúry</Label>
        <Input
          {...register("invoiceNumber")}
          className="mt-1 font-mono"
        />
        {errors.invoiceNumber && (
          <p className="text-xs text-destructive mt-1">{errors.invoiceNumber.message}</p>
        )}
      </div>
      <div>
        <Label className="text-xs">Variabilný symbol</Label>
        <Input
          {...register("variableSymbol")}
          className="mt-1 font-mono"
        />
      </div>
    </div>
  );
};
