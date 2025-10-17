import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Printer, Download } from "lucide-react";
import { QRCodeSVG } from "qrcode.react";

interface InvoicePreviewProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  invoiceData?: {
    id: string;
    date: string;
    dueDate: string;
    client: {
      name: string;
      address: string;
      ico: string;
      dic: string;
    };
    items: Array<{
      description: string;
      quantity: number;
      price: number;
    }>;
  };
}

export const InvoicePreview = ({ open, onOpenChange, invoiceData }: InvoicePreviewProps) => {
  // Default demo data
  const invoice = invoiceData || {
    id: "INV-001",
    date: "15.10.2025",
    dueDate: "30.10.2025",
    client: {
      name: "ABC s.r.o.",
      address: "Hlavná 123, 811 01 Bratislava",
      ico: "12345678",
      dic: "2023456789",
    },
    items: [
      { description: "Webový dizajn", quantity: 1, price: 800 },
      { description: "Frontend vývoj", quantity: 20, price: 50 },
      { description: "Hosting (1 rok)", quantity: 1, price: 120 },
    ],
  };

  const subtotal = invoice.items.reduce((sum, item) => sum + item.quantity * item.price, 0);
  const vat = subtotal * 0.2; // 20% DPH
  const total = subtotal + vat;

  // Generate Pay by Square QR code data
  const generatePayBySquareData = () => {
    const iban = "SK1234567890123456789012";
    const amount = total.toFixed(2);
    const vs = invoice.id.replace("INV-", "");
    const message = `Faktura ${invoice.id}`;
    
    // Simple Pay by Square format (basic version)
    // Format: IBAN|Amount|Currency|VS|Message
    return `PAY|${iban}|${amount}|EUR|${vs}|${message}`;
  };

  const handlePrint = () => {
    window.print();
  };

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle className="flex items-center justify-between">
            <span>Náhľad faktúry</span>
            <div className="flex gap-2">
              <Button variant="outline" size="sm" onClick={handlePrint}>
                <Printer className="w-4 h-4 mr-2" />
                Tlačiť
              </Button>
              <Button variant="outline" size="sm">
                <Download className="w-4 h-4 mr-2" />
                PDF
              </Button>
            </div>
          </DialogTitle>
        </DialogHeader>

        <div className="bg-white text-black p-8 rounded-lg" id="invoice-content">
          {/* Header with Company Logo */}
          <div className="mb-8 pb-6 border-b-2 border-purple-600">
            <div className="flex justify-between items-center mb-6">
              <h1 className="text-4xl font-bold text-purple-600">InvoiceHub</h1>
              <h2 className="text-3xl font-bold text-purple-600">FAKTÚRA {invoice.id}</h2>
            </div>
            
            {/* Top row: Supplier + Client side-by-side */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
              {/* Supplier Info */}
              <div className="flex-1 bg-purple-50/50 p-6 rounded-lg border border-purple-200">
                <h3 className="font-bold text-purple-600 mb-3 text-sm uppercase tracking-wide">Dodávateľ</h3>
                <p className="font-semibold text-lg mb-2">Vaša firma s.r.o.</p>
                <p className="text-sm text-gray-600">Podnikateľská 456</p>
                <p className="text-sm text-gray-600">811 02 Bratislava</p>
                <div className="mt-3 pt-3 border-t border-purple-200">
                  <p className="text-sm text-gray-600">IČO: 87654321</p>
                  <p className="text-sm text-gray-600">DIČ: 9876543210</p>
                </div>
              </div>

              {/* Client Info */}
              <div className="flex-1 bg-gray-50 p-6 rounded-lg border border-gray-200">
                <h3 className="font-bold text-gray-700 mb-3 text-sm uppercase tracking-wide">Odberateľ</h3>
                <p className="font-semibold text-lg mb-2">{invoice.client.name}</p>
                <p className="text-sm text-gray-600">{invoice.client.address}</p>
                <div className="mt-3 pt-3 border-t border-gray-200">
                  <p className="text-sm text-gray-600">IČO: {invoice.client.ico}</p>
                  <p className="text-sm text-gray-600">DIČ: {invoice.client.dic}</p>
                </div>
              </div>
            </div>

          </div>

          {/* Payment Info with QR Code */}
          <div className="border-2 border-purple-200 rounded-lg p-8 mb-8 bg-gradient-to-br from-purple-50/50 to-white">
            <h3 className="font-bold mb-6 text-xl text-purple-600 border-b border-purple-200 pb-3">Platobné údaje</h3>
            <div className="flex gap-8 items-start">
              <div className="flex-1 space-y-5">
                {/* Invoice Details */}
                <div className="grid grid-cols-2 gap-x-6 gap-y-4">
                  <div className="bg-white/60 p-4 rounded-lg border border-purple-100">
                    <p className="text-xs text-gray-500 mb-1 uppercase tracking-wide">Dátum vystavenia</p>
                    <p className="font-semibold text-gray-900">{invoice.date}</p>
                  </div>
                  <div className="bg-white/60 p-4 rounded-lg border border-purple-100">
                    <p className="text-xs text-gray-500 mb-1 uppercase tracking-wide">Dátum splatnosti</p>
                    <p className="font-semibold text-purple-600">{invoice.dueDate}</p>
                  </div>
                </div>

                {/* Bank Details */}
                <div className="bg-white/80 p-5 rounded-lg border border-purple-200 space-y-3">
                  <div className="flex justify-between items-center border-b border-gray-100 pb-2">
                    <span className="text-xs text-gray-500 uppercase tracking-wide">Číslo účtu</span>
                    <span className="font-mono font-semibold text-gray-900">SK12 3456 7890 1234 5678 9012</span>
                  </div>
                  <div className="flex justify-between items-center border-b border-gray-100 pb-2">
                    <span className="text-xs text-gray-500 uppercase tracking-wide">Variabilný symbol</span>
                    <span className="font-mono font-semibold text-gray-900">{invoice.id.replace("INV-", "")}</span>
                  </div>
                  <div className="flex justify-between items-center pt-1">
                    <span className="text-sm font-semibold text-gray-700">Suma k úhrade</span>
                    <span className="font-bold text-2xl text-purple-600">€{total.toFixed(2)}</span>
                  </div>
                </div>
              </div>
              
              {/* QR Code */}
              <div className="flex flex-col items-center gap-3 bg-white p-6 border-2 border-purple-300 rounded-xl shadow-md">
                <QRCodeSVG
                  value={generatePayBySquareData()}
                  size={150}
                  level="M"
                  includeMargin={false}
                />
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
                {invoice.items.map((item, index) => (
                  <tr key={index} className="border-b border-gray-200">
                    <td className="py-3 px-2">{item.description}</td>
                    <td className="text-right py-3 px-2">{item.quantity}</td>
                    <td className="text-right py-3 px-2">€{item.price.toFixed(2)}</td>
                    <td className="text-right py-3 px-2 font-semibold">
                      €{(item.quantity * item.price).toFixed(2)}
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
                <span className="text-gray-600">Medzisúčet:</span>
                <span className="font-semibold">€{subtotal.toFixed(2)}</span>
              </div>
              <div className="flex justify-between py-2 border-b border-gray-200">
                <span className="text-gray-600">DPH (20%):</span>
                <span className="font-semibold">€{vat.toFixed(2)}</span>
              </div>
              <div className="flex justify-between py-3 bg-purple-50 px-4 rounded-lg mt-2">
                <span className="font-bold text-lg">Celkom k úhrade:</span>
                <span className="font-bold text-lg text-purple-600">€{total.toFixed(2)}</span>
              </div>
            </div>
          </div>

          {/* Footer Note */}
          <div className="border-t border-gray-200 pt-4">
            <p className="text-sm text-gray-600">
              Faktúru je potrebné uhradiť do dátumu splatnosti. V prípade otázok nás kontaktujte na email@invoicehub.sk
            </p>
          </div>

          {/* Footer */}
          <div className="mt-8 text-center text-xs text-gray-500 border-t border-gray-200 pt-4">
            <p>Ďakujeme za vašu dôveru!</p>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  );
};
