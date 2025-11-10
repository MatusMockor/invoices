import { useState } from "react";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { useToast } from "@/hooks/use-toast";
import { invoiceService } from "@/services/invoiceService";
import { Loader2 } from "lucide-react";

interface InvoiceStatusDropdownProps {
  invoiceId: number;
  currentStatus: string;
  onStatusChange?: (newStatus: string) => void;
}

const STATUS_LABELS: Record<string, string> = {
  draft: "Koncept",
  sent: "Odoslaná",
  paid: "Zaplatená",
  overdue: "Po splatnosti",
  cancelled: "Zrušená",
};

const STATUSES = ["draft", "sent", "paid", "overdue", "cancelled"] as const;

export const InvoiceStatusDropdown = ({
  invoiceId,
  currentStatus,
  onStatusChange,
}: InvoiceStatusDropdownProps) => {
  const [isUpdating, setIsUpdating] = useState(false);
  const { toast } = useToast();

  const handleStatusChange = async (newStatus: string) => {
    if (newStatus === currentStatus) return;

    setIsUpdating(true);
    try {
      await invoiceService.updateStatus(invoiceId, newStatus);

      toast({
        title: "Stav faktúry aktualizovaný",
        description: `Stav faktúry bol zmenený na "${STATUS_LABELS[newStatus]}"`,
      });

      if (onStatusChange) {
        onStatusChange(newStatus);
      }
    } catch (error: any) {
      console.error("Error updating invoice status:", error);
      toast({
        title: "Chyba",
        description: error.response?.data?.message || "Nepodarilo sa zmeniť stav faktúry",
        variant: "destructive",
      });
    } finally {
      setIsUpdating(false);
    }
  };

  return (
    <div className="flex items-center gap-2">
      <Select
        value={currentStatus}
        onValueChange={handleStatusChange}
        disabled={isUpdating}
      >
        <SelectTrigger className="w-[180px]">
          {isUpdating ? (
            <div className="flex items-center gap-2">
              <Loader2 className="h-4 w-4 animate-spin" />
              <span>Aktualizuje sa...</span>
            </div>
          ) : (
            <SelectValue placeholder="Vyberte stav" />
          )}
        </SelectTrigger>
        <SelectContent>
          {STATUSES.map((status) => (
            <SelectItem key={status} value={status}>
              {STATUS_LABELS[status]}
            </SelectItem>
          ))}
        </SelectContent>
      </Select>
    </div>
  );
};
