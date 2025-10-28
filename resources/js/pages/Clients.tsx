import { useState } from "react";
import { DashboardLayout } from "@/components/dashboard/DashboardLayout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { Badge } from "@/components/ui/badge";
import { Plus, Search, Eye, Edit, MoreVertical, Building2 } from "lucide-react";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { Label } from "@/components/ui/label";

const clientsData = [
  {
    id: "1",
    name: "ABC s.r.o.",
    email: "kontakt@abc.sk",
    phone: "+421 902 123 456",
    address: "Hlavná 123, 811 01 Bratislava",
    ico: "12345678",
    dic: "2023456789",
    totalInvoices: 12,
    totalAmount: 15400,
    status: "active",
  },
  {
    id: "2",
    name: "Tech Solutions",
    email: "info@techsolutions.sk",
    phone: "+421 905 234 567",
    address: "Technická 45, 040 01 Košice",
    ico: "23456789",
    dic: "2034567890",
    totalInvoices: 8,
    totalAmount: 9200,
    status: "active",
  },
  {
    id: "3",
    name: "Digital Marketing Ltd.",
    email: "hello@digitalmarketing.sk",
    phone: "+421 908 345 678",
    address: "Digitálna 67, 949 01 Nitra",
    ico: "34567890",
    dic: "2045678901",
    totalInvoices: 15,
    totalAmount: 22100,
    status: "active",
  },
  {
    id: "4",
    name: "Startup Hub",
    email: "contact@startuphub.sk",
    phone: "+421 911 456 789",
    address: "Inovačná 89, 010 01 Žilina",
    ico: "45678901",
    dic: "2056789012",
    totalInvoices: 5,
    totalAmount: 6500,
    status: "inactive",
  },
  {
    id: "5",
    name: "Corporate Design",
    email: "info@corpdesign.sk",
    phone: "+421 903 567 890",
    address: "Korporátna 12, 811 03 Bratislava",
    ico: "56789012",
    dic: "2067890123",
    totalInvoices: 20,
    totalAmount: 31800,
    status: "active",
  },
];

