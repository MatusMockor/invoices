import { QRCodeSVG } from "qrcode.react";

interface InvoiceData {
  id: string;
  date: string;
  dueDate: string;
  variableSymbol?: string;
  constantSymbol?: string;
  specificSymbol?: string;
  supplier?: {
    name: string;
    address: string;
    ico: string;
    dic: string;
    icDph?: string;
    registryOffice?: string;
    registryNumber?: string;
  };
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

interface InvoicePreviewModernProps {
  invoiceData: InvoiceData;
}

export const InvoicePreviewModern = ({ invoiceData }: InvoicePreviewModernProps) => {
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
    <div className="bg-gradient-to-br from-slate-50 to-blue-50 text-slate-900 p-8 rounded-lg" id="invoice-content">
      {/* Modern Header with Gradient - Compact */}
      <div className="mb-6 relative overflow-hidden">
        <div className="absolute inset-0 bg-gradient-to-r from-blue-600 to-purple-600 opacity-10 rounded-xl"></div>
        <div className="relative bg-white/80 backdrop-blur-sm p-5 rounded-xl border border-blue-200 shadow-lg">
          <div className="flex justify-between items-start mb-4">
            <div>
              <h1 className="text-xl font-black bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-1">
                InvoiceHub
              </h1>
              <p className="text-xs text-slate-600">Profesionálne riešenie pre faktúry</p>
            </div>
            <div className="text-right">
              <div className="inline-block bg-gradient-to-r from-blue-600 to-purple-600 text-white px-3 py-1.5 rounded-lg shadow-lg">
                <p className="text-xs uppercase tracking-wider font-semibold opacity-90">Faktúra</p>
                <p className="text-base font-bold">{invoiceData.id}</p>
              </div>
            </div>
          </div>

          {/* Client and Supplier Info in Modern Cards - Compact */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3">
            {/* Supplier */}
            <div className="relative">
              <div className="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg opacity-20"></div>
              <div className="relative bg-white p-3 rounded-lg">
                <div className="flex items-center gap-1.5 mb-2">
                  <div className="w-5 h-5 bg-gradient-to-r from-blue-500 to-purple-500 rounded flex items-center justify-center">
                    <span className="text-white text-[10px] font-bold">OD</span>
                  </div>
                  <h3 className="font-bold text-slate-700 uppercase text-[10px] tracking-wider">Dodávateľ</h3>
                </div>
                <p className="font-bold text-sm mb-1 text-slate-900">{invoiceData.supplier?.name || 'N/A'}</p>
                {invoiceData.supplier?.address.split(',').map((part, index) => (
                  <p key={index} className="text-xs text-slate-600">{part.trim()}</p>
                ))}
                <div className="border-t border-slate-200 pt-1.5 mt-1.5 space-y-0.5">
                  <p className="text-[10px] text-slate-500"><span className="font-semibold">IČO:</span> {invoiceData.supplier?.ico}</p>
                  <p className="text-[10px] text-slate-500"><span className="font-semibold">DIČ:</span> {invoiceData.supplier?.dic}</p>
                  {invoiceData.supplier?.icDph && (
                    <p className="text-[10px] text-slate-500"><span className="font-semibold">IČ DPH:</span> {invoiceData.supplier?.icDph}</p>
                  )}
                  {invoiceData.supplier?.registryOffice && (
                    <p className="text-[10px] text-slate-500">{invoiceData.supplier.registryOffice}</p>
                  )}
                  {invoiceData.supplier?.registryNumber && (
                    <p className="text-[10px] text-slate-500">{invoiceData.supplier.registryNumber}</p>
                  )}
                </div>
              </div>
            </div>

            {/* Client */}
            <div className="relative">
              <div className="absolute -inset-0.5 bg-gradient-to-r from-slate-300 to-slate-400 rounded-lg opacity-20"></div>
              <div className="relative bg-white p-3 rounded-lg">
                <div className="flex items-center gap-1.5 mb-2">
                  <div className="w-5 h-5 bg-slate-600 rounded flex items-center justify-center">
                    <span className="text-white text-[10px] font-bold">PRE</span>
                  </div>
                  <h3 className="font-bold text-slate-700 uppercase text-[10px] tracking-wider">Odberateľ</h3>
                </div>
                <p className="font-bold text-sm mb-1 text-slate-900">{invoiceData.client.name}</p>
                {invoiceData.client.address.split(',').map((part, index) => (
                  <p key={index} className="text-xs text-slate-600">{part.trim()}</p>
                ))}
                <div className="border-t border-slate-200 pt-1.5 mt-1.5 space-y-0.5">
                  <p className="text-[10px] text-slate-500"><span className="font-semibold">IČO:</span> {invoiceData.client.ico}</p>
                  <p className="text-[10px] text-slate-500"><span className="font-semibold">DIČ:</span> {invoiceData.client.dic}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Payment Section with Modern Design - Compact */}
      <div className="bg-white rounded-xl p-4 mb-6 shadow-lg border border-slate-200">
        <div className="flex items-start justify-between gap-4">
          <div className="flex-1">
            <h3 className="text-base font-bold mb-3 bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
              Platobné údaje
            </h3>

            {/* Dates in Pills */}
            <div className="flex gap-2 mb-3">
              <div className="flex-1 bg-gradient-to-br from-blue-50 to-blue-100 p-2 rounded-lg border border-blue-200">
                <p className="text-xs text-blue-600 font-semibold mb-0.5">Vystavené</p>
                <p className="text-xs font-bold text-slate-900">{invoiceData.date}</p>
              </div>
              <div className="flex-1 bg-gradient-to-br from-purple-50 to-purple-100 p-2 rounded-lg border border-purple-200">
                <p className="text-xs text-purple-600 font-semibold mb-0.5">Splatnosť</p>
                <p className="text-xs font-bold text-slate-900">{invoiceData.dueDate}</p>
              </div>
            </div>

            {/* Bank Details */}
            <div className="bg-gradient-to-br from-slate-50 to-slate-100 p-3 rounded-lg border border-slate-200 space-y-1 text-xs">
              <div className="flex justify-between items-center pb-1 border-b border-slate-300">
                <span className="text-slate-600 font-semibold">Číslo účtu</span>
                <span className="font-mono font-bold text-slate-900">SK12 3456 7890 1234 5678 9012</span>
              </div>
              <div className="flex justify-between items-center pb-1 border-b border-slate-300">
                <span className="text-slate-600 font-semibold">Variabilný symbol</span>
                <span className="font-mono font-bold text-slate-900">{invoiceData.variableSymbol || invoiceData.id.replace("INV-", "")}</span>
              </div>
              {invoiceData.constantSymbol && (
                <div className="flex justify-between items-center pb-1 border-b border-slate-300">
                  <span className="text-slate-600 font-semibold">Konštantný symbol</span>
                  <span className="font-mono font-bold text-slate-900">{invoiceData.constantSymbol}</span>
                </div>
              )}
              {invoiceData.specificSymbol && (
                <div className="flex justify-between items-center pb-1 border-b border-slate-300">
                  <span className="text-slate-600 font-semibold">Špecifický symbol</span>
                  <span className="font-mono font-bold text-slate-900">{invoiceData.specificSymbol}</span>
                </div>
              )}
              <div className="flex justify-between items-center pt-1">
                <span className="font-bold text-slate-700">Suma k úhrade</span>
                <span className="font-black text-lg bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                  €{total.toFixed(2)}
                </span>
              </div>
            </div>
          </div>

          {/* QR Code with Modern Styling - Smaller */}
          <div className="flex flex-col items-center gap-1">
            <div className="relative">
              <div className="absolute -inset-1 bg-gradient-to-r from-blue-500 to-purple-500 rounded-xl opacity-30 blur"></div>
              <div className="relative bg-white p-2 rounded-xl shadow-lg border border-slate-200">
                <QRCodeSVG
                  value={generatePayBySquareData()}
                  size={90}
                  level="M"
                  includeMargin={false}
                />
              </div>
            </div>
            <p className="text-xs font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
              Pay by Square
            </p>
          </div>
        </div>
      </div>

      {/* Modern Items Table */}
      <div className="bg-white rounded-2xl p-5 mb-6 shadow-xl border border-slate-200">
        <h3 className="text-lg font-bold mb-4 text-slate-900">Položky</h3>
        <div className="overflow-hidden rounded-xl border border-slate-200">
          <table className="w-full">
            <thead>
              <tr className="bg-gradient-to-r from-blue-600 to-purple-600 text-white">
                <th className="text-left py-3 px-3 font-semibold text-xs uppercase tracking-wide">Popis</th>
                <th className="text-right py-3 px-3 w-16 font-semibold text-xs uppercase tracking-wide">Počet</th>
                {isVatPayer ? (
                  <>
                    <th className="text-right py-3 px-3 w-24 font-semibold text-xs uppercase tracking-wide">Cena/ks<br/><span className="text-[10px] font-normal opacity-80">(bez DPH)</span></th>
                    <th className="text-right py-3 px-3 w-16 font-semibold text-xs uppercase tracking-wide">DPH</th>
                    <th className="text-right py-3 px-3 w-24 font-semibold text-xs uppercase tracking-wide">Výška<br/><span className="text-[10px] font-normal opacity-80">DPH</span></th>
                    <th className="text-right py-3 px-3 w-28 font-semibold text-xs uppercase tracking-wide">Celkom</th>
                  </>
                ) : (
                  <>
                    <th className="text-right py-3 px-3 w-28 font-semibold text-xs uppercase tracking-wide">Cena/ks</th>
                    <th className="text-right py-3 px-3 w-28 font-semibold text-xs uppercase tracking-wide">Celkom</th>
                  </>
                )}
              </tr>
            </thead>
            <tbody>
              {invoiceData.items.map((item, index) => (
                <tr
                  key={index}
                  className={`border-b border-slate-200 ${index % 2 === 0 ? 'bg-slate-50' : 'bg-white'} hover:bg-blue-50 transition-colors`}
                >
                  <td className="py-3 px-3 text-slate-900 text-sm">{item.description}</td>
                  <td className="text-right py-3 px-3 text-slate-700 text-sm">{item.quantity}</td>
                  {isVatPayer ? (
                    <>
                      <td className="text-right py-3 px-3 text-slate-700 text-sm">{(item.unitPriceWithoutTax ?? item.price).toFixed(2)} €</td>
                      <td className="text-right py-3 px-3 text-slate-700 text-sm">{(item.taxRate ?? taxRate).toFixed(0)}%</td>
                      <td className="text-right py-3 px-3 text-slate-700 text-sm">{(item.taxAmount ?? 0).toFixed(2)} €</td>
                      <td className="text-right py-3 px-3 font-bold text-slate-900 text-sm">{(item.totalPrice ?? item.quantity * item.price).toFixed(2)} €</td>
                    </>
                  ) : (
                    <>
                      <td className="text-right py-3 px-3 text-slate-700 text-sm">{item.price.toFixed(2)} €</td>
                      <td className="text-right py-3 px-3 font-bold text-slate-900 text-sm">{(item.totalPrice ?? item.quantity * item.price).toFixed(2)} €</td>
                    </>
                  )}
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* Totals with Modern Styling */}
      <div className="flex justify-end mb-6">
        <div className="w-80">
          <div className="bg-white rounded-2xl p-4 shadow-xl border border-slate-200">
            {isVatPayer && (
              <>
                <div className="flex justify-between py-2 border-b border-slate-200">
                  <span className="text-slate-600 font-medium text-sm">Základ dane (bez DPH):</span>
                  <span className="font-semibold text-slate-900 text-sm">{subtotal.toFixed(2)} €</span>
                </div>
                <div className="flex justify-between py-2 border-b border-slate-200">
                  <span className="text-slate-600 font-medium text-sm">DPH ({taxRate.toFixed(0)}%):</span>
                  <span className="font-semibold text-slate-900 text-sm">{vat.toFixed(2)} €</span>
                </div>
              </>
            )}
            <div className="flex justify-between py-3 mt-2 bg-gradient-to-r from-blue-600 to-purple-600 px-4 rounded-xl">
              <span className="font-bold text-base text-white">Celkom k úhrade:</span>
              <span className="font-black text-xl text-white">{total.toFixed(2)} €</span>
            </div>
          </div>
        </div>
      </div>

      {/* Footer */}
      <div className="bg-white rounded-2xl p-6 border border-slate-200">
        <p className="text-sm text-slate-600 text-center">
          Faktúru je potrebné uhradiť do dátumu splatnosti. V prípade otázok nás kontaktujte na email@invoicehub.sk
        </p>
      </div>

      <div className="mt-6 text-center text-xs text-slate-500">
        <p className="font-medium">Ďakujeme za vašu dôveru! ✨</p>
      </div>
    </div>
  );
};
