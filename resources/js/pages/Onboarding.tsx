import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import * as z from "zod";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from "@/components/ui/form";
import { Command, CommandEmpty, CommandGroup, CommandItem, CommandList } from "@/components/ui/command";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Search, Building2, Loader2 } from "lucide-react";
import { toast } from "sonner";
import { onboardingService, type CompanyData } from "@/services/onboardingService";
import { businessEntityService } from "@/services/businessEntityService";
import { companyService } from "@/services/companyService";
import { useAuthContext } from "@/contexts/AuthContext";
import { VAT_PAYER_STATUS_OPTIONS } from "@/constants/vatPayerStatus";
import { Company } from "@/types/company";

const companySchema = z.object({
  ico: z.string().trim().min(1, "IČO je povinné").max(20),
  name: z.string().trim().min(1, "Názov firmy je povinný").max(200),
  street: z.string().trim().min(1, "Miesto podnikania / Sídlo firmy je povinné").max(200),
  city: z.string().trim().min(1, "Mesto je povinné").max(100),
  postal_code: z.string().trim().min(1, "PSČ je povinné").max(10),
  dic: z.string().trim().max(20).optional(),
  ic_dph: z.string().trim().max(20).optional(),
  vat_payer_status: z.enum(['not_vat_payer', 'vat_payer', 'vat_payer_paragraph_7']).optional(),
  registration_office: z.string().trim().max(255).optional(),
  registration_number: z.string().trim().max(255).optional(),
  iban: z.string().trim().max(34).optional()
    .refine(
      (val) => !val || /^[A-Z]{2}[0-9]{2}[A-Z0-9]{1,30}$/i.test(val.replace(/\s/g, '')),
      { message: "Neplatny format IBAN" }
    ),
  swift: z.string().trim().max(11).optional()
    .refine(
      (val) => !val || /^[A-Z]{4}[A-Z]{2}[A-Z0-9]{2}([A-Z0-9]{3})?$/i.test(val),
      { message: "Neplatny format SWIFT/BIC" }
    ),
});

type CompanyFormData = z.infer<typeof companySchema>;

