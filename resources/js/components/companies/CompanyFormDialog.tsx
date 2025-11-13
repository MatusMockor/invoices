import React from "react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogFooter,
} from "@/components/ui/dialog";
import { UserCompanyFormData } from "@/types";

/**
 * Props for the CompanyFormDialog component
 */
interface CompanyFormDialogProps {
  /** Dialog mode: 'create' for new company, 'edit' for existing */
  mode: 'create' | 'edit';
  /** Whether the dialog is open */
  open: boolean;
  /** Callback to control dialog open state */
  onOpenChange: (open: boolean) => void;
  /** Form data */
  formData: Partial<UserCompanyFormData>;
  /** Form validation errors */
  formErrors: Partial<Record<keyof UserCompanyFormData, string>>;
  /** Whether the form is currently submitting */
  isSubmitting: boolean;
  /** Form submit handler */
  onSubmit: (e: React.FormEvent) => void;
  /** Callback to update form data */
  onFormDataChange: (data: Partial<UserCompanyFormData>) => void;
}

/**
 * Helper component for consistent form field rendering
 */
interface FormFieldProps {
  id: string;
  label: string;
  required?: boolean;
  type?: string;
  placeholder?: string;
  value: string;
  onChange: (value: string) => void;
  error?: string;
  maxLength?: number;
  colSpan?: 1 | 2;
}

const FormField: React.FC<FormFieldProps> = ({
  id,
  label,
  required = false,
  type = "text",
  placeholder,
  value,
  onChange,
  error,
  maxLength,
  colSpan = 1,
}) => {
  const colSpanClass = colSpan === 2 ? "md:col-span-2" : "";

  return (
    <div className={`space-y-2 ${colSpanClass}`}>
      <Label htmlFor={id}>
        {label} {required && <span className="text-destructive">*</span>}
      </Label>
      <Input
        id={id}
        type={type}
        placeholder={placeholder}
        required={required}
        value={value}
        onChange={(e) => onChange(e.target.value)}
        className="border-primary/30"
        aria-required={required}
        aria-invalid={!!error}
        aria-describedby={error ? `${id}-error` : undefined}
        maxLength={maxLength}
      />
      {error && (
        <p id={`${id}-error`} className="text-sm text-destructive">
          {error}
        </p>
      )}
    </div>
  );
};

/**
 * Reusable company form dialog component
 *
 * Supports both create and edit modes with a single implementation.
 * Includes all form sections: Basic Info, Contact Info, and Bank Info.
 *
 * @example
 * ```tsx
 * <CompanyFormDialog
 *   mode="create"
 *   open={isAddOpen}
 *   onOpenChange={setIsAddOpen}
 *   formData={formData}
 *   formErrors={formErrors}
 *   isSubmitting={isCreating}
 *   onSubmit={handleSubmit}
 *   onFormDataChange={setFormData}
 * />
 * ```
 */
