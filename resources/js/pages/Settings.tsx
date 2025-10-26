import { useState } from "react";
import { DashboardLayout } from "@/components/dashboard/DashboardLayout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Switch } from "@/components/ui/switch";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Card } from "@/components/ui/card";
import { Separator } from "@/components/ui/separator";
import { useToast } from "@/hooks/use-toast";
import { InvoicePreview } from "@/components/Invoice/InvoicePreview";
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
} from "lucide-react";

const Settings = () => {
  const { toast } = useToast();
  const [isDarkMode, setIsDarkMode] = useState(false);
  const [invoiceTemplate, setInvoiceTemplate] = useState(
    localStorage.getItem('invoiceTemplate') || 'classic'
  );
  const [previewOpen, setPreviewOpen] = useState(false);
  const [previewInvoiceId, setPreviewInvoiceId] = useState<number | null>(null);
  const [notifications, setNotifications] = useState({
    emailInvoices: true,
    emailPayments: true,
    emailReminders: true,
    pushNotifications: false,
  });

  const handlePreview = (template: string) => {
    // Set template for preview - will be restored when dialog closes
    localStorage.setItem('invoiceTemplate', template);
    setPreviewInvoiceId(1); // Use a demo invoice ID
    setPreviewOpen(true);
  };

  const handleSaveProfile = (e: React.FormEvent) => {
    e.preventDefault();
    toast({
      title: "Profil uložený",
      description: "Vaše zmeny boli úspešne uložené.",
    });
  };

  const handleSaveCompany = (e: React.FormEvent) => {
    e.preventDefault();
    localStorage.setItem('invoiceTemplate', invoiceTemplate);
    toast({
      title: "Firemné údaje uložené",
      description: "Údaje vašej firmy boli úspešne aktualizované.",
    });
  };

  const handleSavePassword = (e: React.FormEvent) => {
    e.preventDefault();
    toast({
      title: "Heslo zmenené",
      description: "Vaše heslo bolo úspešne zmenené.",
    });
  };

  const handleSaveNotifications = () => {
    toast({
      title: "Nastavenia uložené",
      description: "Vaše notifikačné preferencie boli aktualizované.",
    });
  };

  return (
    <DashboardLayout disableLoading={true}>
      <div className="max-w-5xl mx-auto space-y-6 animate-fade-in">
        <div>
          <h1 className="text-3xl font-bold text-foreground">Nastavenia</h1>
          <p className="text-muted-foreground mt-1">
            Spravujte svoj účet a preferencie
          </p>
        </div>

        <Tabs defaultValue="profile" className="space-y-6">
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
                      defaultValue="Ján"
                      placeholder="Vaše meno"
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="lastName">Priezvisko *</Label>
                    <Input
                      id="lastName"
                      defaultValue="Novák"
                      placeholder="Vaše priezvisko"
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="email">Email *</Label>
                    <Input
                      id="email"
                      type="email"
                      defaultValue="jan.novak@example.sk"
                      placeholder="váš@email.sk"
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="phone">Telefón</Label>
                    <Input
                      id="phone"
                      defaultValue="+421 902 123 456"
                      placeholder="+421"
                    />
                  </div>
                </div>

                <div className="flex justify-end pt-4">
                  <Button type="submit" className="bg-primary hover:bg-primary/90">
                    <Save className="h-4 w-4 mr-2" />
                    Uložiť zmeny
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
                    defaultValue="Vaša firma s.r.o."
                    placeholder="Názov firmy"
                  />
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div className="space-y-2">
                    <Label htmlFor="companyIco">IČO *</Label>
                    <Input
                      id="companyIco"
                      defaultValue="87654321"
                      placeholder="12345678"
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="companyDic">DIČ *</Label>
                    <Input
                      id="companyDic"
                      defaultValue="9876543210"
                      placeholder="2023456789"
                    />
                  </div>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="companyAddress">Adresa *</Label>
                  <Input
                    id="companyAddress"
                    defaultValue="Podnikateľská 456, 811 02 Bratislava"
                    placeholder="Ulica, PSČ Mesto"
                  />
                </div>

                <Separator />

                <div className="space-y-6">
                  <h4 className="text-lg font-semibold text-foreground">
                    Bankové údaje
                  </h4>
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div className="space-y-2">
                      <Label htmlFor="iban">IBAN *</Label>
                      <Input
                        id="iban"
                        defaultValue="SK12 3456 7890 1234 5678 9012"
                        placeholder="SK00 0000 0000 0000 0000 0000"
                      />
                    </div>
                    <div className="space-y-2">
                      <Label htmlFor="swift">SWIFT/BIC</Label>
                      <Input
                        id="swift"
                        defaultValue="SUBASKBX"
                        placeholder="SWIFT kód"
                      />
                    </div>
                  </div>
                </div>

                <div className="flex justify-end pt-4">
                  <Button type="submit" className="bg-primary hover:bg-primary/90">
                    <Save className="h-4 w-4 mr-2" />
                    Uložiť zmeny
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
                    />
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="newPassword">Nové heslo *</Label>
                    <Input
                      id="newPassword"
                      type="password"
                      placeholder="••••••••"
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
                    />
                  </div>

                  <div className="flex justify-end pt-4">
                    <Button type="submit" className="bg-primary hover:bg-primary/90">
                      <Lock className="h-4 w-4 mr-2" />
                      Zmeniť heslo
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
                      {isDarkMode ? (
                        <Moon className="h-5 w-5" />
                      ) : (
                        <Sun className="h-5 w-5" />
                      )}
                      {isDarkMode ? "Tmavý režim" : "Svetlý režim"}
                    </Label>
                    <p className="text-sm text-muted-foreground">
                      Prepnúť medzi svetlým a tmavým režimom
                    </p>
                  </div>
                  <Switch
                    id="darkMode"
                    checked={isDarkMode}
                    onCheckedChange={setIsDarkMode}
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
                <Button variant="destructive">
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

      <InvoicePreview
        open={previewOpen}
        onOpenChange={(open) => {
          setPreviewOpen(open);
          // Restore original template when closing
          if (!open) {
            localStorage.setItem('invoiceTemplate', invoiceTemplate);
          }
        }}
        invoiceId={previewInvoiceId}
      />
    </DashboardLayout>
  );
};

export default Settings;
