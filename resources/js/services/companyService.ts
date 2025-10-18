import api from '@/lib/axios';
import type { Company, ApiResponse, PaginatedResponse } from '@/types';

export interface CompanyCreateData {
  name: string;
  ico: string;
  dic?: string;
  ic_dph?: string;
  address: string;
  city: string;
  postal_code: string;
  country: string;
  phone?: string;
  email?: string;
  bank_account?: string;
  iban?: string;
  swift?: string;
}

export type CompanyUpdateData = Partial<CompanyCreateData>;

export const companyService = {
  async getAll(): Promise<ApiResponse<Company[]>> {
    const response = await api.get('/companies');
    return response.data;
  },

  async getMinimal(): Promise<ApiResponse<Array<{ id: number; name: string }>>> {
    const response = await api.get('/companies/minimal');
    return response.data;
  },

  async getById(id: number): Promise<ApiResponse<Company>> {
    const response = await api.get(`/companies/${id}`);
    return response.data;
  },

  async create(data: CompanyCreateData): Promise<ApiResponse<Company>> {
    const response = await api.post('/companies', data);
    return response.data;
  },

  async update(id: number, data: CompanyUpdateData): Promise<ApiResponse<Company>> {
    const response = await api.put(`/companies/${id}`, data);
    return response.data;
  },

  async delete(id: number): Promise<void> {
    await api.delete(`/companies/${id}`);
  },

  async switchCompany(id: number): Promise<ApiResponse<Company>> {
    const response = await api.post(`/companies/${id}/switch`);
    return response.data;
  },
};

