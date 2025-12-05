import { UseFormReturn } from "react-hook-form";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Card } from "@/components/ui/card";
import { InvoiceFormData } from "./ClientInformationSection";

interface PaymentDPHSectionProps {
  form: UseFormReturn<InvoiceFormData>;
}

export const PaymentDPHSection = ({ form }: PaymentDPHSectionProps) => {
  const { register, formState: { errors }, watch } = form;
  const reverseCharge = watch("reverseCharge");

  return (
    <Card className="p-5">
      <div className="grid md:grid-cols-2 gap-4">
        {/* Konštantný symbol */}
        <div>
          <Label className="text-xs">Konštantný symbol</Label>
          <Input {...register("constantSymbol")} placeholder="0308" className="mt-1" />
        </div>
        {/* Špecifický symbol */}
        <div>
          <Label className="text-xs">Špecifický symbol</Label>
          <Input {...register("specificSymbol")} placeholder="Voliteľné" className="mt-1" />
        </div>
      </div>

      {/* Reverse Charge */}
      <div className="mt-4 flex items-center gap-2">
        <input
          type="checkbox"
          id="reverseCharge"
          {...register("reverseCharge")}
          className="rounded"
        />
        <Label htmlFor="reverseCharge" className="text-sm cursor-pointer">
          Prenos daňovej povinnosti (Reverse Charge)
        </Label>
      </div>

      {reverseCharge && (
        <div className="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded text-sm">
          ⚠️ Pri prenose daňovej povinnosti nebude účtované DPH
        </div>
      )}

      {/* Dôvod oslobodenia */}
      <div className="mt-4">
        <Label className="text-xs">Dôvod oslobodenia od dane</Label>
        <Input
          {...register("taxExemptionReason")}
          placeholder="Napr. export do EÚ..."
          className="mt-1"
        />
        {errors.taxExemptionReason && (
          <p className="text-xs text-destructive mt-1">{errors.taxExemptionReason.message}</p>
        )}
      </div>

      {/* Špecifický text */}
      <div className="mt-4">
        <Label className="text-xs">Špecifický text</Label>
        <Input
          {...register("specialText")}
          placeholder="Napr. úprava zdaňovania..."
          className="mt-1"
        />
      </div>

      {/* Poznámky */}
      <div className="mt-4">
        <Label className="text-xs">Poznámky</Label>
        <Textarea
          {...register("notes")}
          placeholder="Doplňujúce informácie..."
          rows={3}
          className="mt-1 resize-none"
        />
      </div>
    </Card>
  );
};
