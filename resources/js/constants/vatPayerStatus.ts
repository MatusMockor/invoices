/**
 * VAT Payer Status constants and types
 *
 * Defines the available VAT payer statuses for companies in Slovakia
 */

export const VAT_PAYER_STATUS_OPTIONS = [
  {
    value: 'not_vat_payer',
    label: 'Nie je platca DPH',
    description: 'Vystavujem faktúry bez DPH',
    requiresPeriod: false,
  },
  {
    value: 'vat_payer',
    label: 'Platca DPH',
    description: 'Vystavujem faktúry s DPH, podávam daňové priznania',
    requiresPeriod: true,
  },
  {
    value: 'vat_payer_paragraph_7',
    label: 'Platca DPH podľa §7',
    description: 'Dobrovoľná registrácia podľa §7 zákona o DPH',
    requiresPeriod: true,
  },
  {
    value: 'registered_paragraph_7a',
    label: 'Registrovaná osoba podľa §7a',
    description: 'Poskytujem služby do EÚ, faktúrujem s prenesenou daňovou povinnosťou',
    requiresPeriod: false,
  },
] as const;

export type VatPayerStatusValue = typeof VAT_PAYER_STATUS_OPTIONS[number]['value'];

/**
 * VAT Period constants
 *
 * Defines the available VAT filing periods
 */
export const VAT_PERIOD_OPTIONS = [
  { value: 'monthly', label: 'Mesačný platca' },
  { value: 'quarterly', label: 'Štvrťročný platca' },
] as const;

export type VatPeriodValue = typeof VAT_PERIOD_OPTIONS[number]['value'];

/**
 * Check if a VAT status requires a VAT period to be set
 */
export function statusRequiresPeriod(status: VatPayerStatusValue | string | null): boolean {
  if (!status) return false;
  const option = VAT_PAYER_STATUS_OPTIONS.find(opt => opt.value === status);
  return option?.requiresPeriod ?? false;
}
