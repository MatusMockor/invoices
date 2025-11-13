import { useState, useCallback, useMemo } from "react";
import { useQueryClient } from "@tanstack/react-query";
import { DashboardLayout } from "@/components/dashboard/DashboardLayout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader } from "@/components/ui/card";
import { Plus, Search, Building2 } from "lucide-react";
import { useAuth } from "@/hooks/useAuth";
import { useSwitchCompany } from "@/hooks/useSwitchCompany";
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from "@/components/ui/alert-dialog";
import { useToast } from "@/hooks/use-toast";
import { Skeleton } from "@/components/ui/skeleton";
import { useDebounce } from "@/hooks/useDebounce";
import { useUserCompanies } from "@/hooks/useUserCompanies";
import { CompanyFormDialog } from "@/components/companies/CompanyFormDialog";
import { CompanyCard } from "@/components/companies/CompanyCard";
import { UserCompany, UserCompanyFormData, CompanyStats, AxiosErrorResponse } from "@/types";

const Companies = () => {
  const [searchQuery, setSearchQuery] = useState("");
  const debouncedSearch = useDebounce(searchQuery, 500);
  const [isAddOpen, setIsAddOpen] = useState(false);
  const [isEditOpen, setIsEditOpen] = useState(false);
  const [isDeleteOpen, setIsDeleteOpen] = useState(false);
  const [selectedCompany, setSelectedCompany] = useState<UserCompany | null>(null);
  const [formData, setFormData] = useState<Partial<UserCompanyFormData>>({
    status: 'active',
    country: 'Slovenská republika',
  });
  const [formErrors, setFormErrors] = useState<Partial<Record<keyof UserCompanyFormData, string>>>({});
  const [switchAnnouncement, setSwitchAnnouncement] = useState("");

  const { toast } = useToast();
  const queryClient = useQueryClient();
  const { user } = useAuth();
  const { switchCompany, isSwitching } = useSwitchCompany();

  // Use custom hook for data fetching and mutations
  const {
    companies,
    isLoading,
    error,
    createCompany,
    updateCompany,
    deleteCompany,
    isCreating,
    isUpdating,
    isDeleting,
  } = useUserCompanies(debouncedSearch);

  // Calculate statistics
  const stats: CompanyStats = useMemo(() => {
    return {
      total: companies.length,
    };
  }, [companies]);

  // Form validation
  const validateForm = useCallback((): boolean => {
    const errors: Partial<Record<keyof UserCompanyFormData, string>> = {};

    if (!formData.name?.trim()) {
      errors.name = "Názov firmy je povinný";
    }

    if (!formData.ico?.trim()) {
      errors.ico = "IČO je povinné";
    } else if (!/^\d{8}$/.test(formData.ico)) {
      errors.ico = "IČO musí obsahovať 8 číslic";
    }

    if (!formData.dic?.trim()) {
      errors.dic = "DIČ je povinné";
    }

    if (!formData.email?.trim()) {
      errors.email = "Email je povinný";
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
      errors.email = "Neplatný formát emailu";
    }

    if (!formData.street?.trim()) {
      errors.street = "Ulica je povinná";
    }

    if (!formData.city?.trim()) {
      errors.city = "Mesto je povinné";
    }

    if (!formData.postal_code?.trim()) {
      errors.postal_code = "PSČ je povinné";
    }

    if (!formData.country?.trim()) {
      errors.country = "Krajina je povinná";
    }

    setFormErrors(errors);
    return Object.keys(errors).length === 0;
  }, [formData]);

  // Handlers
  const handleAdd = useCallback((e: React.FormEvent) => {
    e.preventDefault();

    if (!validateForm()) {
      toast({
        title: "Chyba validácie",
        description: "Prosím, skontrolujte všetky povinné polia.",
        variant: "destructive",
      });
      return;
    }

    createCompany.mutate(formData as UserCompanyFormData, {
      onSuccess: (newCompany) => {
        setIsAddOpen(false);
        setFormData({ status: 'active', country: 'Slovenská republika' });
        setFormErrors({});
        toast({
          title: "Firma pridaná",
          description: `Firma ${newCompany.name} bola úspešne pridaná.`,
        });
      },
      onError: (error: unknown) => {
        const axiosError = error as AxiosErrorResponse;
        const errorMessage = axiosError.response?.data?.message || "Nepodarilo sa pridať firmu.";
        toast({
          title: "Chyba",
          description: errorMessage,
          variant: "destructive",
        });

        // Handle validation errors
        const validationErrors = axiosError.response?.data?.errors;
        if (validationErrors) {
          // Convert Laravel validation errors to our format
          const formattedErrors: Partial<Record<keyof UserCompanyFormData, string>> = {};
          Object.entries(validationErrors).forEach(([key, messages]) => {
            formattedErrors[key as keyof UserCompanyFormData] = messages[0];
          });
          setFormErrors(formattedErrors);
        }
      },
    });
  }, [formData, validateForm, createCompany, toast]);

  const handleEdit = useCallback((e: React.FormEvent) => {
    e.preventDefault();

    if (!selectedCompany) return;

    if (!validateForm()) {
      toast({
        title: "Chyba validácie",
        description: "Prosím, skontrolujte všetky povinné polia.",
        variant: "destructive",
      });
      return;
    }

    updateCompany.mutate(
      {
        id: selectedCompany.id,
        data: formData as UserCompanyFormData,
      },
      {
        onSuccess: (updatedCompany) => {
          setIsEditOpen(false);
          setSelectedCompany(null);
          setFormData({ status: 'active', country: 'Slovenská republika' });
          setFormErrors({});
          toast({
            title: "Firma upravená",
            description: `Údaje firmy ${updatedCompany.name} boli úspešne aktualizované.`,
          });
        },
        onError: (error: unknown) => {
          const axiosError = error as AxiosErrorResponse;
          const errorMessage = axiosError.response?.data?.message || "Nepodarilo sa upraviť firmu.";
          toast({
            title: "Chyba",
            description: errorMessage,
            variant: "destructive",
          });

          const validationErrors = axiosError.response?.data?.errors;
          if (validationErrors) {
            // Convert Laravel validation errors to our format
            const formattedErrors: Partial<Record<keyof UserCompanyFormData, string>> = {};
            Object.entries(validationErrors).forEach(([key, messages]) => {
              formattedErrors[key as keyof UserCompanyFormData] = messages[0];
            });
            setFormErrors(formattedErrors);
          }
        },
      }
    );
  }, [selectedCompany, formData, validateForm, updateCompany, toast]);

  const handleDelete = useCallback(() => {
    if (!selectedCompany) return;

    deleteCompany.mutate(selectedCompany.id, {
      onSuccess: () => {
        setIsDeleteOpen(false);
        const companyName = selectedCompany.name;
        setSelectedCompany(null);
        toast({
          title: "Firma zmazaná",
          description: `Firma ${companyName} bola úspešne odstránená.`,
          variant: "destructive",
        });
      },
      onError: (error: unknown) => {
        const axiosError = error as AxiosErrorResponse;
        const errorMessage = axiosError.response?.data?.message || "Nepodarilo sa zmazať firmu.";
        toast({
          title: "Chyba",
          description: errorMessage,
          variant: "destructive",
        });
      },
    });
  }, [selectedCompany, deleteCompany, toast]);

  const openAddDialog = useCallback(() => {
    setFormData({ status: 'active', country: 'Slovenská republika' });
    setFormErrors({});
    setIsAddOpen(true);
  }, []);

  const openEditDialog = useCallback((company: UserCompany) => {
    setSelectedCompany(company);
    setFormData({
      name: company.name,
      ico: company.ico,
      dic: company.dic || '',
      ic_dph: company.ic_dph || '',
      email: company.email || '',
      phone: company.phone || '',
      street: company.street,
      city: company.city,
      postal_code: company.postal_code,
      country: company.country,
      iban: company.iban || '',
      swift: company.swift || '',
      status: company.status,
    });
    setFormErrors({});
    setIsEditOpen(true);
  }, []);

  const openDeleteDialog = useCallback((company: UserCompany) => {
    setSelectedCompany(company);
    setIsDeleteOpen(true);
  }, []);

  const handleSwitchCompany = useCallback(async (companyId: number) => {
    try {
      await switchCompany(companyId);
      const company = companies.find(c => c.id === companyId);
      if (company) {
        setSwitchAnnouncement(`Prepnuté na firmu ${company.name}`);
        setTimeout(() => setSwitchAnnouncement(''), 3000);
      }
    } catch (error) {
      console.error('Failed to switch company:', error);
      // Error is already handled in useSwitchCompany hook with toast
    }
  }, [switchCompany, companies]);

  return (
    <DashboardLayout>
      <div className="space-y-6 animate-fade-in">
        {/* Live region for screen reader announcements */}
        <div role="status" aria-live="polite" aria-atomic="true" className="sr-only">
          {switchAnnouncement}
        </div>

        {/* Header */}
        <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
          <div>
            <h1 className="text-4xl font-bold bg-gradient-primary bg-clip-text text-transparent">
              Moje firmy
            </h1>
            <p className="text-muted-foreground mt-2">
              Spravujte všetky vaše firmy na jednom mieste
            </p>
          </div>
          <Button
            onClick={openAddDialog}
            className="bg-gradient-primary hover:opacity-90 transition-opacity shadow-md"
            disabled={isLoading}
          >
            <Plus className="h-4 w-4 mr-2" aria-hidden="true" />
            Nová firma
          </Button>
        </div>

        {/* Stats Cards */}
        <div className="grid grid-cols-1 md:grid-cols-1 gap-4">
          {isLoading ? (
            <Card className="bg-gradient-card border-border/50">
              <CardContent className="p-6">
                <Skeleton className="h-20 w-full" />
              </CardContent>
            </Card>
          ) : (
            <Card className="bg-gradient-card border-border/50 hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
              <CardContent className="p-6">
                <div className="flex items-center justify-between">
                  <div>
                    <p className="text-sm text-muted-foreground font-medium">Celkom firiem</p>
                    <p className="text-3xl font-bold text-foreground mt-2">
                      {stats.total}
                    </p>
                  </div>
                  <div className="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center">
                    <Building2 className="w-6 h-6 text-primary" aria-hidden="true" />
                  </div>
                </div>
              </CardContent>
            </Card>
          )}
        </div>

        {/* Search */}
        <Card className="bg-gradient-card border-border/50">
          <CardContent className="p-6">
            <div className="relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-muted-foreground" aria-hidden="true" />
              <Input
                placeholder="Hľadať firmy podľa názvu, emailu alebo IČO..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="pl-11 h-12 bg-background/50 border-border/50 focus:border-primary/50"
                disabled={isLoading}
              />
            </div>
          </CardContent>
        </Card>

        {/* Error State */}
        {error && (
          <Card className="bg-gradient-card border-destructive/50">
            <CardContent className="p-12 text-center">
              <div className="w-16 h-16 rounded-full bg-destructive/10 flex items-center justify-center mx-auto mb-4">
                <Building2 className="w-8 h-8 text-destructive" aria-hidden="true" />
              </div>
              <h3 className="text-xl font-semibold text-foreground mb-2">Chyba pri načítaní firiem</h3>
              <p className="text-muted-foreground">
                Nepodarilo sa načítať zoznam firiem. Skúste to prosím znova.
              </p>
              <Button
                onClick={() => queryClient.invalidateQueries({ queryKey: ['user-companies'] })}
                className="mt-4"
                variant="outline"
              >
                Skúsiť znova
              </Button>
            </CardContent>
          </Card>
        )}

        {/* Loading Skeletons */}
        {isLoading && (
          <>
            <span className="sr-only" role="status" aria-live="polite">
              Načítavam firmy...
            </span>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {[...Array(6)].map((_, i) => (
                <Card key={i} className="bg-gradient-card border-border/50">
                  <CardHeader>
                    <Skeleton className="h-16 w-full" />
                  </CardHeader>
                  <CardContent className="space-y-4">
                    <Skeleton className="h-4 w-full" />
                    <Skeleton className="h-4 w-full" />
                    <Skeleton className="h-4 w-2/3" />
                    <div className="grid grid-cols-2 gap-3">
                      <Skeleton className="h-16 w-full" />
                      <Skeleton className="h-16 w-full" />
                    </div>
                    <div className="flex gap-2">
                      <Skeleton className="h-10 flex-1" />
                      <Skeleton className="h-10 w-10" />
                    </div>
                  </CardContent>
                </Card>
              ))}
            </div>
          </>
        )}

        {/* Companies Grid */}
        {!isLoading && !error && companies.length > 0 && (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {companies.map((company) => {
              const isCurrentCompany = user?.current_company_id === company.id;
              return (
                <CompanyCard
                  key={company.id}
                  company={company}
                  isCurrentCompany={isCurrentCompany}
                  onSwitch={handleSwitchCompany}
                  onEdit={openEditDialog}
                  onDelete={openDeleteDialog}
                  isSwitching={isSwitching}
                  isUpdating={isUpdating}
                  isDeleting={isDeleting}
                />
              );
            })}
          </div>
        )}

        {/* Empty State */}
        {!isLoading && !error && companies.length === 0 && (
          <Card className="bg-gradient-card border-border/50">
            <CardContent className="p-12 text-center">
              <Building2 className="w-16 h-16 text-muted-foreground/50 mx-auto mb-4" aria-hidden="true" />
              <h3 className="text-xl font-semibold text-foreground mb-2">
                {searchQuery ? 'Žiadne výsledky' : 'Žiadne firmy'}
              </h3>
              <p className="text-muted-foreground">
                {searchQuery
                  ? 'Nenašli sa žiadne firmy zodpovedajúce vášmu vyhľadávaniu.'
                  : 'Zatiaľ nemáte pridané žiadne firmy. Pridajte svoju prvú firmu.'}
              </p>
              {!searchQuery && (
                <Button onClick={openAddDialog} className="mt-4">
                  <Plus className="h-4 w-4 mr-2" aria-hidden="true" />
                  Pridať prvú firmu
                </Button>
              )}
            </CardContent>
          </Card>
        )}
      </div>

      {/* Add Company Dialog */}
      <CompanyFormDialog
        mode="create"
        open={isAddOpen}
        onOpenChange={setIsAddOpen}
        formData={formData}
        formErrors={formErrors}
        isSubmitting={isCreating}
        onSubmit={handleAdd}
        onFormDataChange={setFormData}
      />

      {/* Edit Company Dialog */}
      <CompanyFormDialog
        mode="edit"
        open={isEditOpen}
        onOpenChange={setIsEditOpen}
        formData={formData}
        formErrors={formErrors}
        isSubmitting={isUpdating}
        onSubmit={handleEdit}
        onFormDataChange={setFormData}
      />

      {/* Delete Confirmation Dialog */}
      <AlertDialog open={isDeleteOpen} onOpenChange={setIsDeleteOpen}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Naozaj chcete zmazať túto firmu?</AlertDialogTitle>
            <AlertDialogDescription>
              Táto akcia sa nedá vrátiť späť. Firma <strong>{selectedCompany?.name}</strong> bude
              trvalo odstránená zo systému.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel disabled={isDeleting}>
              Zrušiť
            </AlertDialogCancel>
            <AlertDialogAction
              onClick={handleDelete}
              className="bg-destructive hover:bg-destructive/90"
              disabled={isDeleting}
            >
              {isDeleting ? "Mažem..." : "Zmazať"}
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </DashboardLayout>
  );
};

export default Companies;
