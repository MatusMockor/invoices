import { Badge } from "@/components/ui/badge";
import { FileText, Eye } from "lucide-react";
import { useState } from "react";
import { InvoicePreview } from "@/components/invoice/InvoicePreview";

const invoices = [
  { id: "INV-001", numericId: 1, client: "ABC s.r.o.", amount: "€1,200", status: "paid", date: "15.10.2025" },
  { id: "INV-002", numericId: 2, client: "XYZ company", amount: "€850", status: "pending", date: "14.10.2025" },
  { id: "INV-003", numericId: 3, client: "Tech Solutions", amount: "€2,400", status: "paid", date: "12.10.2025" },
  { id: "INV-004", numericId: 4, client: "Digital Agency", amount: "€1,650", status: "overdue", date: "08.10.2025" },
  { id: "INV-005", numericId: 5, client: "StartupCo", amount: "€920", status: "pending", date: "05.10.2025" },
];

const getStatusVariant = (status: string) => {
  switch (status) {
    case "paid":
      return "default";
    case "pending":
      return "secondary";
    case "overdue":
      return "destructive";
    default:
      return "secondary";
  }
};

const getStatusLabel = (status: string) => {
  switch (status) {
    case "paid":
      return "Zaplatené";
    case "pending":
      return "Čaká";
    case "overdue":
      return "Po termíne";
    default:
      return status;
  }
};

export const RecentInvoices = () => {
  const [previewOpen, setPreviewOpen] = useState(false);
  const [selectedInvoice, setSelectedInvoice] = useState<number | null>(null);

  const handleViewInvoice = (invoiceId: number) => {
    setSelectedInvoice(invoiceId);
    setPreviewOpen(true);
  };

  return (
    <>
      <InvoicePreview open={previewOpen} onOpenChange={setPreviewOpen} invoiceId={selectedInvoice} />
    <div className="bg-gradient-card rounded-xl p-6 border border-border shadow-elegant-sm animate-fade-in">
      <div className="flex items-center justify-between mb-6">
        <h3 className="text-lg font-semibold text-foreground">Posledné faktúry</h3>
        <button className="text-sm text-primary hover:text-primary-dark transition-colors font-medium">
          Zobraziť všetky
        </button>
      </div>

      <div className="space-y-4">
        {invoices.map((invoice) => (
          <div
            key={invoice.id}
            className="flex items-center justify-between p-4 bg-background/50 rounded-lg border border-border hover:border-primary/30 transition-all duration-200 group"
          >
            <div className="flex items-center gap-4">
              <div className="p-2 bg-primary/10 rounded-lg group-hover:bg-primary/20 transition-colors">
                <FileText className="w-5 h-5 text-primary" />
              </div>
              <div>
                <p className="font-semibold text-foreground">{invoice.id}</p>
                <p className="text-sm text-muted-foreground">{invoice.client}</p>
              </div>
            </div>

            <div className="flex items-center gap-6">
              <div className="text-right">
                <p className="font-semibold text-foreground">{invoice.amount}</p>
                <p className="text-sm text-muted-foreground">{invoice.date}</p>
              </div>
              <Badge variant={getStatusVariant(invoice.status)}>
                {getStatusLabel(invoice.status)}
              </Badge>
              <button
                onClick={() => handleViewInvoice(invoice.numericId)}
                className="p-2 hover:bg-secondary rounded-lg transition-colors opacity-0 group-hover:opacity-100"
              >
                <Eye className="w-4 h-4 text-muted-foreground" />
              </button>
            </div>
          </div>
        ))}
      </div>
    </div>
    </>
  );
};
