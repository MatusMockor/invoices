import { UseFormReturn } from "react-hook-form";
import { Settings2 } from "lucide-react";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { InvoiceFormData } from "./ClientInformationSection";
import { SectionHeader } from "@/components/ui/SectionHeader";

interface PaymentSectionProps {
  form: UseFormReturn<InvoiceFormData>;
  /** Whether to show VAT settings (based on supplier VAT payer status) */
  showVatSettings?: boolean;
}

export const PaymentSection = ({ form, showVatSettings = false }: PaymentSectionProps) => {
  const { register, watch, formState: { errors } } = form;
  const reverseCharge = watch("reverseCharge");

  return (
    <div className="space-y-4">
      {/* Payment Symbols Grid */}
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <Label className="text-xs">Konstantny symbol</Label>
          <Input
            {...register("constantSymbol")}
            placeholder="0308"
            className="mt-1"
            maxLength={10}
          />
          {errors.constantSymbol && (
            <p className="text-xs text-destructive mt-1">{errors.constantSymbol.message}</p>
          )}
        </div>
        <div>
          <Label className="text-xs">Specificky symbol</Label>
          <Input
            {...register("specificSymbol")}
            placeholder="Volitelne"
            className="mt-1"
            maxLength={10}
          />
          {errors.specificSymbol && (
            <p className="text-xs text-destructive mt-1">{errors.specificSymbol.message}</p>
          )}
        </div>
      </div>

      {/* VAT Settings - only shown for VAT payers */}
      {showVatSettings && (
        <div className="pt-4 border-t">
          <SectionHeader icon={Settings2} title="Nastavenia DPH" className="mb-3" />

          {/* Reverse Charge Card - touch-friendly */}
          <label
            htmlFor="reverseCharge"
            className="flex items-start gap-3 p-3 rounded-lg bg-muted/50 border cursor-pointer hover:bg-muted/70 transition-colors min-h-[44px]"
          >
            <input
              type="checkbox"
              id="reverseCharge"
              {...register("reverseCharge")}
              className="mt-0.5 h-5 w-5 rounded border-input cursor-pointer"
            />
            <div className="flex-1">
              <span className="text-sm font-medium">
                Prenos danovej povinnosti
              </span>
              <p className="text-xs text-muted-foreground">
                Reverse Charge - DPH plati odberatel
              </p>
            </div>
          </label>

          {/* Reverse Charge Warning */}
          {reverseCharge && (
            <div className="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-700 dark:bg-amber-950/30 dark:border-amber-800 dark:text-amber-400">
              Pri prenose danovej povinnosti nebude uctovane DPH
            </div>
          )}

          {/* Tax Exemption Reason - shown when reverse charge is enabled */}
          {reverseCharge && (
            <div className="mt-3">
              <Label className="text-xs">Dovod oslobodenia od dane *</Label>
              <Input
                {...register("taxExemptionReason")}
                placeholder="Napr. dodanie do EU podla paragrafu 43..."
                className="mt-1"
              />
              {errors.taxExemptionReason && (
                <p className="text-xs text-destructive mt-1">{errors.taxExemptionReason.message}</p>
              )}
            </div>
          )}
        </div>
      )}
    </div>
  );
};
