import { Link } from "react-router-dom";
import { useMemo } from "react";
import { DashboardLayout } from "@/components/dashboard/DashboardLayout";
import { MetricCard } from "@/components/dashboard/MetricCard";
import { RevenueChart } from "@/components/dashboard/RevenueChart";
import { RecentInvoices } from "@/components/dashboard/RecentInvoices";
import { Euro, FileText, TrendingUp } from "lucide-react";
import { useAnalytics } from "@/hooks/useAnalytics";

const Dashboard = () => {
  const { statistics, monthlyData, isLoading, error } = useAnalytics();

  const formatCurrency = useMemo(() => {
    return (value: number) => new Intl.NumberFormat('sk-SK', {
      style: 'currency',
      currency: 'EUR',
    }).format(value);
  }, []);

  const growthInfo = useMemo(() => {
    const percentage = statistics?.yearGrowthPercentage;
    if (!percentage) return { text: 'Bez zmeny', type: 'neutral' as const };
    if (percentage > 0) return { text: 'Pozitívny trend', type: 'positive' as const };
    return { text: 'Negatívny trend', type: 'negative' as const };
  }, [statistics?.yearGrowthPercentage]);

  const balanceText = useMemo(() => {
    if (statistics?.balance && statistics.balance > 0) {
      return `Bilancia: ${formatCurrency(statistics.balance)}`;
    }
    return undefined;
  }, [statistics?.balance, formatCurrency]);

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <div className="animate-fade-in">
          <h1 className="text-3xl font-bold text-foreground">Dashboard</h1>
          <p className="text-muted-foreground mt-1">Vitajte späť! Tu je prehľad vášho biznisu.</p>
        </div>

        {error ? (
          <div className="flex items-center justify-center min-h-[300px]">
            <div className="text-center space-y-4 p-8 bg-destructive/10 rounded-xl border border-destructive/20">
              <p className="text-lg font-semibold text-destructive">Chyba pri načítaní analytiky</p>
              <p className="text-sm text-muted-foreground">
                Skúste obnoviť stránku alebo skontrolujte pripojenie k internetu.
              </p>
            </div>
          </div>
        ) : isLoading ? (
          <div className="flex items-center justify-center min-h-[300px]">
            <div className="text-center space-y-4">
              <div className="w-16 h-16 border-4 border-primary border-t-transparent rounded-full animate-spin mx-auto" aria-label="Načítavam"></div>
              <p className="text-muted-foreground">Načítavam analytiku...</p>
            </div>
          </div>
        ) : (
          <>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <MetricCard
                title="Celkové príjmy"
                value={formatCurrency(statistics?.totalIncome || 0)}
                change={balanceText}
                changeType="positive"
                icon={Euro}
              />
              <MetricCard
                title="Celkové výdavky"
                value={formatCurrency(statistics?.totalExpenses || 0)}
                changeType="neutral"
                icon={FileText}
              />
              <MetricCard
                title="Rast tento rok"
                value={`${statistics?.yearGrowthPercentage || 0}%`}
                change={growthInfo.text}
                changeType={growthInfo.type}
                icon={TrendingUp}
              />
            </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div className="lg:col-span-2">
            <RevenueChart monthlyData={monthlyData} />
          </div>
          <div className="bg-gradient-card rounded-xl p-6 border border-border shadow-elegant-sm animate-fade-in">
            <h3 className="text-lg font-semibold text-foreground mb-4">Rýchle akcie</h3>
            <div className="space-y-3">
              <Link
                to="/app/invoices/new"
                className="block w-full text-left p-4 bg-background/50 rounded-lg border border-border hover:border-primary/30 hover:bg-background/80 transition-all duration-200"
              >
                <p className="font-semibold text-foreground">Vytvoriť faktúru</p>
                <p className="text-sm text-muted-foreground">Nová faktúra pre klienta</p>
              </Link>
              <button className="w-full text-left p-4 bg-background/50 rounded-lg border border-border hover:border-primary/30 hover:bg-background/80 transition-all duration-200">
                <p className="font-semibold text-foreground">Pridať klienta</p>
                <p className="text-sm text-muted-foreground">Registrovať nového klienta</p>
              </button>
              <button className="w-full text-left p-4 bg-background/50 rounded-lg border border-border hover:border-primary/30 hover:bg-background/80 transition-all duration-200">
                <p className="font-semibold text-foreground">Exportovať dáta</p>
                <p className="text-sm text-muted-foreground">Stiahnuť mesačný report</p>
              </button>
            </div>
          </div>
        </div>

        <RecentInvoices />
          </>
        )}
      </div>
    </DashboardLayout>
  );
};

export default Dashboard;
