import axios from "@/lib/axios";
import { UserCompany, VatPayerStatus, VatPeriod } from "@/types";

/**
 * Form data structure for creating/updating user companies
 */
export interface UserCompanyFormData {
  name: string;
  ico: string;
  dic: string;
  ic_dph?: string;
  vat_payer_status?: VatPayerStatus | null;
  vat_period?: VatPeriod | null;
  email: string;
  phone?: string;
  street: string;
  city: string;
  postal_code: string;
  country: string;
  iban?: string;
  swift?: string;
  status?: 'active' | 'inactive';
}

/**
 * API response wrapper
 */
interface ApiResponse<T> {
  data: T;
  message?: string;
}

/**
 * Service layer for user's own companies (CRUD operations)
 *
 * This service handles all operations related to companies owned by the authenticated user.
 * For customer companies (used in invoices), use companyService instead.
 */
export const userCompanyService = {
  /**
   * Fetch all user companies with optional search filter
   *
   * @param {string} [search] - Optional search query to filter companies
   * @returns {Promise<UserCompany[]>} List of user companies
   */
  list: async (search?: string): Promise<UserCompany[]> => {
    const params = search ? { search } : {};
    const response = await axios.get<ApiResponse<UserCompany[]>>('/user/companies', { params });
    return response.data.data;
  },

  /**
   * Fetch a single user company by ID
   *
   * @param {number} id - The company ID
   * @returns {Promise<UserCompany>} The company data
   */
  getById: async (id: number): Promise<UserCompany> => {
    const response = await axios.get<ApiResponse<UserCompany>>(`/user/companies/${id}`);
    return response.data.data;
  },

  /**
   * Create a new user company
   *
   * @param {UserCompanyFormData} data - The company data to create
   * @returns {Promise<UserCompany>} The created company
   */
  create: async (data: UserCompanyFormData): Promise<UserCompany> => {
    const response = await axios.post<ApiResponse<UserCompany>>('/user/companies', data);
    return response.data.data;
  },

  /**
   * Update an existing user company
   *
   * @param {number} id - The company ID to update
   * @param {Partial<UserCompanyFormData>} data - The partial company data to update
   * @returns {Promise<UserCompany>} The updated company
   */
  update: async (id: number, data: Partial<UserCompanyFormData>): Promise<UserCompany> => {
    const response = await axios.put<ApiResponse<UserCompany>>(`/user/companies/${id}`, data);
    return response.data.data;
  },

  /**
   * Delete a user company
   *
   * @param {number} id - The company ID to delete
   * @returns {Promise<void>}
   */
  delete: async (id: number): Promise<void> => {
    await axios.delete(`/user/companies/${id}`);
  },

  /**
   * Switch the active company for the authenticated user
   *
   * @param {number} id - The company ID to switch to
   * @returns {Promise<UserCompany>} The switched company
   */
  switchActive: async (id: number): Promise<UserCompany> => {
    const response = await axios.post<ApiResponse<UserCompany>>(`/user/companies/${id}/switch`);
    return response.data.data;
  },

  /**
   * Fetch minimal company data (id and name only) for dropdowns
   *
   * @returns {Promise<Array<{ id: number; name: string }>>} Minimal company list
   */
  getMinimal: async (): Promise<Array<{ id: number; name: string }>> => {
    const response = await axios.get<ApiResponse<Array<{ id: number; name: string }>>>('/user/companies/minimal');
    return response.data.data;
  },
};
