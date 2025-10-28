import { useState } from "react";
import { Link } from "react-router-dom";
import { DashboardLayout } from "@/components/dashboard/DashboardLayout";
import { InvoicePreview } from "@/components/Invoice/InvoicePreview";
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
import { Plus, Search, Eye, Download, MoreVertical, Pencil, Loader2 } from "lucide-react";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { useInvoices } from "@/hooks/useInvoices";
import type { Invoice } from "@/types";

const Invoices = () => {
  const [searchQuery, setSearchQuery] = useState("");
  const [previewOpen, setPreviewOpen] = useState(false);
  const [selectedInvoice, setSelectedInvoice] = useState<number | null>(null);

  const { invoices, isLoading, downloadPdf } = useInvoices();

  const getStatusBadge = (status: string) => {
    const variants = {
      paid: "bg-green-100 text-green-700 border-green-200",
      sent: "bg-blue-100 text-blue-700 border-blue-200",
      draft: "bg-gray-100 text-gray-700 border-gray-200",
      overdue: "bg-red-100 text-red-700 border-red-200",
      cancelled: "bg-red-100 text-red-700 border-red-200",
    };

    const labels = {
      paid: "Zaplatené",
      sent: "Odoslaná",
      draft: "Koncept",
      overdue: "Po splatnosti",
      cancelled: "Zrušená",
    };

    return (
      <Badge className={variants[status as keyof typeof variants]}>
        {labels[status as keyof typeof labels]}
      </Badge>
    );
  };

  const filteredInvoices = invoices.filter(
    (invoice) =>
      invoice.invoice_number.toLowerCase().includes(searchQuery.toLowerCase()) ||
      invoice.business_entity?.name.toLowerCase().includes(searchQuery.toLowerCase())
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
          <Link to="/app/invoices/new">
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
              {isLoading ? <Loader2 className="h-6 w-6 animate-spin" /> : invoices.length}
            </p>
          </div>
          <div className="bg-gradient-card rounded-xl p-6 border border-border shadow-elegant-sm">
            <p className="text-sm text-muted-foreground">Zaplatené</p>
            <p className="text-2xl font-bold text-green-600 mt-2">
              {isLoading ? <Loader2 className="h-6 w-6 animate-spin" /> : invoices.filter((i) => i.status === "paid").length}
            </p>
          </div>
          <div className="bg-gradient-card rounded-xl p-6 border border-border shadow-elegant-sm">
            <p className="text-sm text-muted-foreground">Odoslané</p>
            <p className="text-2xl font-bold text-blue-600 mt-2">
              {isLoading ? <Loader2 className="h-6 w-6 animate-spin" /> : invoices.filter((i) => i.status === "sent").length}
            </p>
          </div>
          <div className="bg-gradient-card rounded-xl p-6 border border-border shadow-elegant-sm">
            <p className="text-sm text-muted-foreground">Po splatnosti</p>
            <p className="text-2xl font-bold text-red-600 mt-2">
              {isLoading ? <Loader2 className="h-6 w-6 animate-spin" /> : invoices.filter((i) => i.status === "overdue").length}
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
              {isLoading ? (
                <TableRow>
                  <TableCell colSpan={7} className="text-center py-8">
                    <Loader2 className="h-8 w-8 animate-spin mx-auto" />
                  </TableCell>
                </TableRow>
              ) : filteredInvoices.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={7} className="text-center py-8 text-muted-foreground">
                    Žiadne faktúry
                  </TableCell>
                </TableRow>
              ) : (
                filteredInvoices.map((invoice) => (
                  <TableRow key={invoice.id}>
                    <TableCell className="font-medium">{invoice.invoice_number}</TableCell>
                    <TableCell>{invoice.business_entity?.name || 'N/A'}</TableCell>
                    <TableCell>{new Date(invoice.issue_date).toLocaleDateString('sk-SK')}</TableCell>
                    <TableCell>{new Date(invoice.due_date).toLocaleDateString('sk-SK')}</TableCell>
                    <TableCell className="font-semibold">
                      {Number(invoice.total_amount).toFixed(2)} {invoice.currency}
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
                            <Link to={`/app/invoices/edit/${invoice.id}`}>
                              <Pencil className="h-4 w-4 mr-2" />
                              Upraviť
                            </Link>
                          </DropdownMenuItem>
                          <DropdownMenuItem onClick={() => downloadPdf(invoice.id)}>
                            <Download className="h-4 w-4 mr-2" />
                            Stiahnuť PDF
                          </DropdownMenuItem>
                        </DropdownMenuContent>
                      </DropdownMenu>
                    </TableCell>
                  </TableRow>
                ))
              )}
            </TableBody>
          </Table>
        </div>
      </div>

      <InvoicePreview
        open={previewOpen}
        onOpenChange={setPreviewOpen}
        invoiceId={selectedInvoice}
      />
    </DashboardLayout>
  );
};

export default Invoices;
