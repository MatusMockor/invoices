import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { invoiceService, type InvoiceCreateData, type InvoiceUpdateData, type InvoiceFilters } from '@/services';
import { useCompanyContext } from '@/contexts/CompanyContext';

export const useInvoices = (filters?: InvoiceFilters) => {
  const queryClient = useQueryClient();
  const { selectedCompanyId } = useCompanyContext();

  const { data, isLoading, error } = useQuery({
    queryKey: ['invoices', selectedCompanyId, filters],
    queryFn: () => invoiceService.getAll(filters),
  });

  const createMutation = useMutation({
    mutationFn: (data: InvoiceCreateData) => invoiceService.create(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['invoices'] });
    },
  });

  const updateMutation = useMutation({
    mutationFn: ({ id, data }: { id: number; data: InvoiceUpdateData }) => 
      invoiceService.update(id, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['invoices'] });
    },
  });

  const deleteMutation = useMutation({
    mutationFn: (id: number) => invoiceService.delete(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['invoices'] });
    },
  });

  const downloadPdfMutation = useMutation({
    mutationFn: (id: number) => invoiceService.downloadPdf(id),
    onSuccess: (blob, id) => {
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = `invoice-${id}.pdf`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      window.URL.revokeObjectURL(url);
    },
  });

  return {
    invoices: data?.data || [],
    pagination: data ? {
      currentPage: data.current_page,
      lastPage: data.last_page,
      perPage: data.per_page,
      total: data.total,
    } : undefined,
    isLoading,
    error,
    createInvoice: createMutation.mutateAsync,
    updateInvoice: updateMutation.mutateAsync,
    deleteInvoice: deleteMutation.mutateAsync,
    downloadPdf: downloadPdfMutation.mutateAsync,
    isCreating: createMutation.isPending,
    isUpdating: updateMutation.isPending,
    isDeleting: deleteMutation.isPending,
  };
};

export const useInvoice = (id: number) => {
  const { data, isLoading, error } = useQuery({
    queryKey: ['invoice', id],
    queryFn: () => invoiceService.getById(id),
    enabled: !!id,
  });

  return {
    invoice: data?.data,
    isLoading,
    error,
  };
};

