import { useNavigate, useParams } from "react-router-dom";
import { useForm, useFieldArray, FieldErrors } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import * as z from "zod";
import { Save, ArrowLeft, Loader2, FileText, CreditCard, Receipt, Calculator } from "lucide-react";
import { DashboardLayout } from "@/components/dashboard/DashboardLayout";
import { Button } from "@/components/ui/button";
import { Label } from "@/components/ui/label";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { useToast } from "@/hooks/use-toast";
import { useState, useEffect, useCallback } from "react";
import { useInvoice, useInvoices } from "@/hooks/useInvoices";
import { companyService } from "@/services/companyService";
import { invoiceService } from "@/services/invoiceService";
import { ClientInformationSection } from "@/components/invoices/ClientInformationSection";
import { CustomCompanySection } from "@/components/invoices/CustomCompanySection";
import { InvoiceDateSection } from "@/components/invoices/InvoiceDateSection";
import { PaymentSymbolsSection } from "@/components/invoices/PaymentSymbolsSection";
import { PaymentDPHSection } from "@/components/invoices/PaymentDPHSection";
import { InvoiceNumberSection } from "@/components/invoices/InvoiceNumberSection";
import { InvoiceItemsSection } from "@/components/invoices/InvoiceItemsSection";
import { InvoiceSummarySection } from "@/components/invoices/InvoiceSummarySection";
import { InvoiceStatusDropdown } from "@/components/invoices/InvoiceStatusDropdown";
import type { InvoiceFormData } from "@/components/invoices/ClientInformationSection";
import { Company } from "@/types/company";
import { useQueryClient } from "@tanstack/react-query";

