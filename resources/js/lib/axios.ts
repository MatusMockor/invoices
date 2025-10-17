import axios from 'axios';

const instance = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true,
  withXSRFToken: true,
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
    // Nepreposielaj na login pri 401 ak ide o /api/user request (check auth status)
    if (error.response?.status === 401 && !error.config?.url?.includes('/api/user')) {
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default instance;

