import { createContext, useContext, type ReactNode } from 'react';
import { useAuth } from '@/hooks/useAuth';
import type { User } from '@/types';

interface AuthContextType {
  user: User | undefined;
  isLoading: boolean;
  isError: boolean;
  isAuthenticated: boolean;
  login: (credentials: any) => Promise<any>;
  register: (data: any) => Promise<any>;
  logout: () => Promise<void>;
  refetch: () => void;
  isLoggingIn: boolean;
  isRegistering: boolean;
  isLoggingOut: boolean;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider = ({ children }: { children: ReactNode }) => {
  const auth = useAuth();

  return <AuthContext.Provider value={auth}>{children}</AuthContext.Provider>;
};

export const useAuthContext = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuthContext must be used within AuthProvider');
  }
  return context;
};

