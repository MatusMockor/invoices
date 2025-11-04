import { UseFormReturn } from "react-hook-form";
import { Loader2 } from "lucide-react";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Command, CommandEmpty, CommandGroup, CommandItem, CommandList } from "@/components/ui/command";

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
  filteredCompanies: any[];
  onCompanySelect: (company: any) => void;
}

export const ClientInformationSection = ({
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
}: ClientInformationSectionProps) => {
  const { register, formState: { errors }, setValue } = form;

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
            💡 Údaje o klientovi sú uložené z času vytvorenia. Pre úpravu zaškrtnite "Zadať vlastné údaje o spoločnosti".
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
        {/* IČO s autocomplete */}
        <div className="space-y-2">
          <Label htmlFor="clientIco">IČO klienta *</Label>
          <div className="relative">
            <div className="relative">
              <Input
                id="clientIco"
                value={icoSearch}
                onChange={(e) => {
                  setIcoSearch(e.target.value);
                  setValue("clientIco", e.target.value);
                  setShowSuggestions(true);
                }}
                onFocus={() => {
                  if (icoSearch.length > 0) {
                    setShowSuggestions(true);
                  }
                }}
                placeholder="Začnite písať IČO alebo názov firmy..."
                className="border-primary/30"
                disabled={isEditMode && !useCustomCompany}
                inputMode="numeric"
                pattern="[0-9]*"
              />
              {isSearching && (
                <Loader2 className="absolute right-3 top-3 h-4 w-4 animate-spin text-primary" />
              )}
            </div>

            {/* Dropdown s návrhmi */}
            {showSuggestions && icoSearch.length > 0 && (
              <div className="absolute z-50 w-full mt-1 bg-popover border border-border rounded-md shadow-lg">
                <Command>
                  <CommandList>
                    {filteredCompanies.length === 0 ? (
                      <CommandEmpty className="py-6 text-center text-sm">
                        Žiadne výsledky
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
                                IČO: {company.ico} | {company.address}, {company.postal_code} {company.city}
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

        {/* Ostatné polia */}
        <div className="space-y-2">
          <Label htmlFor="clientName">Názov / Meno klienta *</Label>
          <Input
            id="clientName"
            {...register("clientName")}
            placeholder="ABC s.r.o."
            className="border-primary/30"
            disabled={isEditMode && !useCustomCompany}
          />
          {errors.clientName && (
            <p className="text-sm text-destructive">{errors.clientName.message}</p>
          )}
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div className="space-y-2">
            <Label htmlFor="clientDic">DIČ</Label>
            <Input
              id="clientDic"
              {...register("clientDic")}
              placeholder="2023456789"
              className="border-primary/30"
              disabled={isEditMode && !useCustomCompany}
              inputMode="numeric"
              pattern="[0-9]*"
            />
            {errors.clientDic && (
              <p className="text-sm text-destructive">{errors.clientDic.message}</p>
            )}
          </div>
          <div className="space-y-2">
            <Label htmlFor="clientIcDph">IČ DPH</Label>
            <Input
              id="clientIcDph"
              {...register("clientIcDph")}
              placeholder="SK2023456789"
              className="border-primary/30"
              disabled={isEditMode && !useCustomCompany}
            />
            {errors.clientIcDph && (
              <p className="text-sm text-destructive">{errors.clientIcDph.message}</p>
            )}
          </div>
        </div>

        <div className="space-y-2">
          <Label htmlFor="clientStreet">Ulica a číslo *</Label>
          <Input
            id="clientStreet"
            {...register("clientStreet")}
            placeholder="Hlavná 123"
            className="border-primary/30"
            disabled={isEditMode && !useCustomCompany}
          />
          {errors.clientStreet && (
            <p className="text-sm text-destructive">{errors.clientStreet.message}</p>
          )}
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div className="space-y-2">
            <Label htmlFor="clientPostalCode">PSČ *</Label>
            <Input
              id="clientPostalCode"
              {...register("clientPostalCode")}
              placeholder="811 01"
              className="border-primary/30"
              disabled={isEditMode && !useCustomCompany}
            />
            {errors.clientPostalCode && (
              <p className="text-sm text-destructive">{errors.clientPostalCode.message}</p>
            )}
          </div>
          <div className="md:col-span-2 space-y-2">
            <Label htmlFor="clientCity">Mesto *</Label>
            <Input
              id="clientCity"
              {...register("clientCity")}
              placeholder="Bratislava"
              className="border-primary/30"
              disabled={isEditMode && !useCustomCompany}
            />
            {errors.clientCity && (
              <p className="text-sm text-destructive">{errors.clientCity.message}</p>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};
