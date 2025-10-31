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
    <div className="space-y-4">
      <div className="space-y-2">
        <Label htmlFor="customCompanyIco">IČO spoločnosti *</Label>
        <Input
          id="customCompanyIco"
          {...register("customCompanyIco")}
          placeholder="12345678"
          className="border-primary/30"
          disabled={isEditMode && !useCustomCompany}
          inputMode="numeric"
          pattern="[0-9]*"
        />
        {errors.customCompanyIco && (
          <p className="text-sm text-destructive">{errors.customCompanyIco.message}</p>
        )}
      </div>

      <div className="space-y-2">
        <Label htmlFor="customCompanyDic">DIČ</Label>
        <Input
          id="customCompanyDic"
          {...register("customCompanyDic")}
          placeholder="1234567890"
          className="border-primary/30"
          disabled={isEditMode && !useCustomCompany}
          inputMode="numeric"
          pattern="[0-9]*"
        />
        {errors.customCompanyDic && (
          <p className="text-sm text-destructive">{errors.customCompanyDic.message}</p>
        )}
      </div>

      <div className="space-y-2">
        <Label htmlFor="customCompanyIcDph">IČ DPH</Label>
        <Input
          id="customCompanyIcDph"
          {...register("customCompanyIcDph")}
          placeholder="SK1234567890"
          className="border-primary/30"
          disabled={isEditMode && !useCustomCompany}
        />
        {errors.customCompanyIcDph && (
          <p className="text-sm text-destructive">{errors.customCompanyIcDph.message}</p>
        )}
      </div>

      <div className="space-y-2">
        <Label htmlFor="customCompanyName">Názov spoločnosti *</Label>
        <Input
          id="customCompanyName"
          {...register("customCompanyName")}
          placeholder="XYZ s.r.o."
          className="border-primary/30"
          disabled={isEditMode && !useCustomCompany}
        />
        {errors.customCompanyName && (
          <p className="text-sm text-destructive">{errors.customCompanyName.message}</p>
        )}
      </div>

      <div className="space-y-2">
        <Label htmlFor="customCompanyAddress">Adresa</Label>
        <Input
          id="customCompanyAddress"
          {...register("customCompanyAddress")}
          placeholder="Hlavná 123"
          className="border-primary/30"
          disabled={isEditMode && !useCustomCompany}
        />
        {errors.customCompanyAddress && (
          <p className="text-sm text-destructive">{errors.customCompanyAddress.message}</p>
        )}
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div className="space-y-2">
          <Label htmlFor="customCompanyZip">PSČ</Label>
          <Input
            id="customCompanyZip"
            {...register("customCompanyZip")}
            placeholder="811 01"
            className="border-primary/30"
            disabled={isEditMode && !useCustomCompany}
          />
          {errors.customCompanyZip && (
            <p className="text-sm text-destructive">{errors.customCompanyZip.message}</p>
          )}
        </div>
        <div className="space-y-2">
          <Label htmlFor="customCompanyCity">Mesto</Label>
          <Input
            id="customCompanyCity"
            {...register("customCompanyCity")}
            placeholder="Bratislava"
            className="border-primary/30"
            disabled={isEditMode && !useCustomCompany}
          />
          {errors.customCompanyCity && (
            <p className="text-sm text-destructive">{errors.customCompanyCity.message}</p>
          )}
        </div>
        <div className="space-y-2">
          <Label htmlFor="customCompanyCountry">Krajina</Label>
          <Input
            id="customCompanyCountry"
            {...register("customCompanyCountry")}
            placeholder="SK"
            className="border-primary/30"
            disabled={isEditMode && !useCustomCompany}
          />
          {errors.customCompanyCountry && (
            <p className="text-sm text-destructive">{errors.customCompanyCountry.message}</p>
          )}
        </div>
      </div>
    </div>
  );
};
