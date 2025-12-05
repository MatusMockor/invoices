import { UseFormReturn } from "react-hook-form";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { InvoiceFormData } from "./ClientInformationSection";

interface CustomCompanySectionProps {
  form: UseFormReturn<InvoiceFormData>;
  isEditMode: boolean;
  useCustomCompany: boolean;
}

export const CustomCompanySection = ({
  form,
  isEditMode,
  useCustomCompany,
}: CustomCompanySectionProps) => {
  const { register, formState: { errors } } = form;

  return (
    <div className="space-y-3">
      {/* Validation errors for custom company */}
      {useCustomCompany && (errors.customCompanyIco || errors.customCompanyName) && (
        <div className="mb-3 p-3 bg-destructive/10 rounded-md border border-destructive/30">
          <p className="text-xs font-semibold text-destructive mb-1">Údaje o spoločnosti sú neúplné:</p>
          <ul className="list-disc list-inside space-y-0.5 text-xs text-destructive">
            {errors.customCompanyIco && <li>{errors.customCompanyIco.message}</li>}
            {errors.customCompanyName && <li>{errors.customCompanyName.message}</li>}
          </ul>
        </div>
      )}

      <div>
        <Label className="text-xs">Názov firmy</Label>
        <Input
          {...register("customCompanyName")}
          placeholder="XYZ s.r.o."
          className="mt-1"
          disabled={isEditMode && !useCustomCompany}
        />
        {errors.customCompanyName && (
          <p className="text-xs text-destructive mt-1">{errors.customCompanyName.message}</p>
        )}
      </div>

      <div>
        <Label className="text-xs">Adresa</Label>
        <Input
          {...register("customCompanyAddress")}
          placeholder="Hlavná 123"
          className="mt-1"
          disabled={isEditMode && !useCustomCompany}
        />
      </div>

      <div className="grid grid-cols-3 gap-2">
        <div>
          <Label className="text-xs">IČO</Label>
          <Input
            {...register("customCompanyIco")}
            placeholder="12345678"
            className="mt-1"
            disabled={isEditMode && !useCustomCompany}
          />
        </div>
        <div>
          <Label className="text-xs">DIČ</Label>
          <Input
            {...register("customCompanyDic")}
            placeholder="1234567890"
            className="mt-1"
            disabled={isEditMode && !useCustomCompany}
          />
        </div>
        <div>
          <Label className="text-xs">IČ DPH</Label>
          <Input
            {...register("customCompanyIcDph")}
            placeholder="SK1234567890"
            className="mt-1"
            disabled={isEditMode && !useCustomCompany}
          />
        </div>
      </div>

      <div className="grid grid-cols-3 gap-2">
        <div>
          <Label className="text-xs">PSČ</Label>
          <Input
            {...register("customCompanyZip")}
            placeholder="811 01"
            className="mt-1"
            disabled={isEditMode && !useCustomCompany}
          />
        </div>
        <div>
          <Label className="text-xs">Mesto</Label>
          <Input
            {...register("customCompanyCity")}
            placeholder="Bratislava"
            className="mt-1"
            disabled={isEditMode && !useCustomCompany}
          />
        </div>
        <div>
          <Label className="text-xs">Krajina</Label>
          <Input
            {...register("customCompanyCountry")}
            placeholder="SK"
            className="mt-1"
            disabled={isEditMode && !useCustomCompany}
          />
        </div>
      </div>
    </div>
  );
};
