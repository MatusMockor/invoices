import { useQuery } from '@tanstack/react-query';
import { analyticsService } from '@/services';
import { useCompanyContext } from '@/contexts/CompanyContext';

export const useAnalytics = () => {
  const { selectedCompanyId } = useCompanyContext();

  const { data, isLoading, error } = useQuery({
    queryKey: ['analytics', selectedCompanyId],
    queryFn: () => analyticsService.getAnalytics(),
    enabled: !!selectedCompanyId,
  });

  return {
    analytics: data,
    statistics: data?.statistics,
    monthlyData: data?.monthlyData,
    currentYear: data?.currentYear,
    isLoading,
    error,
  };
};
