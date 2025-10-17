import { useState } from "react";
import { Link } from "react-router-dom";
import { DashboardLayout } from "@/components/dashboard/DashboardLayout";
import { InvoicePreview } from "@/components/invoice/InvoicePreview";
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
import { Plus, Search, Eye, Download, MoreVertical, Pencil } from "lucide-react";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";

const invoicesData = [
  {
    id: "INV-001",
    client: "ABC s.r.o.",
    date: "15.10.2025",
    dueDate: "30.10.2025",
    amount: 1920,
    status: "paid",
  },
  {
    id: "INV-002",
    client: "Tech Solutions",
    date: "18.10.2025",
    dueDate: "02.11.2025",
    amount: 3450,
    status: "pending",
  },
  {
    id: "INV-003",
    client: "Digital Marketing Ltd.",
    date: "20.10.2025",
    dueDate: "04.11.2025",
    amount: 2100,
    status: "pending",
  },
  {
    id: "INV-004",
    client: "Startup Hub",
    date: "12.10.2025",
    dueDate: "27.10.2025",
    amount: 1500,
    status: "overdue",
  },
  {
    id: "INV-005",
    client: "Corporate Design",
    date: "10.10.2025",
    dueDate: "25.10.2025",
    amount: 5200,
    status: "paid",
  },
];

const Invoices = () => {
  const [searchQuery, setSearchQuery] = useState("");
  const [previewOpen, setPreviewOpen] = useState(false);
  const [selectedInvoice, setSelectedInvoice] = useState<string | null>(null);

  const getStatusBadge = (status: string) => {
    const variants = {
      paid: "bg-green-100 text-green-700 border-green-200",
      pending: "bg-yellow-100 text-yellow-700 border-yellow-200",
      overdue: "bg-red-100 text-red-700 border-red-200",
    };

    const labels = {
      paid: "Zaplatené",
      pending: "Čaká na platbu",
      overdue: "Po splatnosti",
    };

    return (
      <Badge className={variants[status as keyof typeof variants]}>
        {labels[status as keyof typeof labels]}
      </Badge>
    );
  };

  const filteredInvoices = invoicesData.filter(
    (invoice) =>
      invoice.id.toLowerCase().includes(searchQuery.toLowerCase()) ||
      invoice.client.toLowerCase().includes(searchQuery.toLowerCase())
  );

  return (
    <DashboardLayout>
      <div className="space-y-6 animate-fade-in">
        <div className="flex justify-between items-center">
          <div>
            <h1 className="text-3xl font-bold text-foreground">Faktúry</h1>
            <p className="text-muted-foreground mt-1">
              Spravujte a sledujte všetky vaše faktúry
            </p>
          </div>
          <Link to="/invoices/new">
            <Button className="bg-purple-600 hover:bg-purple-700">
              <Plus className="h-4 w-4 mr-2" />
              Nová faktúra
            </Button>
          </Link>
        </div>

        {/* Stats Cards */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div className="bg-gradient-card rounded-xl p-6 border border-border shadow-elegant-sm">
            <p className="text-sm text-muted-foreground">Celkom faktúr</p>
            <p className="text-2xl font-bold text-foreground mt-2">
              {invoicesData.length}
            </p>
          </div>
          <div className="bg-gradient-card rounded-xl p-6 border border-border shadow-elegant-sm">
            <p className="text-sm text-muted-foreground">Zaplatené</p>
            <p className="text-2xl font-bold text-green-600 mt-2">
              {invoicesData.filter((i) => i.status === "paid").length}
            </p>
          </div>
          <div className="bg-gradient-card rounded-xl p-6 border border-border shadow-elegant-sm">
            <p className="text-sm text-muted-foreground">Čaká na platbu</p>
            <p className="text-2xl font-bold text-yellow-600 mt-2">
              {invoicesData.filter((i) => i.status === "pending").length}
            </p>
          </div>
          <div className="bg-gradient-card rounded-xl p-6 border border-border shadow-elegant-sm">
            <p className="text-sm text-muted-foreground">Po splatnosti</p>
            <p className="text-2xl font-bold text-red-600 mt-2">
              {invoicesData.filter((i) => i.status === "overdue").length}
            </p>
          </div>
        </div>

        {/* Search and Filter */}
        <div className="bg-gradient-card rounded-xl p-6 border border-border shadow-elegant-sm">
          <div className="flex gap-4 items-center">
            <div className="relative flex-1">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input
                placeholder="Hľadať faktúry..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="pl-10"
              />
            </div>
          </div>
        </div>

        {/* Invoices Table */}
        <div className="bg-gradient-card rounded-xl border border-border shadow-elegant-sm overflow-hidden">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Číslo faktúry</TableHead>
                <TableHead>Klient</TableHead>
                <TableHead>Dátum vystavenia</TableHead>
                <TableHead>Splatnosť</TableHead>
                <TableHead>Suma</TableHead>
                <TableHead>Stav</TableHead>
                <TableHead className="text-right">Akcie</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {filteredInvoices.map((invoice) => (
                <TableRow key={invoice.id}>
                  <TableCell className="font-medium">{invoice.id}</TableCell>
                  <TableCell>{invoice.client}</TableCell>
                  <TableCell>{invoice.date}</TableCell>
                  <TableCell>{invoice.dueDate}</TableCell>
                  <TableCell className="font-semibold">
                    €{invoice.amount.toFixed(2)}
                  </TableCell>
                  <TableCell>{getStatusBadge(invoice.status)}</TableCell>
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
                            setSelectedInvoice(invoice.id);
                            setPreviewOpen(true);
                          }}
                        >
                          <Eye className="h-4 w-4 mr-2" />
                          Náhľad
                        </DropdownMenuItem>
                        <DropdownMenuItem asChild>
                          <Link to={`/invoices/edit/${invoice.id}`}>
                            <Pencil className="h-4 w-4 mr-2" />
                            Upraviť
                          </Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem>
                          <Download className="h-4 w-4 mr-2" />
                          Stiahnuť PDF
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

      <InvoicePreview open={previewOpen} onOpenChange={setPreviewOpen} />
    </DashboardLayout>
  );
};

export default Invoices;
