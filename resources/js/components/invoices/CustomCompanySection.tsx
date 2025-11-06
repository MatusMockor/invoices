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
      {/* Validation errors for custom company */}
      {useCustomCompany && (errors.customCompanyIco || errors.customCompanyName) && (
        <div className="mb-4 p-4 bg-destructive/10 rounded-lg border border-destructive/30">
          <p className="text-sm font-semibold text-destructive mb-2">Údaje o spoločnosti sú neúplné:</p>
          <ul className="list-disc list-inside space-y-1 text-sm text-destructive">
            {errors.customCompanyIco && <li>{errors.customCompanyIco.message}</li>}
            {errors.customCompanyName && <li>{errors.customCompanyName.message}</li>}
          </ul>
        </div>
      )}

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
          autoComplete="off"
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
          autoComplete="off"
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
          autoComplete="off"
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
          autoComplete="organization"
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
          autoComplete="street-address"
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
            autoComplete="postal-code"
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
            autoComplete="address-level2"
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
            autoComplete="country"
          />
          {errors.customCompanyCountry && (
            <p className="text-sm text-destructive">{errors.customCompanyCountry.message}</p>
          )}
        </div>
      </div>
    </div>
  );
};
