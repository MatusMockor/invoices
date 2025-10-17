import { Home, FileText, Users, Settings, BarChart3, Plus, Clock, Car, Wrench, Moon, Sun } from "lucide-react";
import { NavLink, Link } from "react-router-dom";
import { useTheme } from "next-themes";
import { cn } from "@/lib/utils";
import { Button } from "@/components/ui/button";
import { Tooltip, TooltipContent, TooltipTrigger } from "@/components/ui/tooltip";

const navItems = [
  { icon: Home, label: "Dashboard", path: "/dashboard" },
  { icon: FileText, label: "Faktúry", path: "/invoices" },
  { icon: Users, label: "Klienti", path: "/clients" },
  { icon: BarChart3, label: "Reporty", path: "/reports" },
  { icon: Clock, label: "Dochádzka", path: "/attendance" },
  { icon: Car, label: "Kniha jázd", path: "/vehicle-log" },
  { icon: Wrench, label: "Vozidlá", path: "/vehicles" },
  { icon: Settings, label: "Nastavenia", path: "/settings" },
];

export const Sidebar = () => {
  const { theme, setTheme } = useTheme();

  return (
    <aside className="fixed left-0 top-0 z-40 h-screen w-64 border-r border-border bg-card/50 backdrop-blur-xl hidden lg:block">
      <div className="flex flex-col h-full">
        <div className="p-6 border-b border-border">
          <div className="flex items-center justify-between">
            <div>
              <h1 className="text-2xl font-bold bg-gradient-primary bg-clip-text text-transparent">
                InvoiceHub
              </h1>
              <p className="text-sm text-muted-foreground mt-1">Fakturačný systém</p>
            </div>
          </div>
          <div className="mt-4 flex items-center justify-between p-3 rounded-lg bg-secondary/50">
            <span className="text-sm font-medium text-foreground">
              {theme === "dark" ? "Tmavý režim" : "Svetlý režim"}
            </span>
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
          </div>
        </div>

        <nav className="flex-1 p-4 space-y-2">
          {navItems.map((item) => (
            <NavLink
              key={item.path}
              to={item.path}
              className={({ isActive }) =>
                cn(
                  "flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200",
                  "hover:bg-secondary/80",
                  isActive
                    ? "bg-gradient-primary text-primary-foreground shadow-elegant-md"
                    : "text-foreground"
                )
              }
            >
              <item.icon className="w-5 h-5" />
              <span className="font-medium">{item.label}</span>
            </NavLink>
          ))}
        </nav>

        <div className="p-4 border-t border-border">
          <Link to="/invoices/new" className="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-primary text-primary-foreground rounded-lg hover:opacity-90 transition-opacity shadow-elegant-md">
            <Plus className="w-5 h-5" />
            <span className="font-medium">Nová faktúra</span>
          </Link>
        </div>
      </div>
    </aside>
  );
};
