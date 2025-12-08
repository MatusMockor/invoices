import { AlertTriangle, Loader2, Info } from "lucide-react";
import {
  AlertDialog,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from "@/components/ui/alert-dialog";
import { Button } from "@/components/ui/button";

interface VatStatusChangeDialogProps {
  isOpen: boolean;
  onClose: () => void;
  onConfirm: () => void;
  isSubmitting: boolean;
  newStatusLabel: string;
  validFrom: string;
}

/**
 * Warning dialog shown before VAT status change
 */
export function VatStatusChangeDialog({
  isOpen,
  onClose,
  onConfirm,
  isSubmitting,
  newStatusLabel,
  validFrom,
}: VatStatusChangeDialogProps) {
  const formattedDate = new Date(validFrom).toLocaleDateString("sk-SK");

  return (
    <AlertDialog open={isOpen} onOpenChange={onClose}>
      <AlertDialogContent className="max-w-lg">
        <AlertDialogHeader>
          <div className="flex items-center gap-3 mb-2">
            <div className="p-2 bg-yellow-100 rounded-full">
              <AlertTriangle className="h-5 w-5 text-yellow-600" />
            </div>
            <AlertDialogTitle className="text-lg">
              Zmena DPH statusu
            </AlertDialogTitle>
          </div>
          <AlertDialogDescription className="text-left space-y-3">
            <p>
              Chystáte sa zmeniť DPH status na <strong>{newStatusLabel}</strong>{" "}
              s platnosťou od <strong>{formattedDate}</strong>.
            </p>
            <p>
              Zmena DPH statusu sa prejaví len na <strong>nových faktúrach</strong>.
              Existujúce faktúry zostanú nezmenené.
            </p>
          </AlertDialogDescription>
        </AlertDialogHeader>

        <div className="flex items-start gap-3 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700">
          <Info className="h-4 w-4 mt-0.5 flex-shrink-0" />
          <p>
            <strong>Tip:</strong> Po zmene statusu skontrolujte vaše šablóny
            a opakované faktúry, či sú správne nastavené.
          </p>
        </div>

        <AlertDialogFooter className="mt-4">
          <Button
            variant="outline"
            onClick={onClose}
            disabled={isSubmitting}
          >
            Zrušiť
          </Button>
          <Button
            onClick={onConfirm}
            disabled={isSubmitting}
          >
            {isSubmitting ? (
              <>
                <Loader2 className="h-4 w-4 mr-2 animate-spin" />
                Potvrdzujem...
              </>
            ) : (
              "Potvrdiť zmenu"
            )}
          </Button>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  );
}
