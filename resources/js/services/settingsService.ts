import api from '@/lib/axios';
import type { ApiResponse } from '@/types';

export interface CompanyData {
  id: number;
  name: string;
  ico: string;
  dic: string | null;
  ic_dph: string | null;
  address: string | null;
  city: string | null;
  postal_code: string | null;
  country: string | null;
  phone: string | null;
  email: string | null;
  iban: string | null;
  swift: string | null;
}

export interface UserSettings {
  invoice_template: 'classic' | 'modern' | 'minimal' | 'bold';
  company: CompanyData | null;
}

export interface SettingsUpdateData {
  invoice_template: 'classic' | 'modern' | 'minimal' | 'bold';
}

export const settingsService = {
  async get(): Promise<ApiResponse<UserSettings>> {
    const response = await api.get('/settings');
    return response.data;
  },

  async update(data: SettingsUpdateData): Promise<ApiResponse<UserSettings>> {
    const response = await api.put('/settings', data);
    return response.data;
  },
};
