import axios from "@/lib/axios";
import type { VatPayerStatus, VatPeriod } from "@/types";

/**
 * VAT Status History record from API
 */
export interface VatStatusHistoryRecord {
  id: number;
  vat_status: VatPayerStatus;
  vat_status_label: string;
  vat_period: VatPeriod | null;
  vat_period_label: string | null;
  valid_from: string;
  valid_to: string | null;
  is_current: boolean;
  notes: string | null;
  created_at: string;
  updated_at: string;
}

/**
 * Payload for updating VAT status
 */
export interface VatStatusUpdatePayload {
  vat_status: VatPayerStatus;
  vat_period?: VatPeriod | null;
  valid_from: string;
  notes?: string;
}

/**
 * API response wrapper
 */
interface ApiResponse<T> {
  data: T;
  message?: string;
}

/**
 * Service for VAT status API calls
 */
export const vatStatusService = {
  /**
   * Get VAT status history for a company
   */
  async getHistory(companyId: number): Promise<VatStatusHistoryRecord[]> {
    const response = await axios.get<ApiResponse<VatStatusHistoryRecord[]>>(
      `/api/user/companies/${companyId}/vat-status-history`
    );
    return response.data.data;
  },

  /**
   * Get current VAT status for a company
   */
  async getCurrentStatus(companyId: number): Promise<{
    vat_status: VatPayerStatus;
    vat_status_label: string;
    vat_period: VatPeriod | null;
    vat_period_label: string | null;
    valid_from: string;
    valid_to: string | null;
    is_current: boolean;
    requires_vat_fields: boolean;
    allows_vat_fields: boolean;
  }> {
    const response = await axios.get(`/api/user/companies/${companyId}/vat-status`);
    return response.data.data;
  },

  /**
   * Update VAT status for a company
   */
  async updateStatus(companyId: number, payload: VatStatusUpdatePayload): Promise<{
    message: string;
    company: any;
  }> {
    const response = await axios.put(
      `/api/user/companies/${companyId}/vat-status`,
      payload
    );
    return response.data;
  },
};
