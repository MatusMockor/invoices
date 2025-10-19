import api from '@/lib/axios';
import type { Invoice, InvoiceItem, ApiResponse, PaginatedResponse } from '@/types';

export interface InvoiceCreateData {
  // Client information
  clientName: string;
  clientIco: string;
  clientDic: string;
  clientIcDph: string;
  clientAddress: string;

  // Invoice details
  invoiceNumber: string;
  issue_date: string;
  due_date: string;
  delivery_date: string;
  variableSymbol?: string;
  constantSymbol?: string;
  specificSymbol?: string;
  currency?: string;
  notes?: string;
  status?: 'draft' | 'sent' | 'paid' | 'overdue' | 'cancelled';

  // Invoice items
  items: InvoiceItemData[];
}

export interface InvoiceItemData {
  description: string;
  quantity: number;
  price: number;
  vat_rate?: number; // Optional, defaults to 20% on backend
}

export type InvoiceUpdateData = Partial<InvoiceCreateData>;

export interface InvoiceFilters {
  status?: string;
  company_id?: number;
  date_from?: string;
  date_to?: string;
  page?: number;
  per_page?: number;
}

export const invoiceService = {
  async getAll(filters?: InvoiceFilters): Promise<PaginatedResponse<Invoice>> {
    const response = await api.get('/invoices', { params: filters });
    return response.data;
  },

  async getById(id: number): Promise<ApiResponse<Invoice>> {
    const response = await api.get(`/invoices/${id}`);
    return response.data;
  },

  async create(data: InvoiceCreateData): Promise<ApiResponse<Invoice>> {
    const response = await api.post('/invoices', data);
    return response.data;
  },

  async update(id: number, data: InvoiceUpdateData): Promise<ApiResponse<Invoice>> {
    const response = await api.put(`/invoices/${id}`, data);
    return response.data;
  },

  async delete(id: number): Promise<void> {
    await api.delete(`/invoices/${id}`);
  },

  async downloadPdf(id: number): Promise<Blob> {
    const response = await api.get(`/invoices/${id}/pdf/download`, {
      responseType: 'blob',
    });
    return response.data;
  },

  async viewPdf(id: number): Promise<Blob> {
    const response = await api.get(`/invoices/${id}/pdf/view`, {
      responseType: 'blob',
    });
    return response.data;
  },
};

