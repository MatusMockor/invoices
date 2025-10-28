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
  }>;
}

interface InvoicePreviewMinimalProps {
  invoiceData: InvoiceData;
}

export const InvoicePreviewMinimal = ({ invoiceData }: InvoicePreviewMinimalProps) => {
  const subtotal = invoiceData.items.reduce((sum, item) => sum + item.quantity * item.price, 0);
  const vat = subtotal * 0.2; // 20% DPH
  const total = subtotal + vat;

  const generatePayBySquareData = () => {
    const iban = "SK1234567890123456789012";
    const amount = total.toFixed(2);
    const vs = invoiceData.id.replace("INV-", "");
    const message = `Faktura ${invoiceData.id}`;

    return `PAY|${iban}|${amount}|EUR|${vs}|${message}`;
  };

  return (
    <div className="bg-white text-slate-900 p-8 rounded-lg" id="invoice-content">
      {/* Minimal Header */}
      <div className="mb-8">
        <div className="flex justify-between items-start pb-6 border-b border-slate-300">
          <div>
            <h1 className="text-2xl font-light tracking-wide text-slate-800 mb-1">InvoiceHub</h1>
            <p className="text-xs text-slate-500 uppercase tracking-widest">Invoice Management</p>
          </div>
          <div className="text-right">
            <p className="text-xs text-slate-500 uppercase tracking-wider mb-1">Invoice</p>
            <p className="text-2xl font-light text-slate-900">{invoiceData.id}</p>
          </div>
        </div>
      </div>

      {/* Supplier and Client - Side by Side */}
      <div className="grid grid-cols-2 gap-12 mb-8">
        {/* Supplier */}
        <div>
          <p className="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">From</p>
          <p className="font-medium text-slate-900 mb-1">Vaša firma s.r.o.</p>
          <p className="text-sm text-slate-600">Podnikateľská 456</p>
          <p className="text-sm text-slate-600 mb-3">811 02 Bratislava</p>
          <div className="space-y-0.5">
            <p className="text-xs text-slate-500">IČO: 87654321</p>
            <p className="text-xs text-slate-500">DIČ: 9876543210</p>
          </div>
        </div>

        {/* Client */}
        <div>
          <p className="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">To</p>
          <p className="font-medium text-slate-900 mb-1">{invoiceData.client.name}</p>
          {invoiceData.client.address.split(',').map((part, index) => (
            <p key={index} className="text-sm text-slate-600">{part.trim()}</p>
          ))}
          <div className="space-y-0.5 mt-3">
            <p className="text-xs text-slate-500">IČO: {invoiceData.client.ico}</p>
            <p className="text-xs text-slate-500">DIČ: {invoiceData.client.dic}</p>
          </div>
        </div>
      </div>

      {/* Invoice Details & Payment */}
      <div className="grid grid-cols-[1fr_auto_120px] gap-8 mb-8 pb-6 border-b border-slate-200">
        <div className="space-y-2">
          <div>
            <p className="text-xs text-slate-500 uppercase tracking-wider">Issue Date</p>
            <p className="text-sm font-medium text-slate-900">{invoiceData.date}</p>
          </div>
          <div>
            <p className="text-xs text-slate-500 uppercase tracking-wider">Due Date</p>
            <p className="text-sm font-medium text-slate-900">{invoiceData.dueDate}</p>
          </div>
        </div>

        <div className="space-y-2">
          <div>
            <p className="text-xs text-slate-500 uppercase tracking-wider">Account Number</p>
            <p className="text-xs font-mono font-medium text-slate-900">SK12 3456 7890 1234 5678 9012</p>
          </div>
          <div>
            <p className="text-xs text-slate-500 uppercase tracking-wider">Variable Symbol</p>
            <p className="text-xs font-mono font-medium text-slate-900">{invoiceData.variableSymbol || invoiceData.id.replace("INV-", "")}</p>
          </div>
          {invoiceData.constantSymbol && (
            <div>
              <p className="text-xs text-slate-500 uppercase tracking-wider">Constant Symbol</p>
              <p className="text-xs font-mono font-medium text-slate-900">{invoiceData.constantSymbol}</p>
            </div>
          )}
          {invoiceData.specificSymbol && (
            <div>
              <p className="text-xs text-slate-500 uppercase tracking-wider">Specific Symbol</p>
              <p className="text-xs font-mono font-medium text-slate-900">{invoiceData.specificSymbol}</p>
            </div>
          )}
        </div>

        {/* QR Code */}
        <div className="flex flex-col items-center">
          <div className="border border-slate-300 p-3 rounded">
            <QRCodeSVG
              value={generatePayBySquareData()}
              size={100}
              level="M"
              includeMargin={false}
            />
          </div>
          <p className="text-xs text-slate-500 mt-2">Pay by Square</p>
        </div>
      </div>

      {/* Items Table - Minimal Style */}
      <div className="mb-8">
        <table className="w-full">
          <thead>
            <tr className="border-b border-slate-300">
              <th className="text-left py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Description</th>
              <th className="text-right py-3 w-20 text-xs font-semibold text-slate-500 uppercase tracking-wider">Qty</th>
              <th className="text-right py-3 w-28 text-xs font-semibold text-slate-500 uppercase tracking-wider">Price</th>
              <th className="text-right py-3 w-28 text-xs font-semibold text-slate-500 uppercase tracking-wider">Total</th>
            </tr>
          </thead>
          <tbody>
            {invoiceData.items.map((item, index) => (
              <tr key={index} className="border-b border-slate-100">
                <td className="py-3 text-sm text-slate-900">{item.description}</td>
                <td className="text-right py-3 text-sm text-slate-600">{item.quantity}</td>
                <td className="text-right py-3 text-sm text-slate-600">€{item.price.toFixed(2)}</td>
                <td className="text-right py-3 text-sm font-medium text-slate-900">
                  €{(item.quantity * item.price).toFixed(2)}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {/* Totals - Minimal */}
      <div className="flex justify-end">
        <div className="w-80">
          <div className="space-y-2">
            <div className="flex justify-between py-2 text-sm">
              <span className="text-slate-600">Subtotal:</span>
              <span className="font-medium text-slate-900">€{subtotal.toFixed(2)}</span>
            </div>
            <div className="flex justify-between py-2 text-sm">
              <span className="text-slate-600">VAT (20%):</span>
              <span className="font-medium text-slate-900">€{vat.toFixed(2)}</span>
            </div>
            <div className="flex justify-between py-3 border-t-2 border-slate-900">
              <span className="font-semibold text-slate-900">Total Amount:</span>
              <span className="font-bold text-xl text-slate-900">€{total.toFixed(2)}</span>
            </div>
          </div>
        </div>
      </div>

      {/* Footer */}
      <div className="mt-12 pt-6 border-t border-slate-200">
        <p className="text-xs text-slate-500 text-center">
          Payment is due by the due date. For any questions, please contact us at email@invoicehub.sk
        </p>
      </div>

      <div className="mt-4 text-center">
        <p className="text-xs text-slate-400">Thank you for your business</p>
      </div>
    </div>
  );
};
