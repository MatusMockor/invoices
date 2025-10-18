import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { authService, type LoginCredentials, type RegisterData } from '@/services';
import type { User } from '@/types';

export const useAuth = () => {
  const queryClient = useQueryClient();

  const { data: user, isLoading, refetch } = useQuery<User>({
    queryKey: ['user'],
    queryFn: async () => {
      try {
        const response = await authService.getCurrentUser();
        return response.user;
      } catch (error) {
        // If 401, user is not authenticated - this is expected
        return null;
      }
    },
    retry: false,
    staleTime: Infinity,
    enabled: false, // Don't fetch automatically, only manually via refetch
  });

  const loginMutation = useMutation({
    mutationFn: async (credentials: LoginCredentials) => {
      // Get CSRF cookie before login
      await authService.getCsrfCookie();
      return authService.login(credentials);
    },
    onSuccess: (data) => {
      queryClient.setQueryData(['user'], data.user);
    },
  });

  const registerMutation = useMutation({
    mutationFn: async (data: RegisterData) => {
      // Get CSRF cookie before register
      await authService.getCsrfCookie();
      return authService.register(data);
    },
    onSuccess: (data) => {
      queryClient.setQueryData(['user'], data.user);
    },
  });

  const logoutMutation = useMutation({
    mutationFn: () => authService.logout(),
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

