import { useMutation, useQueryClient } from '@tanstack/react-query';
import { companyService } from '@/services/companyService';
import { useToast } from '@/hooks/use-toast';
import type { User } from '@/types';

interface UseSwitchCompanyOptions {
  onSuccess?: (companyId: number) => void;
  onError?: (error: unknown) => void;
  showToast?: boolean;
}

export const useSwitchCompany = (options: UseSwitchCompanyOptions = {}) => {
  const { toast } = useToast();
  const queryClient = useQueryClient();
  const { onSuccess, onError, showToast = true } = options;

  const mutation = useMutation({
    mutationFn: (companyId: number) => companyService.switchCompany(companyId),
    onMutate: async (companyId) => {
      // Cancel outgoing refetches to prevent race conditions
      await queryClient.cancelQueries({ queryKey: ['user'] });

      // Snapshot previous value for rollback
      const previousUser = queryClient.getQueryData<User>(['user']);

      // Optimistically update user's current company
      queryClient.setQueryData<User>(['user'], (old) => {
        if (!old) return old;
        return {
          ...old,
          current_company_id: companyId,
        };
      });

      return { previousUser };
    },
    onSuccess: async (_, companyId) => {
      // Invalidate all company-dependent queries in parallel
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: ['user'] }),
        queryClient.invalidateQueries({ queryKey: ['user-companies'] }),
        queryClient.invalidateQueries({ queryKey: ['companies'] }),
        queryClient.invalidateQueries({ queryKey: ['analytics'] }),
        queryClient.invalidateQueries({ queryKey: ['invoices'] }),
        queryClient.invalidateQueries({ queryKey: ['simple-contacts'] }),
        queryClient.invalidateQueries({ queryKey: ['business-entities'] }),
        queryClient.invalidateQueries({ queryKey: ['customer-companies'] }),
      ]);

      if (showToast) {
        toast({
          title: "Firma zmenená",
          description: "Úspešne ste prepli firmu.",
        });
      }

      onSuccess?.(companyId);
    },
    onError: (error: unknown, _, context) => {
      // Rollback optimistic update on error
      if (context?.previousUser) {
        queryClient.setQueryData(['user'], context.previousUser);
      }

      console.error('Failed to switch company:', error);

      if (showToast) {
        let errorMessage = "Nepodarilo sa prepnúť firmu. Skúste to znova.";

        if (error instanceof Error) {
          if (error.message.includes('Network') || error.message.includes('network')) {
            errorMessage = "Problém s pripojením. Skontrolujte svoje internetové pripojenie.";
          } else if (error.message.includes('404')) {
            errorMessage = "Firma nebola nájdená.";
          } else if (error.message.includes('403') || error.message.includes('Forbidden')) {
            errorMessage = "Nemáte oprávnenie na prepnutie na túto firmu.";
          }
        }

        toast({
          title: "Chyba",
          description: errorMessage,
          variant: "destructive",
        });
      }

      onError?.(error);
    },
  });

  return {
    switchCompany: mutation.mutateAsync,
    isSwitching: mutation.isPending,
    error: mutation.error,
  };
};
