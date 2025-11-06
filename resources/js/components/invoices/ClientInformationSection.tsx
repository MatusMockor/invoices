import React, { useRef, useEffect } from "react";
import { UseFormReturn } from "react-hook-form";
import { Loader2, Building2, MapPin, FileText, Search } from "lucide-react";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
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
  items: Array<{
    description: string;
    quantity: number;
    price: number;
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
    <div className="bg-gradient-card rounded-xl p-6 border-2 border-primary/30 shadow-elegant-sm">
      <h3 className="text-lg font-bold text-primary mb-4 flex items-center gap-2">
        <span className="w-8 h-8 bg-primary text-primary-foreground rounded-full flex items-center justify-center text-sm">1</span>
        Informácie o klientovi
      </h3>

      {/* Info in edit mode */}
      {isEditMode && !useCustomCompany && (
        <div className="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
          <p className="text-sm text-blue-700">
            Údaje o klientovi sú uložené z času vytvorenia. Pre úpravu zaškrtnite "Zadať vlastné údaje o spoločnosti".
          </p>
        </div>
      )}

      {/* Toggle between standard and custom company */}
      <div className="flex items-center space-x-2 mb-6 p-4 bg-card/50 rounded-lg border border-primary/20">
        <input
          type="checkbox"
          id="useCustomCompany"
          {...register("useCustomCompany")}
          className="h-4 w-4 rounded border-primary/30 text-primary focus:ring-primary"
        />
        <Label htmlFor="useCustomCompany" className="cursor-pointer text-sm">
          Zadať vlastné údaje o spoločnosti (neregistrovaná v databáze)
        </Label>
      </div>

      <div className="space-y-4">
        {/* IČO search with autocomplete */}
        {!selectedCompany && !isSelectingCompany && (
          <div className="space-y-2">
            <Label htmlFor="clientIco">Vyhľadať firmu podľa IČO alebo názvu</Label>
            <div className="relative">
              <div className="relative">
                <Search className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
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
                  placeholder="Začnite písať IČO alebo názov firmy..."
                  className="border-primary/30 pl-9"
                  disabled={isEditMode && !useCustomCompany}
                  inputMode="numeric"
                  pattern="[0-9]*"
                  autoComplete="off"
                  data-form-type="other"
                  data-lpignore="true"
                  data-1p-ignore="true"
                  role="combobox"
                  aria-expanded={showSuggestions}
                  aria-controls="company-listbox"
                  aria-autocomplete="list"
                  aria-label="Vyhľadať firmu podľa IČO alebo názvu"
                />
                {isSearching && (
                  <Loader2 className="absolute right-3 top-3 h-4 w-4 animate-spin text-primary" />
                )}
              </div>

              {/* Validation message */}
              {icoSearch.length > 0 && icoSearch.length < 2 && (
                <p className="text-sm text-muted-foreground mt-1">
                  Zadajte aspoň 2 znaky pre vyhľadávanie
                </p>
              )}

              {/* Dropdown with suggestions */}
              {showSuggestions && icoSearch.length >= 2 && (
                <div
                  ref={dropdownRef}
                  className="absolute z-50 w-full mt-1 bg-popover border border-border rounded-md shadow-lg"
                  role="listbox"
                  id="company-listbox"
                  aria-label="Výsledky vyhľadávania spoločností"
                >
                  <Command>
                    <CommandList>
                      {searchError ? (
                        <div className="py-6 px-4 text-center text-sm text-destructive">
                          <p className="font-medium">{searchError}</p>
                          <p className="text-xs mt-1 text-muted-foreground">Skontrolujte pripojenie a skúste znova</p>
                        </div>
                      ) : isSearching ? (
                        <div className="py-6 text-center text-sm flex items-center justify-center gap-2">
                          <Loader2 className="h-4 w-4 animate-spin text-primary" />
                          <span>Vyhľadávam...</span>
                        </div>
                      ) : filteredCompanies.length === 0 ? (
                        <CommandEmpty className="py-6 text-center">
                          <div className="flex flex-col items-center gap-2">
                            <Building2 className="h-8 w-8 text-muted-foreground" />
                            <p className="text-sm font-medium">Žiadne výsledky</p>
                            <p className="text-xs text-muted-foreground">
                              Skúste iné IČO alebo názov spoločnosti
                            </p>
                          </div>
                        </CommandEmpty>
                      ) : (
                        <CommandGroup>
                          {filteredCompanies.map((company) => (
                            <CommandItem
                              key={company.ico}
                              onSelect={() => onCompanySelect(company)}
                              className="cursor-pointer"
                            >
                              <div className="flex flex-col gap-1">
                                <div className="font-semibold">{company.name}</div>
                                <div className="text-sm text-muted-foreground">
                                  IČO: {company.ico} | {company.address}
                                </div>
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
          <Card className="p-5 bg-primary/5 border-primary/30">
            <div className="flex items-start justify-between mb-4">
              <div className="flex items-center gap-3">
                <div className="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                  <Building2 className="h-6 w-6 text-primary" />
                </div>
                <div>
                  <h4 className="font-bold text-lg text-foreground truncate max-w-xs">{selectedCompany.name}</h4>
                  <p className="text-sm text-muted-foreground">IČO: {selectedCompany.ico}</p>
                </div>
              </div>
              {(!isEditMode || useCustomCompany) && (
                <Button
                  type="button"
                  variant="ghost"
                  size="sm"
                  onClick={onClearSelection}
                  className="text-muted-foreground hover:text-foreground"
                  aria-label={`Zmeniť spoločnosť ${selectedCompany.name}`}
                >
                  Zmeniť
                </Button>
              )}
            </div>

            <div className="space-y-3">
              {selectedCompany.address && (
                <div className="flex items-start gap-2">
                  <MapPin className="h-4 w-4 text-primary mt-0.5 shrink-0" />
                  <div>
                    <p className="text-xs text-muted-foreground">Adresa</p>
                    <p className="text-sm font-medium">
                      {selectedCompany.address}
                      {selectedCompany.postal_code && selectedCompany.city &&
                        `, ${selectedCompany.postal_code} ${selectedCompany.city}`}
                    </p>
                  </div>
                </div>
              )}

              {(selectedCompany.dic || selectedCompany.ic_dph) && (
                <div className="grid grid-cols-2 gap-4">
                  {selectedCompany.dic && (
                    <div className="flex items-start gap-2">
                      <FileText className="h-4 w-4 text-primary mt-0.5 shrink-0" />
                      <div>
                        <p className="text-xs text-muted-foreground">DIČ</p>
                        <p className="text-sm font-medium">{selectedCompany.dic}</p>
                      </div>
                    </div>
                  )}

                  {selectedCompany.ic_dph && (
                    <div className="flex items-start gap-2">
                      <FileText className="h-4 w-4 text-primary mt-0.5 shrink-0" />
                      <div>
                        <p className="text-xs text-muted-foreground">IČ DPH</p>
                        <p className="text-sm font-medium">{selectedCompany.ic_dph}</p>
                      </div>
                    </div>
                  )}
                </div>
              )}
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
