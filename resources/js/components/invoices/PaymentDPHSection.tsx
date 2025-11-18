import { UseFormReturn } from "react-hook-form";
import { CreditCard, Calculator, FileText } from "lucide-react";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { InvoiceFormData } from "./ClientInformationSection";

interface PaymentDPHSectionProps {
  form: UseFormReturn<InvoiceFormData>;
}

export const PaymentDPHSection = ({ form }: PaymentDPHSectionProps) => {
  const { register, formState: { errors }, watch } = form;
  const reverseCharge = watch("reverseCharge");

  return (
    <div className="space-y-6">
      {/* Payment Symbols */}
      <div className="bg-gradient-to-br from-background to-primary/5 rounded-xl p-6 border-2 border-primary/20 shadow-elegant-sm">
        <div className="flex items-center gap-3 mb-6">
          <div className="p-2 rounded-lg bg-primary/10">
            <CreditCard className="h-5 w-5 text-primary" />
          </div>
          <h3 className="text-lg font-semibold">Platobné symboly</h3>
        </div>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div className="space-y-2">
            <Label className="flex items-center gap-2">
              <span className="text-primary">●</span>
              Variabilný symbol *
            </Label>
            <Input
              {...register("variableSymbol")}
              readOnly
              className="bg-muted/50 border-primary/30 font-semibold"
            />
            {errors.variableSymbol && (
              <p className="text-sm text-destructive">{errors.variableSymbol.message}</p>
            )}
          </div>
          <div className="space-y-2">
            <Label className="flex items-center gap-2">
              <span className="text-muted-foreground">○</span>
              Konštantný symbol
            </Label>
            <Input
              {...register("constantSymbol")}
              placeholder="0308"
              className="border-primary/20"
            />
          </div>
          <div className="space-y-2">
            <Label className="flex items-center gap-2">
              <span className="text-muted-foreground">○</span>
              Špecifický symbol
            </Label>
            <Input
              {...register("specificSymbol")}
              placeholder="Voliteľné"
              className="border-primary/20"
            />
          </div>
        </div>
      </div>

      {/* DPH Settings */}
      <div className="bg-gradient-to-br from-background to-accent/5 rounded-xl p-6 border-2 border-accent/20 shadow-elegant-sm">
        <div className="flex items-center gap-3 mb-6">
          <div className="p-2 rounded-lg bg-accent/10">
            <Calculator className="h-5 w-5 text-accent" />
          </div>
          <h3 className="text-lg font-semibold">DPH nastavenia</h3>
        </div>
        <div className="space-y-4">
          <div className="flex items-center space-x-3 p-3 rounded-lg border border-border/50 bg-background/50 hover:border-accent/30 transition-colors">
            <input
              type="checkbox"
              id="reverseCharge"
              {...register("reverseCharge")}
              className="h-4 w-4 rounded accent-accent"
              aria-describedby="reverse-charge-warning"
              aria-label="Povoliť prenos daňovej povinnosti (Reverse Charge)"
            />
            <Label htmlFor="reverseCharge" className="cursor-pointer font-medium">
              Prenos daňovej povinnosti (Reverse Charge)
            </Label>
          </div>
          {reverseCharge && (
            <div
              id="reverse-charge-warning"
              role="alert"
              aria-live="polite"
              className="p-3 bg-yellow-50 border border-yellow-200 rounded text-sm animate-in fade-in duration-200"
            >
              ⚠️ Pri prenose daňovej povinnosti nebude účtované DPH
            </div>
          )}
          <div className="space-y-2">
            <Label htmlFor="taxExemptionReason">Dôvod oslobodenia od dane</Label>
            <Input
              id="taxExemptionReason"
              {...register("taxExemptionReason")}
              placeholder="Napr. export do EÚ..."
              className="border-accent/20"
              aria-label="Zadajte dôvod oslobodenia od dane"
            />
          </div>
          <div className="space-y-2">
            <Label htmlFor="specialText">Špecifický text</Label>
            <Input
              id="specialText"
              {...register("specialText")}
              placeholder="Napr. úprava zdaňovania..."
              className="border-accent/20"
              aria-label="Zadajte špecifický text pre faktúru"
            />
          </div>
        </div>
      </div>

      {/* Notes */}
      <div className="bg-gradient-to-br from-background to-muted/10 rounded-xl p-6 border-2 border-muted shadow-elegant-sm">
        <div className="flex items-center gap-3 mb-4">
          <div className="p-2 rounded-lg bg-muted">
            <FileText className="h-5 w-5 text-foreground" />
          </div>
          <h3 className="text-lg font-semibold">Poznámky</h3>
        </div>
        <Textarea
          id="notes"
          {...register("notes")}
          placeholder="Doplňujúce informácie k faktúre..."
          rows={4}
          className="border-muted resize-none"
          aria-label="Poznámky k faktúre"
        />
      </div>
    </div>
  );
};
