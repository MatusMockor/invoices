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
        authService.removeToken();
        return null;
      }
    },
    retry: false,
    staleTime: Infinity,
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

