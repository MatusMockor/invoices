import { useQuery, useQueryClient } from "@tanstack/react-query";
import { userCompanyService } from "@/services/userCompanyService";
import { useAuth } from "./useAuth";
import type { UserCompany } from "@/types";

/**
 * Custom hook for accessing the current active company for the authenticated user.
 *
 * Returns the company data with VAT status information for conditional VAT field rendering.
 *
 * @returns Current company data with VAT helper flags
 *
 * @example
 * ```tsx
 * const { currentCompany, isVatPayer, showVatFields } = useCurrentCompany();
 *
 * if (showVatFields) {
 *   // Render VAT rate selector
 * }
 * ```
 */
export function useCurrentCompany() {
  const { user } = useAuth();
  const queryClient = useQueryClient();
  const currentCompanyId = user?.current_company_id;

  const {
    data: currentCompany,
    isLoading,
    error,
  } = useQuery<UserCompany | null, Error>({
    queryKey: ['current-company', currentCompanyId],
    queryFn: async () => {
      if (!currentCompanyId) return null;
      return userCompanyService.getById(currentCompanyId);
    },
    enabled: !!currentCompanyId,
    staleTime: 30000, // 30 seconds
  });

  // Helper computed values
  const vatPayerStatus = currentCompany?.vat_payer_status ?? null;

  // Use API flags if available, otherwise compute from status
  const isNotVatPayer = vatPayerStatus === 'not_vat_payer';
  const isVatPayer = currentCompany?.is_vat_payer ??
    (vatPayerStatus === 'vat_payer' || vatPayerStatus === 'vat_payer_paragraph_7');
  const isRegisteredParagraph7a = currentCompany?.is_registered_paragraph_7a ??
    vatPayerStatus === 'registered_paragraph_7a';

  // VAT field visibility flags
  const requiresVatFields = currentCompany?.requires_vat_fields ?? isVatPayer;
  const allowsVatFields = currentCompany?.allows_vat_fields ?? !isNotVatPayer;

  /**
   * Refresh current company data
   */
  const refreshCurrentCompany = () => {
    if (currentCompanyId) {
      queryClient.invalidateQueries({ queryKey: ['current-company', currentCompanyId] });
    }
  };

  return {
    currentCompany,
    isLoading,
    error,
    // VAT status
    vatPayerStatus,
    vatPeriod: currentCompany?.vat_period ?? null,
    // VAT helper flags
    isNotVatPayer,
    isVatPayer,
    isRegisteredParagraph7a,
    requiresVatFields,
    allowsVatFields,
    // Actions
    refreshCurrentCompany,
  };
}
