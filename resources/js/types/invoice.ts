/**
 * Shared invoice data types for invoice preview components
 */

export interface InvoiceSupplier {
  name: string;
  address: string;
  ico: string;
  dic: string;
  icDph?: string;
  iban?: string;
  registryOffice?: string;
  registryNumber?: string;
}

export interface InvoiceClient {
  name: string;
  address: string;
  ico: string;
  dic: string;
}

export interface InvoiceItem {
  description: string;
  quantity: number;
  price: number;
  unitPriceWithoutTax?: number;
  taxRate?: number;
  taxAmount?: number;
  totalPrice?: number;
}

export interface InvoiceData {
  id: string;
  date: string;
  dueDate: string;
  deliveryDate?: string;
  variableSymbol?: string;
  constantSymbol?: string;
  specificSymbol?: string;
  supplier?: InvoiceSupplier;
  client: InvoiceClient;
  items: InvoiceItem[];
  isVatPayer?: boolean;
  subtotal?: number;
  taxRate?: number;
  taxAmount?: number;
  totalAmount?: number;
  currency?: string;
  qrCode?: string;
  reverseChargeText?: string;
  taxExemptionText?: string;
  notes?: string;
}