const Clients = () => {
  const [searchQuery, setSearchQuery] = useState("");
  const [isAddClientOpen, setIsAddClientOpen] = useState(false);
  const [isDetailOpen, setIsDetailOpen] = useState(false);
  const [isEditOpen, setIsEditOpen] = useState(false);
  const [selectedClient, setSelectedClient] = useState<typeof clientsData[0] | null>(null);

  const getStatusBadge = (status: string) => {
    if (status === "active") {
      return (
        <Badge className="bg-success/10 text-success border-success/30">
          Aktívny
        </Badge>
      );
    }
    return (
      <Badge className="bg-muted text-muted-foreground border-border">
        Neaktívny
      </Badge>
    );
  };

  const filteredClients = clientsData.filter(
    (client) =>
      client.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
      client.email.toLowerCase().includes(searchQuery.toLowerCase()) ||
      client.ico.includes(searchQuery)
  );

  return (
    <DashboardLayout>
      <div className="space-y-6 animate-fade-in">
        <div className="flex justify-between items-center">
          <div>
            <h1 className="text-3xl font-bold text-foreground">Klienti</h1>
            <p className="text-muted-foreground mt-1">
              Spravujte databázu vašich klientov
            </p>
          </div>
          <Button
            onClick={() => setIsAddClientOpen(true)}
            className="bg-primary hover:bg-primary/90"
          >
            <Plus className="h-4 w-4 mr-2" />
            Nový klient
          </Button>
        </div>

        {/* Stats Cards */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div className="bg-gradient-card rounded-xl p-6 border border-border shadow-elegant-sm">
            <p className="text-sm text-muted-foreground">Celkom klientov</p>
            <p className="text-2xl font-bold text-foreground mt-2">
              {clientsData.length}
            </p>
          </div>
          <div className="bg-gradient-card rounded-xl p-6 border border-border shadow-elegant-sm">
            <p className="text-sm text-muted-foreground">Aktívnych</p>
            <p className="text-2xl font-bold text-success mt-2">
              {clientsData.filter((c) => c.status === "active").length}
            </p>
          </div>
          <div className="bg-gradient-card rounded-xl p-6 border border-border shadow-elegant-sm">
            <p className="text-sm text-muted-foreground">Celkový obrat</p>
            <p className="text-2xl font-bold text-primary mt-2">
              €
              {clientsData
                .reduce((sum, c) => sum + c.totalAmount, 0)
                .toLocaleString()}
            </p>
          </div>
          <div className="bg-gradient-card rounded-xl p-6 border border-border shadow-elegant-sm">
            <p className="text-sm text-muted-foreground">Priemerná hodnota</p>
            <p className="text-2xl font-bold text-accent mt-2">
              €
              {Math.round(
                clientsData.reduce((sum, c) => sum + c.totalAmount, 0) /
                  clientsData.length
              ).toLocaleString()}
            </p>
          </div>
        </div>

        {/* Search */}
        <div className="bg-gradient-card rounded-xl p-6 border border-border shadow-elegant-sm">
          <div className="flex gap-4 items-center">
            <div className="relative flex-1">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input
                placeholder="Hľadať klientov..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="pl-10"
              />
            </div>
          </div>
        </div>

        {/* Clients Table */}
        <div className="bg-gradient-card rounded-xl border border-border shadow-elegant-sm overflow-hidden">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Klient</TableHead>
                <TableHead>Kontakt</TableHead>
                <TableHead>IČO / DIČ</TableHead>
                <TableHead>Faktúr</TableHead>
                <TableHead>Celková suma</TableHead>
                <TableHead>Stav</TableHead>
                <TableHead className="text-right">Akcie</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {filteredClients.map((client) => (
                <TableRow key={client.id}>
                  <TableCell>
                    <div className="flex items-center gap-3">
                      <div className="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                        <Building2 className="w-5 h-5 text-primary" />
                      </div>
                      <div>
                        <p className="font-medium">{client.name}</p>
                        <p className="text-sm text-muted-foreground">
                          {client.address}
                        </p>
                      </div>
                    </div>
                  </TableCell>
                  <TableCell>
                    <div>
                      <p className="text-sm">{client.email}</p>
                      <p className="text-sm text-muted-foreground">
                        {client.phone}
                      </p>
                    </div>
                  </TableCell>
                  <TableCell>
                    <div>
                      <p className="text-sm">IČO: {client.ico}</p>
                      <p className="text-sm text-muted-foreground">
                        DIČ: {client.dic}
                      </p>
                    </div>
                  </TableCell>
                  <TableCell className="font-semibold">
                    {client.totalInvoices}
                  </TableCell>
                  <TableCell className="font-semibold text-primary">
                    €{client.totalAmount.toLocaleString()}
                  </TableCell>
                  <TableCell>{getStatusBadge(client.status)}</TableCell>
                  <TableCell className="text-right">
                    <DropdownMenu>
                      <DropdownMenuTrigger asChild>
                        <Button variant="ghost" size="icon">
                          <MoreVertical className="h-4 w-4" />
                        </Button>
                      </DropdownMenuTrigger>
                      <DropdownMenuContent align="end">
                        <DropdownMenuItem
                          onClick={() => {
                            setSelectedClient(client);
                            setIsDetailOpen(true);
                          }}
                        >
                          <Eye className="h-4 w-4 mr-2" />
                          Detail
                        </DropdownMenuItem>
                        <DropdownMenuItem
                          onClick={() => {
                            setSelectedClient(client);
                            setIsEditOpen(true);
                          }}
                        >
                          <Edit className="h-4 w-4 mr-2" />
                          Upraviť
                        </DropdownMenuItem>
                      </DropdownMenuContent>
                    </DropdownMenu>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </div>
      </div>

      {/* Add Client Dialog */}
      <Dialog open={isAddClientOpen} onOpenChange={setIsAddClientOpen}>
        <DialogContent className="max-w-2xl">
          <DialogHeader>
            <DialogTitle className="text-2xl font-bold text-primary">
              Nový klient
            </DialogTitle>
          </DialogHeader>
          <form className="space-y-6">
            <div className="bg-gradient-card rounded-xl p-6 border-2 border-primary/30">
              <h3 className="text-lg font-bold text-primary mb-4">
                Základné informácie
              </h3>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="space-y-2 md:col-span-2">
                  <Label htmlFor="clientName">Názov / Meno *</Label>
                  <Input
                    id="clientName"
                    placeholder="ABC s.r.o."
                    className="border-primary/30"
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="email">Email *</Label>
                  <Input
                    id="email"
                    type="email"
                    placeholder="kontakt@abc.sk"
                    className="border-primary/30"
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="phone">Telefón</Label>
                  <Input
                    id="phone"
                    placeholder="+421 902 123 456"
                    className="border-primary/30"
                  />
                </div>
                <div className="space-y-2 md:col-span-2">
                  <Label htmlFor="address">Adresa *</Label>
                  <Input
                    id="address"
                    placeholder="Hlavná 123, 811 01 Bratislava"
                    className="border-primary/30"
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="ico">IČO *</Label>
                  <Input
                    id="ico"
                    placeholder="12345678"
                    className="border-primary/30"
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="dic">DIČ *</Label>
                  <Input
                    id="dic"
                    placeholder="2023456789"
                    className="border-primary/30"
                  />
                </div>
              </div>
            </div>

            <div className="flex justify-end gap-3">
              <Button
                type="button"
                variant="outline"
                onClick={() => setIsAddClientOpen(false)}
              >
                Zrušiť
              </Button>
              <Button
                type="submit"
                className="bg-primary hover:bg-primary/90"
              >
                Pridať klienta
              </Button>
            </div>
          </form>
        </DialogContent>
      </Dialog>

      {/* Client Detail Dialog */}
      <Dialog open={isDetailOpen} onOpenChange={setIsDetailOpen}>
        <DialogContent className="max-w-2xl">
          <DialogHeader>
            <DialogTitle className="text-2xl font-bold text-primary">
              Detail klienta
            </DialogTitle>
          </DialogHeader>
          {selectedClient && (
            <div className="space-y-6">
              <div className="bg-gradient-card rounded-xl p-6 border-2 border-primary/30">
                <div className="flex items-center gap-4 mb-6">
                  <div className="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center">
                    <Building2 className="w-8 h-8 text-primary" />
                  </div>
                  <div>
                    <h3 className="text-xl font-bold text-foreground">
                      {selectedClient.name}
                    </h3>
                    <p className="text-sm text-muted-foreground">
                      {getStatusBadge(selectedClient.status)}
                    </p>
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <p className="text-sm text-muted-foreground mb-1">Email</p>
                    <p className="font-semibold">{selectedClient.email}</p>
                  </div>
                  <div>
                    <p className="text-sm text-muted-foreground mb-1">Telefón</p>
                    <p className="font-semibold">{selectedClient.phone}</p>
                  </div>
                  <div className="col-span-2">
                    <p className="text-sm text-muted-foreground mb-1">Adresa</p>
                    <p className="font-semibold">{selectedClient.address}</p>
                  </div>
                  <div>
                    <p className="text-sm text-muted-foreground mb-1">IČO</p>
                    <p className="font-semibold">{selectedClient.ico}</p>
                  </div>
                  <div>
                    <p className="text-sm text-muted-foreground mb-1">DIČ</p>
                    <p className="font-semibold">{selectedClient.dic}</p>
                  </div>
                </div>
              </div>

              <div className="bg-gradient-card rounded-xl p-6 border-2 border-border">
                <h4 className="text-lg font-bold text-foreground mb-4">Štatistiky</h4>
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <p className="text-sm text-muted-foreground mb-1">Počet faktúr</p>
                    <p className="text-2xl font-bold text-primary">
                      {selectedClient.totalInvoices}
                    </p>
                  </div>
                  <div>
                    <p className="text-sm text-muted-foreground mb-1">Celková suma</p>
                    <p className="text-2xl font-bold text-success">
                      €{selectedClient.totalAmount.toLocaleString()}
                    </p>
                  </div>
                </div>
              </div>

              <div className="flex justify-end gap-3">
                <Button
                  variant="outline"
                  onClick={() => setIsDetailOpen(false)}
                >
                  Zavrieť
                </Button>
                <Button
                  className="bg-primary hover:bg-primary/90"
                  onClick={() => {
                    setIsDetailOpen(false);
                    setIsEditOpen(true);
                  }}
                >
                  <Edit className="h-4 w-4 mr-2" />
                  Upraviť
                </Button>
              </div>
            </div>
          )}
        </DialogContent>
      </Dialog>

      {/* Edit Client Dialog */}
      <Dialog open={isEditOpen} onOpenChange={setIsEditOpen}>
        <DialogContent className="max-w-2xl">
          <DialogHeader>
            <DialogTitle className="text-2xl font-bold text-primary">
              Upraviť klienta
            </DialogTitle>
          </DialogHeader>
          {selectedClient && (
            <form className="space-y-6">
              <div className="bg-gradient-card rounded-xl p-6 border-2 border-primary/30">
                <h3 className="text-lg font-bold text-primary mb-4">
                  Základné informácie
                </h3>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div className="space-y-2 md:col-span-2">
                    <Label htmlFor="editClientName">Názov / Meno *</Label>
                    <Input
                      id="editClientName"
                      defaultValue={selectedClient.name}
                      className="border-primary/30"
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="editEmail">Email *</Label>
                    <Input
                      id="editEmail"
                      type="email"
                      defaultValue={selectedClient.email}
                      className="border-primary/30"
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="editPhone">Telefón</Label>
                    <Input
                      id="editPhone"
                      defaultValue={selectedClient.phone}
                      className="border-primary/30"
                    />
                  </div>
                  <div className="space-y-2 md:col-span-2">
                    <Label htmlFor="editAddress">Adresa *</Label>
                    <Input
                      id="editAddress"
                      defaultValue={selectedClient.address}
                      className="border-primary/30"
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="editIco">IČO *</Label>
                    <Input
                      id="editIco"
                      defaultValue={selectedClient.ico}
                      className="border-primary/30"
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="editDic">DIČ *</Label>
                    <Input
                      id="editDic"
                      defaultValue={selectedClient.dic}
                      className="border-primary/30"
                    />
                  </div>
                </div>
              </div>

              <div className="flex justify-end gap-3">
                <Button
                  type="button"
                  variant="outline"
                  onClick={() => setIsEditOpen(false)}
                >
                  Zrušiť
                </Button>
                <Button
                  type="submit"
                  className="bg-primary hover:bg-primary/90"
                >
                  Uložiť zmeny
                </Button>
              </div>
            </form>
          )}
        </DialogContent>
      </Dialog>
    </DashboardLayout>
  );
};

export default Clients;
