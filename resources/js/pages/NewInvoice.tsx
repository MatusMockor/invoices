import { useNavigate, useParams } from "react-router-dom";
import { useForm, useFieldArray } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import * as z from "zod";
import { format } from "date-fns";
import { CalendarIcon, Plus, Trash2, Save, ArrowLeft, Loader2 } from "lucide-react";
import { DashboardLayout } from "@/components/dashboard/DashboardLayout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Calendar } from "@/components/ui/calendar";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { Command, CommandEmpty, CommandGroup, CommandItem, CommandList } from "@/components/ui/command";
import { cn } from "@/lib/utils";
import { useToast } from "@/hooks/use-toast";
import { useState, useEffect } from "react";
import { useInvoice, useInvoices } from "@/hooks/useInvoices";
import { companyService } from "@/services/companyService";

const invoiceSchema = z.object({
  invoiceNumber: z.string().trim().min(1, "Číslo faktúry je povinné"),
  clientName: z.string().trim().max(100).optional(),
  clientStreet: z.string().trim().max(255).optional(),
  clientCity: z.string().trim().max(100).optional(),
  clientPostalCode: z.string().trim().max(20).optional(),
  clientIco: z.string().trim().max(20).optional(),
  clientDic: z.string().trim().max(20).optional(),
  clientIcDph: z.string().trim().max(20).optional(),
  useCustomCompany: z.boolean().optional().default(false),
  customCompanyIco: z.string().trim().max(12).optional(),
  customCompanyDic: z.string().trim().max(20).optional(),
  customCompanyIcDph: z.string().trim().max(20).optional(),
  customCompanyName: z.string().trim().max(255).optional(),
  customCompanyAddress: z.string().trim().max(500).optional(),
  customCompanyCity: z.string().trim().max(100).optional(),
  customCompanyZip: z.string().trim().max(20).optional(),
  customCompanyCountry: z.string().trim().max(100).optional(),
  issueDate: z.date({ required_error: "Dátum vystavenia je povinný" }),
  dueDate: z.date({ required_error: "Dátum splatnosti je povinný" }),
  deliveryDate: z.date({ required_error: "Dátum dodania je povinný" }),
  variableSymbol: z.string().trim().min(1, "Variabilný symbol je povinný").max(20),
  constantSymbol: z.string().trim().max(20).optional(),
  specificSymbol: z.string().trim().max(20).optional(),
  items: z.array(
    z.object({
      description: z.string().trim().min(1, "Popis je povinný").max(200),
      quantity: z.number().min(1, "Množstvo musí byť aspoň 1"),
      price: z.number().min(0, "Cena musí byť nezáporná"),
    })
  ).min(1, "Aspoň jedna položka je povinná"),
}).refine((data) => {
  // Conditional validation based on useCustomCompany
  if (data.useCustomCompany) {
    // Custom company mode - require custom fields
    return !!(data.customCompanyIco && data.customCompanyName);
  } else {
    // Standard company mode - require client fields
    return !!(data.clientIco && data.clientName && data.clientStreet &&
              data.clientCity && data.clientPostalCode && data.clientDic);
  }
}, {
  message: "Vyplňte všetky povinné polia",
  path: ["clientName"], // Error will be shown on the form
});

type InvoiceFormData = z.infer<typeof invoiceSchema>;

