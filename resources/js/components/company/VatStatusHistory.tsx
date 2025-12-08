import { useState, useEffect, useImperativeHandle, forwardRef } from "react";
import { Loader2, History, AlertCircle } from "lucide-react";
import { Card } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { vatStatusService, type VatStatusHistoryRecord } from "@/services/vatStatusService";

interface VatStatusHistoryProps {
  companyId: number;
}

export interface VatStatusHistoryRef {
  refetch: () => void;
}

/**
 * Component displaying VAT status change history for a company
 */
export const VatStatusHistory = forwardRef<VatStatusHistoryRef, VatStatusHistoryProps>(
  ({ companyId }, ref) => {
    const [history, setHistory] = useState<VatStatusHistoryRecord[]>([]);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    const fetchHistory = async () => {
      setIsLoading(true);
      setError(null);

      try {
        const data = await vatStatusService.getHistory(companyId);
        setHistory(data);
      } catch (err: any) {
        setError(err?.response?.data?.message || "Nepodarilo sa načítať históriu");
      } finally {
        setIsLoading(false);
      }
    };

    useEffect(() => {
      if (companyId) {
        fetchHistory();
      }
    }, [companyId]);

    // Expose refetch method to parent
    useImperativeHandle(ref, () => ({
      refetch: fetchHistory,
    }));

    const formatDate = (date: string | null): string => {
      if (!date) return "súčasnosť";
      return new Date(date).toLocaleDateString("sk-SK");
    };

    const formatPeriod = (record: VatStatusHistoryRecord): string => {
      const from = formatDate(record.valid_from);
      const to = record.valid_to ? formatDate(record.valid_to) : "súčasnosť";
      return `${from} – ${to}`;
    };

    if (isLoading) {
      return (
        <Card className="p-6 mt-6">
          <div className="flex items-center gap-2 mb-4">
            <History className="h-5 w-5 text-muted-foreground" />
            <h4 className="text-lg font-semibold">História zmien DPH statusu</h4>
          </div>
          <div className="flex justify-center py-8">
            <Loader2 className="h-6 w-6 animate-spin text-muted-foreground" />
          </div>
        </Card>
      );
    }

    if (error) {
      return (
        <Card className="p-6 mt-6">
          <div className="flex items-center gap-2 mb-4">
            <History className="h-5 w-5 text-muted-foreground" />
            <h4 className="text-lg font-semibold">História zmien DPH statusu</h4>
          </div>
          <div className="flex items-center gap-2 p-4 bg-destructive/10 border border-destructive/20 rounded-lg text-destructive text-sm">
            <AlertCircle className="h-4 w-4" />
            <span>{error}</span>
          </div>
        </Card>
      );
    }

    if (history.length === 0) {
      return (
        <Card className="p-6 mt-6">
          <div className="flex items-center gap-2 mb-4">
            <History className="h-5 w-5 text-muted-foreground" />
            <h4 className="text-lg font-semibold">História zmien DPH statusu</h4>
          </div>
          <p className="text-center py-8 text-muted-foreground">
            Žiadna história zmien
          </p>
        </Card>
      );
    }

    return (
      <Card className="p-6 mt-6">
        <div className="flex items-center gap-2 mb-4">
          <History className="h-5 w-5 text-muted-foreground" />
          <h4 className="text-lg font-semibold">História zmien DPH statusu</h4>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead>
              <tr className="border-b border-border">
                <th className="text-left py-3 px-4 font-medium text-muted-foreground">
                  Obdobie
                </th>
                <th className="text-left py-3 px-4 font-medium text-muted-foreground">
                  Status
                </th>
                <th className="text-left py-3 px-4 font-medium text-muted-foreground">
                  Periodicita
                </th>
                <th className="text-left py-3 px-4 font-medium text-muted-foreground">
                  Poznámka
                </th>
              </tr>
            </thead>
            <tbody>
              {history.map((record) => (
                <tr
                  key={record.id}
                  className={`border-b border-border/50 ${
                    record.is_current ? "bg-primary/5" : ""
                  }`}
                >
                  <td className="py-3 px-4">
                    <span className="whitespace-nowrap">{formatPeriod(record)}</span>
                    {record.is_current && (
                      <Badge variant="secondary" className="ml-2 text-xs">
                        Aktuálne
                      </Badge>
                    )}
                  </td>
                  <td className="py-3 px-4">{record.vat_status_label}</td>
                  <td className="py-3 px-4 text-muted-foreground">
                    {record.vat_period_label || "–"}
                  </td>
                  <td className="py-3 px-4 text-muted-foreground">
                    {record.notes || "–"}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </Card>
    );
  }
);

VatStatusHistory.displayName = "VatStatusHistory";
