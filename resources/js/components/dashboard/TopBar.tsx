import { Building2, Menu, Moon, Sun } from "lucide-react";
import { useEffect } from "react";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Button } from "@/components/ui/button";
import { cn } from "@/lib/utils";
import { useCompanyContext } from "@/contexts/CompanyContext";
import { useCompaniesMinimal } from "@/hooks/useCompanies";
import { useSwitchCompany } from "@/hooks/useSwitchCompany";
import { useTheme } from "next-themes";
import { Tooltip, TooltipContent, TooltipTrigger } from "@/components/ui/tooltip";

interface TopBarProps {
  onMenuClick: () => void;
}

export const TopBar = ({ onMenuClick }: TopBarProps) => {
  const { selectedCompanyId, setSelectedCompanyId, isLoading: isContextLoading } = useCompanyContext();
  const { companies, isLoading, error } = useCompaniesMinimal();
  const { switchCompany, isSwitching } = useSwitchCompany();
  const { theme, setTheme } = useTheme();

  // Initialize or validate selectedCompanyId when companies load
  useEffect(() => {
    if (!isLoading && companies.length > 0) {
      const selectedExists = selectedCompanyId &&
        companies.some(c => c.id.toString() === selectedCompanyId);

      // Set first company if no selection or selected company was deleted
      if (!selectedExists) {
        setSelectedCompanyId(companies[0].id.toString());
      }
    }
  }, [isLoading, companies, selectedCompanyId, setSelectedCompanyId]);

  // Handle company switch with backend call
  const handleCompanySwitch = async (companyId: string) => {
    // Don't switch if already selected
    if (companyId === selectedCompanyId) {
      return;
    }

    try {
      // Call backend switch endpoint using the dedicated hook
      await switchCompany(parseInt(companyId));

      // Update frontend context after successful backend call
      setSelectedCompanyId(companyId);
    } catch (error) {
      // Error handling is done in the useSwitchCompany hook
      console.error('Company switch failed:', error);
    }
  };

  return (
    <div className="fixed top-0 left-0 right-0 z-50 h-16 border-b border-border bg-card/95 backdrop-blur-lg">
      <div className="flex items-center justify-between h-full px-6">
        <div className="flex items-center gap-4">
          <Button
            variant="ghost"
            size="icon"
            className="lg:hidden"
            onClick={onMenuClick}
          >
            <Menu className="h-5 w-5" />
          </Button>
          <div className="flex items-center gap-2">
            <div className="h-8 w-8 rounded-lg bg-gradient-primary flex items-center justify-center">
              <Building2 className="h-5 w-5 text-primary-foreground" />
            </div>
            <span className="text-lg font-semibold text-foreground hidden sm:inline">
              InvoiceHub
            </span>
          </div>
        </div>

        <div className="flex items-center gap-4">
          <Tooltip>
            <TooltipTrigger asChild>
              <Button
                variant="outline"
                size="icon"
                onClick={() => setTheme(theme === "dark" ? "light" : "dark")}
                className="h-9 w-9"
              >
                <Sun className="h-5 w-5 rotate-0 scale-100 transition-all dark:-rotate-90 dark:scale-0" />
                <Moon className="absolute h-5 w-5 rotate-90 scale-0 transition-all dark:rotate-0 dark:scale-100" />
                <span className="sr-only">Prepnúť tému</span>
              </Button>
            </TooltipTrigger>
            <TooltipContent>
              <p>Prepnúť {theme === "dark" ? "na svetlý" : "na tmavý"} režim</p>
            </TooltipContent>
          </Tooltip>

          {error ? (
            <div className="text-destructive text-sm px-4 py-2 bg-destructive/10 rounded-lg border border-destructive/20">
              Nepodarilo sa načítať firmy
            </div>
          ) : (
            companies.length > 0 && selectedCompanyId && (
              <>
                <Select
                  value={selectedCompanyId}
                  onValueChange={handleCompanySwitch}
                  disabled={isContextLoading || isSwitching}
                >
                  <SelectTrigger
                    className={cn(
                      "w-[240px] h-10 bg-secondary/50 border-border",
                      "hover:bg-secondary/80 transition-colors",
                      (isContextLoading || isSwitching) && "opacity-50 cursor-not-allowed"
                    )}
                    aria-label="Výber firmy"
                    aria-busy={isContextLoading || isSwitching}
                  >
                    <div className="flex items-center gap-2">
                      <Building2 className="h-4 w-4 text-primary" />
                      <SelectValue placeholder="Vyberte firmu" />
                    </div>
                  </SelectTrigger>
                  <SelectContent
                    position="popper"
                    sideOffset={6}
                    align="start"
                    avoidCollisions={false}
                    className={cn(
                      "z-50 w-[var(--radix-select-trigger-width)] rounded-md border border-border bg-popover shadow-lg",
                      "data-[state=open]:animate-none data-[state=closed]:animate-none"
                    )}
                  >
                    {companies.map((company) => (
                      <SelectItem
                        key={company.id}
                        value={company.id.toString()}
                        className="cursor-pointer hover:bg-secondary/80"
                      >
                        {company.name}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>

                {(isContextLoading || isSwitching) && (
                  <span className="sr-only" aria-live="polite">
                    Prepínanie firmy...
                  </span>
                )}
              </>
            )
          )}
        </div>
      </div>
    </div>
  );
};

