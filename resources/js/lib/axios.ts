import axios from 'axios';
import { API_CONFIG, getApiUrl } from '@/config/api';

const TOKEN_KEY = 'auth_token';

const instance = axios.create({
  baseURL: getApiUrl(),
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  timeout: API_CONFIG.timeout,
});

instance.interceptors.request.use(
  (config) => {
    // Add Bearer token if available
    const token = localStorage.getItem(TOKEN_KEY);
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

instance.interceptors.response.use(
  (response) => response,
  (error) => {
    // If 401 unauthorized, remove invalid/expired token
    if (error.response?.status === 401) {
      localStorage.removeItem(TOKEN_KEY);
    }
    return Promise.reject(error);
  }
);

export default instance;

