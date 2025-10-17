import { DashboardLayout } from "@/components/dashboard/DashboardLayout";
import { Card } from "@/components/ui/card";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Button } from "@/components/ui/button";
import {
  BarChart3,
  Download,
  TrendingUp,
  TrendingDown,
  Euro,
  FileText,
  Users,
  Calendar,
} from "lucide-react";
import {
  LineChart,
  Line,
  BarChart,
  Bar,
  PieChart,
  Pie,
  Cell,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
  ResponsiveContainer,
} from "recharts";

const monthlyData = [
  { month: "Jan", income: 12400, expenses: 8200, profit: 4200 },
  { month: "Feb", income: 15200, expenses: 9100, profit: 6100 },
  { month: "Mar", income: 18900, expenses: 10300, profit: 8600 },
  { month: "Apr", income: 16700, expenses: 9800, profit: 6900 },
  { month: "Máj", income: 21500, expenses: 11200, profit: 10300 },
  { month: "Jún", income: 19800, expenses: 10800, profit: 9000 },
  { month: "Júl", income: 23400, expenses: 12100, profit: 11300 },
  { month: "Aug", income: 20100, expenses: 11500, profit: 8600 },
  { month: "Sep", income: 25600, expenses: 13200, profit: 12400 },
  { month: "Okt", income: 22900, expenses: 12600, profit: 10300 },
];

const clientDistribution = [
  { name: "Corporate Design", value: 31800, color: "#9b87f5" },
  { name: "Digital Marketing", value: 22100, color: "#7E69AB" },
  { name: "ABC s.r.o.", value: 15400, color: "#6E59A5" },
  { name: "Tech Solutions", value: 9200, color: "#D6BCFA" },
  { name: "Startup Hub", value: 6500, color: "#E5DEFF" },
];

const invoiceStatus = [
  { name: "Zaplatené", value: 45, color: "#10b981" },
  { name: "Čaká na platbu", value: 30, color: "#f59e0b" },
  { name: "Po splatnosti", value: 15, color: "#ef4444" },
];

