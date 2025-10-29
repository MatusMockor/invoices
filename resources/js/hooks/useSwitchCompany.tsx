import { useMutation, useQueryClient } from '@tanstack/react-query';
import { companyService } from '@/services/companyService';
import { useToast } from '@/hooks/use-toast';

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
    onSuccess: async (_, companyId) => {
      // Invalidate all company-dependent queries
      await queryClient.invalidateQueries({ queryKey: ['analytics'] });
      await queryClient.invalidateQueries({ queryKey: ['invoices'] });
      await queryClient.invalidateQueries({ queryKey: ['simple-contacts'] });
      await queryClient.invalidateQueries({ queryKey: ['companies'] });

      if (showToast) {
        toast({
          title: "Firma zmenená",
          description: "Úspešne ste prepli firmu.",
        });
      }

      onSuccess?.(companyId);
    },
    onError: (error: unknown) => {
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
