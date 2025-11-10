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
import { Plus, Search, Eye, Download, MoreVertical, Pencil, Loader2, CheckCircle2, Clock, AlertCircle, FileText, XCircle, Send } from "lucide-react";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
  DropdownMenuSeparator,
  DropdownMenuLabel,
} from "@/components/ui/dropdown-menu";
import { useInvoices } from "@/hooks/useInvoices";
import type { Invoice } from "@/types";
import { useToast } from "@/hooks/use-toast";
import { invoiceService } from "@/services/invoiceService";
import { useQueryClient } from "@tanstack/react-query";
import type { LucideIcon } from "lucide-react";

// Type definition for invoice statuses
type InvoiceStatus = 'draft' | 'sent' | 'paid' | 'overdue' | 'cancelled';

// Centralized status configuration
interface StatusConfig {
  label: string;
  icon: LucideIcon;
  iconColor: string;
  badgeClasses: string;
}

const STATUS_CONFIG: Record<InvoiceStatus, StatusConfig> = {
  draft: {
    label: 'Koncept',
    icon: FileText,
    iconColor: 'text-gray-600',
    badgeClasses: 'bg-gray-100 text-gray-700 border-gray-200',
  },
  sent: {
    label: 'Odoslaná',
    icon: Send,
    iconColor: 'text-blue-600',
    badgeClasses: 'bg-blue-100 text-blue-700 border-blue-200',
  },
  paid: {
    label: 'Zaplatená',
    icon: CheckCircle2,
    iconColor: 'text-green-600',
    badgeClasses: 'bg-green-100 text-green-700 border-green-200',
  },
  overdue: {
    label: 'Po splatnosti',
    icon: AlertCircle,
    iconColor: 'text-red-600',
    badgeClasses: 'bg-red-100 text-red-700 border-red-200',
  },
  cancelled: {
    label: 'Zrušená',
    icon: XCircle,
    iconColor: 'text-red-600',
    badgeClasses: 'bg-red-100 text-red-700 border-red-200',
  },
};

