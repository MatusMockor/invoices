import { UseFormReturn } from "react-hook-form";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { InvoiceFormData } from "./ClientInformationSection";
import { InvoiceStatusDropdown } from "./InvoiceStatusDropdown";

interface InvoiceNumberSectionProps {
  form: UseFormReturn<InvoiceFormData>;
  isEditMode?: boolean;
  invoiceId?: number;
  currentStatus?: string;
  onStatusChange?: () => void;
}

export const InvoiceNumberSection = ({
  form,
  isEditMode = false,
  invoiceId,
  currentStatus,
  onStatusChange,
}: InvoiceNumberSectionProps) => {
  const { register, formState: { errors } } = form;

  return (
    <div className="space-y-3">
      {/* Invoice Number & Status Row (edit mode) OR Invoice Number & VS Row (create mode) */}
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
        {/* Invoice Number */}
        <div>
          <Label className="text-xs">Cislo faktury</Label>
          <Input
            {...register("invoiceNumber")}
            className="mt-1 font-mono"
            placeholder="20250001"
          />
          {errors.invoiceNumber && (
            <p className="text-xs text-destructive mt-1">{errors.invoiceNumber.message}</p>
          )}
        </div>

        {/* Status (only in edit mode) */}
        {isEditMode && invoiceId && currentStatus && (
          <div>
            <Label className="text-xs">Stav</Label>
            <div className="mt-1">
              <InvoiceStatusDropdown
                invoiceId={invoiceId}
                currentStatus={currentStatus}
                onStatusChange={onStatusChange}
              />
            </div>
          </div>
        )}

        {/* Variable Symbol (in create mode, fills second column) */}
        {!isEditMode && (
          <div>
            <Label className="text-xs">Variabilny symbol</Label>
            <Input
              {...register("variableSymbol")}
              className="mt-1 font-mono"
              placeholder="20250001"
            />
            <p className="text-xs text-muted-foreground mt-1">
              Predvyplneny podla cisla faktury
            </p>
          </div>
        )}
      </div>

      {/* Variable Symbol in edit mode - full width */}
      {isEditMode && (
        <div>
          <Label className="text-xs">Variabilny symbol</Label>
          <Input
            {...register("variableSymbol")}
            className="mt-1 font-mono"
          />
          <p className="text-xs text-muted-foreground mt-1">
            Predvyplneny podla cisla faktury
          </p>
        </div>
      )}
    </div>
  );
};
