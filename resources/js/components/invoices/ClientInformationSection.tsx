import React, { useRef, useEffect } from "react";
import { UseFormReturn } from "react-hook-form";
import { Loader2, Building2, Search, X, MapPin, Hash } from "lucide-react";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { Command, CommandEmpty, CommandGroup, CommandItem, CommandList } from "@/components/ui/command";
import { Company } from "@/types/company";

export interface InvoiceFormData {
  invoiceNumber: string;
  clientName?: string;
  clientStreet?: string;
  clientCity?: string;
  clientPostalCode?: string;
  clientIco?: string;
  clientDic?: string;
  clientIcDph?: string;
  useCustomCompany?: boolean;
  customCompanyIco?: string;
  customCompanyDic?: string;
  customCompanyIcDph?: string;
  customCompanyName?: string;
  customCompanyAddress?: string;
  customCompanyCity?: string;
  customCompanyZip?: string;
  customCompanyCountry?: string;
  issueDate: Date;
  dueDate: Date;
  deliveryDate: Date;
  variableSymbol: string;
  constantSymbol?: string;
  specificSymbol?: string;
  reverseCharge?: boolean;
  taxExemptionReason?: string;
  specialText?: string;
  notes?: string;
  items: Array<{
    description: string;
    quantity: number;
    price: number; // This is unit_price_without_tax
    tax_rate: number; // Default 20
  }>;
}

interface ClientInformationSectionProps {
  form: UseFormReturn<InvoiceFormData>;
  isEditMode: boolean;
  useCustomCompany: boolean;
  icoSearch: string;
  setIcoSearch: (value: string) => void;
  isSearching: boolean;
  showSuggestions: boolean;
  setShowSuggestions: (value: boolean) => void;
  filteredCompanies: Company[];
  onCompanySelect: (company: Company) => void;
  selectedCompany: Company | null;
  onClearSelection: () => void;
  searchError: string | null;
  isSelectingCompany: boolean;
}

