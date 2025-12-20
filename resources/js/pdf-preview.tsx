import { createRoot } from "react-dom/client";
import { InvoicePreviewModern } from "./components/invoice/InvoicePreviewModern";
import { InvoicePreviewMinimal } from "./components/invoice/InvoicePreviewMinimal";
import { InvoicePreviewBold } from "./components/invoice/InvoicePreviewBold";
import { InvoicePreviewClassic } from "./components/invoice/InvoicePreviewClassic";
import "./index.css";

// Type for the data injected from Blade
interface InvoicePageData {
  invoice: {
    id: number;
    invoice_number: string;
    issue_date: string;
    due_date: string;
    delivery_date?: string;
    variable_symbol?: string;
    constant_symbol?: string;
    specific_symbol?: string;
    subtotal: number;
    tax_rate: number;
    tax_amount: number;
    total_amount: number;
    currency: string;
    notes?: string;
    reverse_charge_text?: string;
    tax_exemption_text?: string;
    supplier_is_vat_payer: boolean;
    supplier_registry_office?: string;
    supplier_registry_number?: string;
    supplier_company?: {
      name: string;
      address: string;
      city: string;
      postal_code: string;
      ico: string;
      dic: string;
      ic_dph?: string;
      iban?: string;
    };
    business_entity?: {
      name: string;
      address: string;
      city: string;
      postal_code: string;
      ico: string;
      dic: string;
      ic_dph?: string;
    };
    items: Array<{
      description: string;
      quantity: number;
      unit_price: number;
      unit_price_without_tax: number;
      tax_rate: number;
      tax_amount: number;
      total_price: number;
    }>;
    qr_code?: string;
  };
  template: string;
}

declare global {
  interface Window {
    __INVOICE_DATA__: InvoicePageData;
  }
}

// Transform server data to component format
function transformInvoiceData(data: InvoicePageData['invoice']) {
  return {
    id: data.invoice_number,
    date: new Date(data.issue_date).toLocaleDateString('sk-SK'),
    dueDate: new Date(data.due_date).toLocaleDateString('sk-SK'),
    deliveryDate: data.delivery_date
      ? new Date(data.delivery_date).toLocaleDateString('sk-SK')
      : undefined,
    variableSymbol: data.variable_symbol,
    constantSymbol: data.constant_symbol,
    specificSymbol: data.specific_symbol,
    supplier: data.supplier_company ? {
      name: data.supplier_company.name,
      address: `${data.supplier_company.address}, ${data.supplier_company.postal_code} ${data.supplier_company.city}`,
      ico: data.supplier_company.ico,
      dic: data.supplier_company.dic,
      icDph: data.supplier_company.ic_dph,
      iban: data.supplier_company.iban,
      registryOffice: data.supplier_registry_office,
      registryNumber: data.supplier_registry_number,
    } : undefined,
    client: {
      name: data.business_entity?.name || 'N/A',
      address: data.business_entity
        ? `${data.business_entity.address}, ${data.business_entity.postal_code} ${data.business_entity.city}`
        : '',
      ico: data.business_entity?.ico || '',
      dic: data.business_entity?.dic || '',
    },
    items: data.items.map(item => ({
      description: item.description,
      quantity: Number(item.quantity),
      price: Number(item.unit_price || item.unit_price_without_tax),
      unitPriceWithoutTax: Number(item.unit_price_without_tax),
      taxRate: Number(item.tax_rate),
      taxAmount: Number(item.tax_amount),
      totalPrice: Number(item.total_price),
    })),
    isVatPayer: data.supplier_is_vat_payer,
    subtotal: Number(data.subtotal),
    taxRate: Number(data.tax_rate),
    taxAmount: Number(data.tax_amount),
    totalAmount: Number(data.total_amount),
    currency: data.currency,
    qrCode: data.qr_code,
    reverseChargeText: data.reverse_charge_text,
    taxExemptionText: data.tax_exemption_text,
  };
}

// PDF Preview App - renders the correct template based on data
function PdfPreviewApp() {
  const pageData = window.__INVOICE_DATA__;

  if (!pageData?.invoice) {
    return <div id="invoice-content" className="p-8 text-red-500">Error: No invoice data found</div>;
  }

  const invoiceData = transformInvoiceData(pageData.invoice);
  const template = pageData.template || 'classic';

  // Wrap in div with id for Browsershot to wait for
  return (
    <div id="invoice-content">
      {template === 'modern' && <InvoicePreviewModern invoiceData={invoiceData} />}
      {template === 'minimal' && <InvoicePreviewMinimal invoiceData={invoiceData} />}
      {template === 'bold' && <InvoicePreviewBold invoiceData={invoiceData} />}
      {(template === 'classic' || !['modern', 'minimal', 'bold'].includes(template)) &&
        <InvoicePreviewClassic invoiceData={invoiceData} />}
    </div>
  );
}

createRoot(document.getElementById("root")!).render(<PdfPreviewApp />);
