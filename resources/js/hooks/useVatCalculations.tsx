import { useMemo } from 'react';
import type { VatRate } from '@/types';

export interface VatItem {
  quantity: number;
  price: number;
  tax_rate?: VatRate | number | null;
  discount_amount?: number | null;
}

export interface VatSummaryItem {
  rate: number;
  base: number;
  vatAmount: number;
}

export interface VatSummary {
  items: VatSummaryItem[];
  totalBase: number;
  totalVat: number;
  grandTotal: number;
}

export interface UseVatCalculationsOptions {
  /** Invoice items with quantity, price, and tax_rate */
  items: VatItem[];
  /** Whether reverse charge is enabled (no VAT applied) */
  reverseCharge: boolean;
}

export interface UseVatCalculationsReturn {
  /** Calculate subtotal for a single item (quantity * price - discount) */
  calculateItemSubtotal: (item: VatItem) => number;
  /** Calculate VAT amount for a single item */
  calculateItemVat: (item: VatItem) => number;
  /** Calculate total for a single item (subtotal + VAT) */
  calculateItemTotal: (item: VatItem) => number;
  /** VAT summary grouped by rate */
  vatSummary: VatSummary;
}

/**
 * Custom hook for VAT calculations on invoice items.
 *
 * Provides functions to calculate subtotals, VAT amounts, and totals for
 * individual items, as well as a summary grouped by VAT rate.
 *
 * @param options - Items and reverse charge flag
 * @returns Calculation functions and VAT summary
 *
 * @example
 * ```tsx
 * const { vatSummary, calculateItemTotal } = useVatCalculations({
 *   items: invoiceItems,
 *   reverseCharge: false,
 * });
 *
 * // Display VAT summary
 * {vatSummary.items.map(item => (
 *   <div key={item.rate}>
 *     {item.rate}%: {item.base} EUR + {item.vatAmount} EUR DPH
 *   </div>
 * ))}
 * ```
 */
export function useVatCalculations({
  items,
  reverseCharge,
}: UseVatCalculationsOptions): UseVatCalculationsReturn {
  /**
   * Calculate item subtotal (quantity * price - discount)
   */
  const calculateItemSubtotal = (item: VatItem): number => {
    const baseAmount = item.quantity * item.price;
    const discount = item.discount_amount ?? 0;
    return Math.round((baseAmount - discount) * 100) / 100;
  };

  /**
   * Calculate VAT amount for an item
   * Returns 0 if reverse charge is enabled or no tax rate
   */
  const calculateItemVat = (item: VatItem): number => {
    if (reverseCharge || item.tax_rate === null || item.tax_rate === undefined) {
      return 0;
    }
    const subtotal = calculateItemSubtotal(item);
    return Math.round(subtotal * (item.tax_rate / 100) * 100) / 100;
  };

  /**
   * Calculate item total (subtotal + VAT)
   */
  const calculateItemTotal = (item: VatItem): number => {
    const subtotal = calculateItemSubtotal(item);
    const vat = calculateItemVat(item);
    return Math.round((subtotal + vat) * 100) / 100;
  };

  /**
   * Calculate VAT summary grouped by rate
   */
  const vatSummary = useMemo<VatSummary>(() => {
    const grouped = new Map<number, { base: number; vatAmount: number }>();

    items.forEach(item => {
      const rate = item.tax_rate ?? 0;
      const subtotal = calculateItemSubtotal(item);
      const vatAmount = reverseCharge ? 0 : calculateItemVat(item);

      if (grouped.has(rate)) {
        const existing = grouped.get(rate)!;
        existing.base += subtotal;
        existing.vatAmount += vatAmount;
      } else {
        grouped.set(rate, { base: subtotal, vatAmount });
      }
    });

    const summaryItems: VatSummaryItem[] = Array.from(grouped.entries())
      .map(([rate, { base, vatAmount }]) => ({
        rate,
        base: Math.round(base * 100) / 100,
        vatAmount: Math.round(vatAmount * 100) / 100,
      }))
      .sort((a, b) => b.rate - a.rate); // Sort by rate descending

    const totalBase = summaryItems.reduce((sum, item) => sum + item.base, 0);
    const totalVat = summaryItems.reduce((sum, item) => sum + item.vatAmount, 0);
    const grandTotal = totalBase + totalVat;

    return {
      items: summaryItems,
      totalBase: Math.round(totalBase * 100) / 100,
      totalVat: Math.round(totalVat * 100) / 100,
      grandTotal: Math.round(grandTotal * 100) / 100,
    };
  }, [items, reverseCharge]);

  return {
    calculateItemSubtotal,
    calculateItemVat,
    calculateItemTotal,
    vatSummary,
  };
}
