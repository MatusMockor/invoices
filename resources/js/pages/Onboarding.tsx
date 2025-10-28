import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import * as z from "zod";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from "@/components/ui/form";
import { Search, Building2 } from "lucide-react";
import { toast } from "sonner";
import { onboardingService } from "@/services/onboardingService";
import { businessEntityService } from "@/services/businessEntityService";

const companySchema = z.object({
  ico: z.string().trim().min(1, "IČO je povinné").max(20),
  name: z.string().trim().min(1, "Názov firmy je povinný").max(200),
  street: z.string().trim().min(1, "Miesto podnikania / Sídlo firmy je povinné").max(200),
  city: z.string().trim().min(1, "Mesto je povinné").max(100),
  postal_code: z.string().trim().min(1, "PSČ je povinné").max(10),
  dic: z.string().trim().min(1, "DIČ je povinné").max(20),
  ic_dph: z.string().trim().max(20).optional(),
});

type CompanyFormData = z.infer<typeof companySchema>;

const Onboarding = () => {
  const [icoSearch, setIcoSearch] = useState("");
  const [isSearching, setIsSearching] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const navigate = useNavigate();

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

        companyForm.setValue("ico", data.ico || ico);
        companyForm.setValue("name", data.name || "");
        companyForm.setValue("street", data.street || "");
        companyForm.setValue("city", data.city || "");
        companyForm.setValue("postal_code", data.postal_code || "");
        companyForm.setValue("dic", data.dic || "");
        companyForm.setValue("ic_dph", data.ic_dph || "");

        toast.success("Údaje o firme boli načítané");
      }
    } catch (error: any) {
      toast.error(error.response?.data?.message || "Nepodarilo sa načítať údaje o firme");
    } finally {
      setIsSearching(false);
    }
  };

  const onSubmit = async (data: CompanyFormData) => {
    setIsSubmitting(true);

    try {
      await onboardingService.createCompany(data);

      toast.success("Firma bola úspešne vytvorená!");
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
                    name="name"
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
                    name="street"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Miesto podnikania / Sídlo firmy *</FormLabel>
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
                      name="city"
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
                      name="postal_code"
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
                      name="dic"
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
                      name="ic_dph"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>IČ DPH</FormLabel>
                          <FormControl>
                            <Input placeholder="SK2023456789" {...field} />
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