const invoiceSchema = z.object({
  invoiceNumber: z.string().trim().min(1, "Číslo faktúry je povinné"),
  clientName: z.string().trim().max(100).optional(),
  clientStreet: z.string().trim().max(255).optional(),
  clientCity: z.string().trim().max(100).optional(),
  clientPostalCode: z.string().trim().max(20).optional(),
  clientIco: z.string().trim().max(20).optional(),
  clientDic: z.string().trim().regex(/^\d*$/, "DIČ musí obsahovať len číslice").max(20).optional(),
  clientIcDph: z.string().trim().max(20).optional(),
  useCustomCompany: z.boolean().optional().default(false),
  customCompanyIco: z.string().trim().max(12).optional(),
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
  reverseCharge: z.boolean().optional().default(false),
  taxExemptionReason: z.string().trim().max(200).optional(),
  specialText: z.string().trim().max(200).optional(),
  notes: z.string().trim().max(500).optional(),
  items: z.array(
    z.object({
      description: z.string().trim().min(1, "Popis je povinný").max(200),
      quantity: z.number().min(1, "Množstvo musí byť aspoň 1"),
      price: z.number().min(0, "Cena musí byť nezáporná"),
      tax_rate: z.number().min(0).max(100).default(20), // Slovak VAT rates
    })
  ).min(1, "Aspoň jedna položka je povinná"),
}).superRefine((data, ctx) => {
  // Conditional validation for reverse charge - require tax exemption reason
  if (data.reverseCharge && (!data.taxExemptionReason || data.taxExemptionReason.trim() === '')) {
    ctx.addIssue({
      code: z.ZodIssueCode.custom,
      message: "Pri prenose daňovej povinnosti je potrebné uviesť dôvod oslobodenia od dane",
      path: ["taxExemptionReason"],
    });
  }

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

// Type for tab values - ensures type safety when switching tabs
type TabValue = "basic" | "payment" | "items" | "summary";

// Constants for validation error handling
const MAX_VISIBLE_ERRORS = 5;
const ERROR_TOAST_DURATION_MS = 12000;
const MAX_RECURSION_DEPTH = 10;

// Tab order for determining which tab to show first when errors occur
const TAB_ORDER: TabValue[] = ["basic", "payment", "items", "summary"];

// Field to tab mapping - maps form field names to their corresponding tab
const BASIC_TAB_FIELDS = [
  "invoiceNumber",
  "clientName", "clientIco", "clientDic", "clientIcDph", "clientStreet", "clientCity", "clientPostalCode",
  "customCompanyIco", "customCompanyDic", "customCompanyIcDph", "customCompanyName",
  "customCompanyAddress", "customCompanyCity", "customCompanyZip", "customCompanyCountry",
  "issueDate", "dueDate", "deliveryDate"
];

const PAYMENT_TAB_FIELDS = [
  "variableSymbol", "constantSymbol", "specificSymbol",
  "reverseCharge", "taxExemptionReason", "specialText", "notes"
];

// Helper function to determine which tab a field belongs to
const getTabForField = (fieldName: string): TabValue => {
  // Items tab: items array
  if (fieldName === "items" || fieldName.startsWith("items.")) {
    return "items";
  }

  if (BASIC_TAB_FIELDS.includes(fieldName)) {
    return "basic";
  }

  if (PAYMENT_TAB_FIELDS.includes(fieldName)) {
    return "payment";
  }

  // Default to basic if unknown
  return "basic";
};

const NewInvoice = () => {
  const navigate = useNavigate();
  const { id } = useParams();
  const { toast } = useToast();
  const queryClient = useQueryClient();
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

  // State for generated invoice number
  const [generatedInvoiceNumber, setGeneratedInvoiceNumber] = useState<string>("");

  // State for controlled tabs
  const [activeTab, setActiveTab] = useState<TabValue>("basic");

  const form = useForm<InvoiceFormData>({
    resolver: zodResolver(invoiceSchema),
    defaultValues: {
      invoiceNumber: isEditMode ? "" : generatedInvoiceNumber,
      items: [{ description: "", quantity: 1, price: 0, tax_rate: 20 }],
      variableSymbol: isEditMode ? "" : generatedInvoiceNumber,
      constantSymbol: "",
      specificSymbol: "",
      issueDate: new Date(),
      deliveryDate: new Date(),
      dueDate: new Date(new Date().setDate(new Date().getDate() + 15)),
      useCustomCompany: false,
      reverseCharge: false,
      taxExemptionReason: "",
      specialText: "",
      notes: "",
    },
  });

  const {
    register,
    control,
    handleSubmit,
    setValue,
    watch,
    reset,
  } = form;

  const { fields, append, remove } = useFieldArray({
    control,
    name: "items",
  });

  // Fetch latest invoice number and generate next one (only in create mode)
  useEffect(() => {
    if (!isEditMode) {
      const fetchAndGenerateNumber = async () => {
        try {
          const response = await invoiceService.getLatestNumber();
          const latestNumber = response.data.latest_number;

          let nextNumber: string;

          if (latestNumber) {
            // Parse the latest number and increment
            // Assuming format like "20250001" (YYYYNNNN)
            const numberPart = parseInt(latestNumber.replace(/\D/g, ''), 10);
            const incremented = numberPart + 1;
            // Keep same length by padding
            nextNumber = String(incremented).padStart(latestNumber.length, '0');
          } else {
            // No previous invoices, start with default
            const currentYear = new Date().getFullYear();
            nextNumber = `${currentYear}0001`;
          }

          setGeneratedInvoiceNumber(nextNumber);
          setValue("invoiceNumber", nextNumber);
          setValue("variableSymbol", nextNumber);
        } catch (error) {
          console.error('Error fetching latest invoice number:', error);
          // Fallback to default generation
          const currentYear = new Date().getFullYear();
          const fallbackNumber = `${currentYear}0001`;
          setGeneratedInvoiceNumber(fallbackNumber);
          setValue("invoiceNumber", fallbackNumber);
          setValue("variableSymbol", fallbackNumber);
        }
      };

      fetchAndGenerateNumber();
    }
  }, [isEditMode, setValue]);

  useEffect(() => {
    if (issueDate) {
      const newDueDate = new Date(issueDate);
      newDueDate.setDate(newDueDate.getDate() + dueDateDays);
      setDueDate(newDueDate);
      setValue("dueDate", newDueDate);
    }
  }, [issueDate, dueDateDays, setValue]);

  const items = watch("items");
  const useCustomCompany = watch("useCustomCompany");

  // Handle validation errors with proper typing and depth protection
  const onError = useCallback((errors: FieldErrors<InvoiceFormData>) => {
    if (import.meta.env.DEV) {
      console.log("Validation errors:", errors);
    }

    // Collect all error messages
    const errorMessages: string[] = [];
    const errorTabs = new Set<TabValue>();

    // Helper to process errors recursively with depth protection
    const processErrors = (obj: any, path: string = "", depth: number = 0): void => {
      if (!obj || depth > MAX_RECURSION_DEPTH) return; // Protect against infinite recursion

      Object.keys(obj).forEach(key => {
        const fullPath = path ? `${path}.${key}` : key;
        const error = obj[key];

        if (error?.message) {
          // Direct error message
          errorMessages.push(error.message);
          errorTabs.add(getTabForField(fullPath));
        } else if (Array.isArray(error)) {
          // Array errors (for items)
          error.forEach((item, index) => {
            if (item) {
              processErrors(item, `${fullPath}.${index}`, depth + 1);
            }
          });
        } else if (typeof error === "object") {
          // Nested errors
          processErrors(error, fullPath, depth + 1);
        }
      });
    };

    processErrors(errors);

    // Switch to the first tab with errors
    const firstErrorTab = TAB_ORDER.find(tab => errorTabs.has(tab));
    if (firstErrorTab) {
      setActiveTab(firstErrorTab);
    }

    // Show toast with errors (max from constant)
    const displayErrors = errorMessages.slice(0, MAX_VISIBLE_ERRORS);
    const remainingCount = errorMessages.length - MAX_VISIBLE_ERRORS;

    toast({
      title: "Chyba pri validácii",
      description: (
        <div className="space-y-2">
          <ol className="list-decimal list-inside space-y-1 text-sm">
            {displayErrors.map((msg, idx) => (
              <li key={idx}>{msg}</li>
            ))}
          </ol>
          {remainingCount > 0 && (
            <p className="text-sm font-semibold">
              ...a ďalších {remainingCount}
            </p>
          )}
        </div>
      ),
      variant: "destructive",
      duration: ERROR_TOAST_DURATION_MS,
    });
  }, [toast, setActiveTab]);

  // Reset form and state when switching from edit to create mode
  useEffect(() => {
    if (!isEditMode) {
      // Reset form to default values
      reset({
        invoiceNumber: generatedInvoiceNumber,
        items: [{ description: "", quantity: 1, price: 0, tax_rate: 20 }],
        variableSymbol: generatedInvoiceNumber,
        constantSymbol: "",
        specificSymbol: "",
        issueDate: new Date(),
        deliveryDate: new Date(),
        dueDate: new Date(new Date().setDate(new Date().getDate() + 15)),
        useCustomCompany: false,
        reverseCharge: false,
        taxExemptionReason: "",
        specialText: "",
        notes: "",
        clientName: "",
        clientStreet: "",
        clientCity: "",
        clientPostalCode: "",
        clientIco: "",
        clientDic: "",
        clientIcDph: "",
        customCompanyIco: "",
        customCompanyDic: "",
        customCompanyIcDph: "",
        customCompanyName: "",
        customCompanyAddress: "",
        customCompanyCity: "",
        customCompanyZip: "",
        customCompanyCountry: "",
      });

      // Reset all state variables
      setSelectedCompany(null);
      setIcoSearch("");
      setFilteredCompanies([]);
      setShowSuggestions(false);
      setSearchError(null);
      setIsSelectingCompany(false);

      // Reset dates
      const today = new Date();
      setIssueDate(today);
      setDeliveryDate(today);
      const defaultDueDate = new Date();
      defaultDueDate.setDate(defaultDueDate.getDate() + 15);
      setDueDate(defaultDueDate);
    }
  }, [isEditMode, reset, generatedInvoiceNumber]);

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

      // Set VAT and notes fields
      setValue("reverseCharge", invoice.reverse_charge || false);
      setValue("taxExemptionReason", invoice.tax_exemption_reason || "");
      setValue("specialText", invoice.special_text || "");
      setValue("notes", invoice.notes || "");

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
          price: Number(item.unit_price_without_tax || item.unit_price || 0),
          tax_rate: Number(item.tax_rate ?? 20),
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
        reverseCharge: data.reverseCharge || false,
        taxExemptionReason: data.taxExemptionReason,
        specialText: data.specialText,
        notes: data.notes,
        currency: 'EUR',
        items: data.items.map(item => ({
          description: item.description,
          quantity: item.quantity,
          price: item.price, // Backend expects this as unit_price_without_tax
          tax_rate: item.tax_rate ?? 20,
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

  const handleStatusChange = useCallback(() => {
    // Invalidate invoice query to refresh data
    if (id) {
      queryClient.invalidateQueries({ queryKey: ['invoice', Number(id)] });
      queryClient.invalidateQueries({ queryKey: ['invoices'] });
    }
  }, [id, queryClient]);

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
          <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
              <h1 className="text-3xl font-bold text-foreground">{isEditMode ? "Upraviť faktúru" : "Nová faktúra"}</h1>
              <p className="text-muted-foreground mt-1">{isEditMode ? "Upravte existujúcu faktúru" : "Vytvorte novú faktúru pre vášho klienta"}</p>
            </div>
            {isEditMode && invoice && (
              <div className="flex items-center gap-2">
                <span className="text-sm text-muted-foreground">Stav:</span>
                <InvoiceStatusDropdown
                  invoiceId={Number(id)}
                  currentStatus={invoice.status}
                  onStatusChange={handleStatusChange}
                />
              </div>
            )}
          </div>
        </div>

        <form onSubmit={handleSubmit(onSubmit, onError)} className="space-y-6">
          <Tabs value={activeTab} onValueChange={setActiveTab} className="w-full">
            <TabsList className="grid w-full grid-cols-4 h-auto">
              <TabsTrigger value="basic" className="flex items-center gap-2 py-3">
                <FileText className="h-4 w-4" />
                <span className="hidden sm:inline">Základné</span>
              </TabsTrigger>
              <TabsTrigger value="payment" className="flex items-center gap-2 py-3">
                <CreditCard className="h-4 w-4" />
                <span className="hidden sm:inline">Platba</span>
              </TabsTrigger>
              <TabsTrigger value="items" className="flex items-center gap-2 py-3">
                <Receipt className="h-4 w-4" />
                <span className="hidden sm:inline">Položky</span>
              </TabsTrigger>
              <TabsTrigger value="summary" className="flex items-center gap-2 py-3">
                <Calculator className="h-4 w-4" />
                <span className="hidden sm:inline">Súhrn</span>
              </TabsTrigger>
            </TabsList>

            {/* Tab 1: Základné údaje */}
            <TabsContent value="basic" className="space-y-6 mt-6">
              {/* Single card with 2-column grid layout */}
              <div className="rounded-lg border bg-card text-card-foreground shadow-sm p-6">
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                  {/* Left column: Client Information */}
                  <div className="space-y-4">
                    <h3 className="text-lg font-semibold mb-4">Klient</h3>

                    {/* Toggle between standard and custom company */}
                    <div className="flex items-center space-x-2 mb-4 p-3 bg-muted/30 rounded-lg border">
                      <input
                        type="checkbox"
                        id="useCustomCompany"
                        {...register("useCustomCompany")}
                        className="h-4 w-4 rounded border-input text-primary focus:ring-primary"
                      />
                      <Label htmlFor="useCustomCompany" className="cursor-pointer text-sm">
                        Zadať vlastné údaje o spoločnosti
                      </Label>
                    </div>

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
                      <CustomCompanySection
                        form={form}
                        isEditMode={isEditMode}
                        useCustomCompany={useCustomCompany}
                      />
                    )}
                  </div>

                  {/* Right column: Invoice Details + Dates */}
                  <div className="space-y-6">
                    {/* Invoice Details */}
                    <div>
                      <h3 className="text-lg font-semibold mb-4">Detaily faktúry</h3>
                      <InvoiceNumberSection form={form} />
                    </div>

                    {/* Dates */}
                    <div>
                      <h3 className="text-lg font-semibold mb-4">Dátumy</h3>
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
                    </div>
                  </div>
                </div>
              </div>
            </TabsContent>

            {/* Tab 2: Platba & DPH */}
            <TabsContent value="payment" className="space-y-6 mt-6">
              <PaymentDPHSection form={form} />
            </TabsContent>

            {/* Tab 3: Položky */}
            <TabsContent value="items" className="space-y-6 mt-6">
              <InvoiceItemsSection
                form={form}
                fields={fields}
                append={append}
                remove={remove}
                items={items}
              />
            </TabsContent>

            {/* Tab 4: Súhrn */}
            <TabsContent value="summary" className="space-y-6 mt-6">
              <InvoiceSummarySection form={form} items={items} />
            </TabsContent>
          </Tabs>

          {/* Actions */}
          <div className="flex gap-3 justify-end border-t pt-6 mt-6">
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
