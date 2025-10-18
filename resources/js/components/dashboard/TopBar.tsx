import { Building2, Menu, Moon, Sun } from "lucide-react";
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
import { useTheme } from "next-themes";
import { Tooltip, TooltipContent, TooltipTrigger } from "@/components/ui/tooltip";

interface TopBarProps {
  onMenuClick: () => void;
}

export const TopBar = ({ onMenuClick }: TopBarProps) => {
  const { selectedCompanyId, setSelectedCompanyId } = useCompanyContext();
  const { companies, isLoading } = useCompaniesMinimal();
  const { theme, setTheme } = useTheme();

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

          {companies && companies.length > 0 && (
            <Select
              value={selectedCompanyId || companies[0]?.id?.toString()}
              onValueChange={setSelectedCompanyId}
            >
              <SelectTrigger className={cn(
                "w-[240px] h-10 bg-secondary/50 border-border",
                "hover:bg-secondary/80 transition-colors"
              )}>
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
          )}
        </div>
      </div>
    </div>
  );
};