export const CompanyFormDialog: React.FC<CompanyFormDialogProps> = ({
  mode,
  open,
  onOpenChange,
  formData,
  formErrors,
  isSubmitting,
  onSubmit,
  onFormDataChange,
}) => {
  const isCreate = mode === 'create';
  const dialogTitle = isCreate ? "Nová firma" : "Upraviť firmu";
  const submitButtonText = isCreate
    ? (isSubmitting ? "Pridávam..." : "Pridať firmu")
    : (isSubmitting ? "Ukladám..." : "Uložiť zmeny");

  // Generate unique IDs based on mode to avoid conflicts when both dialogs exist
  const idPrefix = isCreate ? "" : "edit";

  const updateField = (field: keyof UserCompanyFormData, value: string) => {
    onFormDataChange({ ...formData, [field]: value });
  };

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle className="text-2xl font-bold text-primary">
            {dialogTitle}
          </DialogTitle>
        </DialogHeader>
        <form onSubmit={onSubmit} className="space-y-6">
          {/* Basic Information Section */}
          <div className="bg-gradient-card rounded-xl p-6 border-2 border-primary/30">
            <h3 className="text-lg font-bold text-primary mb-4">
              Základné informácie
            </h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <FormField
                id={`${idPrefix}Name`}
                label="Názov firmy"
                required
                placeholder="Dopravná spoločnosť s.r.o."
                value={formData.name || ""}
                onChange={(value) => updateField('name', value)}
                error={formErrors.name}
                colSpan={2}
              />
              <FormField
                id={`${idPrefix}Ico`}
                label="IČO"
                required
                placeholder="12345678"
                value={formData.ico || ""}
                onChange={(value) => updateField('ico', value)}
                error={formErrors.ico}
                maxLength={8}
              />
              <FormField
                id={`${idPrefix}Dic`}
                label="DIČ"
                required
                placeholder="2023456789"
                value={formData.dic || ""}
                onChange={(value) => updateField('dic', value)}
                error={formErrors.dic}
              />
              <FormField
                id={`${idPrefix}IcDph`}
                label="IČ DPH"
                placeholder="SK2023456789"
                value={formData.ic_dph || ""}
                onChange={(value) => updateField('ic_dph', value)}
              />
              <FormField
                id={`${idPrefix}Email`}
                label="Email"
                type="email"
                required
                placeholder="info@firma.sk"
                value={formData.email || ""}
                onChange={(value) => updateField('email', value)}
                error={formErrors.email}
              />
            </div>
          </div>

          {/* Contact Information Section */}
          <div className="bg-gradient-card rounded-xl p-6 border-2 border-primary/30">
            <h3 className="text-lg font-bold text-primary mb-4">
              Kontaktné údaje
            </h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <FormField
                id={`${idPrefix}Phone`}
                label="Telefón"
                type="tel"
                placeholder="+421 902 123 456"
                value={formData.phone || ""}
                onChange={(value) => updateField('phone', value)}
              />
              <FormField
                id={`${idPrefix}Country`}
                label="Krajina"
                required
                placeholder="Slovenská republika"
                value={formData.country || ""}
                onChange={(value) => updateField('country', value)}
                error={formErrors.country}
              />
              <FormField
                id={`${idPrefix}Street`}
                label="Ulica a číslo"
                required
                placeholder="Hlavná 123"
                value={formData.street || ""}
                onChange={(value) => updateField('street', value)}
                error={formErrors.street}
                colSpan={2}
              />
              <FormField
                id={`${idPrefix}City`}
                label="Mesto"
                required
                placeholder="Bratislava"
                value={formData.city || ""}
                onChange={(value) => updateField('city', value)}
                error={formErrors.city}
              />
              <FormField
                id={`${idPrefix}PostalCode`}
                label="PSČ"
                required
                placeholder="811 01"
                value={formData.postal_code || ""}
                onChange={(value) => updateField('postal_code', value)}
                error={formErrors.postal_code}
              />
            </div>
          </div>

          {/* Bank Information Section */}
          <div className="bg-gradient-card rounded-xl p-6 border-2 border-primary/30">
            <h3 className="text-lg font-bold text-primary mb-4">
              Bankové údaje
            </h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <FormField
                id={`${idPrefix}Iban`}
                label="IBAN"
                placeholder="SK31 1200 0000 1987 4263 7541"
                value={formData.iban || ""}
                onChange={(value) => updateField('iban', value)}
              />
              <FormField
                id={`${idPrefix}Swift`}
                label="SWIFT/BIC"
                placeholder="GIBASKBX"
                value={formData.swift || ""}
                onChange={(value) => updateField('swift', value)}
              />
            </div>
          </div>

          <DialogFooter>
            <Button
              type="button"
              variant="outline"
              onClick={() => onOpenChange(false)}
              disabled={isSubmitting}
            >
              Zrušiť
            </Button>
            <Button
              type="submit"
              className="bg-primary hover:bg-primary/90"
              disabled={isSubmitting}
            >
              {submitButtonText}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
};
