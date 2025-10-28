import api from '@/lib/axios';
import type { ApiResponse } from '@/types';

export interface UserSettings {
  invoice_template: 'classic' | 'modern' | 'minimal' | 'bold';
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
