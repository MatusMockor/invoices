import { useState, useEffect } from "react";
import { DashboardLayout } from "@/components/dashboard/DashboardLayout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Switch } from "@/components/ui/switch";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Card } from "@/components/ui/card";
import { Separator } from "@/components/ui/separator";
import { useToast } from "@/hooks/use-toast";
import { useSettings } from "@/hooks/useSettings";
import { InvoicePreview } from "@/components/invoice/InvoicePreview";
import { useTheme } from "next-themes";
import { useAuth } from "@/hooks/useAuth";
import api from "@/lib/axios";
import { userCompanyService } from "@/services/userCompanyService";
import { useCompanyContext } from "@/contexts/CompanyContext";
import { VAT_PAYER_STATUS_OPTIONS } from "@/constants/vatPayerStatus";
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
import {
  User,
  Building2,
  Bell,
  Lock,
  Save,
  Moon,
  Sun,
  FileText,
  Eye,
  Palette,
  Loader2,
} from "lucide-react";

const Settings = () => {
  const { toast } = useToast();
  const { settings, updateSettings, isUpdating, isFetching } = useSettings();
  const { theme, setTheme } = useTheme();
  const { logout, user } = useAuth();
  const { selectedCompanyId } = useCompanyContext();
  const [activeTab, setActiveTab] = useState("profile");
  const [invoiceTemplate, setInvoiceTemplate] = useState<'classic' | 'modern' | 'minimal' | 'bold'>('classic');
  const [previewOpen, setPreviewOpen] = useState(false);
  const [previewInvoiceId, setPreviewInvoiceId] = useState<number | null>(null);
  const [notifications, setNotifications] = useState({
    emailInvoices: true,
    emailPayments: true,
    emailReminders: true,
    pushNotifications: false,
  });

  // Password change form state
  const [currentPassword, setCurrentPassword] = useState("");
  const [newPassword, setNewPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");
  const [isChangingPassword, setIsChangingPassword] = useState(false);

  // Account deletion state
  const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
  const [deletePassword, setDeletePassword] = useState("");
  const [isDeletingAccount, setIsDeletingAccount] = useState(false);

  // Profile form state
  const [profileData, setProfileData] = useState({
    firstName: '',
    lastName: '',
    email: '',
  });
  const [isSavingProfile, setIsSavingProfile] = useState(false);

  // Company form state
  const [companyData, setCompanyData] = useState({
    name: '',
    ico: '',
    dic: '',
    ic_dph: '',
    vat_payer_status: '',
    street: '',
    city: '',
    postal_code: '',
    country: '',
    phone: '',
    email: '',
    iban: '',
    swift: '',
  });
  const [isSavingCompany, setIsSavingCompany] = useState(false);

  // Load user profile data
  useEffect(() => {
    if (user) {
      setProfileData({
        firstName: user.first_name || '',
        lastName: user.last_name || '',
        email: user.email || '',
      });
    }
  }, [user]);

  // Load settings from API and reset form when company changes
  useEffect(() => {
    if (settings) {
      setInvoiceTemplate(settings.invoice_template);

      // Load company data if available
      if (settings.company) {
        setCompanyData({
          name: settings.company.name || '',
          ico: settings.company.ico || '',
          dic: settings.company.dic || '',
          ic_dph: settings.company.ic_dph || '',
          vat_payer_status: settings.company.vat_payer_status || '',
          street: settings.company.address || '',
          city: settings.company.city || '',
          postal_code: settings.company.postal_code || '',
          country: settings.company.country || '',
          phone: settings.company.phone || '',
          email: settings.company.email || '',
          iban: settings.company.iban || '',
          swift: settings.company.swift || '',
        });
      }
    }
  }, [settings, selectedCompanyId]); // Added selectedCompanyId to ensure form resets on company change

  const handlePreview = (template: 'classic' | 'modern' | 'minimal' | 'bold') => {
    // Temporarily set template for preview only
    setPreviewInvoiceId(1); // Use a demo invoice ID
    setPreviewOpen(true);
    // Store the preview template temporarily
    localStorage.setItem('previewTemplate', template);
  };

  const handleSaveProfile = async (e: React.FormEvent) => {
    e.preventDefault();

    // Validation
    if (!profileData.firstName.trim()) {
      toast({
        title: "Chyba",
        description: "Meno je povinné.",
        variant: "destructive",
      });
      return;
    }

    if (!profileData.lastName.trim()) {
      toast({
        title: "Chyba",
        description: "Priezvisko je povinné.",
        variant: "destructive",
      });
      return;
    }

    if (!profileData.email.trim()) {
      toast({
        title: "Chyba",
        description: "Email je povinný.",
        variant: "destructive",
      });
      return;
    }

    setIsSavingProfile(true);

    try {
      await api.put("/api/profile", {
        first_name: profileData.firstName,
        last_name: profileData.lastName,
        email: profileData.email,
      });

      toast({
        title: "Profil uložený",
        description: "Vaše zmeny boli úspešne uložené.",
      });
    } catch (error: any) {
      const message = error?.response?.data?.message || "Nepodarilo sa uložiť profil.";
      const errors = error?.response?.data?.errors;

      let description = message;
      if (errors) {
        const errorMessages = Object.values(errors).flat() as string[];
        description = errorMessages[0] || message;
      }

      toast({
        title: "Chyba",
        description,
        variant: "destructive",
      });
    } finally {
      setIsSavingProfile(false);
    }
  };

  const handleSaveCompany = async (e: React.FormEvent) => {
    e.preventDefault();

    // Validation
    if (!companyData.name.trim()) {
      toast({
        title: "Chyba",
        description: "Názov firmy je povinný.",
        variant: "destructive",
      });
      return;
    }

    if (!companyData.ico.trim()) {
      toast({
        title: "Chyba",
        description: "IČO je povinné.",
        variant: "destructive",
      });
      return;
    }

    if (!companyData.street.trim()) {
      toast({
        title: "Chyba",
        description: "Adresa je povinná.",
        variant: "destructive",
      });
      return;
    }

    if (!companyData.city.trim()) {
      toast({
        title: "Chyba",
        description: "Mesto je povinné.",
        variant: "destructive",
      });
      return;
    }

    if (!companyData.postal_code.trim()) {
      toast({
        title: "Chyba",
        description: "PSČ je povinné.",
        variant: "destructive",
      });
      return;
    }

    if (!companyData.country.trim()) {
      toast({
        title: "Chyba",
        description: "Krajina je povinná.",
        variant: "destructive",
      });
      return;
    }

    if (!settings?.company?.id) {
      toast({
        title: "Chyba",
        description: "ID firmy nebolo nájdené.",
        variant: "destructive",
      });
      return;
    }

    setIsSavingCompany(true);

    try {
      // Save company data
      await userCompanyService.update(settings.company.id, {
        name: companyData.name,
        ico: companyData.ico,
        dic: companyData.dic.trim() || null,
        ic_dph: companyData.ic_dph.trim() || null,
        vat_payer_status: companyData.vat_payer_status?.trim() ? companyData.vat_payer_status : null,
        street: companyData.street,
        city: companyData.city,
        postal_code: companyData.postal_code,
        country: companyData.country,
        phone: companyData.phone.trim() || null,
        email: companyData.email.trim() || null,
        iban: companyData.iban.trim() || null,
        swift: companyData.swift.trim() || null,
      });

      // Also save invoice template
      await updateSettings({ invoice_template: invoiceTemplate });

      toast({
        title: "Nastavenia uložené",
        description: "Firemné údaje a dizajn faktúry boli úspešne uložené.",
      });
    } catch (error: any) {
      const message = error?.response?.data?.message || "Nepodarilo sa uložiť nastavenia.";
      const errors = error?.response?.data?.errors;

      let description = message;
      if (errors) {
        const errorMessages = Object.values(errors).flat() as string[];
        description = errorMessages[0] || message;
      }

      toast({
        title: "Chyba",
        description,
        variant: "destructive",
      });
    } finally {
      setIsSavingCompany(false);
    }
  };

  const handleSavePassword = async (e: React.FormEvent) => {
    e.preventDefault();

    // Validation
    if (!currentPassword || !newPassword || !confirmPassword) {
      toast({
        title: "Chyba",
        description: "Všetky polia sú povinné.",
        variant: "destructive",
      });
      return;
    }

    if (newPassword !== confirmPassword) {
      toast({
        title: "Chyba",
        description: "Nové heslá sa nezhodujú.",
        variant: "destructive",
      });
      return;
    }

    if (newPassword.length < 8) {
      toast({
        title: "Chyba",
        description: "Heslo musí obsahovať aspoň 8 znakov.",
        variant: "destructive",
      });
      return;
    }

    setIsChangingPassword(true);

    try {
      await api.patch("/api/user/password", {
        current_password: currentPassword,
        password: newPassword,
        password_confirmation: confirmPassword,
      });

      toast({
        title: "Heslo zmenené",
        description: "Vaše heslo bolo úspešne zmenené.",
      });

      // Clear form
      setCurrentPassword("");
      setNewPassword("");
      setConfirmPassword("");
    } catch (error: any) {
      const message = error?.response?.data?.message || "Nepodarilo sa zmeniť heslo.";
      const errors = error?.response?.data?.errors;

      let description = message;
      if (errors) {
        // Display first error from each field
        const errorMessages = Object.values(errors).flat() as string[];
        description = errorMessages[0] || message;
      }

      toast({
        title: "Chyba",
        description,
        variant: "destructive",
      });
    } finally {
      setIsChangingPassword(false);
    }
  };

  const handleSaveNotifications = () => {
    toast({
      title: "Nastavenia uložené",
      description: "Vaše notifikačné preferencie boli aktualizované.",
    });
  };

  const handleDeleteAccount = async () => {
    if (!deletePassword) {
      toast({
        title: "Chyba",
        description: "Zadajte heslo pre potvrdenie.",
        variant: "destructive",
      });
      return;
    }

    setIsDeletingAccount(true);

    try {
      await api.delete("/api/profile", {
        data: { password: deletePassword },
      });

      toast({
        title: "Účet vymazaný",
        description: "Váš účet bol úspešne vymazaný.",
      });

      // Clear sensitive data
      setDeletePassword("");
      setDeleteDialogOpen(false);

      // Logout and redirect to login
      await logout();
    } catch (error: any) {
      const message = error?.response?.data?.message || "Nepodarilo sa vymazať účet.";

      toast({
        title: "Chyba",
        description: message,
        variant: "destructive",
      });
    } finally {
      setIsDeletingAccount(false);
    }
  };

  return (
    <DashboardLayout disableLoading={true}>
      {isFetching ? (
        <div className="flex flex-col items-center justify-center min-h-[500px]">
          <Loader2 className="h-8 w-8 animate-spin text-primary mb-3" />
          <p className="text-sm text-muted-foreground">Načítavam nastavenia...</p>
        </div>
      ) : (
      <div className="max-w-5xl mx-auto space-y-6 animate-fade-in">
        <div>
          <h1 className="text-3xl font-bold text-foreground">Nastavenia</h1>
          <p className="text-muted-foreground mt-1">
            Spravujte svoj účet a preferencie
          </p>
        </div>

        <Tabs value={activeTab} onValueChange={setActiveTab} className="space-y-6">
          <TabsList className="bg-card border border-border">
            <TabsTrigger value="profile" className="gap-2">
              <User className="h-4 w-4" />
              Profil
            </TabsTrigger>
            <TabsTrigger value="company" className="gap-2">
              <Building2 className="h-4 w-4" />
              Firma
            </TabsTrigger>
            <TabsTrigger value="invoice-design" className="gap-2">
              <Palette className="h-4 w-4" />
              Dizajn faktúry
            </TabsTrigger>
            <TabsTrigger value="notifications" className="gap-2">
              <Bell className="h-4 w-4" />
              Notifikácie
            </TabsTrigger>
            <TabsTrigger value="security" className="gap-2">
              <Lock className="h-4 w-4" />
              Zabezpečenie
            </TabsTrigger>
          </TabsList>

          {/* Profile Tab */}
          <TabsContent value="profile">
            <Card className="bg-gradient-card p-6 border border-border shadow-elegant-sm">
              <div className="mb-6">
                <h3 className="text-xl font-bold text-foreground">
                  Osobné údaje
                </h3>
                <p className="text-sm text-muted-foreground mt-1">
                  Upravte svoje osobné informácie
                </p>
              </div>

              <form onSubmit={handleSaveProfile} className="space-y-6">
                <div className="flex items-center gap-6 mb-6">
                  <div className="w-20 h-20 rounded-full bg-primary/10 flex items-center justify-center">
                    <User className="w-10 h-10 text-primary" />
                  </div>
                  <div>
                    <Button type="button" variant="outline" size="sm">
                      Zmeniť fotku
                    </Button>
                    <p className="text-sm text-muted-foreground mt-2">
                      JPG, PNG max. 2MB
                    </p>
                  </div>
                </div>

                <Separator />

                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div className="space-y-2">
                    <Label htmlFor="firstName">Meno *</Label>
                    <Input
                      id="firstName"
                      value={profileData.firstName}
                      onChange={(e) => setProfileData({ ...profileData, firstName: e.target.value })}
                      placeholder="Vaše meno"
                      disabled={isSavingProfile}
                      autoComplete="given-name"
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="lastName">Priezvisko *</Label>
                    <Input
                      id="lastName"
                      value={profileData.lastName}
                      onChange={(e) => setProfileData({ ...profileData, lastName: e.target.value })}
                      placeholder="Vaše priezvisko"
                      disabled={isSavingProfile}
                      autoComplete="family-name"
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="email">Email *</Label>
                    <Input
                      id="email"
                      type="email"
                      value={profileData.email}
                      onChange={(e) => setProfileData({ ...profileData, email: e.target.value })}
                      placeholder="váš@email.sk"
                      disabled={isSavingProfile}
                      autoComplete="email"
                    />
                  </div>
                </div>

                <div className="flex justify-end pt-4">
                  <Button
                    type="submit"
                    className="bg-primary hover:bg-primary/90"
                    disabled={isSavingProfile}
                  >
                    {isSavingProfile ? (
                      <>
                        <Loader2 className="h-4 w-4 mr-2 animate-spin" />
                        Ukladám...
                      </>
                    ) : (
                      <>
                        <Save className="h-4 w-4 mr-2" />
                        Uložiť zmeny
                      </>
                    )}
                  </Button>
                </div>
              </form>
            </Card>
          </TabsContent>

          {/* Company Tab */}
          <TabsContent value="company">
            <Card className="bg-gradient-card p-6 border border-border shadow-elegant-sm">
              <div className="mb-6">
                <h3 className="text-xl font-bold text-foreground">
                  Firemné údaje
                </h3>
                <p className="text-sm text-muted-foreground mt-1">
                  Tieto údaje sa zobrazia na vašich faktúrach
                </p>
              </div>

              <form onSubmit={handleSaveCompany} className="space-y-6">
                <div className="space-y-2">
                  <Label htmlFor="companyName">Názov firmy *</Label>
                  <Input
                    id="companyName"
                    value={companyData.name}
                    onChange={(e) => setCompanyData({ ...companyData, name: e.target.value })}
                    placeholder="Názov firmy"
                    disabled={isSavingCompany}
                    autoComplete="organization"
                  />
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div className="space-y-2">
                    <Label htmlFor="companyIco">IČO *</Label>
                    <Input
                      id="companyIco"
                      value={companyData.ico}
                      onChange={(e) => setCompanyData({ ...companyData, ico: e.target.value })}
                      placeholder="12345678"
                      disabled={isSavingCompany}
                      autoComplete="off"
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="companyDic">DIČ</Label>
                    <Input
                      id="companyDic"
                      value={companyData.dic}
                      onChange={(e) => setCompanyData({ ...companyData, dic: e.target.value })}
                      placeholder="2023456789"
                      disabled={isSavingCompany}
                      autoComplete="off"
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="companyIcDph">IČ DPH</Label>
                    <Input
                      id="companyIcDph"
                      value={companyData.ic_dph}
                      onChange={(e) => setCompanyData({ ...companyData, ic_dph: e.target.value })}
                      placeholder="SK2023456789"
                      disabled={isSavingCompany}
                      autoComplete="off"
                    />
                  </div>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="vatPayerStatus">Status platcu DPH</Label>
                  <Select
                    value={companyData.vat_payer_status}
                    onValueChange={(value) => setCompanyData({ ...companyData, vat_payer_status: value })}
                    disabled={isSavingCompany}
                  >
                    <SelectTrigger id="vatPayerStatus">
                      <SelectValue placeholder="Vyberte status platcu DPH" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="">Nezadané</SelectItem>
                      {VAT_PAYER_STATUS_OPTIONS.map((option) => (
                        <SelectItem key={option.value} value={option.value}>
                          {option.label}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="companyStreet">Adresa *</Label>
                  <Input
                    id="companyStreet"
                    value={companyData.street}
                    onChange={(e) => setCompanyData({ ...companyData, street: e.target.value })}
                    placeholder="Ulica a číslo"
                    disabled={isSavingCompany}
                    autoComplete="street-address"
                  />
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div className="space-y-2">
                    <Label htmlFor="companyCity">Mesto *</Label>
                    <Input
                      id="companyCity"
                      value={companyData.city}
                      onChange={(e) => setCompanyData({ ...companyData, city: e.target.value })}
                      placeholder="Mesto"
                      disabled={isSavingCompany}
                      autoComplete="address-level2"
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="companyPostalCode">PSČ *</Label>
                    <Input
                      id="companyPostalCode"
                      value={companyData.postal_code}
                      onChange={(e) => setCompanyData({ ...companyData, postal_code: e.target.value })}
                      placeholder="PSČ"
                      disabled={isSavingCompany}
                      autoComplete="postal-code"
                    />
                  </div>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="companyCountry">Krajina *</Label>
                  <Input
                    id="companyCountry"
                    value={companyData.country}
                    onChange={(e) => setCompanyData({ ...companyData, country: e.target.value })}
                    placeholder="Slovensko"
                    disabled={isSavingCompany}
                    autoComplete="country"
                  />
                </div>

                <Separator />

                <div className="space-y-6">
                  <h4 className="text-lg font-semibold text-foreground">
                    Kontaktné údaje
                  </h4>
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div className="space-y-2">
                      <Label htmlFor="companyPhone">Telefón</Label>
                      <Input
                        id="companyPhone"
                        value={companyData.phone}
                        onChange={(e) => setCompanyData({ ...companyData, phone: e.target.value })}
                        placeholder="+421 XXX XXX XXX"
                        disabled={isSavingCompany}
                        autoComplete="tel"
                      />
                    </div>
                    <div className="space-y-2">
                      <Label htmlFor="companyEmail">Email</Label>
                      <Input
                        id="companyEmail"
                        type="email"
                        value={companyData.email}
                        onChange={(e) => setCompanyData({ ...companyData, email: e.target.value })}
                        placeholder="info@firma.sk"
                        disabled={isSavingCompany}
                        autoComplete="email"
                      />
                    </div>
                  </div>
                </div>

                <Separator />

                <div className="space-y-6">
                  <h4 className="text-lg font-semibold text-foreground">
                    Bankové údaje
                  </h4>
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div className="space-y-2">
                      <Label htmlFor="iban">IBAN</Label>
                      <Input
                        id="iban"
                        value={companyData.iban}
                        onChange={(e) => setCompanyData({ ...companyData, iban: e.target.value })}
                        placeholder="SK00 0000 0000 0000 0000 0000"
                        disabled={isSavingCompany}
                        autoComplete="off"
                      />
                    </div>
                    <div className="space-y-2">
                      <Label htmlFor="swift">SWIFT/BIC</Label>
                      <Input
                        id="swift"
                        value={companyData.swift}
                        onChange={(e) => setCompanyData({ ...companyData, swift: e.target.value })}
                        placeholder="SWIFT kód"
                        disabled={isSavingCompany}
                        autoComplete="off"
                      />
                    </div>
                  </div>
                </div>

                <div className="flex justify-end pt-4">
                  <Button
                    type="submit"
                    className="bg-primary hover:bg-primary/90"
                    disabled={isSavingCompany}
                  >
                    {isSavingCompany ? (
                      <>
                        <Loader2 className="h-4 w-4 mr-2 animate-spin" />
                        Ukladám...
                      </>
                    ) : (
                      <>
                        <Save className="h-4 w-4 mr-2" />
                        Uložiť zmeny
                      </>
                    )}
                  </Button>
                </div>
              </form>
            </Card>
          </TabsContent>

          {/* Invoice Design Tab */}
          <TabsContent value="invoice-design">
            <Card className="bg-gradient-card p-6 border border-border shadow-elegant-sm">
              <div className="mb-6">
                <h3 className="text-xl font-bold text-foreground">
                  Dizajn faktúry
                </h3>
                <p className="text-sm text-muted-foreground mt-1">
                  Vyberte vzhľad vašich faktúr
                </p>
              </div>

              <div className="space-y-6">
                <div className="space-y-4">
                  <Label>Šablóna faktúry</Label>
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div
                      className={`border-2 rounded-lg p-4 transition-all ${
                        invoiceTemplate === 'classic'
                          ? 'border-primary bg-primary/5'
                          : 'border-border'
                      }`}
                    >
                      <div className="flex items-center justify-between mb-2">
                        <div className="flex items-center gap-3">
                          <FileText className="h-5 w-5 text-primary" />
                          <h5 className="font-semibold text-foreground">Klasický</h5>
                        </div>
                        <Button
                          type="button"
                          variant="ghost"
                          size="sm"
                          onClick={() => handlePreview('classic')}
                        >
                          <Eye className="h-4 w-4 mr-1" />
                          Náhľad
                        </Button>
                      </div>
                      <p className="text-sm text-muted-foreground mb-3">
                        Tradičný profesionálny vzhľad s fialovým akcetom
                      </p>
                      <Button
                        type="button"
                        variant={invoiceTemplate === 'classic' ? 'default' : 'outline'}
                        size="sm"
                        className="w-full"
                        onClick={() => setInvoiceTemplate('classic')}
                      >
                        {invoiceTemplate === 'classic' ? 'Aktívna šablóna' : 'Vybrať šablónu'}
                      </Button>
                    </div>
                    <div
                      className={`border-2 rounded-lg p-4 transition-all ${
                        invoiceTemplate === 'modern'
                          ? 'border-primary bg-primary/5'
                          : 'border-border'
                      }`}
                    >
                      <div className="flex items-center justify-between mb-2">
                        <div className="flex items-center gap-3">
                          <FileText className="h-5 w-5 text-primary" />
                          <h5 className="font-semibold text-foreground">Moderný</h5>
                        </div>
                        <Button
                          type="button"
                          variant="ghost"
                          size="sm"
                          onClick={() => handlePreview('modern')}
                        >
                          <Eye className="h-4 w-4 mr-1" />
                          Náhľad
                        </Button>
                      </div>
                      <p className="text-sm text-muted-foreground mb-3">
                        Moderný vzhľad s gradientami a zaoblenými rohami
                      </p>
                      <Button
                        type="button"
                        variant={invoiceTemplate === 'modern' ? 'default' : 'outline'}
                        size="sm"
                        className="w-full"
                        onClick={() => setInvoiceTemplate('modern')}
                      >
                        {invoiceTemplate === 'modern' ? 'Aktívna šablóna' : 'Vybrať šablónu'}
                      </Button>
                    </div>
                    <div
                      className={`border-2 rounded-lg p-4 transition-all ${
                        invoiceTemplate === 'minimal'
                          ? 'border-primary bg-primary/5'
                          : 'border-border'
                      }`}
                    >
                      <div className="flex items-center justify-between mb-2">
                        <div className="flex items-center gap-3">
                          <FileText className="h-5 w-5 text-primary" />
                          <h5 className="font-semibold text-foreground">Minimalistický</h5>
                        </div>
                        <Button
                          type="button"
                          variant="ghost"
                          size="sm"
                          onClick={() => handlePreview('minimal')}
                        >
                          <Eye className="h-4 w-4 mr-1" />
                          Náhľad
                        </Button>
                      </div>
                      <p className="text-sm text-muted-foreground mb-3">
                        Čistý minimalistický dizajn s dôrazom na čitateľnosť
                      </p>
                      <Button
                        type="button"
                        variant={invoiceTemplate === 'minimal' ? 'default' : 'outline'}
                        size="sm"
                        className="w-full"
                        onClick={() => setInvoiceTemplate('minimal')}
                      >
                        {invoiceTemplate === 'minimal' ? 'Aktívna šablóna' : 'Vybrať šablónu'}
                      </Button>
                    </div>
                    <div
                      className={`border-2 rounded-lg p-4 transition-all ${
                        invoiceTemplate === 'bold'
                          ? 'border-primary bg-primary/5'
                          : 'border-border'
                      }`}
                    >
                      <div className="flex items-center justify-between mb-2">
                        <div className="flex items-center gap-3">
                          <FileText className="h-5 w-5 text-primary" />
                          <h5 className="font-semibold text-foreground">Odvážny</h5>
                        </div>
                        <Button
                          type="button"
                          variant="ghost"
                          size="sm"
                          onClick={() => handlePreview('bold')}
                        >
                          <Eye className="h-4 w-4 mr-1" />
                          Náhľad
                        </Button>
                      </div>
                      <p className="text-sm text-muted-foreground mb-3">
                        Odvážny moderný dizajn s tmavým pozadím a živými farbami
                      </p>
                      <Button
                        type="button"
                        variant={invoiceTemplate === 'bold' ? 'default' : 'outline'}
                        size="sm"
                        className="w-full"
                        onClick={() => setInvoiceTemplate('bold')}
                      >
                        {invoiceTemplate === 'bold' ? 'Aktívna šablóna' : 'Vybrať šablónu'}
                      </Button>
                    </div>
                  </div>
                </div>

                <div className="flex justify-end pt-4">
                  <Button
                    onClick={handleSaveCompany}
                    className="bg-primary hover:bg-primary/90"
                  >
                    <Save className="h-4 w-4 mr-2" />
                    Uložiť nastavenia
                  </Button>
                </div>
              </div>
            </Card>
          </TabsContent>

          {/* Notifications Tab */}
          <TabsContent value="notifications">
            <Card className="bg-gradient-card p-6 border border-border shadow-elegant-sm">
              <div className="mb-6">
                <h3 className="text-xl font-bold text-foreground">
                  Notifikácie
                </h3>
                <p className="text-sm text-muted-foreground mt-1">
                  Nastavte ako chcete dostávať upozornenia
                </p>
              </div>

              <div className="space-y-6">
                <div className="space-y-4">
                  <h4 className="text-lg font-semibold text-foreground">
                    Emailové notifikácie
                  </h4>
                  
                  <div className="flex items-center justify-between p-4 bg-card rounded-lg border border-border">
                    <div className="space-y-0.5">
                      <Label htmlFor="emailInvoices" className="text-base">
                        Nové faktúry
                      </Label>
                      <p className="text-sm text-muted-foreground">
                        Dostávať email pri vytvorení novej faktúry
                      </p>
                    </div>
                    <Switch
                      id="emailInvoices"
                      checked={notifications.emailInvoices}
                      onCheckedChange={(checked) =>
                        setNotifications({ ...notifications, emailInvoices: checked })
                      }
                    />
                  </div>

                  <div className="flex items-center justify-between p-4 bg-card rounded-lg border border-border">
                    <div className="space-y-0.5">
                      <Label htmlFor="emailPayments" className="text-base">
                        Platby
                      </Label>
                      <p className="text-sm text-muted-foreground">
                        Upozorniť na prijaté platby
                      </p>
                    </div>
                    <Switch
                      id="emailPayments"
                      checked={notifications.emailPayments}
                      onCheckedChange={(checked) =>
                        setNotifications({ ...notifications, emailPayments: checked })
                      }
                    />
                  </div>

                  <div className="flex items-center justify-between p-4 bg-card rounded-lg border border-border">
                    <div className="space-y-0.5">
                      <Label htmlFor="emailReminders" className="text-base">
                        Upomienky
                      </Label>
                      <p className="text-sm text-muted-foreground">
                        Pripomienky o blížiacich sa splatnostiach
                      </p>
                    </div>
                    <Switch
                      id="emailReminders"
                      checked={notifications.emailReminders}
                      onCheckedChange={(checked) =>
                        setNotifications({ ...notifications, emailReminders: checked })
                      }
                    />
                  </div>
                </div>

                <Separator />

                <div className="space-y-4">
                  <h4 className="text-lg font-semibold text-foreground">
                    Push notifikácie
                  </h4>
                  
                  <div className="flex items-center justify-between p-4 bg-card rounded-lg border border-border">
                    <div className="space-y-0.5">
                      <Label htmlFor="pushNotifications" className="text-base">
                        Povoliť push notifikácie
                      </Label>
                      <p className="text-sm text-muted-foreground">
                        Dostávať notifikácie v prehliadači
                      </p>
                    </div>
                    <Switch
                      id="pushNotifications"
                      checked={notifications.pushNotifications}
                      onCheckedChange={(checked) =>
                        setNotifications({ ...notifications, pushNotifications: checked })
                      }
                    />
                  </div>
                </div>

                <div className="flex justify-end pt-4">
                  <Button
                    onClick={handleSaveNotifications}
                    className="bg-primary hover:bg-primary/90"
                  >
                    <Save className="h-4 w-4 mr-2" />
                    Uložiť nastavenia
                  </Button>
                </div>
              </div>
            </Card>
          </TabsContent>

          {/* Security Tab */}
          <TabsContent value="security">
            <div className="space-y-6">
              <Card className="bg-gradient-card p-6 border border-border shadow-elegant-sm">
                <div className="mb-6">
                  <h3 className="text-xl font-bold text-foreground">
                    Zmena hesla
                  </h3>
                  <p className="text-sm text-muted-foreground mt-1">
                    Zabezpečte svoj účet silným heslom
                  </p>
                </div>

                <form onSubmit={handleSavePassword} className="space-y-6">
                  <div className="space-y-2">
                    <Label htmlFor="currentPassword">Súčasné heslo *</Label>
                    <Input
                      id="currentPassword"
                      type="password"
                      placeholder="••••••••"
                      value={currentPassword}
                      onChange={(e) => setCurrentPassword(e.target.value)}
                      disabled={isChangingPassword}
                      autoComplete="current-password"
                    />
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="newPassword">Nové heslo *</Label>
                    <Input
                      id="newPassword"
                      type="password"
                      placeholder="••••••••"
                      value={newPassword}
                      onChange={(e) => setNewPassword(e.target.value)}
                      disabled={isChangingPassword}
                      autoComplete="new-password"
                    />
                    <p className="text-sm text-muted-foreground">
                      Heslo musí obsahovať aspoň 8 znakov
                    </p>
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="confirmPassword">Potvrďte nové heslo *</Label>
                    <Input
                      id="confirmPassword"
                      type="password"
                      placeholder="••••••••"
                      value={confirmPassword}
                      onChange={(e) => setConfirmPassword(e.target.value)}
                      disabled={isChangingPassword}
                      autoComplete="new-password"
                    />
                  </div>

                  <div className="flex justify-end pt-4">
                    <Button
                      type="submit"
                      className="bg-primary hover:bg-primary/90"
                      disabled={isChangingPassword}
                    >
                      {isChangingPassword ? (
                        <>
                          <Loader2 className="h-4 w-4 mr-2 animate-spin" />
                          Mením heslo...
                        </>
                      ) : (
                        <>
                          <Lock className="h-4 w-4 mr-2" />
                          Zmeniť heslo
                        </>
                      )}
                    </Button>
                  </div>
                </form>
              </Card>

              <Card className="bg-gradient-card p-6 border border-border shadow-elegant-sm">
                <div className="mb-6">
                  <h3 className="text-xl font-bold text-foreground">
                    Vzhľad aplikácie
                  </h3>
                  <p className="text-sm text-muted-foreground mt-1">
                    Prispôsobte si vzhľad podľa svojich preferencií
                  </p>
                </div>

                <div className="flex items-center justify-between p-4 bg-card rounded-lg border border-border">
                  <div className="space-y-0.5">
                    <Label htmlFor="darkMode" className="text-base flex items-center gap-2">
                      {theme === "dark" ? (
                        <Moon className="h-5 w-5" />
                      ) : (
                        <Sun className="h-5 w-5" />
                      )}
                      {theme === "dark" ? "Tmavý režim" : "Svetlý režim"}
                    </Label>
                    <p className="text-sm text-muted-foreground">
                      Prepnúť medzi svetlým a tmavým režimom
                    </p>
                  </div>
                  <Switch
                    id="darkMode"
                    checked={theme === "dark"}
                    onCheckedChange={(checked) => setTheme(checked ? "dark" : "light")}
                  />
                </div>
              </Card>

              <Card className="bg-gradient-card p-6 border-2 border-destructive/30 shadow-elegant-sm">
                <div className="mb-4">
                  <h3 className="text-xl font-bold text-destructive">
                    Nebezpečná zóna
                  </h3>
                  <p className="text-sm text-muted-foreground mt-1">
                    Nenávratné akcie
                  </p>
                </div>
                <Button
                  variant="destructive"
                  onClick={() => setDeleteDialogOpen(true)}
                >
                  Vymazať účet
                </Button>
                <p className="text-sm text-muted-foreground mt-2">
                  Týmto natrvalo vymažete svoj účet a všetky dáta
                </p>
              </Card>
            </div>
          </TabsContent>
        </Tabs>
      </div>
      )}

      <InvoicePreview
        open={previewOpen}
        onOpenChange={setPreviewOpen}
        invoiceId={previewInvoiceId}
      />

      {/* Account Deletion Confirmation Dialog */}
      <AlertDialog open={deleteDialogOpen} onOpenChange={setDeleteDialogOpen}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle className="text-destructive">
              Vymazať účet?
            </AlertDialogTitle>
            <AlertDialogDescription>
              Táto akcia je nenávratná. Všetky vaše dáta vrátane faktúr, kontaktov a nastavení budú natrvalo vymazané.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <div className="space-y-2 py-4">
            <Label htmlFor="deletePassword">Zadajte heslo pre potvrdenie *</Label>
            <Input
              id="deletePassword"
              type="password"
              placeholder="••••••••"
              value={deletePassword}
              onChange={(e) => setDeletePassword(e.target.value)}
              disabled={isDeletingAccount}
              autoComplete="current-password"
            />
          </div>
          <AlertDialogFooter>
            <AlertDialogCancel
              disabled={isDeletingAccount}
              onClick={() => {
                setDeletePassword("");
                setDeleteDialogOpen(false);
              }}
            >
              Zrušiť
            </AlertDialogCancel>
            <Button
              variant="destructive"
              onClick={handleDeleteAccount}
              disabled={isDeletingAccount}
            >
              {isDeletingAccount ? (
                <>
                  <Loader2 className="h-4 w-4 mr-2 animate-spin" />
                  Vymazávam...
                </>
              ) : (
                "Vymazať účet"
              )}
            </Button>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </DashboardLayout>
  );
};

export default Settings;
