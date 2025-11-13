import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { companyService } from '@/services';
import { userCompanyService } from '@/services/userCompanyService';
import type { UserCompany } from '@/types';

/**
 * Hook for managing user's own companies
 *
 * @deprecated Use useUserCompanies hook instead for better type safety and consistency
 */
export const useCompanies = () => {
  const queryClient = useQueryClient();

  const { data, isLoading, error } = useQuery<UserCompany[]>({
    queryKey: ['companies'],
    queryFn: () => userCompanyService.list(),
  });

  /**
   * @deprecated This mutation is legacy code that reloads the entire page.
   * Use the `useSwitchCompany` hook instead for better UX and proper query invalidation.
   * This is kept for backwards compatibility but should be removed in the future.
   */
  const switchMutation = useMutation({
    mutationFn: (id: number) => companyService.switchCompany(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['companies'] });
      window.location.reload();
    },
  });

  return {
    companies: data || [],
    isLoading,
    error,
    switchCompany: switchMutation.mutateAsync,
    isSwitching: switchMutation.isPending,
  };
};

/**
 * Hook for getting a single user company by ID
 *
 * @deprecated Use useUserCompany hook instead
 */
export const useCompany = (id: number) => {
  const { data, isLoading, error } = useQuery<UserCompany>({
    queryKey: ['company', id],
    queryFn: () => userCompanyService.getById(id),
    enabled: !!id,
  });

  return {
    company: data,
    isLoading,
    error,
  };
};

/**
 * Hook for getting minimal company data (only id and name) for TopBar/dropdowns.
 * More performant than useCompanies() as it fetches less data.
 * Data is cached for 5 minutes to reduce unnecessary API calls.
 */
export const useCompaniesMinimal = () => {
  const { data, isLoading, error } = useQuery<Array<{ id: number; name: string }>>({
    queryKey: ['companies-minimal'],
    queryFn: () => userCompanyService.getMinimal(),
    staleTime: 5 * 60 * 1000, // 5 minutes
    gcTime: 10 * 60 * 1000, // 10 minutes
  });

  return {
    companies: data || [],
    isLoading,
    error,
  };
};