const ClientInformationSectionComponent = ({
  form,
  isEditMode,
  useCustomCompany,
  icoSearch,
  setIcoSearch,
  isSearching,
  showSuggestions,
  setShowSuggestions,
  filteredCompanies,
  onCompanySelect,
  selectedCompany,
  onClearSelection,
  searchError,
  isSelectingCompany,
}: ClientInformationSectionProps) => {
  const { register, formState: { errors }, setValue } = form;
  const dropdownRef = useRef<HTMLDivElement>(null);

  // Handle Escape key
  const handleKeyDown = (e: React.KeyboardEvent) => {
    if (e.key === 'Escape') {
      setShowSuggestions(false);
    }
  };

  // Handle click outside
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
        setShowSuggestions(false);
      }
    };

    if (showSuggestions) {
      document.addEventListener('mousedown', handleClickOutside);
      return () => document.removeEventListener('mousedown', handleClickOutside);
    }
  }, [showSuggestions, setShowSuggestions]);

  return (
    <div className="space-y-4">
      {/* Info in edit mode */}
      {isEditMode && !useCustomCompany && (
        <div className="mb-4 p-3 bg-blue-50 dark:bg-blue-950/30 rounded-lg border border-blue-200 dark:border-blue-800">
          <p className="text-sm text-blue-700 dark:text-blue-300">
            Údaje o klientovi sú uložené z času vytvorenia. Pre úpravu zaškrtnite "Zadať vlastné údaje".
          </p>
        </div>
      )}

      {/* Validation errors for client information */}
      {!useCustomCompany && (errors.clientIco || errors.clientName || errors.clientStreet || errors.clientCity || errors.clientPostalCode) && (
        <div className="mb-4 p-4 bg-destructive/10 rounded-lg border border-destructive/30">
          <p className="text-sm font-semibold text-destructive mb-2">Údaje o klientovi sú neúplné:</p>
          <ul className="list-disc list-inside space-y-1 text-sm text-destructive">
            {errors.clientIco && <li>{errors.clientIco.message}</li>}
            {errors.clientName && <li>{errors.clientName.message}</li>}
            {errors.clientStreet && <li>{errors.clientStreet.message}</li>}
            {errors.clientCity && <li>{errors.clientCity.message}</li>}
            {errors.clientPostalCode && <li>{errors.clientPostalCode.message}</li>}
          </ul>
        </div>
      )}

      {/* IČO search with autocomplete */}
      {!selectedCompany && !isSelectingCompany && (
          <div className="space-y-2">
            <div className="relative">
              <div className="relative">
                <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                <Input
                  id="clientIco"
                  value={icoSearch}
                  onChange={(e) => {
                    const newValue = e.target.value;
                    setIcoSearch(newValue);
                    setValue("clientIco", newValue);
                    if (newValue.length >= 2) {
                      setShowSuggestions(true);
                    } else {
                      setShowSuggestions(false);
                    }
                  }}
                  onFocus={() => {
                    if (icoSearch.length >= 2) {
                      setShowSuggestions(true);
                    }
                  }}
                  onKeyDown={handleKeyDown}
                  placeholder="Hladať podľa ICO alebo názvu..."
                  className="pl-9 pr-10 border-primary/30 focus:border-primary"
                  disabled={isEditMode && !useCustomCompany}
                  autoComplete="off"
                  data-form-type="other"
                  data-lpignore="true"
                  data-1p-ignore="true"
                  role="combobox"
                  aria-expanded={showSuggestions}
                  aria-controls="company-listbox"
                  aria-autocomplete="list"
                  aria-label="Vyhladať firmu podla ICO alebo názvu"
                />
                {isSearching && (
                  <div className="absolute right-3 top-1/2 -translate-y-1/2">
                    <Loader2 className="h-4 w-4 animate-spin text-primary" />
                  </div>
                )}
              </div>

              {/* Validation message */}
              {icoSearch.length > 0 && icoSearch.length < 2 && (
                <p className="text-xs text-muted-foreground mt-1.5">
                  Zadajte aspoň 2 znaky pre vyhľadávanie
                </p>
              )}

              {/* Dropdown with suggestions */}
              {showSuggestions && icoSearch.length >= 2 && (
                <div
                  ref={dropdownRef}
                  className="absolute z-50 w-full mt-1 bg-popover border border-border rounded-lg shadow-lg overflow-hidden"
                  role="listbox"
                  id="company-listbox"
                  aria-label="Výsledky vyhľadávania spoločností"
                >
                  <Command>
                    <CommandList className="max-h-[280px]">
                      {searchError ? (
                        <div className="py-6 px-4 text-center text-sm text-destructive">
                          <p className="font-medium">{searchError}</p>
                          <p className="text-xs mt-1 text-muted-foreground">Skontrolujte pripojenie a skúste znova</p>
                        </div>
                      ) : isSearching ? (
                        <div className="py-8 text-center text-sm flex flex-col items-center justify-center gap-2">
                          <Loader2 className="h-5 w-5 animate-spin text-primary" />
                          <span className="text-muted-foreground">Vyhľadávam...</span>
                        </div>
                      ) : filteredCompanies.length === 0 ? (
                        <CommandEmpty className="py-8 text-center">
                          <div className="flex flex-col items-center gap-2">
                            <div className="p-2 rounded-full bg-muted">
                              <Building2 className="h-5 w-5 text-muted-foreground" />
                            </div>
                            <p className="text-sm font-medium">Žiadne výsledky</p>
                            <p className="text-xs text-muted-foreground">
                              Skúste iné IČO alebo názov
                            </p>
                          </div>
                        </CommandEmpty>
                      ) : (
                        <CommandGroup>
                          {filteredCompanies.map((company) => (
                            <CommandItem
                              key={company.ico}
                              onSelect={() => onCompanySelect(company)}
                              className="cursor-pointer py-3 px-3 group"
                            >
                              <div className="flex flex-col gap-1 w-full">
                                <div className="font-medium text-sm group-hover:text-white">{company.name}</div>
                                <div className="flex items-center gap-2 text-xs text-muted-foreground group-hover:text-white/80">
                                  <span>IČO: {company.ico}</span>
                                  {company.dic && <span>DIČ: {company.dic}</span>}
                                </div>
                                {company.address && (
                                  <div className="text-xs text-muted-foreground group-hover:text-white/70 flex items-center gap-1">
                                    <MapPin className="h-3 w-3 shrink-0" />
                                    <span className="truncate">
                                      {company.address}
                                      {company.postal_code && company.city && `, ${company.postal_code} ${company.city}`}
                                    </span>
                                  </div>
                                )}
                              </div>
                            </CommandItem>
                          ))}
                        </CommandGroup>
                      )}
                    </CommandList>
                  </Command>
                </div>
              )}
            </div>
            {errors.clientIco && (
              <p className="text-sm text-destructive">{errors.clientIco.message}</p>
            )}
          </div>
        )}

      {/* Loading skeleton when selecting company */}
      {isSelectingCompany && (
        <Card className="p-5 bg-primary/5 border-primary/30 animate-pulse">
          <div className="flex items-start gap-3 mb-4">
            <div className="w-12 h-12 rounded-full bg-muted"></div>
            <div className="flex-1 space-y-2">
              <div className="h-6 bg-muted rounded w-3/4"></div>
              <div className="h-4 bg-muted rounded w-1/2"></div>
            </div>
          </div>
          <div className="space-y-3">
            <div className="h-4 bg-muted rounded"></div>
            <div className="grid grid-cols-2 gap-4">
              <div className="h-4 bg-muted rounded"></div>
              <div className="h-4 bg-muted rounded"></div>
            </div>
          </div>
        </Card>
      )}

      {/* Selected company - display card */}
      {selectedCompany && !isSelectingCompany && (
        <Card className="p-4 bg-gradient-to-br from-primary/5 to-primary/10 border-primary/20 relative overflow-hidden">
          {/* Background decoration */}
          <div className="absolute top-0 right-0 w-20 h-20 bg-primary/5 rounded-full -translate-y-1/2 translate-x-1/2" />

          <div className="relative">
            {/* Header with company name and change button */}
            <div className="flex justify-between items-start gap-2 mb-3">
              <div className="flex items-center gap-2 min-w-0">
                <div className="p-1.5 rounded-md bg-primary/10 shrink-0">
                  <Building2 className="h-4 w-4 text-primary" />
                </div>
                <span className="font-semibold text-sm truncate">{selectedCompany.name}</span>
              </div>
              {(!isEditMode || useCustomCompany) && (
                <Button
                  type="button"
                  variant="ghost"
                  size="sm"
                  onClick={onClearSelection}
                  className="h-7 px-2 text-xs text-muted-foreground hover:text-foreground shrink-0"
                >
                  <X className="h-3 w-3 mr-1" />
                  Zmeniť
                </Button>
              )}
            </div>

            {/* Company details */}
            <div className="space-y-2 text-sm">
              {/* Address */}
              {selectedCompany.address && (
                <div className="flex items-start gap-2 text-muted-foreground">
                  <MapPin className="h-3.5 w-3.5 mt-0.5 shrink-0" />
                  <span className="text-xs">
                    {selectedCompany.address}
                    {selectedCompany.postal_code && selectedCompany.city &&
                      `, ${selectedCompany.postal_code} ${selectedCompany.city}`}
                  </span>
                </div>
              )}

              {/* IDs - IČO, DIČ, IČ DPH */}
              <div className="flex items-start gap-2 text-muted-foreground">
                <Hash className="h-3.5 w-3.5 mt-0.5 shrink-0" />
                <div className="flex flex-wrap gap-x-3 gap-y-0.5 text-xs">
                  <span>IČO: <span className="text-foreground font-medium">{selectedCompany.ico}</span></span>
                  {selectedCompany.dic && (
                    <span>DIČ: <span className="text-foreground font-medium">{selectedCompany.dic}</span></span>
                  )}
                  {selectedCompany.ic_dph && (
                    <span>IČ DPH: <span className="text-foreground font-medium">{selectedCompany.ic_dph}</span></span>
                  )}
                </div>
              </div>
            </div>
          </div>
        </Card>
      )}

      {/* Hidden inputs for form validation */}
      <input type="hidden" {...register("clientIco")} aria-hidden="true" />
      <input type="hidden" {...register("clientName")} aria-hidden="true" />
      <input type="hidden" {...register("clientStreet")} aria-hidden="true" />
      <input type="hidden" {...register("clientCity")} aria-hidden="true" />
      <input type="hidden" {...register("clientPostalCode")} aria-hidden="true" />
      <input type="hidden" {...register("clientDic")} aria-hidden="true" />
      <input type="hidden" {...register("clientIcDph")} aria-hidden="true" />
    </div>
  );
};

export const ClientInformationSection = React.memo(ClientInformationSectionComponent, (prevProps, nextProps) => {
  // Custom comparison to prevent unnecessary re-renders
  return (
    prevProps.selectedCompany === nextProps.selectedCompany &&
    prevProps.icoSearch === nextProps.icoSearch &&
    prevProps.isSearching === nextProps.isSearching &&
    prevProps.showSuggestions === nextProps.showSuggestions &&
    prevProps.filteredCompanies === nextProps.filteredCompanies &&
    prevProps.useCustomCompany === nextProps.useCustomCompany &&
    prevProps.isEditMode === nextProps.isEditMode &&
    prevProps.searchError === nextProps.searchError &&
    prevProps.isSelectingCompany === nextProps.isSelectingCompany
  );
});

ClientInformationSection.displayName = 'ClientInformationSection';