const Onboarding = () => {
  const [icoSearch, setIcoSearch] = useState("");
  const [isSearching, setIsSearching] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [showSuggestions, setShowSuggestions] = useState(false);
  const [filteredCompanies, setFilteredCompanies] = useState<Company[]>([]);
  const navigate = useNavigate();
  const { user, refetch } = useAuthContext();

  // Redirect to dashboard if user already has a company
  useEffect(() => {
    if (user?.current_company_id) {
      navigate("/app/dashboard", { replace: true });
    }
  }, [user, navigate]);

  const companyForm = useForm<CompanyFormData>({
    resolver: zodResolver(companySchema),
    defaultValues: {
      ico: "",
      name: "",
      street: "",
      city: "",
      postal_code: "",
      dic: "",
      ic_dph: "",
      vat_payer_status: undefined,
      registration_office: "",
      registration_number: "",
      iban: "",
      swift: "",
    },
  });

  // Search companies with debounce
  useEffect(() => {
    if (icoSearch.length >= 2 && showSuggestions) {
      setIsSearching(true);

      // Real API call with debounce
      const timer = setTimeout(async () => {
        try {
          const response = await companyService.searchCustomerCompanies(icoSearch);
          setFilteredCompanies(response.data);
        } catch (error) {
          console.error('Error searching companies:', error);
          setFilteredCompanies([]);
        } finally {
          setIsSearching(false);
        }
      }, 500);

      return () => {
        clearTimeout(timer);
        setIsSearching(false);
      };
    } else if (icoSearch.length === 0) {
      setFilteredCompanies([]);
      setIsSearching(false);
    }
  }, [icoSearch, showSuggestions]);

  const onCompanySelect = (company: Company) => {
    companyForm.setValue("ico", company.ico || "");
    companyForm.setValue("name", company.name || "");
    companyForm.setValue("street", company.address || "");
    companyForm.setValue("city", company.city || "");
    companyForm.setValue("postal_code", company.postal_code || "");
    companyForm.setValue("dic", company.dic || "");
    companyForm.setValue("ic_dph", company.ic_dph || "");
    companyForm.setValue("registration_office", company.registration_office || "");
    companyForm.setValue("registration_number", company.registration_number || "");
    if (company.vat_payer_status) {
      companyForm.setValue("vat_payer_status", company.vat_payer_status);
    }

    setIcoSearch(company.ico || "");
    setShowSuggestions(false);

    toast.success("Údaje o firme boli načítané");
  };

  const onSubmit = async (data: CompanyFormData) => {
    setIsSubmitting(true);

    try {
      // Convert empty strings to undefined for optional fields
      const submissionData: CompanyData = {
        ...data,
        dic: data.dic?.trim() || undefined,
        ic_dph: data.ic_dph?.trim() || undefined,
        vat_payer_status: data.vat_payer_status || undefined,
        registration_office: data.registration_office?.trim() || undefined,
        registration_number: data.registration_number?.trim() || undefined,
        iban: data.iban?.trim() || undefined,
        swift: data.swift?.trim() || undefined,
      };

      await onboardingService.createCompany(submissionData);

      toast.success("Firma bola úspešne vytvorená!");

      // Refetch user data to update current_company_id
      await refetch();

      navigate("/app/dashboard");
    } catch (error: any) {
      toast.error(error.response?.data?.message || "Nepodarilo sa vytvoriť firmu");
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-background via-background to-secondary/20 flex items-center justify-center p-4">
      <div className="w-full max-w-2xl">
        <Card className="shadow-xl border-border/50 bg-card/95 backdrop-blur animate-scale-in">
          <CardHeader className="space-y-1">
            <div className="flex items-center gap-3">
              <div className="p-2 bg-primary/10 rounded-lg">
                <Building2 className="h-6 w-6 text-primary" />
              </div>
              <div>
                <CardTitle className="text-3xl font-bold">Vitajte!</CardTitle>
                <CardDescription className="text-base">
                  Pridajte informácie o vašej firme
                </CardDescription>
              </div>
            </div>
          </CardHeader>
          <CardContent>
            <Form {...companyForm}>
              <form onSubmit={companyForm.handleSubmit(onSubmit)} className="space-y-6">
                <div className="space-y-4">
                  <FormField
                    control={companyForm.control}
                    name="ico"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>IČO *</FormLabel>
                        <FormControl>
                          <div className="relative">
                            <Input
                              placeholder="Začnite písať IČO alebo názov firmy..."
                              value={icoSearch}
                              onChange={(e) => {
                                setIcoSearch(e.target.value);
                                field.onChange(e.target.value);
                                setShowSuggestions(true);
                              }}
                              onFocus={() => {
                                if (icoSearch.length > 0) {
                                  setShowSuggestions(true);
                                }
                              }}
                              className="pr-10"
                              autoComplete="off"
                              inputMode="numeric"
                              pattern="[0-9]*"
                              data-form-type="other"
                              data-lpignore="true"
                              data-1p-ignore="true"
                            />
                            {isSearching ? (
                              <Loader2 className="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 animate-spin text-primary" />
                            ) : (
                              <Search className="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                            )}

                            {showSuggestions && icoSearch.length > 0 && (
                              <div className="absolute z-50 w-full mt-1 bg-popover border border-border rounded-md shadow-lg">
                                <Command>
                                  <CommandList>
                                    {isSearching ? (
                                      <div className="py-6 text-center text-sm flex items-center justify-center gap-2">
                                        <Loader2 className="h-4 w-4 animate-spin text-primary" />
                                        <span>Vyhľadávam...</span>
                                      </div>
                                    ) : filteredCompanies.length === 0 ? (
                                      <CommandEmpty className="py-6 text-center text-sm">
                                        Žiadne výsledky
                                      </CommandEmpty>
                                    ) : (
                                      <CommandGroup>
                                        {filteredCompanies.map((company) => (
                                          <CommandItem
                                            key={company.ico}
                                            onSelect={() => onCompanySelect(company)}
                                            className="cursor-pointer"
                                          >
                                            <div className="flex flex-col gap-1">
                                              <div className="font-semibold">{company.name}</div>
                                              <div className="text-sm">
                                                IČO: {company.ico} | {company.address}, {company.postal_code} {company.city}
                                              </div>
                                            </div>
                                          </CommandItem>
                                        ))}
                                      </CommandGroup>
                                    )}
                                  </CommandList>
                                </Command>
                              </div>
                            )}
                          </div>
                        </FormControl>
                        <FormMessage />
                      </FormItem>
                    )}
                  />

                  <FormField
                    control={companyForm.control}
                    name="name"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Názov firmy *</FormLabel>
                        <FormControl>
                          <Input placeholder="ABC s.r.o." {...field} autoComplete="organization" />
                        </FormControl>
                        <FormMessage />
                      </FormItem>
                    )}
                  />

                  <FormField
                    control={companyForm.control}
                    name="street"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Miesto podnikania / Sídlo firmy *</FormLabel>
                        <FormControl>
                          <Input placeholder="Hlavná 123" {...field} autoComplete="street-address" />
                        </FormControl>
                        <FormMessage />
                      </FormItem>
                    )}
                  />

                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <FormField
                      control={companyForm.control}
                      name="city"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Mesto *</FormLabel>
                          <FormControl>
                            <Input placeholder="Bratislava" {...field} autoComplete="address-level2" />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />

                    <FormField
                      control={companyForm.control}
                      name="postal_code"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>PSČ *</FormLabel>
                          <FormControl>
                            <Input placeholder="811 01" {...field} autoComplete="postal-code" />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />
                  </div>

                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <FormField
                      control={companyForm.control}
                      name="dic"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>DIČ</FormLabel>
                          <FormControl>
                            <Input
                              placeholder="2023456789"
                              {...field}
                              autoComplete="off"
                              inputMode="numeric"
                              pattern="[0-9]*"
                            />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />

                    <FormField
                      control={companyForm.control}
                      name="ic_dph"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>IČ DPH</FormLabel>
                          <FormControl>
                            <Input placeholder="SK2023456789" {...field} autoComplete="off" />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />
                  </div>

                  <FormField
                    control={companyForm.control}
                    name="vat_payer_status"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Status platcu DPH</FormLabel>
                        <Select onValueChange={field.onChange} value={field.value}>
                          <FormControl>
                            <SelectTrigger>
                              <SelectValue placeholder="Vyberte status platcu DPH" />
                            </SelectTrigger>
                          </FormControl>
                          <SelectContent>
                            {VAT_PAYER_STATUS_OPTIONS.map((option) => (
                              <SelectItem key={option.value} value={option.value}>
                                {option.label}
                              </SelectItem>
                            ))}
                          </SelectContent>
                        </Select>
                        <FormMessage />
                      </FormItem>
                    )}
                  />

                  {/* Bank Details Section */}
                  <div className="space-y-4 p-4 bg-muted/30 rounded-lg border border-border/50">
                    <div className="space-y-1">
                      <h3 className="text-sm font-medium">Bankove udaje</h3>
                      <p className="text-xs text-muted-foreground">
                        Pre generovanie QR kodu na fakturach vyplnte bankove udaje
                      </p>
                    </div>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <FormField
                        control={companyForm.control}
                        name="iban"
                        render={({ field }) => (
                          <FormItem>
                            <FormLabel>IBAN</FormLabel>
                            <FormControl>
                              <Input
                                placeholder="SK31 1200 0000 1987 4263 7541"
                                {...field}
                                autoComplete="off"
                                maxLength={34}
                              />
                            </FormControl>
                            <FormMessage />
                          </FormItem>
                        )}
                      />

                      <FormField
                        control={companyForm.control}
                        name="swift"
                        render={({ field }) => (
                          <FormItem>
                            <FormLabel>SWIFT/BIC</FormLabel>
                            <FormControl>
                              <Input
                                placeholder="GIBASKBX"
                                {...field}
                                autoComplete="off"
                                maxLength={11}
                              />
                            </FormControl>
                            <FormMessage />
                          </FormItem>
                        )}
                      />
                    </div>
                  </div>

                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <FormField
                      control={companyForm.control}
                      name="registration_office"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Registrový úrad</FormLabel>
                          <FormControl>
                            <Input
                              placeholder="Napr. Okresný súd Bratislava I"
                              {...field}
                              autoComplete="off"
                              maxLength={255}
                            />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />

                    <FormField
                      control={companyForm.control}
                      name="registration_number"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Registračné číslo</FormLabel>
                          <FormControl>
                            <Input
                              placeholder="Napr. Oddiel: Sro, Vložka č. 123456/B"
                              {...field}
                              autoComplete="off"
                              maxLength={255}
                            />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />
                  </div>
                </div>

                <Button
                  type="submit"
                  className="w-full"
                  size="lg"
                  disabled={isSubmitting}
                >
                  {isSubmitting ? "Vytváram firmu..." : "Dokončiť nastavenie"}
                </Button>
              </form>
            </Form>
          </CardContent>
        </Card>
      </div>
    </div>
  );
};

export default Onboarding;
