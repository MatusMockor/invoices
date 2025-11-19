/**
 * VAT Payer Status constants and types
 *
 * Defines the available VAT payer statuses for companies in Slovakia
 */

export const VAT_PAYER_STATUS_OPTIONS = [
  { value: 'not_vat_payer', label: 'Nie je platca DPH' },
  { value: 'vat_payer', label: 'Platca DPH' },
  { value: 'vat_payer_paragraph_7', label: 'Platca DPH podľa §7' },
] as const;

export type VatPayerStatusValue = typeof VAT_PAYER_STATUS_OPTIONS[number]['value'];
