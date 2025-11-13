import api from '@/lib/axios';
import type { Company, ApiResponse } from '@/types';

/**
 * Service layer for customer companies (used in invoices and business entities)
 *
 * This service handles operations for customer/client companies that appear in invoices.
 * For user's own companies, use userCompanyService instead.
 */
export const companyService = {
  /**
   * Search customer companies by query string
   * Used for autocomplete in invoice forms
   *
   * @param {string} query - Search query
   * @returns {Promise<ApiResponse<Company[]>>} Matching companies
   */
  async searchCustomerCompanies(query: string): Promise<ApiResponse<Company[]>> {
    const response = await api.get('/customer-companies/search', {
      params: { query },
    });
    return response.data;
  },

  /**
   * Fetch all customer companies
   *
   * @returns {Promise<ApiResponse<Company[]>>} All customer companies
   */
  async getAllCustomerCompanies(): Promise<ApiResponse<Company[]>> {
    const response = await api.get('/customer-companies');
    return response.data;
  },

  /**
   * Switch the active user company
   *
   * Note: This method will be deprecated in favor of userCompanyService.switchActive()
   *
   * @deprecated Use userCompanyService.switchActive() instead
   * @param {number} id - The company ID to switch to
   * @returns {Promise<ApiResponse<Company>>} The switched company
   */
  async switchCompany(id: number): Promise<ApiResponse<Company>> {
    const response = await api.post(`/user/companies/${id}/switch`);
    return response.data;
  },
};

