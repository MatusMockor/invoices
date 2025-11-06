import { useNavigate, useParams } from "react-router-dom";
import { useForm, useFieldArray } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import * as z from "zod";
import { Save, ArrowLeft, Loader2 } from "lucide-react";
import { DashboardLayout } from "@/components/dashboard/DashboardLayout";
import { Button } from "@/components/ui/button";
import { useToast } from "@/hooks/use-toast";
import { useState, useEffect, useCallback } from "react";
import { useInvoice, useInvoices } from "@/hooks/useInvoices";
import { companyService } from "@/services/companyService";
import { ClientInformationSection } from "@/components/invoices/ClientInformationSection";
import { CustomCompanySection } from "@/components/invoices/CustomCompanySection";
import { InvoiceDateSection } from "@/components/invoices/InvoiceDateSection";
import { PaymentSymbolsSection } from "@/components/invoices/PaymentSymbolsSection";
import { InvoiceItemsSection } from "@/components/invoices/InvoiceItemsSection";
import type { InvoiceFormData } from "@/components/invoices/ClientInformationSection";
import { Company } from "@/types/company";

const invoiceSchema = z.object({
  invoiceNumber: z.string().trim().min(1, "Číslo faktúry je povinné"),
  clientName: z.string().trim().max(100).optional(),
  clientStreet: z.string().trim().max(255).optional(),
  clientCity: z.string().trim().max(100).optional(),
  clientPostalCode: z.string().trim().max(20).optional(),
  clientIco: z.string().trim().regex(/^\d*$/, "IČO musí obsahovať len číslice").max(20).optional(),
  clientDic: z.string().trim().regex(/^\d*$/, "DIČ musí obsahovať len číslice").max(20).optional(),
  clientIcDph: z.string().trim().max(20).optional(),
  useCustomCompany: z.boolean().optional().default(false),
  customCompanyIco: z.string().trim().regex(/^\d*$/, "IČO musí obsahovať len číslice").max(12).optional(),
  customCompanyDic: z.string().trim().regex(/^\d*$/, "DIČ musí obsahovať len číslice").max(20).optional(),
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
}).superRefine((data, ctx) => {
  // Conditional validation based on useCustomCompany
  if (data.useCustomCompany) {
    // Custom company mode - require custom fields
    if (!data.customCompanyIco || data.customCompanyIco.trim() === '') {
      ctx.addIssue({
        code: z.ZodIssueCode.custom,
        message: "IČO je povinné",
        path: ["customCompanyIco"],
      });
    }
    if (!data.customCompanyName || data.customCompanyName.trim() === '') {
      ctx.addIssue({
        code: z.ZodIssueCode.custom,
        message: "Názov spoločnosti je povinný",
        path: ["customCompanyName"],
      });
    }
    return;
  }

  // Standard company mode - require client fields
  if (!data.clientIco || data.clientIco.trim() === '') {
    ctx.addIssue({
      code: z.ZodIssueCode.custom,
      message: "IČO klienta je povinné",
      path: ["clientIco"],
    });
  }
  if (!data.clientName || data.clientName.trim() === '') {
    ctx.addIssue({
      code: z.ZodIssueCode.custom,
      message: "Názov klienta je povinný",
      path: ["clientName"],
    });
  }
  if (!data.clientStreet || data.clientStreet.trim() === '') {
    ctx.addIssue({
      code: z.ZodIssueCode.custom,
      message: "Ulica je povinná",
      path: ["clientStreet"],
    });
  }
  if (!data.clientCity || data.clientCity.trim() === '') {
    ctx.addIssue({
      code: z.ZodIssueCode.custom,
      message: "Mesto je povinné",
      path: ["clientCity"],
    });
  }
  if (!data.clientPostalCode || data.clientPostalCode.trim() === '') {
    ctx.addIssue({
      code: z.ZodIssueCode.custom,
      message: "PSČ je povinné",
      path: ["clientPostalCode"],
    });
  }
});

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
  const [filteredCompanies, setFilteredCompanies] = useState<Company[]>([]);
  const [selectedCompany, setSelectedCompany] = useState<Company | null>(null);
  const [searchError, setSearchError] = useState<string | null>(null);
  const [isSelectingCompany, setIsSelectingCompany] = useState(false);

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

  const form = useForm<InvoiceFormData>({
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

  const {
    register,
    control,
    handleSubmit,
    setValue,
    watch,
  } = form;

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
      // Always load from invoice snapshot data (not through relationship)
      // This preserves the historical data from when the invoice was created

      // Check if invoice has custom company data (no company_id means it was custom)
      if (invoice.company_ico && !invoice.company_id) {
        setValue("useCustomCompany", true);
        setValue("customCompanyIco", invoice.company_ico);
        setValue("customCompanyDic", invoice.company_dic || "");
        setValue("customCompanyIcDph", invoice.company_ic_dph || "");
        setValue("customCompanyName", invoice.company_name || "");
        setValue("customCompanyAddress", invoice.company_address || "");
        setValue("customCompanyCity", invoice.company_city || "");
        setValue("customCompanyZip", invoice.company_zip || "");
        setValue("customCompanyCountry", invoice.company_country || "");
      } else {
        // Load from invoice snapshot data (NOT from business_entity relationship)
        setValue("useCustomCompany", false);
        setValue("clientName", invoice.company_name || "");
        setValue("clientStreet", invoice.company_address || "");
        setValue("clientCity", invoice.company_city || "");
        setValue("clientPostalCode", invoice.company_zip || "");
        setValue("clientIco", invoice.company_ico || "");
        setValue("clientDic", invoice.company_dic || "");
        setValue("clientIcDph", invoice.company_ic_dph || "");
        setIcoSearch(invoice.company_ico || "");

        // Set selectedCompany to display company info card in edit mode
        setSelectedCompany({
          ico: invoice.company_ico || "",
          name: invoice.company_name || "",
          address: invoice.company_address,
          city: invoice.company_city,
          postal_code: invoice.company_zip,
          dic: invoice.company_dic,
          ic_dph: invoice.company_ic_dph,
        });

        // Also pre-fill custom company fields from invoice snapshot data for when user toggles checkbox
        setValue("customCompanyIco", invoice.company_ico || "");
        setValue("customCompanyDic", invoice.company_dic || "");
        setValue("customCompanyIcDph", invoice.company_ic_dph || "");
        setValue("customCompanyName", invoice.company_name || "");
        setValue("customCompanyAddress", invoice.company_address || "");
        setValue("customCompanyCity", invoice.company_city || "");
        setValue("customCompanyZip", invoice.company_zip || "");
        setValue("customCompanyCountry", invoice.company_country || "");
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
      setSearchError(null); // Clear previous errors

      // Real API call with debounce
      const timer = setTimeout(async () => {
        try {
          const response = await companyService.searchCustomerCompanies(icoSearch);
          setFilteredCompanies(response.data);
          setSearchError(null);
        } catch (error) {
          console.error('Error searching companies:', error);
          setFilteredCompanies([]);
          setSearchError('Nepodarilo sa načítať spoločnosti. Skúste to znova.');
          toast({
            title: "Chyba pri vyhľadávaní",
            description: "Nepodarilo sa načítať spoločnosti. Skúste to znova.",
            variant: "destructive",
          });
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
      setSearchError(null);
    }
  }, [icoSearch, showSuggestions, toast]);

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

  const handleCompanySelect = useCallback(async (company: Company) => {
    setIsSelectingCompany(true);

    // Smooth transition delay
    await new Promise(resolve => setTimeout(resolve, 150));

    setValue("clientIco", company.ico);
    setValue("clientName", company.name);
    setValue("clientStreet", company.address || "");
    setValue("clientCity", company.city || "");
    setValue("clientPostalCode", company.postal_code || "");
    setValue("clientDic", company.dic || "");
    setValue("clientIcDph", company.ic_dph || "");
    setIcoSearch(company.ico);
    setShowSuggestions(false);
    setSelectedCompany(company);

    setIsSelectingCompany(false);

    toast({
      title: "Údaje predvyplnené",
      description: `Údaje spoločnosti ${company.name} boli načítané.`,
    });
  }, [setValue, toast]);

  const handleClearSelection = useCallback(() => {
    setSelectedCompany(null);
    setIcoSearch("");
    setValue("clientIco", "");
    setValue("clientName", "");
    setValue("clientStreet", "");
    setValue("clientCity", "");
    setValue("clientPostalCode", "");
    setValue("clientDic", "");
    setValue("clientIcDph", "");
  }, [setValue]);

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
          {!useCustomCompany ? (
            <ClientInformationSection
              form={form}
              isEditMode={isEditMode}
              useCustomCompany={useCustomCompany}
              icoSearch={icoSearch}
              setIcoSearch={setIcoSearch}
              isSearching={isSearching}
              showSuggestions={showSuggestions}
              setShowSuggestions={setShowSuggestions}
              filteredCompanies={filteredCompanies}
              onCompanySelect={handleCompanySelect}
              selectedCompany={selectedCompany}
              onClearSelection={handleClearSelection}
              searchError={searchError}
              isSelectingCompany={isSelectingCompany}
            />
          ) : (
            <div className="bg-gradient-card rounded-xl p-6 border-2 border-primary/30 shadow-elegant-sm">
              <h3 className="text-lg font-bold text-primary mb-4 flex items-center gap-2">
                <span className="w-8 h-8 bg-primary text-primary-foreground rounded-full flex items-center justify-center text-sm">1</span>
                Informácie o klientovi
              </h3>

              {/* Toggle between standard and custom company */}
              <div className="flex items-center space-x-2 mb-6 p-4 bg-card/50 rounded-lg border border-primary/20">
                <input
                  type="checkbox"
                  id="useCustomCompany"
                  {...register("useCustomCompany")}
                  className="h-4 w-4 rounded border-primary/30 text-primary focus:ring-primary"
                />
                <label htmlFor="useCustomCompany" className="cursor-pointer text-sm">
                  Zadať vlastné údaje o spoločnosti (neregistrovaná v databáze)
                </label>
              </div>

              <CustomCompanySection
                form={form}
                isEditMode={isEditMode}
                useCustomCompany={useCustomCompany}
              />
            </div>
          )}

          {/* Dates */}
          <InvoiceDateSection
            form={form}
            issueDate={issueDate}
            setIssueDate={setIssueDate}
            dueDate={dueDate}
            setDueDate={setDueDate}
            deliveryDate={deliveryDate}
            setDeliveryDate={setDeliveryDate}
            dueDateDays={dueDateDays}
            setDueDateDays={setDueDateDays}
          />

          {/* Payment Symbols */}
          <PaymentSymbolsSection
            form={form}
          />

          {/* Items */}
          <InvoiceItemsSection
            form={form}
            fields={fields}
            append={append}
            remove={remove}
            items={items}
          />

          {/* Actions */}
          <div className="flex gap-3 justify-end">
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
        </form>
      </div>
    </DashboardLayout>
  );
};

export default NewInvoice;
