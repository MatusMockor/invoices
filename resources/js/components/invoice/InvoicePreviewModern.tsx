import { InvoiceData } from '@/types/invoice';

interface InvoicePreviewModernProps {
  invoiceData: InvoiceData;
}

export const InvoicePreviewModern = ({ invoiceData }: InvoicePreviewModernProps) => {
  // Use actual VAT data from backend
  const isVatPayer = invoiceData.isVatPayer ?? false;
  const subtotal = invoiceData.subtotal ?? invoiceData.items.reduce((sum, item) => sum + item.quantity * item.price, 0);
  const taxRate = invoiceData.taxRate ?? 20;
  const vat = invoiceData.taxAmount ?? subtotal * (taxRate / 100);
  const total = invoiceData.totalAmount ?? subtotal + vat;
  const currency = invoiceData.currency ?? 'EUR';

  return (
    <div className="bg-gradient-to-br from-slate-50 to-blue-50 text-slate-900 p-5 rounded-lg print:p-0" id="invoice-content">
      {/* Modern Header with Gradient - Compact */}
      <div className="mb-3 relative overflow-hidden print:break-inside-avoid">
        <div className="absolute inset-0 bg-gradient-to-r from-blue-600 to-purple-600 opacity-10 rounded-xl"></div>
        <div className="relative bg-white/80 backdrop-blur-sm p-3 rounded-xl border border-blue-200 shadow-lg">
          <div className="flex justify-between items-start mb-2">
            <div>
              <h1 className="text-[16pt] font-black bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-0.5 leading-tight">
                InvoiceHub
              </h1>
              <p className="text-[9pt] text-slate-600 leading-relaxed">Profesionalne riesenie pre faktury</p>
            </div>
            <div className="text-right">
              <div className="inline-block bg-gradient-to-r from-blue-600 to-purple-600 text-white px-2.5 py-1 rounded-lg shadow-lg">
                <p className="text-[9pt] uppercase tracking-wider font-semibold opacity-90">Faktura</p>
                <p className="text-[14pt] font-bold leading-tight">{invoiceData.id}</p>
              </div>
            </div>
          </div>

          {/* Client and Supplier Info in Modern Cards - Compact */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-2 mt-1.5 print:break-inside-avoid">
            {/* Supplier */}
            <div className="relative">
              <div className="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg opacity-20"></div>
              <div className="relative bg-white p-2.5 rounded-lg">
                <div className="flex items-center gap-1.5 mb-1.5">
                  <div className="w-4 h-4 bg-gradient-to-r from-blue-500 to-purple-500 rounded flex items-center justify-center">
                    <span className="text-white text-[8pt] font-bold">OD</span>
                  </div>
                  <h3 className="font-bold text-slate-700 uppercase text-[9pt] tracking-wider">Dodavatel</h3>
                </div>
                <p className="font-bold text-[11pt] mb-0.5 text-slate-900 leading-relaxed">{invoiceData.supplier?.name || 'N/A'}</p>
                {(invoiceData.supplier?.address ?? '').split(',').filter(Boolean).map((part, index) => (
                  <p key={index} className="text-[10pt] text-slate-600 leading-relaxed">{part.trim()}</p>
                ))}
                <div className="border-t border-slate-200 pt-1 mt-1 space-y-0.5">
                  <p className="text-[9pt] text-slate-500 leading-relaxed"><span className="font-semibold">ICO:</span> {invoiceData.supplier?.ico}</p>
                  <p className="text-[9pt] text-slate-500 leading-relaxed"><span className="font-semibold">DIC:</span> {invoiceData.supplier?.dic}</p>
                  {invoiceData.supplier?.icDph && (
                    <p className="text-[9pt] text-slate-500 leading-relaxed"><span className="font-semibold">IC DPH:</span> {invoiceData.supplier?.icDph}</p>
                  )}
                  {(invoiceData.supplier?.registryOffice || invoiceData.supplier?.registryNumber) && (
                    <p className="text-[9pt] text-slate-500 leading-relaxed mt-1">
                      {invoiceData.supplier?.registryOffice}
                      {invoiceData.supplier?.registryOffice && invoiceData.supplier?.registryNumber && ', registracia c. '}
                      {!invoiceData.supplier?.registryOffice && invoiceData.supplier?.registryNumber && 'registracia c. '}
                      {invoiceData.supplier?.registryNumber}
                    </p>
                  )}
                </div>
              </div>
            </div>

            {/* Client */}
            <div className="relative">
              <div className="absolute -inset-0.5 bg-gradient-to-r from-slate-300 to-slate-400 rounded-lg opacity-20"></div>
              <div className="relative bg-white p-2.5 rounded-lg">
                <div className="flex items-center gap-1.5 mb-1.5">
                  <div className="w-4 h-4 bg-slate-600 rounded flex items-center justify-center">
                    <span className="text-white text-[8pt] font-bold">PRE</span>
                  </div>
                  <h3 className="font-bold text-slate-700 uppercase text-[9pt] tracking-wider">Odberatel</h3>
                </div>
                <p className="font-bold text-[11pt] mb-0.5 text-slate-900 leading-relaxed">{invoiceData.client.name}</p>
                {(invoiceData.client.address ?? '').split(',').filter(Boolean).map((part, index) => (
                  <p key={index} className="text-[10pt] text-slate-600 leading-relaxed">{part.trim()}</p>
                ))}
                <div className="border-t border-slate-200 pt-1 mt-1 space-y-0.5">
                  <p className="text-[9pt] text-slate-500 leading-relaxed"><span className="font-semibold">ICO:</span> {invoiceData.client.ico}</p>
                  <p className="text-[9pt] text-slate-500 leading-relaxed"><span className="font-semibold">DIC:</span> {invoiceData.client.dic}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Payment Section with Modern Design - Compact */}
      <div className="bg-white rounded-xl p-3 mb-3 shadow-lg border border-slate-200 print:break-inside-avoid">
        <div className="flex items-start justify-between gap-3">
          <div className="flex-1">
            <h3 className="text-[11pt] font-bold mb-2 bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent leading-tight">
              Platobne udaje
            </h3>

            {/* Dates in Pills */}
            <div className="grid grid-cols-2 gap-1.5 mb-2">
              <div className="bg-gradient-to-br from-blue-50 to-blue-100 p-1.5 rounded-lg border border-blue-200">
                <p className="text-[9pt] text-blue-600 font-semibold mb-0.5">Vystavene</p>
                <p className="text-[10pt] font-bold text-slate-900 leading-relaxed">{invoiceData.date}</p>
              </div>
              <div className="bg-gradient-to-br from-purple-50 to-purple-100 p-1.5 rounded-lg border border-purple-200">
                <p className="text-[9pt] text-purple-600 font-semibold mb-0.5">Splatnost</p>
                <p className="text-[10pt] font-bold text-slate-900 leading-relaxed">{invoiceData.dueDate}</p>
              </div>
              {invoiceData.deliveryDate && (
                <div className="bg-gradient-to-br from-green-50 to-green-100 p-1.5 rounded-lg border border-green-200">
                  <p className="text-[9pt] text-green-600 font-semibold mb-0.5">Dodanie</p>
                  <p className="text-[10pt] font-bold text-slate-900 leading-relaxed">{invoiceData.deliveryDate}</p>
                </div>
              )}
              <div className="bg-gradient-to-br from-slate-50 to-slate-100 p-1.5 rounded-lg border border-slate-200">
                <p className="text-[9pt] text-slate-600 font-semibold mb-0.5">Sposob uhrady</p>
                <p className="text-[10pt] font-bold text-slate-900 leading-relaxed">Bankovy prevod</p>
              </div>
            </div>

            {/* Bank Details */}
            <div className="bg-gradient-to-br from-slate-50 to-slate-100 p-2.5 rounded-lg border border-slate-200 space-y-1">
              {invoiceData.supplier?.iban && (
                <div className="flex justify-between items-center pb-1 border-b border-slate-300">
                  <span className="text-[9pt] text-slate-600 font-semibold">Cislo uctu</span>
                  <span className="font-mono font-bold text-[10pt] text-slate-900">{invoiceData.supplier.iban}</span>
                </div>
              )}
              <div className="flex justify-between items-center pb-1 border-b border-slate-300">
                <span className="text-[9pt] text-slate-600 font-semibold">Variabilny symbol</span>
                <span className="font-mono font-bold text-[10pt] text-slate-900">{invoiceData.variableSymbol || invoiceData.id.replace("INV-", "")}</span>
              </div>
              {invoiceData.constantSymbol && (
                <div className="flex justify-between items-center pb-1 border-b border-slate-300">
                  <span className="text-[9pt] text-slate-600 font-semibold">Konstantny symbol</span>
                  <span className="font-mono font-bold text-[10pt] text-slate-900">{invoiceData.constantSymbol}</span>
                </div>
              )}
              {invoiceData.specificSymbol && (
                <div className="flex justify-between items-center pb-1 border-b border-slate-300">
                  <span className="text-[9pt] text-slate-600 font-semibold">Specificky symbol</span>
                  <span className="font-mono font-bold text-[10pt] text-slate-900">{invoiceData.specificSymbol}</span>
                </div>
              )}
              <div className="flex justify-between items-center pt-0.5">
                <span className="font-bold text-[11pt] text-slate-700">Suma k uhrade</span>
                <span className="font-black text-[14pt] bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                  {total.toFixed(2)} {currency}
                </span>
              </div>
            </div>
          </div>

          {/* QR Code with Modern Styling - Smaller */}
          {invoiceData.qrCode && (
            <div className="flex flex-col items-center gap-1">
              <div className="relative">
                <div className="absolute -inset-1 bg-gradient-to-r from-blue-500 to-purple-500 rounded-xl opacity-30 blur"></div>
                <div className="relative bg-white p-2 rounded-xl shadow-lg border border-slate-200">
                  <img src={invoiceData.qrCode} alt="Pay by Square QR Code" className="w-[80px] h-[80px]" />
                </div>
              </div>
              <p className="text-[9pt] font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                Pay by Square
              </p>
            </div>
          )}
        </div>
      </div>

      {/* Modern Items Table */}
      <div className="bg-white rounded-xl p-3 mb-3 shadow-xl border border-slate-200 print:break-inside-auto">
        <h3 className="text-[11pt] font-bold mb-2 text-slate-900 leading-tight">Polozky</h3>
        <div className="overflow-hidden rounded-xl border border-slate-200">
          <table className="w-full">
            <thead className="print:table-header-group">
              <tr className="bg-gradient-to-r from-blue-600 to-purple-600 text-white">
                <th className="text-left py-2 px-2 font-semibold text-[10pt] uppercase tracking-wide">Popis</th>
                <th className="text-right py-2 px-2 w-16 font-semibold text-[10pt] uppercase tracking-wide">Pocet</th>
                {isVatPayer ? (
                  <>
                    <th className="text-right py-2 px-2 w-24 font-semibold text-[10pt] uppercase tracking-wide">Cena/ks<br/><span className="text-[9pt] font-normal opacity-80">(bez DPH)</span></th>
                    <th className="text-right py-2 px-2 w-16 font-semibold text-[10pt] uppercase tracking-wide">DPH</th>
                    <th className="text-right py-2 px-2 w-24 font-semibold text-[10pt] uppercase tracking-wide">Vyska<br/><span className="text-[9pt] font-normal opacity-80">DPH</span></th>
                    <th className="text-right py-2 px-2 w-28 font-semibold text-[10pt] uppercase tracking-wide">Celkom</th>
                  </>
                ) : (
                  <>
                    <th className="text-right py-2 px-2 w-28 font-semibold text-[10pt] uppercase tracking-wide">Cena/ks</th>
                    <th className="text-right py-2 px-2 w-28 font-semibold text-[10pt] uppercase tracking-wide">Celkom</th>
                  </>
                )}
              </tr>
            </thead>
            <tbody>
              {invoiceData.items.map((item, index) => (
                <tr
                  key={index}
                  className={`border-b border-slate-200 ${index % 2 === 0 ? 'bg-slate-50' : 'bg-white'} hover:bg-blue-50 transition-colors print:break-inside-avoid`}
                >
                  <td className="py-2 px-2 text-slate-900 text-[11pt] leading-relaxed">{item.description}</td>
                  <td className="text-right py-2 px-2 text-slate-700 text-[11pt] leading-relaxed">{item.quantity}</td>
                  {isVatPayer ? (
                    <>
                      <td className="text-right py-2 px-2 text-slate-700 text-[11pt] leading-relaxed">{(item.unitPriceWithoutTax ?? item.price).toFixed(2)} {currency}</td>
                      <td className="text-right py-2 px-2 text-slate-700 text-[11pt] leading-relaxed">{(item.taxRate ?? taxRate).toFixed(0)}%</td>
                      <td className="text-right py-2 px-2 text-slate-700 text-[11pt] leading-relaxed">{(item.taxAmount ?? 0).toFixed(2)} {currency}</td>
                      <td className="text-right py-2 px-2 font-bold text-slate-900 text-[11pt] leading-relaxed">{(item.totalPrice ?? item.quantity * item.price).toFixed(2)} {currency}</td>
                    </>
                  ) : (
                    <>
                      <td className="text-right py-2 px-2 text-slate-700 text-[11pt] leading-relaxed">{item.price.toFixed(2)} {currency}</td>
                      <td className="text-right py-2 px-2 font-bold text-slate-900 text-[11pt] leading-relaxed">{(item.totalPrice ?? item.quantity * item.price).toFixed(2)} {currency}</td>
                    </>
                  )}
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* Totals with Modern Styling */}
      <div className="flex justify-end mb-3 print:break-inside-avoid print:break-before-avoid">
        <div className="w-72">
          <div className="bg-white rounded-xl p-3 shadow-xl border border-slate-200">
            {isVatPayer && (
              <>
                <div className="flex justify-between py-1.5 border-b border-slate-200">
                  <span className="text-slate-600 font-medium text-[11pt] leading-relaxed">Zaklad dane (bez DPH):</span>
                  <span className="font-semibold text-slate-900 text-[11pt] leading-relaxed">{subtotal.toFixed(2)} {currency}</span>
                </div>
                <div className="flex justify-between py-1.5 border-b border-slate-200">
                  <span className="text-slate-600 font-medium text-[11pt] leading-relaxed">DPH ({taxRate.toFixed(0)}%):</span>
                  <span className="font-semibold text-slate-900 text-[11pt] leading-relaxed">{vat.toFixed(2)} {currency}</span>
                </div>
              </>
            )}
            <div className="flex justify-between py-2 mt-1.5 bg-gradient-to-r from-blue-600 to-purple-600 px-3 rounded-xl">
              <span className="font-bold text-[14pt] text-white leading-tight">Celkom k uhrade:</span>
              <span className="font-black text-[14pt] text-white leading-tight">{total.toFixed(2)} {currency}</span>
            </div>
          </div>
        </div>
      </div>

      {/* Reverse Charge or Tax Exemption Text */}
      {invoiceData.reverseChargeText && (
        <div className="mb-3 p-2.5 bg-yellow-50 border border-yellow-200 rounded-xl print:break-inside-avoid">
          <p className="text-[10pt] font-semibold text-yellow-800 leading-relaxed">{invoiceData.reverseChargeText}</p>
        </div>
      )}

      {invoiceData.taxExemptionText && (
        <div className="mb-3 p-2.5 bg-blue-50 border border-blue-200 rounded-xl print:break-inside-avoid">
          <p className="text-[10pt] font-semibold text-blue-800 leading-relaxed">{invoiceData.taxExemptionText}</p>
        </div>
      )}

      {/* Footer */}
      <div className="bg-white rounded-xl p-3 border border-slate-200">
        <p className="text-[9pt] text-slate-600 text-center leading-relaxed">
          Fakturu je potrebne uhradit do datumu splatnosti. V pripade otazok nas kontaktujte na email@invoicehub.sk
        </p>
      </div>

      <div className="mt-3 text-center text-[9pt] text-slate-500">
        <p className="font-medium">Dakujeme za vasu doveru!</p>
      </div>
    </div>
  );
};
