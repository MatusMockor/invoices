import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Printer, Download, Loader2 } from "lucide-react";
import { useInvoice } from "@/hooks/useInvoices";
import { useToast } from "@/hooks/use-toast";
import { useState } from "react";
import html2canvas from "html2canvas";
import jsPDF from "jspdf";

interface InvoicePreviewProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  invoiceId: number | null;
}

export const InvoicePreview = ({ open, onOpenChange, invoiceId }: InvoicePreviewProps) => {
  const { invoice, isLoading } = useInvoice(invoiceId || 0);
  const { toast } = useToast();
  const [isDownloading, setIsDownloading] = useState(false);

  const handlePrint = () => {
    window.print();
  };

  const handleDownloadPdf = async () => {
    if (!invoice) return;

    try {
      setIsDownloading(true);

      const invoiceContent = document.getElementById('invoice-content');
      if (!invoiceContent) {
        throw new Error('Invoice content not found');
      }

      // Capture the invoice content as canvas
      const canvas = await html2canvas(invoiceContent, {
        scale: 2, // Higher quality
        useCORS: true,
        logging: false,
        backgroundColor: '#ffffff',
      });

      // Calculate PDF dimensions
      const imgWidth = 210; // A4 width in mm
      const imgHeight = (canvas.height * imgWidth) / canvas.width;

      // Create PDF
      const pdf = new jsPDF('p', 'mm', 'a4');
      const imgData = canvas.toDataURL('image/png');

      pdf.addImage(imgData, 'PNG', 0, 0, imgWidth, imgHeight);

      // Download the PDF
      pdf.save(`faktura-${invoice.invoice_number}.pdf`);

      toast({
        title: "PDF stiahnuté",
        description: "Faktúra bola úspešne stiahnutá.",
      });
    } catch (error) {
      console.error('Error generating PDF:', error);
      toast({
        title: "Chyba",
        description: "Nepodarilo sa vygenerovať PDF",
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
        ) : invoice ? (
        <div className="bg-white text-black p-8 rounded-lg" id="invoice-content">
          {/* Header with Company Logo */}
          <div className="mb-8 pb-6 border-b-2 border-purple-600">
            <div className="flex justify-between items-center mb-6">
              <h1 className="text-2xl font-bold text-purple-600">InvoiceHub</h1>
              <h2 className="text-xl font-bold text-purple-600">FAKTÚRA {invoice.invoice_number}</h2>
            </div>

            {/* Top row: Supplier + Client side-by-side */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
              {/* Supplier Info */}
              <div className="flex-1 bg-purple-50/50 p-6 rounded-lg border border-purple-200">
                <h3 className="font-bold text-purple-600 mb-3 text-sm uppercase tracking-wide">Dodávateľ</h3>
                <p className="font-semibold text-lg mb-2">{invoice.supplier_company?.name || 'N/A'}</p>
                <p className="text-sm text-gray-600">{invoice.supplier_company?.address || ''}</p>
                <p className="text-sm text-gray-600">{invoice.supplier_company?.postal_code} {invoice.supplier_company?.city}</p>
                <div className="mt-3 pt-3 border-t border-purple-200">
                  <p className="text-sm text-gray-600">IČO: {invoice.supplier_company?.ico || 'N/A'}</p>
                  <p className="text-sm text-gray-600">DIČ: {invoice.supplier_company?.dic || 'N/A'}</p>
                  {invoice.supplier_company?.ic_dph && (
                    <p className="text-sm text-gray-600">IČ DPH: {invoice.supplier_company.ic_dph}</p>
                  )}
                </div>
              </div>

              {/* Client Info */}
              <div className="flex-1 bg-gray-50 p-6 rounded-lg border border-gray-200">
                <h3 className="font-bold text-gray-700 mb-3 text-sm uppercase tracking-wide">Odberateľ</h3>
                <p className="font-semibold text-lg mb-2">{invoice.business_entity?.name || 'N/A'}</p>
                <p className="text-sm text-gray-600">{invoice.business_entity?.address || ''}</p>
                <p className="text-sm text-gray-600">{invoice.business_entity?.postal_code} {invoice.business_entity?.city}</p>
                <div className="mt-3 pt-3 border-t border-gray-200">
                  <p className="text-sm text-gray-600">IČO: {invoice.business_entity?.ico || 'N/A'}</p>
                  <p className="text-sm text-gray-600">DIČ: {invoice.business_entity?.dic || 'N/A'}</p>
                  {invoice.business_entity?.ic_dph && (
                    <p className="text-sm text-gray-600">IČ DPH: {invoice.business_entity.ic_dph}</p>
                  )}
                </div>
              </div>
            </div>

          </div>

          {/* Payment Info with QR Code */}
          <div className="border-2 border-purple-200 rounded-lg p-6 mb-6 bg-gradient-to-br from-purple-50/50 to-white">
            <h3 className="font-bold mb-3 text-base text-purple-600 border-b border-purple-200 pb-1.5">Platobné údaje</h3>
            <div className="flex gap-6 items-start">
              <div className="flex-1 space-y-3">
                {/* Invoice Details */}
                <div className="grid grid-cols-2 gap-x-6 gap-y-4">
                  <div className="bg-white/60 p-4 rounded-lg border border-purple-100">
                    <p className="text-xs text-gray-500 mb-1 uppercase tracking-wide">Dátum vystavenia</p>
                    <p className="font-semibold text-gray-900">{new Date(invoice.issue_date).toLocaleDateString('sk-SK')}</p>
                  </div>
                  <div className="bg-white/60 p-4 rounded-lg border border-purple-100">
                    <p className="text-xs text-gray-500 mb-1 uppercase tracking-wide">Dátum splatnosti</p>
                    <p className="font-semibold text-purple-600">{new Date(invoice.due_date).toLocaleDateString('sk-SK')}</p>
                  </div>
                </div>

                {/* Bank Details */}
                <div className="bg-white/80 p-5 rounded-lg border border-purple-200 space-y-3">
                  {invoice.supplier_company?.iban && (
                    <div className="flex justify-between items-center border-b border-gray-100 pb-2">
                      <span className="text-xs text-gray-500 uppercase tracking-wide">Číslo účtu</span>
                      <span className="font-mono font-semibold text-gray-900">{invoice.supplier_company.iban}</span>
                    </div>
                  )}
                  {invoice.variable_symbol && (
                    <div className="flex justify-between items-center border-b border-gray-100 pb-2">
                      <span className="text-xs text-gray-500 uppercase tracking-wide">Variabilný symbol</span>
                      <span className="font-mono font-semibold text-gray-900">{invoice.variable_symbol}</span>
                    </div>
                  )}
                  <div className="flex justify-between items-center pt-1">
                    <span className="text-sm font-semibold text-gray-700">Suma k úhrade</span>
                    <span className="font-bold text-2xl text-purple-600">{Number(invoice.total_amount).toFixed(2)} {invoice.currency}</span>
                  </div>
                </div>
              </div>

              {/* QR Code */}
              <div className="flex flex-col items-center gap-3 bg-white p-6 border-2 border-purple-300 rounded-xl shadow-md">
                {invoice.qr_code ? (
                  <img src={invoice.qr_code} alt="Pay by Square QR Code" className="w-[150px] h-[150px]" />
                ) : (
                  <div className="w-[150px] h-[150px] flex items-center justify-center bg-gray-100">
                    <p className="text-xs text-gray-500 text-center">QR kód nedostupný</p>
                  </div>
                )}
                <div className="text-center">
                  <p className="text-sm font-bold text-purple-600 mb-1">
                    Pay by Square
                  </p>
                  <p className="text-xs text-gray-500">
                    Naskenujte pre platbu
                  </p>
                </div>
              </div>
            </div>
          </div>

          {/* Items Table */}
          <div className="mb-8">
            <table className="w-full">
              <thead>
                <tr className="border-b-2 border-gray-300">
                  <th className="text-left py-3 px-2">Popis</th>
                  <th className="text-right py-3 px-2 w-20">Počet</th>
                  <th className="text-right py-3 px-2 w-28">Cena/ks</th>
                  <th className="text-right py-3 px-2 w-28">Celkom</th>
                </tr>
              </thead>
              <tbody>
                {invoice.items?.map((item, index) => (
                  <tr key={index} className="border-b border-gray-200">
                    <td className="py-3 px-2">{item.description}</td>
                    <td className="text-right py-3 px-2">{Number(item.quantity)}</td>
                    <td className="text-right py-3 px-2">{Number(item.unit_price).toFixed(2)} {invoice.currency}</td>
                    <td className="text-right py-3 px-2 font-semibold">
                      {Number(item.total_price).toFixed(2)} {invoice.currency}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          {/* Totals */}
          <div className="flex justify-end mb-8">
            <div className="w-80">
              <div className="flex justify-between py-2 border-b border-gray-200">
                <span className="text-gray-600">Medzisúčet bez DPH:</span>
                <span className="font-semibold">{Number(invoice.total_amount_without_vat).toFixed(2)} {invoice.currency}</span>
              </div>
              <div className="flex justify-between py-2 border-b border-gray-200">
                <span className="text-gray-600">DPH:</span>
                <span className="font-semibold">{Number(invoice.vat_amount).toFixed(2)} {invoice.currency}</span>
              </div>
              <div className="flex justify-between py-3 bg-purple-50 px-4 rounded-lg mt-2">
                <span className="font-bold text-lg">Celkom k úhrade:</span>
                <span className="font-bold text-lg text-purple-600">{Number(invoice.total_amount).toFixed(2)} {invoice.currency}</span>
              </div>
            </div>
          </div>

          {/* Footer Note */}
          <div className="border-t border-gray-200 pt-4">
            <p className="text-sm text-gray-600">
              Faktúru je potrebné uhradiť do dátumu splatnosti. {invoice.notes && <span>Poznámka: {invoice.notes}</span>}
            </p>
          </div>

          {/* Footer */}
          <div className="mt-8 text-center text-xs text-gray-500 border-t border-gray-200 pt-4">
            <p>Ďakujeme za vašu dôveru!</p>
          </div>
        </div>
        ) : null}
      </DialogContent>
    </Dialog>
  );
};
