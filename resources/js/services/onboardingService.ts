import api from '@/lib/axios';
import type { User } from '@/types';
import {ZodString} from "zod";

export interface CompanyData {
  ico: string;
  name: string;
  street: string;
  city: string;
  postal_code: string;
  dic?: string;
  ic_dph?: string;
}

export const onboardingService = {
  async createCompany(data: {
      ico?: ZodString["_output"];
      name?: ZodString["_output"];
      street?: ZodString["_output"];
      city?: ZodString["_output"];
      postal_code?: ZodString["_output"];
      dic: string;
      ic_dph: string
  }): Promise<{ message: string; user: User }> {
    const response = await api.post('/onboarding', data);
    return response.data;
  },

  async checkOnboarding(): Promise<{ needs_onboarding: boolean }> {
    const response = await api.get('/onboarding/check');
    return response.data;
  },
};
