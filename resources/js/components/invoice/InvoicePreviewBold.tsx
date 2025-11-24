import { QRCodeSVG } from "qrcode.react";

interface InvoiceData {
  id: string;
  date: string;
  dueDate: string;
  variableSymbol?: string;
  constantSymbol?: string;
  specificSymbol?: string;
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
    unitPriceWithoutTax?: number;
    taxRate?: number;
    taxAmount?: number;
    totalPrice?: number;
  }>;
  // VAT related fields
  isVatPayer?: boolean;
  subtotal?: number;
  taxRate?: number;
  taxAmount?: number;
  totalAmount?: number;
  currency?: string;
}

interface InvoicePreviewBoldProps {
  invoiceData: InvoiceData;
}

export const InvoicePreviewBold = ({ invoiceData }: InvoicePreviewBoldProps) => {
  // Use actual VAT data if available, otherwise calculate
  const isVatPayer = invoiceData.isVatPayer ?? true;
  const subtotal = invoiceData.subtotal ?? invoiceData.items.reduce((sum, item) => sum + item.quantity * item.price, 0);
  const taxRate = invoiceData.taxRate ?? 20;
  const vat = invoiceData.taxAmount ?? subtotal * (taxRate / 100);
  const total = invoiceData.totalAmount ?? subtotal + vat;
  const currency = invoiceData.currency ?? 'EUR';

  const generatePayBySquareData = () => {
    const iban = "SK1234567890123456789012";
    const amount = total.toFixed(2);
    const vs = invoiceData.id.replace("INV-", "");
    const message = `Faktura ${invoiceData.id}`;

    return `PAY|${iban}|${amount}|EUR|${vs}|${message}`;
  };

  return (
    <div className="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white p-8 rounded-xl shadow-2xl" id="invoice-content">
      {/* Bold Header with Accent */}
      <div className="mb-8 pb-6 border-b-2 border-cyan-500">
        <div className="flex justify-between items-start">
          <div>
            <h1 className="text-3xl font-bold text-white mb-2">InvoiceHub</h1>
            <div className="h-1 w-24 bg-gradient-to-r from-cyan-500 to-blue-500 rounded-full"></div>
          </div>
          <div className="text-right">
            <p className="text-xs text-cyan-400 uppercase tracking-widest mb-2 font-semibold">Invoice Number</p>
            <p className="text-2xl font-bold bg-gradient-to-r from-cyan-400 to-blue-400 bg-clip-text text-transparent">
              {invoiceData.id}
            </p>
          </div>
        </div>
      </div>

      {/* Client and Supplier Cards */}
      <div className="grid grid-cols-2 gap-6 mb-8">
        {/* Supplier Card */}
        <div className="bg-white/5 backdrop-blur-sm rounded-xl p-5 border border-white/10">
          <div className="flex items-center gap-2 mb-4">
            <div className="h-8 w-1 bg-cyan-500 rounded-full"></div>
            <p className="text-xs font-bold text-cyan-400 uppercase tracking-wider">From</p>
          </div>
          <p className="font-bold text-white mb-2 text-lg">Vaša firma s.r.o.</p>
          <p className="text-sm text-slate-300 mb-1">Podnikateľská 456</p>
          <p className="text-sm text-slate-300 mb-3">811 02 Bratislava</p>
          <div className="space-y-1 pt-3 border-t border-white/10">
            <p className="text-xs text-slate-400">IČO: <span className="text-white font-semibold">87654321</span></p>
            <p className="text-xs text-slate-400">DIČ: <span className="text-white font-semibold">9876543210</span></p>
          </div>
        </div>

        {/* Client Card */}
        <div className="bg-gradient-to-br from-cyan-500/10 to-blue-500/10 backdrop-blur-sm rounded-xl p-5 border border-cyan-500/30">
          <div className="flex items-center gap-2 mb-4">
            <div className="h-8 w-1 bg-cyan-500 rounded-full"></div>
            <p className="text-xs font-bold text-cyan-400 uppercase tracking-wider">Bill To</p>
          </div>
          <p className="font-bold text-white mb-2 text-lg">{invoiceData.client.name}</p>
          {invoiceData.client.address.split(',').map((part, index) => (
            <p key={index} className="text-sm text-slate-300 mb-1">{part.trim()}</p>
          ))}
          <div className="space-y-1 pt-3 border-t border-cyan-500/30">
            <p className="text-xs text-slate-400">IČO: <span className="text-white font-semibold">{invoiceData.client.ico}</span></p>
            <p className="text-xs text-slate-400">DIČ: <span className="text-white font-semibold">{invoiceData.client.dic}</span></p>
          </div>
        </div>
      </div>

      {/* Payment Details Section */}
      <div className="grid grid-cols-[1fr_auto] gap-8 mb-8">
        <div className="bg-white/5 backdrop-blur-sm rounded-xl p-5 border border-white/10">
          <div className="grid grid-cols-2 gap-6">
            <div>
              <p className="text-xs text-cyan-400 uppercase tracking-wider mb-2 font-semibold">Issue Date</p>
              <p className="text-base font-bold text-white">{invoiceData.date}</p>
            </div>
            <div>
              <p className="text-xs text-cyan-400 uppercase tracking-wider mb-2 font-semibold">Due Date</p>
              <p className="text-base font-bold text-white">{invoiceData.dueDate}</p>
            </div>
            <div>
              <p className="text-xs text-cyan-400 uppercase tracking-wider mb-2 font-semibold">Account Number</p>
              <p className="text-xs font-mono font-semibold text-white">SK12 3456 7890 1234 5678 9012</p>
            </div>
            <div>
              <p className="text-xs text-cyan-400 uppercase tracking-wider mb-2 font-semibold">Variable Symbol</p>
              <p className="text-xs font-mono font-semibold text-white">{invoiceData.variableSymbol || invoiceData.id.replace("INV-", "")}</p>
            </div>
            {invoiceData.constantSymbol && (
              <div>
                <p className="text-xs text-cyan-400 uppercase tracking-wider mb-2 font-semibold">Constant Symbol</p>
                <p className="text-xs font-mono font-semibold text-white">{invoiceData.constantSymbol}</p>
              </div>
            )}
            {invoiceData.specificSymbol && (
              <div>
                <p className="text-xs text-cyan-400 uppercase tracking-wider mb-2 font-semibold">Specific Symbol</p>
                <p className="text-xs font-mono font-semibold text-white">{invoiceData.specificSymbol}</p>
              </div>
            )}
          </div>
        </div>

        {/* QR Code with Glow */}
        <div className="flex flex-col items-center justify-center">
          <div className="bg-white p-4 rounded-xl shadow-[0_0_30px_rgba(6,182,212,0.3)]">
            <QRCodeSVG
              value={generatePayBySquareData()}
              size={100}
              level="M"
              includeMargin={false}
            />
          </div>
          <p className="text-xs text-cyan-400 mt-3 font-semibold">Pay by Square</p>
        </div>
      </div>

      {/* Items Table */}
      <div className="mb-8 bg-white/5 backdrop-blur-sm rounded-xl overflow-hidden border border-white/10">
        <table className="w-full">
          <thead>
            <tr className="bg-gradient-to-r from-cyan-500/20 to-blue-500/20">
              <th className="text-left py-4 px-5 text-xs font-bold text-cyan-400 uppercase tracking-wider">Popis</th>
              <th className="text-right py-4 px-5 w-16 text-xs font-bold text-cyan-400 uppercase tracking-wider">Počet</th>
              {isVatPayer ? (
                <>
                  <th className="text-right py-4 px-5 w-24 text-xs font-bold text-cyan-400 uppercase tracking-wider">Cena/ks<br/><span className="text-[10px] font-normal opacity-80">(bez DPH)</span></th>
                  <th className="text-right py-4 px-5 w-16 text-xs font-bold text-cyan-400 uppercase tracking-wider">DPH</th>
                  <th className="text-right py-4 px-5 w-24 text-xs font-bold text-cyan-400 uppercase tracking-wider">Výška<br/><span className="text-[10px] font-normal opacity-80">DPH</span></th>
                  <th className="text-right py-4 px-5 w-28 text-xs font-bold text-cyan-400 uppercase tracking-wider">Celkom</th>
                </>
              ) : (
                <>
                  <th className="text-right py-4 px-5 w-32 text-xs font-bold text-cyan-400 uppercase tracking-wider">Cena/ks</th>
                  <th className="text-right py-4 px-5 w-32 text-xs font-bold text-cyan-400 uppercase tracking-wider">Celkom</th>
                </>
              )}
            </tr>
          </thead>
          <tbody>
            {invoiceData.items.map((item, index) => (
              <tr key={index} className="border-t border-white/10 hover:bg-white/5 transition-colors">
                <td className="py-4 px-5 text-sm text-white font-medium">{item.description}</td>
                <td className="text-right py-4 px-5 text-sm text-slate-300">{item.quantity}</td>
                {isVatPayer ? (
                  <>
                    <td className="text-right py-4 px-5 text-sm text-slate-300">{(item.unitPriceWithoutTax ?? item.price).toFixed(2)} €</td>
                    <td className="text-right py-4 px-5 text-sm text-slate-300">{(item.taxRate ?? taxRate).toFixed(0)}%</td>
                    <td className="text-right py-4 px-5 text-sm text-slate-300">{(item.taxAmount ?? 0).toFixed(2)} €</td>
                    <td className="text-right py-4 px-5 text-sm font-bold text-white">{(item.totalPrice ?? item.quantity * item.price).toFixed(2)} €</td>
                  </>
                ) : (
                  <>
                    <td className="text-right py-4 px-5 text-sm text-slate-300">{item.price.toFixed(2)} €</td>
                    <td className="text-right py-4 px-5 text-sm font-bold text-white">{(item.totalPrice ?? item.quantity * item.price).toFixed(2)} €</td>
                  </>
                )}
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {/* Totals Section */}
      <div className="flex justify-end mb-8">
        <div className="w-96 bg-gradient-to-br from-cyan-500/10 to-blue-500/10 backdrop-blur-sm rounded-xl p-6 border border-cyan-500/30">
          <div className="space-y-3">
            {isVatPayer && (
              <>
                <div className="flex justify-between py-2 text-sm border-b border-white/10">
                  <span className="text-slate-300">Základ dane (bez DPH):</span>
                  <span className="font-bold text-white">{subtotal.toFixed(2)} €</span>
                </div>
                <div className="flex justify-between py-2 text-sm border-b border-white/10">
                  <span className="text-slate-300">DPH ({taxRate.toFixed(0)}%):</span>
                  <span className="font-bold text-white">{vat.toFixed(2)} €</span>
                </div>
              </>
            )}
            <div className="flex justify-between py-3 pt-4">
              <span className="font-bold text-cyan-400 text-base uppercase tracking-wider">Celkom k úhrade:</span>
              <span className="font-bold text-2xl bg-gradient-to-r from-cyan-400 to-blue-400 bg-clip-text text-transparent">
                {total.toFixed(2)} €
              </span>
            </div>
          </div>
        </div>
      </div>

      {/* Footer */}
      <div className="mt-8 pt-6 border-t border-white/10 text-center">
        <p className="text-xs text-slate-400 mb-3">
          Payment is due by the due date. For any questions, please contact us at email@invoicehub.sk
        </p>
        <p className="text-xs text-cyan-400 font-semibold">Thank you for your business</p>
      </div>
    </div>
  );
};
