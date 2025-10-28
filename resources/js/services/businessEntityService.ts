import api from '@/lib/axios';
import type { BusinessEntity, ApiResponse } from '@/types';

export interface BusinessEntityCreateData {
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
}

export type BusinessEntityUpdateData = Partial<BusinessEntityCreateData>;

export interface FetchByIcoParams {
  ico: string;
}

export const businessEntityService = {
  async getAll(): Promise<ApiResponse<BusinessEntity[]>> {
    const response = await api.get('/business-entities');
    return response.data;
  },

  async getById(id: number): Promise<ApiResponse<BusinessEntity>> {
    const response = await api.get(`/business-entities/${id}`);
    return response.data;
  },

  async create(data: BusinessEntityCreateData): Promise<ApiResponse<BusinessEntity>> {
    const response = await api.post('/business-entities', data);
    return response.data;
  },

  async update(id: number, data: BusinessEntityUpdateData): Promise<ApiResponse<BusinessEntity>> {
    const response = await api.put(`/business-entities/${id}`, data);
    return response.data;
  },

  async delete(id: number): Promise<void> {
    await api.delete(`/business-entities/${id}`);
  },

  async fetchByIco(ico: string): Promise<ApiResponse<any>> {
    const response = await api.get('/business-entities-fetch-by-ico', { params: { ico } });
    return response.data;
  },
};

