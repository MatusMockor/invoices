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
    <div className="flex flex-col h-full pt-4">
        <nav className="flex-1 p-4 space-y-2 overflow-y-auto">
          {navItems.map((item) => (
            <NavLink
              key={item.path}
              to={item.path}
              onClick={onMobileMenuClose}
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

        <div className="p-4 border-t border-border space-y-2">
          <Link
            to="/app/invoices/new"
            onClick={onMobileMenuClose}
            className="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-primary text-primary-foreground rounded-lg hover:opacity-90 transition-opacity shadow-elegant-md"
          >
            <Plus className="w-5 h-5" />
            <span className="font-medium">Nová faktúra</span>
          </Link>
          <Button
            variant="outline"
            onClick={handleLogout}
            className="w-full flex items-center justify-center gap-2 px-4 py-3"
          >
            <LogOut className="w-5 h-5" />
            <span className="font-medium">Odhlásiť sa</span>
          </Button>
        </div>
      </div>
  );

  return (
    <>
      {/* Mobile Sheet */}
      <Sheet open={mobileMenuOpen} onOpenChange={onMobileMenuClose}>
        <SheetContent side="left" className="w-64 p-0">
          {sidebarContent}
        </SheetContent>
      </Sheet>

      {/* Desktop Sidebar */}
      <aside className="fixed left-0 top-16 z-40 h-[calc(100vh-4rem)] w-64 border-r border-border bg-card/50 backdrop-blur-xl hidden lg:block">
        {sidebarContent}
      </aside>
    </>
  );
};
