import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Printer, Download, Loader2 } from "lucide-react";
import { useInvoice } from "@/hooks/useInvoices";
import { useSettings } from "@/hooks/useSettings";
import { useToast } from "@/hooks/use-toast";
import { useState, useMemo, useEffect } from "react";
import axios from "@/lib/axios";
import { InvoicePreviewModern } from "./InvoicePreviewModern";
import { InvoicePreviewMinimal } from "./InvoicePreviewMinimal";
import { InvoicePreviewBold } from "./InvoicePreviewBold";
import { InvoicePreviewClassic } from "./InvoicePreviewClassic";

interface InvoicePreviewProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  invoiceId: number | null;
}

export const InvoicePreview = ({ open, onOpenChange, invoiceId }: InvoicePreviewProps) => {
  const { invoice, isLoading } = useInvoice(invoiceId || 0);
  const { settings } = useSettings();
  const { toast } = useToast();
  const [isDownloading, setIsDownloading] = useState(false);
  const [selectedTemplate, setSelectedTemplate] = useState<string>('classic');

  // Update template when dialog opens - use preview template if available, otherwise user's saved setting
  useEffect(() => {
    if (open) {
      const previewTemplate = localStorage.getItem('previewTemplate');
      if (previewTemplate) {
        setSelectedTemplate(previewTemplate);
        localStorage.removeItem('previewTemplate'); // Clear after use
      } else if (settings) {
        setSelectedTemplate(settings.invoice_template);
      }
    }
  }, [open, settings]);

  // Transform invoice data to match the template format
  const invoiceData = useMemo(() => {
    if (!invoice) return null;

    const supplier = invoice.party_snapshot.supplier;
    const customer = invoice.party_snapshot.customer;
    const supplierAddress = [supplier.street, supplier.postal_code, supplier.city].filter(Boolean).join(', ').replace(', ,', ',');
    const customerAddress = [customer.street, customer.postal_code, customer.city].filter(Boolean).join(', ').replace(', ,', ',');

    return {
      id: invoice.invoice_number,
      date: new Date(invoice.issue_date).toLocaleDateString('sk-SK'),
      dueDate: new Date(invoice.due_date).toLocaleDateString('sk-SK'),
      deliveryDate: invoice.delivery_date
        ? new Date(invoice.delivery_date).toLocaleDateString('sk-SK')
        : undefined,
      variableSymbol: invoice.variable_symbol,
      constantSymbol: invoice.constant_symbol,
      specificSymbol: invoice.specific_symbol,
      supplier: {
        name: supplier.name || 'N/A',
        address: supplierAddress,
        ico: supplier.ico || '',
        dic: supplier.dic || '',
        icDph: supplier.ic_dph || '',
        iban: invoice.party_snapshot.supplier.bank.iban || '',
        registryOffice: supplier.registration_office || '',
        registryNumber: supplier.registration_number || '',
      },
      client: {
        name: customer.name || 'N/A',
        address: customerAddress,
        ico: customer.ico || '',
        dic: customer.dic || '',
        icDph: customer.ic_dph || '',
      },
      items: invoice.items?.map(item => ({
        description: item.description,
        quantity: Number(item.quantity),
        price: Number(item.unit_price_without_tax || item.unit_price || 0),
        unitPriceWithoutTax: Number(item.unit_price_without_tax || 0),
        taxRate: Number(item.tax_rate || 0),
        taxAmount: Number(item.tax_amount || 0),
        totalPrice: Number(item.total_price || 0),
      })) || [],
      // VAT related fields
      isVatPayer: invoice.supplier_is_vat_payer || false,
      subtotal: Number(invoice.subtotal || 0),
      taxRate: Number(invoice.tax_rate || 0),
      taxAmount: Number(invoice.tax_amount || 0),
      totalAmount: Number(invoice.total_amount || 0),
      currency: invoice.currency || 'EUR',
      // QR code from API
      qrCode: invoice.qr_code || undefined,
      // Legal texts
      reverseChargeText: invoice.reverse_charge_text || undefined,
      taxExemptionText: invoice.tax_exemption_text || undefined,
    };
  }, [invoice]);

  const handlePrint = () => {
    window.print();
  };

  const handleDownloadPdf = async () => {
    if (!invoice) return;

    try {
      setIsDownloading(true);

      // Download PDF from backend
      const response = await axios.get(`/invoices/${invoice.id}/pdf/download`, {
        responseType: 'blob',
      });

      // Create download link
      const url = URL.createObjectURL(response.data);
      const link = document.createElement('a');
      link.href = url;
      link.download = `faktura-${invoice.invoice_number}.pdf`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);

      toast({
        title: "PDF stiahnuté",
        description: "Faktúra bola úspešne stiahnutá.",
      });
    } catch (error) {
      console.error('Error downloading PDF:', error);
      toast({
        title: "Chyba",
        description: "Nepodarilo sa stiahnuť PDF",
        variant: "destructive",
      });
    } finally {
      setIsDownloading(false);
    }
  };

  if (!invoice && !isLoading) {
    return null;
  }

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle className="flex items-center justify-between">
            <span>Náhľad faktúry</span>
            <div className="flex gap-2">
              <Button variant="outline" size="sm" onClick={handlePrint} disabled={isLoading}>
                <Printer className="w-4 h-4 mr-2" />
                Tlačiť
              </Button>
              <Button variant="outline" size="sm" onClick={handleDownloadPdf} disabled={isLoading || isDownloading}>
                {isDownloading ? (
                  <Loader2 className="w-4 h-4 mr-2 animate-spin" />
                ) : (
                  <Download className="w-4 h-4 mr-2" />
                )}
                PDF
              </Button>
            </div>
          </DialogTitle>
          <DialogDescription className="sr-only">
            Náhľad faktúry s možnosťou tlače a exportu do PDF
          </DialogDescription>
        </DialogHeader>

        {isLoading ? (
          <div className="flex items-center justify-center py-12">
            <Loader2 className="h-8 w-8 animate-spin" />
          </div>
        ) : invoice && invoiceData ? (
          selectedTemplate === 'modern' ? (
            <InvoicePreviewModern invoiceData={invoiceData} />
          ) : selectedTemplate === 'minimal' ? (
            <InvoicePreviewMinimal invoiceData={invoiceData} />
          ) : selectedTemplate === 'bold' ? (
            <InvoicePreviewBold invoiceData={invoiceData} />
          ) : (
            <InvoicePreviewClassic invoiceData={invoiceData} />
          )
        ) : null}
      </DialogContent>
    </Dialog>
  );
};
