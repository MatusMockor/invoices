import api from '@/lib/axios';
import axios from 'axios';
import { getFullUrl } from '@/config/api';
import type { User, ApiResponse } from '@/types';

export interface LoginCredentials {
  email: string;
  password: string;
  remember?: boolean;
}

export interface RegisterData {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

export const authService = {
  async login(credentials: LoginCredentials): Promise<ApiResponse<{ user: User; token: string }>> {
    const response = await api.post('/login', credentials);
    return response.data;
  },

  async register(data: RegisterData): Promise<ApiResponse<{ user: User; token: string }>> {
    const response = await api.post('/register', data);
    return response.data;
  },

  async logout(): Promise<void> {
    await api.post('/logout');
  },

  async getCurrentUser(): Promise<ApiResponse<User>> {
    const response = await api.get('/user');
    return response.data;
  },

  async getCsrfCookie(): Promise<void> {
    await axios.get(getFullUrl('/sanctum/csrf-cookie'), { withCredentials: true });
  },
};

