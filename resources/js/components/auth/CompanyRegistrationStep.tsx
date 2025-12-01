import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import * as z from "zod";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from "@/components/ui/form";
import { Search } from "lucide-react";
import { toast } from "sonner";
import { authService } from "@/services/authService";
import { businessEntityService } from "@/services/businessEntityService";
import type { UserFormData } from "./UserRegistrationStep";

const companySchema = z.object({
  companyIco: z.string().trim().min(1, "IČO je povinné").max(20),
  companyName: z.string().trim().min(1, "Názov firmy je povinný").max(200),
  companyStreet: z.string().trim().min(1, "Ulica je povinná").max(200),
  companyCity: z.string().trim().min(1, "Mesto je povinné").max(100),
  companyPostalCode: z.string().trim().min(1, "PSČ je povinné").max(10),
  companyDic: z.string().trim().min(1, "DIČ je povinné").max(20),
  companyIcDph: z.string().trim().min(1, "IČ DPH je povinné").max(20),
  companyRegistryOffice: z.string().trim().max(255).optional(),
  companyRegistrationNumber: z.string().trim().max(255).optional(),
});

type CompanyFormData = z.infer<typeof companySchema>;

interface CompanyRegistrationStepProps {
  userData: UserFormData;
  onBack: () => void;
}

export const CompanyRegistrationStep = ({ userData, onBack }: CompanyRegistrationStepProps) => {
  const [icoSearch, setIcoSearch] = useState("");
  const [isSearching, setIsSearching] = useState(false);
  const [isRegistering, setIsRegistering] = useState(false);
  const navigate = useNavigate();

  const companyForm = useForm<CompanyFormData>({
    resolver: zodResolver(companySchema),
    defaultValues: {
      companyIco: "",
      companyName: "",
      companyStreet: "",
      companyCity: "",
      companyPostalCode: "",
      companyDic: "",
      companyIcDph: "",
      companyRegistryOffice: "",
      companyRegistrationNumber: "",
    },
  });

  const handleIcoSearch = async (ico: string) => {
    if (!ico || ico.length < 3) {
      return;
    }

    setIsSearching(true);

    try {
      const response = await businessEntityService.fetchByIco(ico);

      if (response.data) {
        const data = response.data;

        companyForm.setValue("companyIco", data.ico || ico);
        companyForm.setValue("companyName", data.name || "");
        companyForm.setValue("companyStreet", data.street || "");
        companyForm.setValue("companyCity", data.city || "");
        companyForm.setValue("companyPostalCode", data.postal_code || "");
        companyForm.setValue("companyDic", data.dic || "");
        companyForm.setValue("companyIcDph", data.ic_dph || "");

        toast.success("Údaje o firme boli načítané");
      }
    } catch (error: any) {
      toast.error(error.response?.data?.message || "Nepodarilo sa načítať údaje o firme");
    } finally {
      setIsSearching(false);
    }
  };

  const onCompanySubmit = async (companyData: CompanyFormData) => {
    setIsRegistering(true);

    try {
      await authService.registerWithCompany({
        name: userData.name,
        email: userData.email,
        password: userData.password,
        password_confirmation: userData.confirmPassword,
        company_ico: companyData.companyIco,
        company_name: companyData.companyName,
        company_street: companyData.companyStreet,
        company_city: companyData.companyCity,
        company_postal_code: companyData.companyPostalCode,
        company_dic: companyData.companyDic,
        company_ic_dph: companyData.companyIcDph,
        company_registry_office: companyData.companyRegistryOffice || undefined,
        company_registration_number: companyData.companyRegistrationNumber || undefined,
      });

      toast.success("Registrácia úspešná!");
      navigate("/app/dashboard");
    } catch (error: any) {
      toast.error(error.response?.data?.message || "Registrácia zlyhala");
    } finally {
      setIsRegistering(false);
    }
  };

  return (
    <>
      <CardHeader className="space-y-1">
        <CardTitle className="text-3xl font-bold">Údaje o firme</CardTitle>
        <CardDescription className="text-base">
          Pridajte informácie o vašej firme
        </CardDescription>
      </CardHeader>
      <CardContent>
        <Form {...companyForm}>
          <form onSubmit={companyForm.handleSubmit(onCompanySubmit)} className="space-y-6">
            <div className="space-y-4">
              <FormField
                control={companyForm.control}
                name="companyIco"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>IČO *</FormLabel>
                    <FormControl>
                      <div className="relative">
                        <Input
                          placeholder="Zadajte IČO"
                          value={icoSearch}
                          onChange={(e) => {
                            setIcoSearch(e.target.value);
                            field.onChange(e.target.value);
                          }}
                          onBlur={() => {
                            if (icoSearch && icoSearch !== field.value) {
                              handleIcoSearch(icoSearch);
                            }
                          }}
                          disabled={isSearching}
                          className="pr-10"
                        />
                        <Search className="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                      </div>
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />

              <FormField
                control={companyForm.control}
                name="companyName"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Názov firmy *</FormLabel>
                    <FormControl>
                      <Input placeholder="ABC s.r.o." {...field} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />

              <FormField
                control={companyForm.control}
                name="companyStreet"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Ulica a číslo *</FormLabel>
                    <FormControl>
                      <Input placeholder="Hlavná 123" {...field} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <FormField
                  control={companyForm.control}
                  name="companyCity"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Mesto *</FormLabel>
                      <FormControl>
                        <Input placeholder="Bratislava" {...field} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />

                <FormField
                  control={companyForm.control}
                  name="companyPostalCode"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>PSČ *</FormLabel>
                      <FormControl>
                        <Input placeholder="811 01" {...field} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <FormField
                  control={companyForm.control}
                  name="companyDic"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>DIČ *</FormLabel>
                      <FormControl>
                        <Input placeholder="2023456789" {...field} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />

                <FormField
                  control={companyForm.control}
                  name="companyIcDph"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>IČ DPH *</FormLabel>
                      <FormControl>
                        <Input placeholder="SK2023456789" {...field} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <FormField
                  control={companyForm.control}
                  name="companyRegistryOffice"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Registrový úrad</FormLabel>
                      <FormControl>
                        <Input placeholder="Napr. Okresný súd Bratislava I" {...field} maxLength={255} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />

                <FormField
                  control={companyForm.control}
                  name="companyRegistrationNumber"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Registračné číslo</FormLabel>
                      <FormControl>
                        <Input placeholder="Napr. Oddiel: Sro, Vložka č. 123456/B" {...field} maxLength={255} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
              </div>
            </div>

            <div className="flex gap-4">
              <Button
                type="button"
                variant="outline"
                className="w-full"
                size="lg"
                onClick={onBack}
              >
                Späť
              </Button>
              <Button
                type="submit"
                className="w-full"
                size="lg"
                disabled={isRegistering}
              >
                {isRegistering ? "Registrujem..." : "Dokončiť registráciu"}
              </Button>
            </div>
          </form>
        </Form>
      </CardContent>
    </>
  );
};
