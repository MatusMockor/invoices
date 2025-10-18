import api from '@/lib/axios';
import type { User, ApiResponse } from '@/types';

export interface ProfileUpdateData {
  name: string;
  email: string;
}

export interface ProfileDeleteData {
  password: string;
}

export const profileService = {
  async get(): Promise<ApiResponse<User>> {
    const response = await api.get('/profile');
    return response.data;
  },

  async update(data: ProfileUpdateData): Promise<ApiResponse<User>> {
    const response = await api.put('/profile', data);
    return response.data;
  },

  async delete(data: ProfileDeleteData): Promise<void> {
    await api.delete('/profile', { data });
  },
};