const Reports = () => {
  const totalIncome = monthlyData.reduce((sum, item) => sum + item.income, 0);
  const totalExpenses = monthlyData.reduce((sum, item) => sum + item.expenses, 0);
  const totalProfit = totalIncome - totalExpenses;
  const profitMargin = ((totalProfit / totalIncome) * 100).toFixed(1);

  return (
    <DashboardLayout>
      <div className="space-y-6 animate-fade-in">
        <div className="flex justify-between items-center">
          <div>
            <h1 className="text-3xl font-bold text-foreground">Reporty</h1>
            <p className="text-muted-foreground mt-1">
              Detailné prehľady a štatistiky vášho podnikania
            </p>
          </div>
          <Button className="bg-purple-600 hover:bg-purple-700">
            <Download className="h-4 w-4 mr-2" />
            Exportovať PDF
          </Button>
        </div>

        {/* Key Metrics */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <Card className="bg-gradient-card p-6 border-2 border-success/30 shadow-elegant-sm">
            <div className="flex items-center justify-between mb-2">
              <p className="text-sm text-muted-foreground">Celkové príjmy</p>
              <Euro className="h-5 w-5 text-success" />
            </div>
            <p className="text-2xl font-bold text-success">
              €{totalIncome.toLocaleString()}
            </p>
            <div className="flex items-center gap-1 mt-2">
              <TrendingUp className="h-4 w-4 text-success" />
              <p className="text-sm text-success">+18.2% vs minulý rok</p>
            </div>
          </Card>

          <Card className="bg-gradient-card p-6 border-2 border-destructive/30 shadow-elegant-sm">
            <div className="flex items-center justify-between mb-2">
              <p className="text-sm text-muted-foreground">Celkové výdavky</p>
              <TrendingDown className="h-5 w-5 text-destructive" />
            </div>
            <p className="text-2xl font-bold text-destructive">
              €{totalExpenses.toLocaleString()}
            </p>
            <div className="flex items-center gap-1 mt-2">
              <TrendingUp className="h-4 w-4 text-destructive" />
              <p className="text-sm text-destructive">+12.5% vs minulý rok</p>
            </div>
          </Card>

          <Card className="bg-gradient-card p-6 border-2 border-primary/30 shadow-elegant-sm">
            <div className="flex items-center justify-between mb-2">
              <p className="text-sm text-muted-foreground">Čistý zisk</p>
              <BarChart3 className="h-5 w-5 text-primary" />
            </div>
            <p className="text-2xl font-bold text-primary">
              €{totalProfit.toLocaleString()}
            </p>
            <div className="flex items-center gap-1 mt-2">
              <TrendingUp className="h-4 w-4 text-primary" />
              <p className="text-sm text-primary">Marža: {profitMargin}%</p>
            </div>
          </Card>

          <Card className="bg-gradient-card p-6 border-2 border-accent/30 shadow-elegant-sm">
            <div className="flex items-center justify-between mb-2">
              <p className="text-sm text-muted-foreground">Priemerná faktúra</p>
              <FileText className="h-5 w-5 text-accent" />
            </div>
            <p className="text-2xl font-bold text-accent">€1,847</p>
            <div className="flex items-center gap-1 mt-2">
              <TrendingUp className="h-4 w-4 text-accent" />
              <p className="text-sm text-accent">+8.4% vs minulý mesiac</p>
            </div>
          </Card>
        </div>

        {/* Charts Tabs */}
        <Tabs defaultValue="revenue" className="space-y-4">
          <TabsList className="bg-card border border-border">
            <TabsTrigger value="revenue">Príjmy a výdavky</TabsTrigger>
            <TabsTrigger value="clients">Top klienti</TabsTrigger>
            <TabsTrigger value="invoices">Stav faktúr</TabsTrigger>
          </TabsList>

          <TabsContent value="revenue">
            <Card className="bg-gradient-card p-6 border border-border shadow-elegant-sm">
              <div className="mb-6">
                <h3 className="text-lg font-bold text-foreground mb-1">
                  Mesačné príjmy a výdavky
                </h3>
                <p className="text-sm text-muted-foreground">
                  Posledných 10 mesiacov
                </p>
              </div>
              <ResponsiveContainer width="100%" height={400}>
                <LineChart data={monthlyData}>
                  <CartesianGrid strokeDasharray="3 3" stroke="#e5e7eb" />
                  <XAxis dataKey="month" stroke="#6b7280" />
                  <YAxis stroke="#6b7280" />
                  <Tooltip
                    contentStyle={{
                      backgroundColor: "#fff",
                      border: "1px solid #e5e7eb",
                      borderRadius: "8px",
                    }}
                  />
                  <Legend />
                  <Line
                    type="monotone"
                    dataKey="income"
                    stroke="#10b981"
                    strokeWidth={3}
                    name="Príjmy"
                    dot={{ fill: "#10b981", r: 4 }}
                  />
                  <Line
                    type="monotone"
                    dataKey="expenses"
                    stroke="#ef4444"
                    strokeWidth={3}
                    name="Výdavky"
                    dot={{ fill: "#ef4444", r: 4 }}
                  />
                  <Line
                    type="monotone"
                    dataKey="profit"
                    stroke="#9b87f5"
                    strokeWidth={3}
                    name="Zisk"
                    dot={{ fill: "#9b87f5", r: 4 }}
                  />
                </LineChart>
              </ResponsiveContainer>
            </Card>
          </TabsContent>

          <TabsContent value="clients">
            <Card className="bg-gradient-card p-6 border border-border shadow-elegant-sm">
              <div className="mb-6">
                <h3 className="text-lg font-bold text-foreground mb-1">
                  Top 5 klientov podľa obratu
                </h3>
                <p className="text-sm text-muted-foreground">
                  Celkové tržby za všetky obdobia
                </p>
              </div>
              <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <ResponsiveContainer width="100%" height={300}>
                  <PieChart>
                    <Pie
                      data={clientDistribution}
                      cx="50%"
                      cy="50%"
                      labelLine={false}
                      outerRadius={100}
                      fill="#8884d8"
                      dataKey="value"
                      label={({ name, percent }) =>
                        `${name}: ${(percent * 100).toFixed(0)}%`
                      }
                    >
                      {clientDistribution.map((entry, index) => (
                        <Cell key={`cell-${index}`} fill={entry.color} />
                      ))}
                    </Pie>
                    <Tooltip
                      formatter={(value: number) => `€${value.toLocaleString()}`}
                    />
                  </PieChart>
                </ResponsiveContainer>

                <div className="space-y-3">
                  {clientDistribution.map((client, index) => (
                    <div
                      key={client.name}
                      className="flex items-center justify-between p-4 bg-card rounded-lg border border-border"
                    >
                      <div className="flex items-center gap-3">
                        <div className="flex items-center justify-center w-8 h-8 rounded-full bg-primary/10 text-primary font-bold">
                          {index + 1}
                        </div>
                        <div>
                          <p className="font-semibold text-foreground">
                            {client.name}
                          </p>
                          <div
                            className="w-3 h-3 rounded-full mt-1"
                            style={{ backgroundColor: client.color }}
                          />
                        </div>
                      </div>
                      <p className="text-lg font-bold text-primary">
                        €{client.value.toLocaleString()}
                      </p>
                    </div>
                  ))}
                </div>
              </div>
            </Card>
          </TabsContent>

          <TabsContent value="invoices">
            <Card className="bg-gradient-card p-6 border border-border shadow-elegant-sm">
              <div className="mb-6">
                <h3 className="text-lg font-bold text-foreground mb-1">
                  Prehľad stavu faktúr
                </h3>
                <p className="text-sm text-muted-foreground">
                  Celkovo 90 faktúr v systéme
                </p>
              </div>
              <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <ResponsiveContainer width="100%" height={300}>
                  <BarChart data={invoiceStatus}>
                    <CartesianGrid strokeDasharray="3 3" stroke="#e5e7eb" />
                    <XAxis dataKey="name" stroke="#6b7280" />
                    <YAxis stroke="#6b7280" />
                    <Tooltip
                      contentStyle={{
                        backgroundColor: "#fff",
                        border: "1px solid #e5e7eb",
                        borderRadius: "8px",
                      }}
                    />
                    <Bar dataKey="value" radius={[8, 8, 0, 0]}>
                      {invoiceStatus.map((entry, index) => (
                        <Cell key={`cell-${index}`} fill={entry.color} />
                      ))}
                    </Bar>
                  </BarChart>
                </ResponsiveContainer>

                <div className="space-y-4">
                  {invoiceStatus.map((status) => (
                    <div
                      key={status.name}
                      className="p-6 bg-card rounded-lg border-2"
                      style={{ borderColor: status.color }}
                    >
                      <div className="flex items-center justify-between mb-2">
                        <p className="font-semibold text-foreground">
                          {status.name}
                        </p>
                        <div
                          className="w-4 h-4 rounded-full"
                          style={{ backgroundColor: status.color }}
                        />
                      </div>
                      <p className="text-3xl font-bold" style={{ color: status.color }}>
                        {status.value}
                      </p>
                      <p className="text-sm text-muted-foreground mt-1">
                        {((status.value / 90) * 100).toFixed(1)}% z celkového počtu
                      </p>
                    </div>
                  ))}
                </div>
              </div>
            </Card>
          </TabsContent>
        </Tabs>

        {/* Recent Activity */}
        <Card className="bg-gradient-card p-6 border border-border shadow-elegant-sm">
          <h3 className="text-lg font-bold text-foreground mb-4 flex items-center gap-2">
            <Calendar className="h-5 w-5 text-primary" />
            Posledná aktivita
          </h3>
          <div className="space-y-3">
            <div className="flex items-center gap-4 p-4 bg-card rounded-lg border border-border">
              <div className="w-10 h-10 rounded-full bg-success/10 flex items-center justify-center">
                <FileText className="h-5 w-5 text-success" />
              </div>
              <div className="flex-1">
                <p className="font-semibold text-foreground">
                  Faktúra INV-025 bola zaplatená
                </p>
                <p className="text-sm text-muted-foreground">
                  Corporate Design • €5,200
                </p>
              </div>
              <p className="text-sm text-muted-foreground">Dnes, 14:32</p>
            </div>

            <div className="flex items-center gap-4 p-4 bg-card rounded-lg border border-border">
              <div className="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                <Users className="h-5 w-5 text-primary" />
              </div>
              <div className="flex-1">
                <p className="font-semibold text-foreground">
                  Nový klient pridaný do systému
                </p>
                <p className="text-sm text-muted-foreground">Innovation Lab s.r.o.</p>
              </div>
              <p className="text-sm text-muted-foreground">Dnes, 11:15</p>
            </div>

            <div className="flex items-center gap-4 p-4 bg-card rounded-lg border border-border">
              <div className="w-10 h-10 rounded-full bg-warning/10 flex items-center justify-center">
                <FileText className="h-5 w-5 text-warning" />
              </div>
              <div className="flex-1">
                <p className="font-semibold text-foreground">
                  Nová faktúra vytvorená
                </p>
                <p className="text-sm text-muted-foreground">
                  Tech Solutions • INV-026 • €3,450
                </p>
              </div>
              <p className="text-sm text-muted-foreground">Včera, 16:20</p>
            </div>
          </div>
        </Card>
      </div>
    </DashboardLayout>
  );
};

export default Reports;
