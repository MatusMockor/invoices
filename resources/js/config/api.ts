/**
 * API Configuration
 * Centralized API endpoint configuration
 */

export const API_CONFIG = {
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost',
  apiPrefix: '/api',
  timeout: 10000, // 10 seconds
} as const;

export const getApiUrl = (path: string = ''): string => {
  // In development mode, use relative URL so Vite proxy works
  // In production, use absolute URL from env or default
  if (import.meta.env.DEV) {
    return `${API_CONFIG.apiPrefix}${path}`;
  }
  return `${API_CONFIG.baseURL}${API_CONFIG.apiPrefix}${path}`;
};

export const getFullUrl = (path: string): string => {
  if (import.meta.env.DEV) {
    return path;
  }
  return `${API_CONFIG.baseURL}${path}`;
};
