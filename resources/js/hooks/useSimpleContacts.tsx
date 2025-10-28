import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { simpleContactService, type SimpleContactCreateData, type SimpleContactUpdateData } from '@/services/simpleContactService';
import { useCompanyContext } from '@/contexts/CompanyContext';

export const useSimpleContacts = (perPage: number = 15) => {
  const queryClient = useQueryClient();
  const { selectedCompanyId } = useCompanyContext();

  const { data, isLoading, error } = useQuery({
    queryKey: ['simple-contacts', selectedCompanyId, perPage],
    queryFn: () => simpleContactService.getAll(perPage),
  });

  const createMutation = useMutation({
    mutationFn: (data: SimpleContactCreateData) => simpleContactService.create(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['simple-contacts'] });
    },
  });

  const updateMutation = useMutation({
    mutationFn: ({ id, data }: { id: number; data: SimpleContactUpdateData }) =>
      simpleContactService.update(id, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['simple-contacts'] });
    },
  });

  const deleteMutation = useMutation({
    mutationFn: (id: number) => simpleContactService.delete(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['simple-contacts'] });
    },
  });

  return {
    contacts: data?.data || null,
    isLoading,
    error,
    createContact: createMutation.mutateAsync,
    updateContact: updateMutation.mutateAsync,
    deleteContact: deleteMutation.mutateAsync,
    isCreating: createMutation.isPending,
    isUpdating: updateMutation.isPending,
    isDeleting: deleteMutation.isPending,
  };
};

export const useSimpleContact = (id: number) => {
  const { data, isLoading, error } = useQuery({
    queryKey: ['simple-contact', id],
    queryFn: () => simpleContactService.getById(id),
    enabled: !!id,
  });

  return {
    contact: data?.data,
    isLoading,
    error,
  };
};
