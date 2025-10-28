import api from '@/lib/axios';

export interface AnalyticsStatistics {
  totalIncome: number;
  totalExpenses: number;
  balance: number;
  totalCompanies: number;
  companiesWithVat: number;
  companiesWithoutVat: number;
  vatPercentage: number;
  companiesThisYear: number;
  companiesLastYear: number;
  yearGrowthPercentage: number;
  topCountries: Record<string, number>;
  companiesPerYear: Record<number, number>;
  companiesPerMonth: Record<number, number>;
}

export interface MonthlyData {
  labels: string[];
  income: number[];
  expenses: number[];
}

export interface AnalyticsResponse {
  statistics: AnalyticsStatistics;
  monthlyData: MonthlyData;
  currentYear: number;
}

export const analyticsService = {
  async getAnalytics(): Promise<AnalyticsResponse> {
    try {
      const response = await api.get('/analytics');
      console.log('Analytics API response:', response.data);

      // Laravel Resource wraps data in 'data' key
      if (response.data && response.data.data) {
        return response.data.data;
      }

      // If no nested data, return the response directly
      if (response.data) {
        return response.data;
      }

      throw new Error('Invalid response format from analytics API');
    } catch (error) {
      console.error('Analytics API error:', error);
      throw error;
    }
  },
};
