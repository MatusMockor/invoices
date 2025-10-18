import { ReactNode, useState } from "react";
import { Sidebar } from "./Sidebar";
import { TopBar } from "./TopBar";
import { useCompanyContext } from "@/contexts/CompanyContext";

interface DashboardLayoutProps {
  children: ReactNode;
  disableLoading?: boolean;
}

export const DashboardLayout = ({ children, disableLoading = false }: DashboardLayoutProps) => {
  const { isLoading } = useCompanyContext();
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

  return (
    <div className="flex min-h-screen w-full bg-background">
      <TopBar onMenuClick={() => setMobileMenuOpen(true)} />
      <Sidebar mobileMenuOpen={mobileMenuOpen} onMobileMenuClose={() => setMobileMenuOpen(false)} />
      <main className="flex-1 p-6 lg:p-8 ml-0 lg:ml-64 mt-16">
        {isLoading && !disableLoading ? (
          <div className="flex items-center justify-center min-h-[60vh]">
            <div className="text-center space-y-4">
              <div className="w-16 h-16 border-4 border-primary border-t-transparent rounded-full animate-spin mx-auto"></div>
              <p className="text-muted-foreground">Načítavam údaje...</p>
            </div>
          </div>
        ) : (
          children
        )}
      </main>
    </div>
  );
};
