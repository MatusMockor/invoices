import { useMemo, useCallback } from "react";
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Legend } from "recharts";
import type { MonthlyData } from "@/services";

interface RevenueChartProps {
  monthlyData?: MonthlyData;
}

export const RevenueChart = ({ monthlyData }: RevenueChartProps) => {
  const formatValue = useCallback((value: number) => {
    return new Intl.NumberFormat('sk-SK', {
      style: 'currency',
      currency: 'EUR',
    }).format(value);
  }, []);

  const chartData = useMemo(() => {
    if (!monthlyData) return [];

    return monthlyData.labels.map((label, index) => ({
      name: label,
      income: monthlyData.income[index] || 0,
      expenses: monthlyData.expenses[index] || 0,
    }));
  }, [monthlyData]);

  if (!monthlyData) {
    return (
      <div className="bg-gradient-card rounded-xl p-6 border border-border shadow-elegant-sm animate-fade-in">
        <h3 className="text-lg font-semibold text-foreground mb-4">Mesačné príjmy a výdavky</h3>
        <div className="flex items-center justify-center h-[300px]">
          <p className="text-muted-foreground">Načítavam dáta...</p>
        </div>
      </div>
    );
  }

  return (
    <div
      className="bg-gradient-card rounded-xl p-6 border border-border shadow-elegant-sm animate-fade-in"
      role="img"
      aria-label="Graf mesačných príjmov a výdavkov"
    >
      <h3 className="text-lg font-semibold text-foreground mb-4">Mesačné príjmy a výdavky</h3>
      <ResponsiveContainer width="100%" height={300}>
        <LineChart data={chartData}>
          <CartesianGrid strokeDasharray="3 3" stroke="hsl(var(--border))" />
          <XAxis
            dataKey="name"
            stroke="hsl(var(--muted-foreground))"
            style={{ fontSize: '12px' }}
          />
          <YAxis
            stroke="hsl(var(--muted-foreground))"
            style={{ fontSize: '12px' }}
          />
          <Tooltip
            contentStyle={{
              backgroundColor: 'hsl(var(--card))',
              border: '1px solid hsl(var(--border))',
              borderRadius: '8px',
            }}
            formatter={formatValue}
          />
          <Legend />
          <Line
            type="monotone"
            dataKey="income"
            name="Príjmy"
            stroke="hsl(var(--success))"
            strokeWidth={3}
            dot={{ fill: 'hsl(var(--success))', r: 4 }}
            activeDot={{ r: 6 }}
          />
          <Line
            type="monotone"
            dataKey="expenses"
            name="Výdavky"
            stroke="hsl(var(--destructive))"
            strokeWidth={3}
            dot={{ fill: 'hsl(var(--destructive))', r: 4 }}
            activeDot={{ r: 6 }}
          />
        </LineChart>
      </ResponsiveContainer>
    </div>
  );
};
