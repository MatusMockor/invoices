import api from '@/lib/axios';
import type { User } from '@/types';

export interface CompanyData {
  ico: string;
  name: string;
  street: string;
  city: string;
  postal_code: string;
  dic: string;
  ic_dph: string;
}

export const onboardingService = {
  async createCompany(data: CompanyData): Promise<{ message: string; user: User }> {
    const response = await api.post('/onboarding', data);
    return response.data;
  },

  async checkOnboarding(): Promise<{ needs_onboarding: boolean }> {
    const response = await api.get('/onboarding/check');
    return response.data;
  },
};
