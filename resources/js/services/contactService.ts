import api from '@/lib/axios';
import type { Contact, ContactActivity, ContactTag, ApiResponse, PaginatedResponse } from '@/types';

export interface ContactFilters {
  status?: 'active' | 'inactive' | 'lead' | 'customer' | 'archived';
  search?: string;
  tag_id?: number;
  page?: number;
  per_page?: number;
}

export interface ContactCreateData {
  first_name: string;
  last_name: string;
  email?: string;
  status: 'active' | 'inactive' | 'lead' | 'customer' | 'archived';
  notes?: string;
  phones?: Array<{
    phone: string;
    type: 'mobile' | 'work' | 'home' | 'other';
    is_primary: boolean;
  }>;
  emails?: Array<{
    email: string;
    type: 'work' | 'personal' | 'other';
    is_primary: boolean;
  }>;
  addresses?: Array<{
    address: string;
    city: string;
    postal_code: string;
    country: string;
    type: 'home' | 'work' | 'other';
    is_primary: boolean;
  }>;
  tag_ids?: number[];
}

export type ContactUpdateData = Partial<ContactCreateData>;

export interface BulkUpdateData {
  contact_ids: number[];
  data: {
    status?: string;
    tag_ids?: number[];
  };
}

export const contactService = {
  async getAll(filters?: ContactFilters): Promise<PaginatedResponse<Contact>> {
    const response = await api.get('/contacts', { params: filters });
    return response.data;
  },

  async getById(id: number): Promise<ApiResponse<Contact>> {
    const response = await api.get(`/contacts/${id}`);
    return response.data;
  },

  async create(data: ContactCreateData): Promise<ApiResponse<Contact>> {
    const response = await api.post('/contacts', data);
    return response.data;
  },

  async update(id: number, data: ContactUpdateData): Promise<ApiResponse<Contact>> {
    const response = await api.put(`/contacts/${id}`, data);
    return response.data;
  },

  async delete(id: number): Promise<void> {
    await api.delete(`/contacts/${id}`);
  },

  async restore(id: number): Promise<ApiResponse<Contact>> {
    const response = await api.post(`/contacts/${id}/restore`);
    return response.data;
  },

  async getActivities(id: number): Promise<ApiResponse<ContactActivity[]>> {
    const response = await api.get(`/contacts/${id}/activities`);
    return response.data;
  },

  async bulkUpdate(data: BulkUpdateData): Promise<ApiResponse<any>> {
    const response = await api.post('/contacts/bulk-update', data);
    return response.data;
  },

  async bulkDelete(contactIds: number[]): Promise<void> {
    await api.post('/contacts/bulk-delete', { contact_ids: contactIds });
  },

  async import(file: File): Promise<ApiResponse<any>> {
    const formData = new FormData();
    formData.append('file', file);
    const response = await api.post('/contacts/import', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data;
  },

  async export(filters?: ContactFilters): Promise<Blob> {
    const response = await api.post('/contacts/export', filters, {
      responseType: 'blob',
    });
    return response.data;
  },

  async getTags(): Promise<ApiResponse<ContactTag[]>> {
    const response = await api.get('/tags');
    return response.data;
  },
};

