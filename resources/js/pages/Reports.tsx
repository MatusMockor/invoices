import { useState, useMemo, useCallback } from "react";
import { DashboardLayout } from "@/components/dashboard/DashboardLayout";
import { MetricCard } from "@/components/dashboard/MetricCard";
import { InvoiceTable } from "@/components/reports/InvoiceTable";
import { useReports } from "@/hooks/useReports";
import { Euro, FileText, TrendingUp, TrendingDown, Calendar } from "lucide-react";

const Reports = () => {
  const [startDate, setStartDate] = useState<string>("");
  const [endDate, setEndDate] = useState<string>("");

  const filters = useMemo(() => {
    const f: { start_date?: string; end_date?: string } = {};
    if (startDate) f.start_date = startDate;
    if (endDate) f.end_date = endDate;
    return Object.keys(f).length > 0 ? f : undefined;
  }, [startDate, endDate]);

  const { financialReport, invoiceSummary, isLoading, error } = useReports(filters);

  const formatCurrency = useCallback((value: number) =>
    new Intl.NumberFormat("sk-SK", {
      style: "currency",
      currency: "EUR",
    }).format(value), []);

  const formatDate = useCallback((dateStr: string) =>
    new Date(dateStr).toLocaleDateString("sk-SK", {
      year: "numeric",
      month: "short",
      day: "numeric",
    }), []);

  const handleReset = () => {
    setStartDate("");
    setEndDate("");
  };

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <div className="animate-fade-in">
          <h1 className="text-3xl font-bold text-foreground">Reporty</h1>
          <p className="text-muted-foreground mt-1">
            Prehľad príjmov a výdavkov pre vašu firmu
          </p>
        </div>

        {/* Filters */}
        <div className="bg-gradient-card rounded-xl p-6 border border-border shadow-elegant-sm animate-fade-in">
          <h3 className="text-lg font-semibold text-foreground mb-4 flex items-center gap-2">
            <Calendar className="w-5 h-5" />
            Filter obdobia
          </h3>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label
                htmlFor="start-date"
                className="block text-sm font-medium text-muted-foreground mb-2"
              >
                Od dátumu
              </label>
              <input
                id="start-date"
                type="date"
                value={startDate}
                onChange={(e) => setStartDate(e.target.value)}
                aria-label="Dátum začiatku filtra"
                className="w-full px-4 py-2 bg-background border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all"
              />
            </div>
            <div>
              <label
                htmlFor="end-date"
                className="block text-sm font-medium text-muted-foreground mb-2"
              >
                Do dátumu
              </label>
              <input
                id="end-date"
                type="date"
                value={endDate}
                onChange={(e) => setEndDate(e.target.value)}
                aria-label="Dátum konca filtra"
                className="w-full px-4 py-2 bg-background border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all"
              />
            </div>
            <div className="flex items-end">
              <button
                onClick={handleReset}
                aria-label="Resetovať filtre obdobia"
                className="w-full px-4 py-2 bg-muted hover:bg-muted/80 text-foreground rounded-lg transition-all duration-200 font-medium"
              >
                Resetovať
              </button>
            </div>
          </div>
          {financialReport && (
            <p className="text-sm text-muted-foreground mt-4">
              Zobrazujem dáta od {formatDate(financialReport.period_start)} do{" "}
              {formatDate(financialReport.period_end)}
            </p>
          )}
        </div>

        {error ? (
          <div className="flex items-center justify-center min-h-[300px]">
            <div
              className="text-center space-y-4 p-8 bg-destructive/10 rounded-xl border border-destructive/20"
              role="alert"
              aria-live="assertive"
            >
              <p className="text-lg font-semibold text-destructive">
                Chyba pri načítaní reportov
              </p>
              <p className="text-sm text-muted-foreground">
                Skúste obnoviť stránku alebo skontrolujte pripojenie k internetu.
              </p>
            </div>
          </div>
        ) : isLoading ? (
          <div className="flex items-center justify-center min-h-[300px]">
            <div className="text-center space-y-4" role="status" aria-live="polite">
              <div
                className="w-16 h-16 border-4 border-primary border-t-transparent rounded-full animate-spin mx-auto"
                aria-label="Načítavam"
              ></div>
              <p className="text-muted-foreground">Načítavam reporty...</p>
            </div>
          </div>
        ) : (
          <>
            {/* Financial Summary */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
              <MetricCard
                title="Celkové príjmy"
                value={formatCurrency(financialReport?.total_income || 0)}
                change={`${financialReport?.income_count || 0} faktúr`}
                changeType="positive"
                icon={TrendingUp}
              />
              <MetricCard
                title="Celkové výdavky"
                value={formatCurrency(financialReport?.total_expenses || 0)}
                change={`${financialReport?.expense_count || 0} faktúr`}
                changeType="neutral"
                icon={TrendingDown}
              />
              <MetricCard
                title="Bilancia"
                value={formatCurrency(financialReport?.balance || 0)}
                change={
                  financialReport && financialReport.balance >= 0
                    ? "Pozitívna"
                    : "Negatívna"
                }
                changeType={
                  financialReport && financialReport.balance >= 0
                    ? "positive"
                    : "negative"
                }
                icon={Euro}
              />
              <MetricCard
                title="Celkom faktúr"
                value={String(
                  (financialReport?.income_count || 0) +
                    (financialReport?.expense_count || 0)
                )}
                change="V tomto období"
                changeType="neutral"
                icon={FileText}
              />
            </div>

            {/* Income Invoices */}
            <InvoiceTable
              title="Príjmové faktúry"
              icon={TrendingUp}
              iconClassName="text-success"
              invoices={invoiceSummary?.income_invoices || []}
              type="income"
              formatCurrency={formatCurrency}
              formatDate={formatDate}
              emptyMessage="Žiadne príjmové faktúry v tomto období"
            />

            {/* Expense Invoices */}
            <InvoiceTable
              title="Výdavkové faktúry"
              icon={TrendingDown}
              iconClassName="text-destructive"
              invoices={invoiceSummary?.expense_invoices || []}
              type="expense"
              formatCurrency={formatCurrency}
              formatDate={formatDate}
              emptyMessage="Žiadne výdavkové faktúry v tomto období"
            />
          </>
        )}
      </div>
    </DashboardLayout>
  );
};

export default Reports;