const NewInvoice = () => {
  const navigate = useNavigate();
  const { id } = useParams();
  const { toast } = useToast();
  const [issueDate, setIssueDate] = useState<Date>(new Date());
  const [deliveryDate, setDeliveryDate] = useState<Date>(new Date());
  const [dueDateDays, setDueDateDays] = useState(15);
  const [dueDate, setDueDate] = useState<Date>(() => {
    const date = new Date();
    date.setDate(date.getDate() + dueDateDays);
    return date;
  });
  const [icoSearch, setIcoSearch] = useState("");
  const [isSearching, setIsSearching] = useState(false);
  const [showSuggestions, setShowSuggestions] = useState(false);
  const [filteredCompanies, setFilteredCompanies] = useState<any[]>([]);

  const isEditMode = !!id;
  const { invoice, isLoading: isLoadingInvoice } = useInvoice(id ? Number(id) : 0);
  const { createInvoice, updateInvoice, isCreating, isUpdating } = useInvoices();

  // Generovanie čísla faktúry vo formáte RRRRCCCC
  const generateInvoiceNumber = () => {
    const currentYear = new Date().getFullYear();
    // TODO: V reálnej aplikácii by sme zistili počet faktúr za daný rok z databázy
    const invoiceCount = 1;
    const invoiceNumberPadded = String(invoiceCount).padStart(4, '0');
    return `${currentYear}${invoiceNumberPadded}`;
  };

  const generatedInvoiceNumber = generateInvoiceNumber();

  const {
    register,
    control,
    handleSubmit,
    formState: { errors },
    setValue,
    watch,
    reset,
  } = useForm<InvoiceFormData>({
    resolver: zodResolver(invoiceSchema),
    defaultValues: {
      invoiceNumber: isEditMode ? "" : generatedInvoiceNumber,
      items: [{ description: "", quantity: 1, price: 0 }],
      variableSymbol: isEditMode ? "" : generatedInvoiceNumber,
      constantSymbol: "",
      specificSymbol: "",
      issueDate: new Date(),
      deliveryDate: new Date(),
      dueDate: new Date(new Date().setDate(new Date().getDate() + 15)),
      useCustomCompany: false,
    },
  });

  const { fields, append, remove } = useFieldArray({
    control,
    name: "items",
  });

  useEffect(() => {
    if (issueDate) {
      const newDueDate = new Date(issueDate);
      newDueDate.setDate(newDueDate.getDate() + dueDateDays);
      setDueDate(newDueDate);
      setValue("dueDate", newDueDate);
    }
  }, [issueDate, dueDateDays, setValue]);

  // Sync invoice number with variable symbol
  const variableSymbol = watch("variableSymbol");
  useEffect(() => {
    if (variableSymbol) {
      setValue("invoiceNumber", variableSymbol);
    }
  }, [variableSymbol, setValue]);

  const items = watch("items");
  const useCustomCompany = watch("useCustomCompany");

  // Load invoice data in edit mode
  useEffect(() => {
    if (isEditMode && invoice) {
      // Check if invoice has custom company data (company_ico present but no company_id)
      if (invoice.company_ico && !invoice.company_id) {
        // Load custom company data
        setValue("useCustomCompany", true);
        setValue("customCompanyIco", invoice.company_ico);
        setValue("customCompanyDic", invoice.company_dic || "");
        setValue("customCompanyIcDph", invoice.company_ic_dph || "");
        setValue("customCompanyName", invoice.company_name || "");
        setValue("customCompanyAddress", invoice.company_address || "");
        setValue("customCompanyCity", invoice.company_city || "");
        setValue("customCompanyZip", invoice.company_zip || "");
        setValue("customCompanyCountry", invoice.company_country || "");
      } else if (invoice.business_entity) {
        // Load from business_entity (existing logic)
        setValue("useCustomCompany", false);
        setValue("clientName", invoice.business_entity.name);
        setValue("clientStreet", invoice.business_entity.address || "");
        setValue("clientCity", invoice.business_entity.city || "");
        setValue("clientPostalCode", invoice.business_entity.postal_code || "");
        setValue("clientIco", invoice.business_entity.ico);
        setValue("clientDic", invoice.business_entity.dic || "");
        setValue("clientIcDph", invoice.business_entity.ic_dph || "");
        setIcoSearch(invoice.business_entity.ico);
      }

      // Set invoice number and variable symbol
      setValue("invoiceNumber", invoice.invoice_number);
      setValue("variableSymbol", invoice.variable_symbol || invoice.invoice_number);
      setValue("constantSymbol", invoice.constant_symbol || "");
      setValue("specificSymbol", invoice.specific_symbol || "");

      // Set dates
      const issueDateObj = new Date(invoice.issue_date);
      const dueDateObj = new Date(invoice.due_date);
      const deliveryDateObj = new Date(invoice.delivery_date);
      setIssueDate(issueDateObj);
      setDueDate(dueDateObj);
      setDeliveryDate(deliveryDateObj);
      setValue("issueDate", issueDateObj);
      setValue("dueDate", dueDateObj);
      setValue("deliveryDate", deliveryDateObj);

      // Set items
      if (invoice.items && invoice.items.length > 0) {
        const formattedItems = invoice.items.map(item => ({
          description: item.description,
          quantity: Number(item.quantity),
          price: Number(item.unit_price),
        }));
        setValue("items", formattedItems);
      }
    }
  }, [isEditMode, invoice, setValue]);

  // Debounce search for companies with IČO/name autocomplete
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

      return () => clearTimeout(timer);
    } else if (icoSearch.length === 0) {
      setFilteredCompanies([]);
      setIsSearching(false);
    }
  }, [icoSearch, showSuggestions]);

  const onSubmit = async (data: InvoiceFormData) => {
    try {
      // Transform form data to API format
      const apiData = {
        clientName: data.clientName,
        clientIco: data.clientIco,
        clientDic: data.clientDic,
        clientIcDph: data.clientIcDph,
        clientStreet: data.clientStreet,
        clientCity: data.clientCity,
        clientPostalCode: data.clientPostalCode,
        clientCountry: 'SK',
        useCustomCompany: data.useCustomCompany || false,
        customCompanyIco: data.customCompanyIco,
        customCompanyDic: data.customCompanyDic,
        customCompanyIcDph: data.customCompanyIcDph,
        customCompanyName: data.customCompanyName,
        customCompanyAddress: data.customCompanyAddress,
        customCompanyCity: data.customCompanyCity,
        customCompanyZip: data.customCompanyZip,
        customCompanyCountry: data.customCompanyCountry,
        invoiceNumber: data.invoiceNumber,
        issue_date: data.issueDate.toISOString().split('T')[0], // Format: YYYY-MM-DD
        due_date: data.dueDate.toISOString().split('T')[0], // Format: YYYY-MM-DD
        delivery_date: data.deliveryDate.toISOString().split('T')[0], // Format: YYYY-MM-DD
        variableSymbol: data.variableSymbol,
        constantSymbol: data.constantSymbol,
        specificSymbol: data.specificSymbol,
        currency: 'EUR',
        items: data.items.map(item => ({
          description: item.description,
          quantity: item.quantity,
          price: item.price,
        })),
      };

      if (isEditMode && id) {
        await updateInvoice({ id: Number(id), data: apiData });
        toast({
          title: "Faktúra upravená",
          description: "Faktúra bola úspešne upravená.",
        });
      } else {
        await createInvoice(apiData);
        toast({
          title: "Faktúra vytvorená",
          description: "Faktúra bola úspešne vytvorená.",
        });
      }

      navigate("/app/invoices");
    } catch (error: any) {
      console.error('Error submitting invoice:', error);
      toast({
        title: "Chyba",
        description: error.response?.data?.message || "Nepodarilo sa uložiť faktúru",
        variant: "destructive",
      });
    }
  };

  const calculateTotal = () => {
    return items.reduce((total, item) => {
      const quantity = item.quantity || 0;
      const price = item.price || 0;
      return total + (quantity * price);
    }, 0);
  };

  const handleCompanySelect = (company: any) => {
    setValue("clientIco", company.ico);
    setValue("clientName", company.name);
    setValue("clientStreet", company.address || "");
    setValue("clientCity", company.city || "");
    setValue("clientPostalCode", company.postal_code || "");
    setValue("clientDic", company.dic || "");
    setValue("clientIcDph", company.ic_dph || "");
    setIcoSearch(company.ico);
    setShowSuggestions(false);
    toast({
      title: "Údaje predvyplnené",
      description: `Údaje spoločnosti ${company.name} boli načítané.`,
    });
  };

  // Show loading state when fetching invoice data in edit mode
  if (isEditMode && isLoadingInvoice) {
    return (
      <DashboardLayout>
        <div className="max-w-6xl mx-auto animate-fade-in">
          <div className="flex items-center justify-center py-12">
            <Loader2 className="h-8 w-8 animate-spin" />
          </div>
        </div>
      </DashboardLayout>
    );
  }

  return (
    <DashboardLayout>
      <div className="max-w-6xl mx-auto animate-fade-in">
        <div className="mb-6">
          <Button
            variant="ghost"
            onClick={() => navigate("/app/dashboard")}
            className="mb-4"
          >
            <ArrowLeft className="h-4 w-4 mr-2" />
            Späť na dashboard
          </Button>
          <h1 className="text-3xl font-bold text-foreground">{isEditMode ? "Upraviť faktúru" : "Nová faktúra"}</h1>
          <p className="text-muted-foreground mt-1">{isEditMode ? "Upravte existujúcu faktúru" : "Vytvorte novú faktúru pre vášho klienta"}</p>
        </div>

        <form onSubmit={handleSubmit(onSubmit)} className="space-y-8">
          {/* Client Information */}
          <div className="bg-gradient-card rounded-xl p-6 border-2 border-primary/30 shadow-elegant-sm">
            <h3 className="text-lg font-bold text-primary mb-4 flex items-center gap-2">
              <span className="w-8 h-8 bg-primary text-primary-foreground rounded-full flex items-center justify-center text-sm">1</span>
              Informácie o klientovi
            </h3>

            {/* Info badge in edit mode */}
            {isEditMode && (
              <div className="mb-4 p-3 bg-muted/50 rounded-lg border border-muted-foreground/20">
                <p className="text-sm text-muted-foreground">
                  ℹ️ Údaje o klientovi sú uložené z času vytvorenia faktúry a nie je možné ich upraviť.
                </p>
              </div>
            )}

            {/* Toggle between standard and custom company */}
            <div className="flex items-center space-x-2 mb-6 p-4 bg-card/50 rounded-lg border border-primary/20">
              <input
                type="checkbox"
                id="useCustomCompany"
                {...register("useCustomCompany")}
                disabled={isEditMode}
                className="h-4 w-4 rounded border-primary/30 text-primary focus:ring-primary disabled:opacity-50 disabled:cursor-not-allowed"
              />
              <Label htmlFor="useCustomCompany" className={cn("text-sm", isEditMode ? "cursor-not-allowed opacity-50" : "cursor-pointer")}>
                Zadať vlastné údaje o spoločnosti (neregistrovaná v databáze)
              </Label>
            </div>

            <div className="space-y-4">
              {!useCustomCompany ? (
                <>
                  {/* Standard company fields with autocomplete */}
                  {/* IČO s autocomplete */}
                  <div className="space-y-2">
                <Label htmlFor="clientIco">IČO klienta *</Label>
                <div className="relative">
                  <div className="relative">
                    <Input
                      id="clientIco"
                      value={icoSearch}
                      onChange={(e) => {
                        setIcoSearch(e.target.value);
                        setValue("clientIco", e.target.value);
                        setShowSuggestions(true);
                      }}
                      onFocus={() => {
                        if (icoSearch.length > 0) {
                          setShowSuggestions(true);
                        }
                      }}
                      placeholder="Začnite písať IČO alebo názov firmy..."
                      className="border-primary/30"
                      disabled={isEditMode}
                    />
                    {isSearching && (
                      <Loader2 className="absolute right-3 top-3 h-4 w-4 animate-spin text-primary" />
                    )}
                  </div>

                  {/* Dropdown s návrhmi */}
                  {showSuggestions && icoSearch.length > 0 && (
                    <div className="absolute z-50 w-full mt-1 bg-popover border border-border rounded-md shadow-lg">
                      <Command>
                        <CommandList>
                          {filteredCompanies.length === 0 ? (
                            <CommandEmpty className="py-6 text-center text-sm">
                              Žiadne výsledky
                            </CommandEmpty>
                          ) : (
                            <CommandGroup>
                              {filteredCompanies.map((company) => (
                                <CommandItem
                                  key={company.ico}
                                  onSelect={() => handleCompanySelect(company)}
                                  className="cursor-pointer"
                                >
                                  <div className="flex flex-col gap-1">
                                    <div className="font-semibold">{company.name}</div>
                                    <div className="text-sm text-muted-foreground">
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
                {errors.clientIco && (
                  <p className="text-sm text-destructive">{errors.clientIco.message}</p>
                )}
              </div>

              {/* Ostatné polia */}
              <div className="space-y-2">
                <Label htmlFor="clientName">Názov / Meno klienta *</Label>
                <Input
                  id="clientName"
                  {...register("clientName")}
                  placeholder="ABC s.r.o."
                  className="border-primary/30"
                  disabled={isEditMode}
                />
                {errors.clientName && (
                  <p className="text-sm text-destructive">{errors.clientName.message}</p>
                )}
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label htmlFor="clientDic">DIČ *</Label>
                  <Input
                    id="clientDic"
                    {...register("clientDic")}
                    placeholder="2023456789"
                    className="border-primary/30"
                    disabled={isEditMode}
                  />
                  {errors.clientDic && (
                    <p className="text-sm text-destructive">{errors.clientDic.message}</p>
                  )}
                </div>
                <div className="space-y-2">
                  <Label htmlFor="clientIcDph">IČ DPH</Label>
                  <Input
                    id="clientIcDph"
                    {...register("clientIcDph")}
                    placeholder="SK2023456789"
                    className="border-primary/30"
                    disabled={isEditMode}
                  />
                  {errors.clientIcDph && (
                    <p className="text-sm text-destructive">{errors.clientIcDph.message}</p>
                  )}
                </div>
              </div>

              <div className="space-y-2">
                <Label htmlFor="clientStreet">Ulica a číslo *</Label>
                <Input
                  id="clientStreet"
                  {...register("clientStreet")}
                  placeholder="Hlavná 123"
                  className="border-primary/30"
                  disabled={isEditMode}
                />
                {errors.clientStreet && (
                  <p className="text-sm text-destructive">{errors.clientStreet.message}</p>
                )}
              </div>

              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div className="space-y-2">
                  <Label htmlFor="clientPostalCode">PSČ *</Label>
                  <Input
                    id="clientPostalCode"
                    {...register("clientPostalCode")}
                    placeholder="811 01"
                    className="border-primary/30"
                    disabled={isEditMode}
                  />
                  {errors.clientPostalCode && (
                    <p className="text-sm text-destructive">{errors.clientPostalCode.message}</p>
                  )}
                </div>
                <div className="md:col-span-2 space-y-2">
                  <Label htmlFor="clientCity">Mesto *</Label>
                  <Input
                    id="clientCity"
                    {...register("clientCity")}
                    placeholder="Bratislava"
                    className="border-primary/30"
                    disabled={isEditMode}
                  />
                  {errors.clientCity && (
                    <p className="text-sm text-destructive">{errors.clientCity.message}</p>
                  )}
                </div>
              </div>
                </>
              ) : (
                <>
                  {/* Custom company fields */}
                  <div className="space-y-2">
                    <Label htmlFor="customCompanyIco">IČO spoločnosti *</Label>
                    <Input
                      id="customCompanyIco"
                      {...register("customCompanyIco")}
                      placeholder="12345678"
                      className="border-primary/30"
                      disabled={isEditMode}
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
                      disabled={isEditMode}
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
                      disabled={isEditMode}
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
                      disabled={isEditMode}
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
                      disabled={isEditMode}
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
                        disabled={isEditMode}
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
                        disabled={isEditMode}
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
                        disabled={isEditMode}
                      />
                      {errors.customCompanyCountry && (
                        <p className="text-sm text-destructive">{errors.customCompanyCountry.message}</p>
                      )}
                    </div>
                  </div>
                </>
              )}
            </div>
          </div>

          {/* Dates */}
          <div className="bg-gradient-card rounded-xl p-6 border-2 border-border shadow-elegant-sm">
            <h3 className="text-lg font-bold text-foreground mb-4 flex items-center gap-2">
              <span className="w-8 h-8 bg-accent text-accent-foreground rounded-full flex items-center justify-center text-sm">2</span>
              Dátumy
            </h3>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div className="space-y-2">
                <Label>Dátum vystavenia *</Label>
                <Popover>
                  <PopoverTrigger asChild>
                    <Button
                      type="button"
                      variant="outline"
                      className={cn(
                        "w-full justify-start text-left font-normal",
                        !issueDate && "text-muted-foreground"
                      )}
                    >
                      <CalendarIcon className="mr-2 h-4 w-4" />
                      {issueDate ? format(issueDate, "dd.MM.yyyy") : "Vyberte dátum"}
                    </Button>
                  </PopoverTrigger>
                  <PopoverContent className="w-auto p-0" align="start">
                    <Calendar
                      mode="single"
                      selected={issueDate}
                      onSelect={(date) => {
                        setIssueDate(date);
                        setValue("issueDate", date as Date);
                      }}
                      initialFocus
                      className="pointer-events-auto"
                    />
                  </PopoverContent>
                </Popover>
                {errors.issueDate && (
                  <p className="text-sm text-destructive">{errors.issueDate.message}</p>
                )}
              </div>
              <div className="space-y-2">
                <Label>Dátum dodania *</Label>
                <Popover>
                  <PopoverTrigger asChild>
                    <Button
                      type="button"
                      variant="outline"
                      className={cn(
                        "w-full justify-start text-left font-normal",
                        !deliveryDate && "text-muted-foreground"
                      )}
                    >
                      <CalendarIcon className="mr-2 h-4 w-4" />
                      {deliveryDate ? format(deliveryDate, "dd.MM.yyyy") : "Vyberte dátum"}
                    </Button>
                  </PopoverTrigger>
                  <PopoverContent className="w-auto p-0" align="start">
                    <Calendar
                      mode="single"
                      selected={deliveryDate}
                      onSelect={(date) => {
                        setDeliveryDate(date);
                        setValue("deliveryDate", date as Date);
                      }}
                      initialFocus
                      className="pointer-events-auto"
                    />
                  </PopoverContent>
                </Popover>
                {errors.deliveryDate && (
                  <p className="text-sm text-destructive">{errors.deliveryDate.message}</p>
                )}
              </div>
              <div className="space-y-2 md:col-span-1">
                <Label>Dátum splatnosti *</Label>
                <div className="grid grid-cols-3 gap-2">
                  <div className="col-span-1">
                    <Input
                      type="number"
                      value={dueDateDays}
                      onChange={(e) => setDueDateDays(Number(e.target.value))}
                      className="border-primary/30 text-center"
                    />
                  </div>
                  <div className="col-span-2">
                    <Popover>
                      <PopoverTrigger asChild>
                        <Button
                          type="button"
                          variant="outline"
                          className={cn(
                            "w-full justify-start text-left font-normal",
                            !dueDate && "text-muted-foreground"
                          )}
                        >
                          <CalendarIcon className="mr-2 h-4 w-4" />
                          {dueDate ? format(dueDate, "dd.MM.yyyy") : "Vyberte dátum"}
                        </Button>
                      </PopoverTrigger>
                      <PopoverContent className="w-auto p-0" align="start">
                        <Calendar
                          mode="single"
                          selected={dueDate}
                          onSelect={(date) => {
                            if (date && issueDate) {
                              const diffTime = Math.abs(date.getTime() - issueDate.getTime());
                              const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                              setDueDateDays(diffDays);
                            }
                            setDueDate(date as Date);
                            setValue("dueDate", date as Date);
                          }}
                          initialFocus
                          className="pointer-events-auto"
                        />
                      </PopoverContent>
                    </Popover>
                  </div>
                </div>
                {errors.dueDate && (
                  <p className="text-sm text-destructive">{errors.dueDate.message}</p>
                )}
              </div>
            </div>
          </div>

          {/* Payment Symbols */}
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

          {/* Items */}
          <div className="bg-gradient-card rounded-xl p-6 border-2 border-primary/30 shadow-elegant-sm">
            <h3 className="text-lg font-bold text-primary mb-4 flex items-center gap-2">
              <span className="w-8 h-8 bg-primary text-primary-foreground rounded-full flex items-center justify-center text-sm">4</span>
              Položky faktúry
            </h3>
            <div className="space-y-4">
              {fields.map((field, index) => (
                <div
                  key={field.id}
                  className="grid grid-cols-12 gap-3 p-4 bg-card rounded-lg border border-primary/20"
                >
                  <div className="col-span-5 space-y-2">
                    <Label htmlFor={`description-${index}`}>Popis</Label>
                    <Input
                      id={`description-${index}`}
                      {...register(`items.${index}.description`)}
                      placeholder="Webový dizajn"
                      className="border-primary/30"
                    />
                    {errors.items?.[index]?.description && (
                      <p className="text-sm text-destructive">
                        {errors.items[index]?.description?.message}
                      </p>
                    )}
                  </div>
                  <div className="col-span-2 space-y-2">
                    <Label htmlFor={`quantity-${index}`}>Počet</Label>
                    <Input
                      id={`quantity-${index}`}
                      type="number"
                      {...register(`items.${index}.quantity`, {
                        valueAsNumber: true,
                      })}
                      placeholder="1"
                      min="1"
                      className="border-primary/30"
                    />
                    {errors.items?.[index]?.quantity && (
                      <p className="text-sm text-destructive">
                        {errors.items[index]?.quantity?.message}
                      </p>
                    )}
                  </div>
                  <div className="col-span-3 space-y-2">
                    <Label htmlFor={`price-${index}`}>Cena/ks (€)</Label>
                    <Input
                      id={`price-${index}`}
                      type="number"
                      {...register(`items.${index}.price`, {
                        valueAsNumber: true,
                      })}
                      placeholder="0.00"
                      min="0"
                      step="0.01"
                      className="border-primary/30"
                    />
                    {errors.items?.[index]?.price && (
                      <p className="text-sm text-destructive">
                        {errors.items[index]?.price?.message}
                      </p>
                    )}
                  </div>
                  <div className="col-span-2 flex items-end">
                    {fields.length > 1 && (
                      <Button
                        type="button"
                        variant="outline"
                        size="icon"
                        onClick={() => remove(index)}
                        className="border-destructive/30 text-destructive hover:bg-destructive/10"
                      >
                        <Trash2 className="h-4 w-4" />
                      </Button>
                    )}
                  </div>
                </div>
              ))}
              <Button
                type="button"
                variant="outline"
                onClick={() => append({ description: "", quantity: 1, price: 0 })}
                className="w-full border-primary/30 text-primary hover:bg-primary/10"
              >
                <Plus className="h-4 w-4 mr-2" />
                Pridať položku
              </Button>
              {errors.items && (
                <p className="text-sm text-destructive">{errors.items.message}</p>
              )}
            </div>
          </div>

          {/* Actions */}
          <div className="flex justify-between items-center pt-6 pb-8 border-t border-border bg-card rounded-xl p-6 shadow-elegant-sm">
            <div className="text-lg font-semibold text-foreground">
              Celkom: <span className="text-2xl text-primary ml-2">€{calculateTotal().toFixed(2)}</span>
            </div>
            <div className="flex gap-3">
              <Button
                type="button"
                variant="outline"
                onClick={() => navigate("/app/dashboard")}
              >
                Zrušiť
              </Button>
              <Button type="submit" className="bg-primary hover:bg-primary/90">
                <Save className="h-4 w-4 mr-2" />
                {isEditMode ? "Uložiť zmeny" : "Vytvoriť faktúru"}
              </Button>
            </div>
          </div>
        </form>
      </div>
    </DashboardLayout>
  );
};

export default NewInvoice;
