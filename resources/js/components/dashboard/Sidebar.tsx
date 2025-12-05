import { Home, FileText, Users, Settings, BarChart3, Plus, Clock, Car, Wrench, LogOut, Building2 } from "lucide-react";
import { NavLink, Link } from "react-router-dom";
import { cn } from "@/lib/utils";
import { Button } from "@/components/ui/button";
import { Sheet, SheetContent } from "@/components/ui/sheet";
import { useAuth } from "@/hooks/useAuth";

const navItems = [
  { icon: Home, label: "Dashboard", path: "/app/dashboard" },
  { icon: Building2, label: "Moje firmy", path: "/app/companies" },
  { icon: FileText, label: "Faktúry", path: "/app/invoices" },
  { icon: Users, label: "Klienti", path: "/app/clients" },
  { icon: BarChart3, label: "Reporty", path: "/app/reports" },
  { icon: Clock, label: "Dochádzka", path: "/app/attendance" },
  { icon: Car, label: "Kniha jázd", path: "/app/vehicle-log" },
  { icon: Wrench, label: "Vozidlá", path: "/app/vehicles" },
  { icon: Settings, label: "Nastavenia", path: "/app/settings" },
];

interface SidebarProps {
  mobileMenuOpen: boolean;
  onMobileMenuClose: () => void;
}

export const Sidebar = ({ mobileMenuOpen, onMobileMenuClose }: SidebarProps) => {
  const { logout } = useAuth();

  const handleLogout = () => {
    logout();
    onMobileMenuClose();
  };

  const sidebarContent = (
    <div className="flex flex-col h-full pt-2">
        <nav className="flex-1 px-2 py-1 space-y-0.5 overflow-y-auto">
          {navItems.map((item) => (
            <NavLink
              key={item.path}
              to={item.path}
              onClick={onMobileMenuClose}
              className={({ isActive }) =>
                cn(
                  "flex items-center gap-2.5 px-3 py-2 rounded-md transition-all duration-200 text-sm",
                  "hover:bg-secondary/80",
                  isActive
                    ? "bg-gradient-primary text-primary-foreground shadow-sm"
                    : "text-foreground"
                )
              }
            >
              <item.icon className="w-4 h-4" />
              <span className="font-medium">{item.label}</span>
            </NavLink>
          ))}
        </nav>

        <div className="px-2 py-2 border-t border-border space-y-1.5">
          <Link
            to="/app/invoices/new"
            onClick={onMobileMenuClose}
            className="w-full flex items-center justify-center gap-2 px-3 py-2 text-sm bg-gradient-primary text-primary-foreground rounded-md hover:opacity-90 transition-opacity shadow-sm"
          >
            <Plus className="w-4 h-4" />
            <span className="font-medium">Nová faktúra</span>
          </Link>
          <Button
            variant="outline"
            size="sm"
            onClick={handleLogout}
            className="w-full flex items-center justify-center gap-2"
          >
            <LogOut className="w-4 h-4" />
            <span className="font-medium">Odhlásiť sa</span>
          </Button>
        </div>
      </div>
  );

  return (
    <>
      {/* Mobile Sheet */}
      <Sheet open={mobileMenuOpen} onOpenChange={onMobileMenuClose}>
        <SheetContent side="left" className="w-48 p-0">
          {sidebarContent}
        </SheetContent>
      </Sheet>

      {/* Desktop Sidebar */}
      <aside className="fixed left-0 top-16 z-40 h-[calc(100vh-4rem)] w-48 border-r border-border bg-card/50 backdrop-blur-xl hidden lg:block">
        {sidebarContent}
      </aside>
    </>
  );
};
