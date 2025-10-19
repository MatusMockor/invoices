import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { authService, type LoginCredentials, type RegisterData } from '@/services';
import type { User } from '@/types';

export const useAuth = () => {
  const queryClient = useQueryClient();

  const { data: user, isLoading, refetch, isError } = useQuery<User | undefined>({
    queryKey: ['user'],
    queryFn: async () => {
      console.log('[useAuth] Fetching user...');
      try {
        // Add a custom timeout wrapper (5 seconds max)
        const timeoutPromise = new Promise<never>((_, reject) => {
          setTimeout(() => reject(new Error('Request timeout')), 5000);
        });

        const fetchPromise = authService.getCurrentUser();
        const response = await Promise.race([fetchPromise, timeoutPromise]);

        console.log('[useAuth] User fetched successfully:', response.user?.email);
        return response.user;
      } catch (error: any) {
        // Only remove token on 401 Unauthorized - invalid/expired token
        if (error?.response?.status === 401) {
          console.log('[useAuth] 401 error - token invalid, logging out');
          authService.removeToken();
          return undefined;
        }
        // For other errors (network, server down, timeout, etc.), keep token
        console.error('[useAuth] Error fetching user (keeping token):', error.message);
        return undefined;
      }
    },
    retry: false, // Disable retry to prevent infinite loading
    staleTime: Infinity,
    refetchOnMount: false,
    refetchOnWindowFocus: false,
    refetchOnReconnect: false,
    gcTime: 1000 * 60 * 5, // 5 minutes
    enabled: !!authService.getToken(), // Only fetch if token exists
  });

  const loginMutation = useMutation({
    mutationFn: (credentials: LoginCredentials) => authService.login(credentials),
    onSuccess: (data) => {
      queryClient.setQueryData(['user'], data.user);
      // Refetch to ensure query is enabled with new token
      refetch();
    },
  });

  const registerMutation = useMutation({
    mutationFn: (data: RegisterData) => authService.register(data),
    onSuccess: (data) => {
      queryClient.setQueryData(['user'], data.user);
      // Refetch to ensure query is enabled with new token
      refetch();
    },
  });

  const logoutMutation = useMutation({
    mutationFn: async () => {
      // Only call API if token exists
      if (authService.getToken()) {
        await authService.logout();
      } else {
        authService.removeToken();
      }
    },
    onSuccess: () => {
      queryClient.setQueryData(['user'], null);
      queryClient.clear();
      window.location.href = '/login';
    },
  });

  return {
    user,
    isLoading,
    isAuthenticated: !!user,
    login: loginMutation.mutateAsync,
    register: registerMutation.mutateAsync,
    logout: logoutMutation.mutateAsync,
    refetch, // Expose refetch to manually check auth status
    isLoggingIn: loginMutation.isPending,
    isRegistering: registerMutation.isPending,
    isLoggingOut: logoutMutation.isPending,
  };
};

