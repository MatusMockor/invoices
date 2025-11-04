import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { settingsService, type SettingsUpdateData } from '@/services/settingsService';
import { useCompanyContext } from '@/contexts/CompanyContext';

export const useSettings = () => {
  const queryClient = useQueryClient();
  const { selectedCompanyId } = useCompanyContext();

  const { data, isLoading, isFetching, error, refetch } = useQuery({
    queryKey: ['settings', selectedCompanyId],
    queryFn: () => settingsService.get(),
    enabled: !!selectedCompanyId, // Only fetch when company is selected
  });

  const updateMutation = useMutation({
    mutationFn: (data: SettingsUpdateData) => settingsService.update(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['settings', selectedCompanyId] });
    },
  });

  return {
    settings: data?.data,
    isLoading,
    isFetching,
    error,
    updateSettings: updateMutation.mutateAsync,
    isUpdating: updateMutation.isPending,
    refetch,
  };
};
