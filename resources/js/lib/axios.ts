import axios from 'axios';
import { API_CONFIG, getApiUrl } from '@/config/api';

const instance = axios.create({
  baseURL: getApiUrl(),
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true,
  withXSRFToken: true,
  timeout: API_CONFIG.timeout,
});

instance.interceptors.request.use(
  (config) => {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (token) {
      config.headers['X-CSRF-TOKEN'] = token;
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
    // Don't redirect on 401 - let components handle authentication state
    // The useAuth hook will handle checking if user is authenticated
    return Promise.reject(error);
  }
);

export default instance;

