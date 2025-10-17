import { Link } from "react-router-dom";
import { DashboardLayout } from "@/components/dashboard/DashboardLayout";
import { MetricCard } from "@/components/dashboard/MetricCard";
import { RevenueChart } from "@/components/dashboard/RevenueChart";
import { RecentInvoices } from "@/components/dashboard/RecentInvoices";
import { Euro, FileText, Users, TrendingUp } from "lucide-react";

const Dashboard = () => {
  return (
    <DashboardLayout>
      <div className="space-y-6">
        <div className="animate-fade-in">
          <h1 className="text-3xl font-bold text-foreground">Dashboard</h1>
          <p className="text-muted-foreground mt-1">Vitajte späť! Tu je prehľad vášho biznisu.</p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <MetricCard
            title="Celkové príjmy"
            value="€35,800"
            change="+12.5% od minulého mesiaca"
            changeType="positive"
            icon={Euro}
          />
          <MetricCard
            title="Aktívne faktúry"
            value="24"
            change="5 čaká na zaplatenie"
            changeType="neutral"
            icon={FileText}
          />
          <MetricCard
            title="Klienti"
            value="142"
            change="+8 tento mesiac"
            changeType="positive"
            icon={Users}
          />
          <MetricCard
            title="Priemerná hodnota"
            value="€1,492"
            change="+5.2% nárast"
            changeType="positive"
            icon={TrendingUp}
          />
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div className="lg:col-span-2">
            <RevenueChart />
          </div>
          <div className="bg-gradient-card rounded-xl p-6 border border-border shadow-elegant-sm animate-fade-in">
            <h3 className="text-lg font-semibold text-foreground mb-4">Rýchle akcie</h3>
            <div className="space-y-3">
              <Link 
                to="/invoices/new"
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
      </div>
    </DashboardLayout>
  );
};

export default Dashboard;
