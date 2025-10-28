import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { companyService, type CompanyCreateData, type CompanyUpdateData } from '@/services';

export const useCompanies = () => {
  const queryClient = useQueryClient();

  const { data, isLoading, error } = useQuery({
    queryKey: ['companies'],
    queryFn: () => companyService.getAll(),
  });

  const createMutation = useMutation({
    mutationFn: (data: CompanyCreateData) => companyService.create(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['companies'] });
    },
  });

  const updateMutation = useMutation({
    mutationFn: ({ id, data }: { id: number; data: CompanyUpdateData }) => 
      companyService.update(id, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['companies'] });
    },
  });

  const deleteMutation = useMutation({
    mutationFn: (id: number) => companyService.delete(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['companies'] });
    },
  });

  const switchMutation = useMutation({
    mutationFn: (id: number) => companyService.switchCompany(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['companies'] });
      window.location.reload();
    },
  });

  return {
    companies: data?.data || [],
    isLoading,
    error,
    createCompany: createMutation.mutateAsync,
    updateCompany: updateMutation.mutateAsync,
    deleteCompany: deleteMutation.mutateAsync,
    switchCompany: switchMutation.mutateAsync,
    isCreating: createMutation.isPending,
    isUpdating: updateMutation.isPending,
    isDeleting: deleteMutation.isPending,
    isSwitching: switchMutation.isPending,
  };
};

export const useCompany = (id: number) => {
  const { data, isLoading, error } = useQuery({
    queryKey: ['company', id],
    queryFn: () => companyService.getById(id),
    enabled: !!id,
  });

  return {
    company: data?.data,
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
  const { data, isLoading, error } = useQuery({
    queryKey: ['companies-minimal'],
    queryFn: () => companyService.getMinimal(),
    staleTime: 5 * 60 * 1000, // 5 minutes
    gcTime: 10 * 60 * 1000, // 10 minutes
  });

  return {
    companies: data?.data || [],
    isLoading,
    error,
  };
};

