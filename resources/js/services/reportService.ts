import api from '@/lib/axios';

export interface FinancialReport {
  total_income: number;
  total_expenses: number;
  balance: number;
  income_count: number;
  expense_count: number;
  period_start: string;
  period_end: string;
}

export interface InvoiceItem {
  id: number;
  invoice_number: string;
  customer_name?: string;
  supplier_name?: string;
  issue_date: string;
  due_date: string;
  total_amount: number;
  currency: string;
  status: string;
}

export interface InvoiceSummary {
  income_invoices: InvoiceItem[];
  expense_invoices: InvoiceItem[];
}

export interface ReportData {
  financial_report: FinancialReport;
  invoice_summary: InvoiceSummary;
}

export interface ReportFilters {
  start_date?: string;
  end_date?: string;
}

export const reportService = {
  async getReports(filters?: ReportFilters): Promise<ReportData> {
    try {
      const response = await api.get('/reports', { params: filters });
      console.log('Reports API response:', response.data);

      if (response.data && response.data.data) {
        return response.data.data;
      }

      if (response.data) {
        return response.data;
      }

      throw new Error('Invalid response format from reports API');
    } catch (error) {
      console.error('Reports API error:', error);
      throw error;
    }
  },
};
