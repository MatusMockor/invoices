import { LucideIcon } from "lucide-react";

interface Invoice {
  id: number;
  invoice_number: string;
  customer_name?: string;
  supplier_name?: string;
  issue_date: string;
  due_date: string;
  total_amount: number;
  currency: string;
  status: string;
}

interface InvoiceTableProps {
  title: string;
  icon: LucideIcon;
  iconClassName: string;
  invoices: Invoice[];
  type: "income" | "expense";
  formatCurrency: (value: number) => string;
  formatDate: (dateStr: string) => string;
  emptyMessage: string;
}

export const InvoiceTable = ({
  title,
  icon: Icon,
  iconClassName,
  invoices,
  type,
  formatCurrency,
  formatDate,
  emptyMessage,
}: InvoiceTableProps) => {
  const entityLabel = type === "income" ? "Zákazník" : "Dodávateľ";
  const entityField = type === "income" ? "customer_name" : "supplier_name";
  const amountClassName = type === "income" ? "text-success" : "text-destructive";
  const statusClassName =
    type === "income"
      ? "bg-success/10 text-success"
      : "bg-destructive/10 text-destructive";

  return (
    <div className="bg-gradient-card rounded-xl p-6 border border-border shadow-elegant-sm animate-fade-in">
      <h3 className="text-lg font-semibold text-foreground mb-4 flex items-center gap-2">
        <Icon className={`w-5 h-5 ${iconClassName}`} />
        {title}
      </h3>
      {invoices && invoices.length > 0 ? (
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead>
              <tr className="border-b border-border">
                <th className="text-left py-3 px-4 text-sm font-medium text-muted-foreground">
                  Číslo faktúry
                </th>
                <th className="text-left py-3 px-4 text-sm font-medium text-muted-foreground">
                  {entityLabel}
                </th>
                <th className="text-left py-3 px-4 text-sm font-medium text-muted-foreground">
                  Dátum vystavenia
                </th>
                <th className="text-left py-3 px-4 text-sm font-medium text-muted-foreground">
                  Splatnosť
                </th>
                <th className="text-right py-3 px-4 text-sm font-medium text-muted-foreground">
                  Suma
                </th>
                <th className="text-center py-3 px-4 text-sm font-medium text-muted-foreground">
                  Status
                </th>
              </tr>
            </thead>
            <tbody>
              {invoices.map((invoice) => (
                <tr
                  key={invoice.id}
                  className="border-b border-border/50 hover:bg-muted/30 transition-colors"
                >
                  <td className="py-3 px-4 text-sm font-medium">
                    {invoice.invoice_number}
                  </td>
                  <td className="py-3 px-4 text-sm">
                    {invoice[entityField]}
                  </td>
                  <td className="py-3 px-4 text-sm">
                    {formatDate(invoice.issue_date)}
                  </td>
                  <td className="py-3 px-4 text-sm">
                    {formatDate(invoice.due_date)}
                  </td>
                  <td
                    className={`py-3 px-4 text-sm text-right font-semibold ${amountClassName}`}
                  >
                    {formatCurrency(invoice.total_amount)}
                  </td>
                  <td className="py-3 px-4 text-center">
                    <span
                      className={`inline-block px-2 py-1 text-xs font-medium rounded ${statusClassName}`}
                    >
                      {invoice.status}
                    </span>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      ) : (
        <p className="text-center text-muted-foreground py-8">{emptyMessage}</p>
      )}
    </div>
  );
};
