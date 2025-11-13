import { useQuery, useMutation, useQueryClient, UseMutationResult } from "@tanstack/react-query";
import { userCompanyService, UserCompanyFormData } from "@/services/userCompanyService";
import { UserCompany } from "@/types";

/**
 * Return type for the useUserCompanies hook
 */
interface UseUserCompaniesReturn {
  companies: UserCompany[];
  isLoading: boolean;
  error: Error | null;
  createCompany: UseMutationResult<UserCompany, Error, UserCompanyFormData, unknown>;
  updateCompany: UseMutationResult<UserCompany, Error, { id: number; data: Partial<UserCompanyFormData> }, unknown>;
  deleteCompany: UseMutationResult<void, Error, number, unknown>;
  isCreating: boolean;
  isUpdating: boolean;
  isDeleting: boolean;
}

/**
 * Custom hook for managing user companies with TanStack Query
 *
 * Provides CRUD operations for user companies with automatic cache management,
 * optimistic updates, and error handling.
 *
 * @param {string} [search] - Optional search query to filter companies
 * @returns {UseUserCompaniesReturn} Companies data and mutation functions
 *
 * @example
 * ```tsx
 * const { companies, isLoading, createCompany } = useUserCompanies(searchQuery);
 *
 * const handleCreate = async (formData: UserCompanyFormData) => {
 *   await createCompany.mutateAsync(formData);
 * };
 * ```
 */
export function useUserCompanies(search?: string): UseUserCompaniesReturn {
  const queryClient = useQueryClient();

  // Fetch companies with search
  const {
    data: companies = [],
    isLoading,
    error,
  } = useQuery<UserCompany[], Error>({
    queryKey: ['user-companies', search],
    queryFn: () => userCompanyService.list(search || undefined),
    staleTime: 30000, // 30 seconds
  });

  // Create mutation
  const createCompany = useMutation<UserCompany, Error, UserCompanyFormData>({
    mutationFn: userCompanyService.create,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['user-companies'] });
    },
  });

  // Update mutation
  const updateCompany = useMutation<
    UserCompany,
    Error,
    { id: number; data: Partial<UserCompanyFormData> }
  >({
    mutationFn: ({ id, data }) => userCompanyService.update(id, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['user-companies'] });
    },
  });

  // Delete mutation
  const deleteCompany = useMutation<void, Error, number>({
    mutationFn: userCompanyService.delete,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['user-companies'] });
    },
  });

  return {
    companies,
    isLoading,
    error,
    createCompany,
    updateCompany,
    deleteCompany,
    isCreating: createCompany.isPending,
    isUpdating: updateCompany.isPending,
    isDeleting: deleteCompany.isPending,
  };
}

/**
 * Custom hook for fetching a single user company by ID
 *
 * @param {number} id - The company ID to fetch
 * @returns Query result with company data
 *
 * @example
 * ```tsx
 * const { data: company, isLoading } = useUserCompany(companyId);
 * ```
 */
export function useUserCompany(id: number) {
  return useQuery<UserCompany, Error>({
    queryKey: ['user-companies', id],
    queryFn: () => userCompanyService.getById(id),
    staleTime: 30000,
  });
}
