import { useMemo } from 'react';
import type { VatPayerStatus, VatRate } from '@/types';

/**
 * EU country codes for reverse charge detection
 */
const EU_COUNTRIES = [
  'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE',
  'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT',
  'RO', 'SI', 'ES', 'SE'
] as const;

export interface UseVatVisibilityOptions {
  /** Supplier company's VAT payer status */
  vatPayerStatus: VatPayerStatus | null;
  /** Customer's country code (e.g., 'SK', 'DE', 'CZ') */
  customerCountry: string | null;
}

export interface UseVatVisibilityReturn {
  /** Whether VAT fields should be visible at all */
  showVatFields: boolean;
  /** Whether VAT rate is editable (false for §7a EU customers - locked to 0%) */
  isVatRateEditable: boolean;
  /** Default VAT rate based on status and customer */
  defaultVatRate: VatRate | null;
  /** Whether DUZP (tax point date) field should be shown */
  showTaxPointDate: boolean;
  /** Whether IC DPH fields should be shown */
  showIcDphFields: boolean;
  /** Whether VAT summary section should be shown */
  showVatSummary: boolean;
  /** Whether reverse charge should be auto-enabled */
  shouldAutoEnableReverseCharge: boolean;
  /** Informational message for §7a users when customer not selected */
  section7aMessage: string | null;
  // Status flags
  isNotVatPayer: boolean;
  isVatPayer: boolean;
  isVatPayerParagraph7: boolean;
  isRegisteredParagraph7a: boolean;
  isCustomerSlovak: boolean;
  isCustomerEU: boolean;
}

/**
 * Custom hook for determining VAT field visibility based on Slovak VAT law.
 *
 * Implements the following rules:
 * - NOT_VAT_PAYER: No VAT fields visible (legally cannot charge VAT)
 * - VAT_PAYER / VAT_PAYER_PARAGRAPH_7: All VAT fields visible
 * - REGISTERED_PARAGRAPH_7A:
 *   - Slovak customers: No VAT fields (cannot charge VAT domestically)
 *   - EU customers: VAT fields visible but locked to 0% (reverse charge)
 *
 * @param options - VAT status and customer country
 * @returns VAT visibility flags and computed values
 *
 * @example
 * ```tsx
 * const { showVatFields, defaultVatRate, isVatRateEditable } = useVatVisibility({
 *   vatPayerStatus: currentCompany?.vat_payer_status,
 *   customerCountry: 'DE',
 * });
 *
 * {showVatFields && (
 *   <VatRateSelector
 *     value={vatRate}
 *     onChange={setVatRate}
 *     disabled={!isVatRateEditable}
 *     defaultValue={defaultVatRate}
 *   />
 * )}
 * ```
 */
export function useVatVisibility({
  vatPayerStatus,
  customerCountry,
}: UseVatVisibilityOptions): UseVatVisibilityReturn {
  return useMemo(() => {
    // Status flags
    const isNotVatPayer = vatPayerStatus === 'not_vat_payer';
    const isVatPayer = vatPayerStatus === 'vat_payer';
    const isVatPayerParagraph7 = vatPayerStatus === 'vat_payer_paragraph_7';
    const isRegisteredParagraph7a = vatPayerStatus === 'registered_paragraph_7a';
    const isFullVatPayer = isVatPayer || isVatPayerParagraph7;

    // Customer location flags
    const isCustomerSlovak = customerCountry === 'SK';
    const isCustomerEU = customerCountry !== null && EU_COUNTRIES.includes(customerCountry as any);

    /**
     * Main visibility flag for VAT fields
     * REQ-12: Conditional VAT Field Visibility
     */
    let showVatFields = false;

    // Always hide for non-VAT payers
    if (isNotVatPayer) {
      showVatFields = false;
    }
    // Full VAT payers: always show
    else if (isFullVatPayer) {
      showVatFields = true;
    }
    // §7a: show only for non-SK customers (EU reverse charge)
    else if (isRegisteredParagraph7a) {
      showVatFields = !isCustomerSlovak && isCustomerEU;
    }

    /**
     * Whether VAT rate is editable
     * For §7a + EU customer: VAT rate is locked to 0% (reverse charge mandatory)
     */
    const isVatRateEditable = !(isRegisteredParagraph7a && isCustomerEU);

    /**
     * Default VAT rate based on status and customer
     */
    let defaultVatRate: VatRate | null = null;
    if (isNotVatPayer) {
      defaultVatRate = null;
    } else if (isRegisteredParagraph7a && isCustomerEU) {
      defaultVatRate = 0; // Locked to 0% for reverse charge
    } else if (isFullVatPayer) {
      defaultVatRate = 23; // Default Slovak VAT rate
    }

    /**
     * Show DUZP (tax point date) field - required for VAT payers
     */
    const showTaxPointDate = showVatFields;

    /**
     * Show IC DPH fields (supplier and customer)
     */
    const showIcDphFields = showVatFields;

    /**
     * Show VAT summary section
     */
    const showVatSummary = showVatFields;

    /**
     * Should auto-enable reverse charge
     * - VAT payer + EU customer
     * - §7a + EU customer (mandatory)
     */
    const shouldAutoEnableReverseCharge =
      (isFullVatPayer && isCustomerEU) ||
      (isRegisteredParagraph7a && isCustomerEU);

    /**
     * Message to display for §7a when customer not selected
     */
    let section7aMessage: string | null = null;
    if (isRegisteredParagraph7a) {
      if (customerCountry === null) {
        section7aMessage = 'Vyberte odberateľa pre zobrazenie DPH polí';
      } else if (isCustomerSlovak) {
        section7aMessage = 'Pre slovenských odberateľov sa DPH neuplatňuje (§7a registrácia)';
      }
    }

    return {
      showVatFields,
      isVatRateEditable,
      defaultVatRate,
      showTaxPointDate,
      showIcDphFields,
      showVatSummary,
      shouldAutoEnableReverseCharge,
      section7aMessage,
      // Status flags
      isNotVatPayer,
      isVatPayer,
      isVatPayerParagraph7,
      isRegisteredParagraph7a,
      isCustomerSlovak,
      isCustomerEU,
    };
  }, [vatPayerStatus, customerCountry]);
}
