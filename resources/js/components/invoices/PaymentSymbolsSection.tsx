import { UseFormReturn } from "react-hook-form";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { InvoiceFormData } from "./ClientInformationSection";

interface PaymentSymbolsSectionProps {
  form: UseFormReturn<InvoiceFormData>;
}

export const PaymentSymbolsSection = ({ form }: PaymentSymbolsSectionProps) => {
  const { register, formState: { errors } } = form;

  return (
    <div className="bg-gradient-card rounded-xl p-6 border-2 border-border shadow-elegant-sm">
      <h3 className="text-lg font-bold text-foreground mb-4 flex items-center gap-2">
        <span className="w-8 h-8 bg-accent text-accent-foreground rounded-full flex items-center justify-center text-sm">3</span>
        Platobné symboly
      </h3>
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div className="space-y-2">
          <Label htmlFor="variableSymbol">Variabilný symbol *</Label>
          <Input
            id="variableSymbol"
            {...register("variableSymbol")}
            placeholder="Napr. číslo faktúry"
            className="border-primary/30"
          />
          {errors.variableSymbol && (
            <p className="text-sm text-destructive">{errors.variableSymbol.message}</p>
          )}
        </div>
        <div className="space-y-2">
          <Label htmlFor="constantSymbol">Konštantný symbol</Label>
          <Input
            id="constantSymbol"
            {...register("constantSymbol")}
            placeholder="0308"
            maxLength={20}
          />
          {errors.constantSymbol && (
            <p className="text-sm text-destructive">{errors.constantSymbol.message}</p>
          )}
        </div>
        <div className="space-y-2">
          <Label htmlFor="specificSymbol">Špecifický symbol</Label>
          <Input
            id="specificSymbol"
            {...register("specificSymbol")}
            placeholder="123456"
            maxLength={20}
          />
          {errors.specificSymbol && (
            <p className="text-sm text-destructive">{errors.specificSymbol.message}</p>
          )}
        </div>
      </div>
    </div>
  );
};
