import { useQuery } from '@tanstack/react-query';
import { reportService, type ReportFilters } from '@/services';
import { useCompanyContext } from '@/contexts/CompanyContext';

export const useReports = (filters?: ReportFilters) => {
  const { selectedCompanyId } = useCompanyContext();

  const { data, isLoading, error, refetch } = useQuery({
    queryKey: ['reports', selectedCompanyId, filters],
    queryFn: () => reportService.getReports(filters),
    enabled: !!selectedCompanyId,
    staleTime: 2 * 60 * 1000, // 2 minutes
    gcTime: 5 * 60 * 1000, // 5 minutes
  });

  return {
    report: data,
    financialReport: data?.financial_report,
    invoiceSummary: data?.invoice_summary,
    isLoading,
    error,
    refetch,
  };
};