const Invoices = () => {
  const [searchQuery, setSearchQuery] = useState("");
  const [previewOpen, setPreviewOpen] = useState(false);
  const [selectedInvoice, setSelectedInvoice] = useState<number | null>(null);
  const [updatingStatus, setUpdatingStatus] = useState<number | null>(null);

  const { invoices, isLoading, downloadPdf } = useInvoices();
  const { toast } = useToast();
  const queryClient = useQueryClient();

  const handleStatusChange = async (invoiceId: number, newStatus: InvoiceStatus) => {
    // Confirmation for cancelled status
    if (newStatus === 'cancelled') {
      const confirmed = window.confirm(
        'Naozaj chcete zrušiť túto faktúru? Táto akcia je reverzibilná, status môžete opäť zmeniť.'
      );
      if (!confirmed) return;
    }

    // Store previous data for rollback
    const previousData = queryClient.getQueryData(['invoices']);

    try {
      setUpdatingStatus(invoiceId);

      // Optimistically update the UI
      queryClient.setQueryData(['invoices'], (old: any) => {
        if (!old?.data) return old;
        return {
          ...old,
          data: old.data.map((inv: Invoice) =>
            inv.id === invoiceId ? { ...inv, status: newStatus } : inv
          ),
        };
      });

      // Make the API call
      await invoiceService.updateStatus(invoiceId, newStatus);

      const statusLabel = STATUS_CONFIG[newStatus].label;
      toast({
        title: "Status aktualizovaný",
        description: `Faktúra bola označená ako: ${statusLabel}`,
      });

      // Revalidate to ensure consistency
      queryClient.invalidateQueries({ queryKey: ['invoices'] });
    } catch (error) {
      // Rollback on error
      if (previousData) {
        queryClient.setQueryData(['invoices'], previousData);
      }

      console.error('Failed to update invoice status:', error);

      // Extract error message if available
      const errorMessage = error instanceof Error ? error.message : 'Nepodarilo sa zmeniť status faktúry.';

      // Check for specific error types
      const isNetworkError = error instanceof Error && (error.message.includes('Network') || error.message.includes('network'));
      const isAuthError = error instanceof Error && error.message.includes('401');

      toast({
        title: "Chyba pri zmene statusu",
        description: isNetworkError
          ? "Problém s pripojením. Skontrolujte internetové pripojenie."
          : isAuthError
          ? "Relácia vypršala. Prosím, prihláste sa znova."
          : errorMessage,
        variant: "destructive",
      });
    } finally {
      setUpdatingStatus(null);
    }
  };

  const getStatusBadge = (status: string) => {
    const config = STATUS_CONFIG[status as InvoiceStatus];
    if (!config) return null;

    return (
      <Badge className={config.badgeClasses}>
        {config.label}
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
      {/* Screen reader announcements for status updates */}
      {updatingStatus && (
        <div role="status" aria-live="polite" aria-atomic="true" className="sr-only">
          Aktualizuje sa status faktúry číslo{" "}
          {invoices.find((i) => i.id === updatingStatus)?.invoice_number}
        </div>
      )}

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
              <Plus className="h-4 w-4 mr-2" aria-hidden="true" />
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
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" aria-hidden="true" />
              <Input
                placeholder="Hľadať faktúry..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="pl-10"
                aria-label="Vyhľadať faktúry podľa čísla alebo klienta"
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
                          <Button
                            variant="ghost"
                            size="icon"
                            disabled={updatingStatus === invoice.id}
                            className="relative"
                            aria-label="Akcie faktúry"
                          >
                            {updatingStatus === invoice.id ? (
                              <Loader2 className="h-4 w-4 animate-spin" aria-hidden="true" />
                            ) : (
                              <MoreVertical className="h-4 w-4" aria-hidden="true" />
                            )}
                          </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent
                          align="end"
                          onInteractOutside={(e) => {
                            // Prevent closing if update is in progress
                            if (updatingStatus === invoice.id) {
                              e.preventDefault();
                            }
                          }}
                        >
                          <DropdownMenuItem
                            onClick={() => {
                              setSelectedInvoice(invoice.id);
                              setPreviewOpen(true);
                            }}
                          >
                            <Eye className="h-4 w-4 mr-2" aria-hidden="true" />
                            Náhľad
                          </DropdownMenuItem>
                          <DropdownMenuItem asChild>
                            <Link to={`/app/invoices/edit/${invoice.id}`}>
                              <Pencil className="h-4 w-4 mr-2" aria-hidden="true" />
                              Upraviť
                            </Link>
                          </DropdownMenuItem>
                          <DropdownMenuItem onClick={() => downloadPdf(invoice.id)}>
                            <Download className="h-4 w-4 mr-2" aria-hidden="true" />
                            Stiahnuť PDF
                          </DropdownMenuItem>

                          <DropdownMenuSeparator />
                          <DropdownMenuLabel>Zmeniť status</DropdownMenuLabel>

                          {(Object.keys(STATUS_CONFIG) as InvoiceStatus[]).map((status) => {
                            const config = STATUS_CONFIG[status];
                            const Icon = config.icon;
                            const isCurrentStatus = invoice.status === status;
                            const isDisabled = isCurrentStatus || updatingStatus === invoice.id;

                            return (
                              <DropdownMenuItem
                                key={status}
                                onClick={() => handleStatusChange(invoice.id, status)}
                                disabled={isDisabled}
                                aria-label={
                                  isCurrentStatus
                                    ? `Status je už nastavený na ${config.label}`
                                    : `Zmeniť status na ${config.label}`
                                }
                                title={
                                  isCurrentStatus
                                    ? `Aktuálny status: ${config.label}`
                                    : updatingStatus === invoice.id
                                    ? "Čaká sa na aktualizáciu..."
                                    : undefined
                                }
                              >
                                <Icon className={`h-4 w-4 mr-2 ${config.iconColor}`} aria-hidden="true" />
                                {config.label}
                                {isCurrentStatus && (
                                  <span className="ml-auto text-xs text-muted-foreground">(aktuálny)</span>
                                )}
                              </DropdownMenuItem>
                            );
                          })}
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
