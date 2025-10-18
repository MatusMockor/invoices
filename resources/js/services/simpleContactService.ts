import api from '@/lib/axios';
import type { SimpleContact, ApiResponse, PaginatedResponse } from '@/types';

export interface SimpleContactCreateData {
  first_name?: string;
  last_name?: string;
  email?: string;
  phone?: string;
  position?: string;
  notes?: string;
}

export type SimpleContactUpdateData = Partial<SimpleContactCreateData>;

export const simpleContactService = {
  async getAll(perPage: number = 15): Promise<ApiResponse<PaginatedResponse<SimpleContact>>> {
    const response = await api.get('/contacts', {
      params: { per_page: perPage },
    });
    return response.data;
  },

  async getById(id: number): Promise<ApiResponse<SimpleContact>> {
    const response = await api.get(`/contacts/${id}`);
    return response.data;
  },

  async create(data: SimpleContactCreateData): Promise<ApiResponse<SimpleContact>> {
    const response = await api.post('/contacts', data);
    return response.data;
  },

  async update(id: number, data: SimpleContactUpdateData): Promise<ApiResponse<SimpleContact>> {
    const response = await api.put(`/contacts/${id}`, data);
    return response.data;
  },

  async delete(id: number): Promise<void> {
    await api.delete(`/contacts/${id}`);
  },
};
