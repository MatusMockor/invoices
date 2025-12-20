import { InvoiceData } from '@/types/invoice';

interface InvoicePreviewBoldProps {
  invoiceData: InvoiceData;
}

export const InvoicePreviewBold = ({ invoiceData }: InvoicePreviewBoldProps) => {
  // Use actual VAT data from backend
  const isVatPayer = invoiceData.isVatPayer ?? false;
  const subtotal = invoiceData.subtotal ?? invoiceData.items.reduce((sum, item) => sum + item.quantity * item.price, 0);
  const taxRate = invoiceData.taxRate ?? 20;
  const vat = invoiceData.taxAmount ?? subtotal * (taxRate / 100);
  const total = invoiceData.totalAmount ?? subtotal + vat;
  const currency = invoiceData.currency ?? 'EUR';

  return (
    <div className="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white p-5 rounded-xl shadow-2xl print:p-0" id="invoice-content">
      {/* Bold Header with Accent */}
      <div className="mb-3 pb-3 border-b-2 border-cyan-500 print:break-inside-avoid">
        <div className="flex justify-between items-start">
          <div>
            <h1 className="text-[16pt] font-bold text-white mb-1 leading-tight">InvoiceHub</h1>
            <div className="h-1 w-20 bg-gradient-to-r from-cyan-500 to-blue-500 rounded-full"></div>
          </div>
          <div className="text-right">
            <p className="text-[9pt] text-cyan-400 uppercase tracking-widest mb-1 font-semibold">Invoice Number</p>
            <p className="text-[14pt] font-bold bg-gradient-to-r from-cyan-400 to-blue-400 bg-clip-text text-transparent leading-tight">
              {invoiceData.id}
            </p>
          </div>
        </div>
      </div>

      {/* Client and Supplier Cards */}
      <div className="grid grid-cols-2 gap-3 mb-3 print:break-inside-avoid">
        {/* Supplier Card */}
        <div className="bg-white/5 backdrop-blur-sm rounded-xl p-3 border border-white/10">
          <div className="flex items-center gap-2 mb-2">
            <div className="h-6 w-1 bg-cyan-500 rounded-full"></div>
            <p className="text-[9pt] font-bold text-cyan-400 uppercase tracking-wider">From</p>
          </div>
          <p className="font-bold text-white mb-1 text-[11pt] leading-relaxed">{invoiceData.supplier?.name || 'N/A'}</p>
          {(invoiceData.supplier?.address ?? '').split(',').filter(Boolean).map((part, index) => (
            <p key={index} className="text-[10pt] text-slate-300 mb-0.5 leading-relaxed">{part.trim()}</p>
          ))}
          <div className="space-y-0.5 pt-2 border-t border-white/10 mt-2">
            <p className="text-[9pt] text-slate-400 leading-relaxed">ICO: <span className="text-white font-semibold">{invoiceData.supplier?.ico}</span></p>
            <p className="text-[9pt] text-slate-400 leading-relaxed">DIC: <span className="text-white font-semibold">{invoiceData.supplier?.dic}</span></p>
            {invoiceData.supplier?.icDph && (
              <p className="text-[9pt] text-slate-400 leading-relaxed">IC DPH: <span className="text-white font-semibold">{invoiceData.supplier?.icDph}</span></p>
            )}
            {(invoiceData.supplier?.registryOffice || invoiceData.supplier?.registryNumber) && (
              <p className="text-[9pt] text-slate-400 leading-relaxed mt-1.5">
                <span className="text-white">
                  {invoiceData.supplier?.registryOffice}
                  {invoiceData.supplier?.registryOffice && invoiceData.supplier?.registryNumber && ', registracia c. '}
                  {!invoiceData.supplier?.registryOffice && invoiceData.supplier?.registryNumber && 'registracia c. '}
                  {invoiceData.supplier?.registryNumber}
                </span>
              </p>
            )}
          </div>
        </div>

        {/* Client Card */}
        <div className="bg-gradient-to-br from-cyan-500/10 to-blue-500/10 backdrop-blur-sm rounded-xl p-3 border border-cyan-500/30">
          <div className="flex items-center gap-2 mb-2">
            <div className="h-6 w-1 bg-cyan-500 rounded-full"></div>
            <p className="text-[9pt] font-bold text-cyan-400 uppercase tracking-wider">Bill To</p>
          </div>
          <p className="font-bold text-white mb-1 text-[11pt] leading-relaxed">{invoiceData.client.name}</p>
          {(invoiceData.client.address ?? '').split(',').filter(Boolean).map((part, index) => (
            <p key={index} className="text-[10pt] text-slate-300 mb-0.5 leading-relaxed">{part.trim()}</p>
          ))}
          <div className="space-y-0.5 pt-2 border-t border-cyan-500/30">
            <p className="text-[9pt] text-slate-400 leading-relaxed">ICO: <span className="text-white font-semibold">{invoiceData.client.ico}</span></p>
            <p className="text-[9pt] text-slate-400 leading-relaxed">DIC: <span className="text-white font-semibold">{invoiceData.client.dic}</span></p>
          </div>
        </div>
      </div>

      {/* Payment Details Section */}
      <div className="grid grid-cols-[1fr_auto] gap-4 mb-3 print:break-inside-avoid">
        <div className="bg-white/5 backdrop-blur-sm rounded-xl p-3 border border-white/10">
          <div className="grid grid-cols-2 gap-3">
            <div>
              <p className="text-[9pt] text-cyan-400 uppercase tracking-wider mb-1 font-semibold">Issue Date</p>
              <p className="text-[11pt] font-bold text-white leading-relaxed">{invoiceData.date}</p>
            </div>
            <div>
              <p className="text-[9pt] text-cyan-400 uppercase tracking-wider mb-1 font-semibold">Due Date</p>
              <p className="text-[11pt] font-bold text-white leading-relaxed">{invoiceData.dueDate}</p>
            </div>
            {invoiceData.deliveryDate && (
              <div>
                <p className="text-[9pt] text-cyan-400 uppercase tracking-wider mb-1 font-semibold">Delivery Date</p>
                <p className="text-[11pt] font-bold text-white leading-relaxed">{invoiceData.deliveryDate}</p>
              </div>
            )}
            <div>
              <p className="text-[9pt] text-cyan-400 uppercase tracking-wider mb-1 font-semibold">Payment Method</p>
              <p className="text-[11pt] font-bold text-white leading-relaxed">Bank Transfer</p>
            </div>
            {invoiceData.supplier?.iban && (
              <div>
                <p className="text-[9pt] text-cyan-400 uppercase tracking-wider mb-1 font-semibold">Account Number</p>
                <p className="text-[10pt] font-mono font-semibold text-white leading-relaxed">{invoiceData.supplier.iban}</p>
              </div>
            )}
            <div>
              <p className="text-[9pt] text-cyan-400 uppercase tracking-wider mb-1 font-semibold">Variable Symbol</p>
              <p className="text-[10pt] font-mono font-semibold text-white leading-relaxed">{invoiceData.variableSymbol || invoiceData.id.replace("INV-", "")}</p>
            </div>
            {invoiceData.constantSymbol && (
              <div>
                <p className="text-[9pt] text-cyan-400 uppercase tracking-wider mb-1 font-semibold">Constant Symbol</p>
                <p className="text-[10pt] font-mono font-semibold text-white leading-relaxed">{invoiceData.constantSymbol}</p>
              </div>
            )}
            {invoiceData.specificSymbol && (
              <div>
                <p className="text-[9pt] text-cyan-400 uppercase tracking-wider mb-1 font-semibold">Specific Symbol</p>
                <p className="text-[10pt] font-mono font-semibold text-white leading-relaxed">{invoiceData.specificSymbol}</p>
              </div>
            )}
          </div>
        </div>

        {/* QR Code with Glow */}
        {invoiceData.qrCode && (
          <div className="flex flex-col items-center justify-center">
            <div className="bg-white p-3 rounded-xl shadow-[0_0_30px_rgba(6,182,212,0.3)]">
              <img src={invoiceData.qrCode} alt="Pay by Square QR Code" className="w-[80px] h-[80px]" />
            </div>
            <p className="text-[9pt] text-cyan-400 mt-2 font-semibold">Pay by Square</p>
          </div>
        )}
      </div>

      {/* Items Table */}
      <div className="mb-3 bg-white/5 backdrop-blur-sm rounded-xl overflow-hidden border border-white/10 print:break-inside-auto">
        <table className="w-full">
          <thead className="print:table-header-group">
            <tr className="bg-gradient-to-r from-cyan-500/20 to-blue-500/20">
              <th className="text-left py-2 px-3 text-[10pt] font-bold text-cyan-400 uppercase tracking-wider">Popis</th>
              <th className="text-right py-2 px-3 w-16 text-[10pt] font-bold text-cyan-400 uppercase tracking-wider">Pocet</th>
              {isVatPayer ? (
                <>
                  <th className="text-right py-2 px-3 w-24 text-[10pt] font-bold text-cyan-400 uppercase tracking-wider">Cena/ks<br/><span className="text-[9pt] font-normal opacity-80">(bez DPH)</span></th>
                  <th className="text-right py-2 px-3 w-16 text-[10pt] font-bold text-cyan-400 uppercase tracking-wider">DPH</th>
                  <th className="text-right py-2 px-3 w-24 text-[10pt] font-bold text-cyan-400 uppercase tracking-wider">Vyska<br/><span className="text-[9pt] font-normal opacity-80">DPH</span></th>
                  <th className="text-right py-2 px-3 w-28 text-[10pt] font-bold text-cyan-400 uppercase tracking-wider">Celkom</th>
                </>
              ) : (
                <>
                  <th className="text-right py-2 px-3 w-32 text-[10pt] font-bold text-cyan-400 uppercase tracking-wider">Cena/ks</th>
                  <th className="text-right py-2 px-3 w-32 text-[10pt] font-bold text-cyan-400 uppercase tracking-wider">Celkom</th>
                </>
              )}
            </tr>
          </thead>
          <tbody>
            {invoiceData.items.map((item, index) => (
              <tr key={index} className="border-t border-white/10 hover:bg-white/5 transition-colors print:break-inside-avoid">
                <td className="py-2 px-3 text-[11pt] text-white font-medium leading-relaxed">{item.description}</td>
                <td className="text-right py-2 px-3 text-[11pt] text-slate-300 leading-relaxed">{item.quantity}</td>
                {isVatPayer ? (
                  <>
                    <td className="text-right py-2 px-3 text-[11pt] text-slate-300 leading-relaxed">{(item.unitPriceWithoutTax ?? item.price).toFixed(2)} {currency}</td>
                    <td className="text-right py-2 px-3 text-[11pt] text-slate-300 leading-relaxed">{(item.taxRate ?? taxRate).toFixed(0)}%</td>
                    <td className="text-right py-2 px-3 text-[11pt] text-slate-300 leading-relaxed">{(item.taxAmount ?? 0).toFixed(2)} {currency}</td>
                    <td className="text-right py-2 px-3 text-[11pt] font-bold text-white leading-relaxed">{(item.totalPrice ?? item.quantity * item.price).toFixed(2)} {currency}</td>
                  </>
                ) : (
                  <>
                    <td className="text-right py-2 px-3 text-[11pt] text-slate-300 leading-relaxed">{item.price.toFixed(2)} {currency}</td>
                    <td className="text-right py-2 px-3 text-[11pt] font-bold text-white leading-relaxed">{(item.totalPrice ?? item.quantity * item.price).toFixed(2)} {currency}</td>
                  </>
                )}
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {/* Totals Section */}
      <div className="flex justify-end mb-3 print:break-inside-avoid print:break-before-avoid">
        <div className="w-80 bg-gradient-to-br from-cyan-500/10 to-blue-500/10 backdrop-blur-sm rounded-xl p-3 border border-cyan-500/30">
          <div className="space-y-2">
            {isVatPayer && (
              <>
                <div className="flex justify-between py-1.5 text-[11pt] border-b border-white/10">
                  <span className="text-slate-300 leading-relaxed">Zaklad dane (bez DPH):</span>
                  <span className="font-bold text-white leading-relaxed">{subtotal.toFixed(2)} {currency}</span>
                </div>
                <div className="flex justify-between py-1.5 text-[11pt] border-b border-white/10">
                  <span className="text-slate-300 leading-relaxed">DPH ({taxRate.toFixed(0)}%):</span>
                  <span className="font-bold text-white leading-relaxed">{vat.toFixed(2)} {currency}</span>
                </div>
              </>
            )}
            <div className="flex justify-between py-2 pt-3">
              <span className="font-bold text-cyan-400 text-[11pt] uppercase tracking-wider leading-tight">Celkom k uhrade:</span>
              <span className="font-bold text-[14pt] bg-gradient-to-r from-cyan-400 to-blue-400 bg-clip-text text-transparent leading-tight">
                {total.toFixed(2)} {currency}
              </span>
            </div>
          </div>
        </div>
      </div>

      {/* Reverse Charge or Tax Exemption Text */}
      {invoiceData.reverseChargeText && (
        <div className="mb-3 p-2.5 bg-yellow-500/20 border border-yellow-500/30 rounded-xl print:break-inside-avoid">
          <p className="text-[10pt] font-semibold text-yellow-300 leading-relaxed">{invoiceData.reverseChargeText}</p>
        </div>
      )}

      {invoiceData.taxExemptionText && (
        <div className="mb-3 p-2.5 bg-blue-500/20 border border-blue-500/30 rounded-xl print:break-inside-avoid">
          <p className="text-[10pt] font-semibold text-blue-300 leading-relaxed">{invoiceData.taxExemptionText}</p>
        </div>
      )}

      {/* Footer */}
      <div className="mt-3 pt-2 border-t border-white/10 text-center">
        <p className="text-[9pt] text-slate-400 mb-1 leading-relaxed">
          Payment is due by the due date. For any questions, please contact us at email@invoicehub.sk
        </p>
        <p className="text-[9pt] text-cyan-400 font-semibold">Thank you for your business</p>
      </div>
    </div>
  );
};
