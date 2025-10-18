/**
 * API Configuration
 * Centralized API endpoint configuration
 */

export const API_CONFIG = {
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000',
  apiPrefix: '/api',
  timeout: 30000, // 30 seconds
} as const;

export const getApiUrl = (path: string = ''): string => {
  return `${API_CONFIG.baseURL}${API_CONFIG.apiPrefix}${path}`;
};

export const getFullUrl = (path: string): string => {
  return `${API_CONFIG.baseURL}${path}`;
};
