import type { VatPayerStatus } from './index';

/**
 * Company search result type returned by company search API endpoints.
 * Used for autocomplete in forms (invoices, onboarding, etc.)
 */
export interface Company {
  ico: string;
  name: string;
  address?: string;
  city?: string;
  postal_code?: string;
  dic?: string;
  ic_dph?: string;
  registration_office?: string;
  registration_number?: string;
  vat_payer_status?: VatPayerStatus;
}
