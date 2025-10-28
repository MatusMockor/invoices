import api from '@/lib/axios';
import type { User } from '@/types';

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

export interface RegisterWithCompanyData {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
  company_ico: string;
  company_name: string;
  company_street: string;
  company_city: string;
  company_postal_code: string;
  company_dic: string;
  company_ic_dph: string;
}

const TOKEN_KEY = 'auth_token';

export const authService = {
  async login(credentials: LoginCredentials): Promise<{ message: string; user: User; token: string }> {
    const response = await api.post('/login', credentials);
    const { token } = response.data;
    localStorage.setItem(TOKEN_KEY, token);
    return response.data;
  },

  async register(data: RegisterData): Promise<{ message: string; user: User; token: string }> {
    const response = await api.post('/register', data);
    const { token } = response.data;
    localStorage.setItem(TOKEN_KEY, token);
    return response.data;
  },

  async registerWithCompany(data: RegisterWithCompanyData): Promise<{ message: string; user: User; token: string }> {
    const response = await api.post('/register-with-company', data);
    const { token } = response.data;
    localStorage.setItem(TOKEN_KEY, token);
    return response.data;
  },

  async logout(): Promise<void> {
    await api.post('/logout');
    localStorage.removeItem(TOKEN_KEY);
  },

  async getCurrentUser(): Promise<{ user: User }> {
    const response = await api.get('/user');
    // Laravel Resource wraps data in 'data' key
    return { user: response.data.data };
  },

  getToken(): string | null {
    return localStorage.getItem(TOKEN_KEY);
  },

  removeToken(): void {
    localStorage.removeItem(TOKEN_KEY);
  },
};

