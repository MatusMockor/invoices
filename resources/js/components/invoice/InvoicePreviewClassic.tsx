import { InvoiceData } from '@/types/invoice';

interface InvoicePreviewClassicProps {
  invoiceData: InvoiceData;
}

export const InvoicePreviewClassic = ({ invoiceData }: InvoicePreviewClassicProps) => {
  const isVatPayer = invoiceData.isVatPayer ?? false;
  const subtotal = invoiceData.subtotal ?? 0;
  const taxRate = invoiceData.taxRate ?? 0;
  const taxAmount = invoiceData.taxAmount ?? 0;
  const total = invoiceData.totalAmount ?? 0;
  const currency = invoiceData.currency ?? 'EUR';

  return (
    <div className="bg-white text-black p-5 rounded-lg print:p-0" id="invoice-content">
      {/* Header with Company Logo */}
      <div className="mb-3 pb-3 border-b-2 border-purple-600 print:break-inside-avoid">
        <div className="flex justify-between items-center mb-2">
          <h1 className="text-[16pt] font-bold text-purple-600 leading-tight">InvoiceHub</h1>
          <h2 className="text-[14pt] font-bold text-purple-600 leading-tight">FAKTURA {invoiceData.id}</h2>
        </div>

        {/* Top row: Supplier + Client side-by-side */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-3 print:break-inside-avoid">
          {/* Supplier Info */}
          <div className="flex-1 bg-purple-50/50 p-3 rounded-lg border border-purple-200">
            <h3 className="font-bold text-purple-600 mb-1.5 text-[9pt] uppercase tracking-wide">Dodavatel</h3>
            <p className="font-semibold text-[11pt] leading-relaxed mb-0.5">{invoiceData.supplier?.name || 'N/A'}</p>
            {(invoiceData.supplier?.address ?? '').split(',').filter(Boolean).map((part, index) => (
              <p key={index} className="text-[10pt] text-gray-600 leading-relaxed">{part.trim()}</p>
            ))}
            <div className="mt-1.5 pt-1.5 border-t border-purple-200">
              <p className="text-[9pt] text-gray-600 leading-relaxed">ICO: {invoiceData.supplier?.ico || 'N/A'}</p>
              <p className="text-[9pt] text-gray-600 leading-relaxed">DIC: {invoiceData.supplier?.dic || 'N/A'}</p>
              {invoiceData.supplier?.icDph && (
                <p className="text-[9pt] text-gray-600 leading-relaxed">IC DPH: {invoiceData.supplier.icDph}</p>
              )}
              {(invoiceData.supplier?.registryOffice || invoiceData.supplier?.registryNumber) && (
                <p className="text-[9pt] text-gray-600 leading-relaxed mt-1.5">
                  {invoiceData.supplier?.registryOffice}
                  {invoiceData.supplier?.registryOffice && invoiceData.supplier?.registryNumber && ', registracia c. '}
                  {!invoiceData.supplier?.registryOffice && invoiceData.supplier?.registryNumber && 'registracia c. '}
                  {invoiceData.supplier?.registryNumber}
                </p>
              )}
            </div>
          </div>

          {/* Client Info */}
          <div className="flex-1 bg-gray-50 p-3 rounded-lg border border-gray-200">
            <h3 className="font-bold text-gray-700 mb-1.5 text-[9pt] uppercase tracking-wide">Odberatel</h3>
            <p className="font-semibold text-[11pt] leading-relaxed mb-0.5">{invoiceData.client.name}</p>
            {(invoiceData.client.address ?? '').split(',').filter(Boolean).map((part, index) => (
              <p key={index} className="text-[10pt] text-gray-600 leading-relaxed">{part.trim()}</p>
            ))}
            <div className="mt-1.5 pt-1.5 border-t border-gray-200">
              <p className="text-[9pt] text-gray-600 leading-relaxed">ICO: {invoiceData.client.ico || 'N/A'}</p>
              <p className="text-[9pt] text-gray-600 leading-relaxed">DIC: {invoiceData.client.dic || 'N/A'}</p>
              {invoiceData.client.icDph && (
                <p className="text-[9pt] text-gray-600 leading-relaxed">IC DPH: {invoiceData.client.icDph}</p>
              )}
            </div>
          </div>
        </div>
      </div>

      {/* Payment Info with QR Code */}
      <div className="border-2 border-purple-200 rounded-lg p-3 mb-3 bg-gradient-to-br from-purple-50/50 to-white print:break-inside-avoid">
        <h3 className="font-bold mb-1.5 text-[11pt] text-purple-600 border-b border-purple-200 pb-1">Platobne udaje</h3>
        <div className="flex gap-3 items-start">
          <div className="flex-1 space-y-1.5">
            {/* Invoice Details */}
            <div className="grid grid-cols-2 gap-x-2 gap-y-1.5">
              <div className="bg-white/60 p-2 rounded-lg border border-purple-100">
                <p className="text-[9pt] text-gray-500 mb-0.5 uppercase tracking-wide">Datum vystavenia</p>
                <p className="font-semibold text-[11pt] text-gray-900 leading-relaxed">{invoiceData.date}</p>
              </div>
              <div className="bg-white/60 p-2 rounded-lg border border-purple-100">
                <p className="text-[9pt] text-gray-500 mb-0.5 uppercase tracking-wide">Datum splatnosti</p>
                <p className="font-semibold text-[11pt] text-purple-600 leading-relaxed">{invoiceData.dueDate}</p>
              </div>
              {invoiceData.deliveryDate && (
                <div className="bg-white/60 p-2 rounded-lg border border-purple-100">
                  <p className="text-[9pt] text-gray-500 mb-0.5 uppercase tracking-wide">Datum dodania</p>
                  <p className="font-semibold text-[11pt] text-gray-900 leading-relaxed">{invoiceData.deliveryDate}</p>
                </div>
              )}
              <div className="bg-white/60 p-2 rounded-lg border border-purple-100">
                <p className="text-[9pt] text-gray-500 mb-0.5 uppercase tracking-wide">Sposob uhrady</p>
                <p className="font-semibold text-[11pt] text-gray-900 leading-relaxed">Bankovy prevod</p>
              </div>
            </div>

            {/* Bank Details */}
            <div className="bg-white/80 p-2.5 rounded-lg border border-purple-200 space-y-1.5">
              {invoiceData.supplier?.iban && (
                <div className="flex justify-between items-center border-b border-gray-100 pb-1">
                  <span className="text-[9pt] text-gray-500 uppercase tracking-wide">Cislo uctu</span>
                  <span className="font-mono font-semibold text-[10pt] text-gray-900">{invoiceData.supplier.iban}</span>
                </div>
              )}
              {invoiceData.variableSymbol && (
                <div className="flex justify-between items-center border-b border-gray-100 pb-1">
                  <span className="text-[9pt] text-gray-500 uppercase tracking-wide">Variabilny symbol</span>
                  <span className="font-mono font-semibold text-[10pt] text-gray-900">{invoiceData.variableSymbol}</span>
                </div>
              )}
              {invoiceData.constantSymbol && (
                <div className="flex justify-between items-center border-b border-gray-100 pb-1">
                  <span className="text-[9pt] text-gray-500 uppercase tracking-wide">Konstantny symbol</span>
                  <span className="font-mono font-semibold text-[10pt] text-gray-900">{invoiceData.constantSymbol}</span>
                </div>
              )}
              {invoiceData.specificSymbol && (
                <div className="flex justify-between items-center border-b border-gray-100 pb-1">
                  <span className="text-[9pt] text-gray-500 uppercase tracking-wide">Specificky symbol</span>
                  <span className="font-mono font-semibold text-[10pt] text-gray-900">{invoiceData.specificSymbol}</span>
                </div>
              )}
              <div className="flex justify-between items-center pt-0.5">
                <span className="text-[11pt] font-semibold text-gray-700">Suma k uhrade</span>
                <span className="font-bold text-[14pt] text-purple-600">{total.toFixed(2)} {currency}</span>
              </div>
            </div>
          </div>

          {/* QR Code - Only render if available */}
          {invoiceData.qrCode && (
            <div className="flex flex-col items-center bg-white p-2 border border-purple-300 rounded-lg">
              <img src={invoiceData.qrCode} alt="Pay by Square QR Code" className="w-[100px] h-[100px]" />
              <p className="text-[8pt] font-semibold text-purple-600 mt-1">Pay by Square</p>
            </div>
          )}
        </div>
      </div>

      {/* Items Table */}
      <div className="mb-3 print:break-inside-auto">
        <table className="w-full">
          <thead className="print:table-header-group">
            <tr className="border-b-2 border-gray-300">
              <th className="text-left py-2 px-2 text-[10pt] font-semibold">Popis</th>
              <th className="text-right py-2 px-2 w-16 text-[10pt] font-semibold">Pocet</th>
              {isVatPayer ? (
                <>
                  <th className="text-right py-2 px-2 w-24 text-[10pt] font-semibold">Cena/ks<br/><span className="text-[9pt] font-normal">(bez DPH)</span></th>
                  <th className="text-right py-2 px-2 w-20 text-[10pt] font-semibold">Sadzba<br/><span className="text-[9pt] font-normal">DPH</span></th>
                  <th className="text-right py-2 px-2 w-24 text-[10pt] font-semibold">Vyska<br/><span className="text-[9pt] font-normal">DPH</span></th>
                  <th className="text-right py-2 px-2 w-28 text-[10pt] font-semibold">Celkom<br/><span className="text-[9pt] font-normal">(s DPH)</span></th>
                </>
              ) : (
                <>
                  <th className="text-right py-2 px-2 w-28 text-[10pt] font-semibold">Cena/ks</th>
                  <th className="text-right py-2 px-2 w-32 text-[10pt] font-semibold">Celkom</th>
                </>
              )}
            </tr>
          </thead>
          <tbody>
            {invoiceData.items.map((item, index) => (
              <tr key={index} className="border-b border-gray-200 print:break-inside-avoid">
                <td className="py-2 px-2 text-[11pt] leading-relaxed">{item.description}</td>
                <td className="text-right py-2 px-2 text-[11pt] leading-relaxed">{item.quantity}</td>
                {isVatPayer ? (
                  <>
                    <td className="text-right py-2 px-2 text-[11pt] leading-relaxed">{(item.unitPriceWithoutTax ?? item.price).toFixed(2)} {currency}</td>
                    <td className="text-right py-2 px-2 text-[11pt] leading-relaxed">{(item.taxRate ?? taxRate).toFixed(0)}%</td>
                    <td className="text-right py-2 px-2 text-[11pt] leading-relaxed">{(item.taxAmount ?? 0).toFixed(2)} {currency}</td>
                    <td className="text-right py-2 px-2 text-[11pt] font-semibold leading-relaxed">{(item.totalPrice ?? item.quantity * item.price).toFixed(2)} {currency}</td>
                  </>
                ) : (
                  <>
                    <td className="text-right py-2 px-2 text-[11pt] leading-relaxed">{item.price.toFixed(2)} {currency}</td>
                    <td className="text-right py-2 px-2 text-[11pt] font-semibold leading-relaxed">{(item.totalPrice ?? item.quantity * item.price).toFixed(2)} {currency}</td>
                  </>
                )}
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {/* Totals */}
      <div className="flex justify-end mb-3 print:break-inside-avoid print:break-before-avoid">
        <div className="w-72">
          <div className="space-y-1.5">
            {isVatPayer && (
              <>
                <div className="flex justify-between py-1.5 px-3 border-b border-gray-200">
                  <span className="text-[11pt] text-gray-700 leading-relaxed">Zaklad dane (bez DPH):</span>
                  <span className="text-[11pt] font-semibold leading-relaxed">{subtotal.toFixed(2)} {currency}</span>
                </div>
                <div className="flex justify-between py-1.5 px-3 border-b border-gray-200">
                  <span className="text-[11pt] text-gray-700 leading-relaxed">DPH {taxRate.toFixed(0)}%:</span>
                  <span className="text-[11pt] font-semibold leading-relaxed">{taxAmount.toFixed(2)} {currency}</span>
                </div>
              </>
            )}
            <div className="flex justify-between py-2 bg-purple-50 px-3 rounded-lg mt-1.5">
              <span className="font-bold text-[14pt] leading-tight">Celkom k uhrade:</span>
              <span className="font-bold text-[14pt] text-purple-600 leading-tight">{total.toFixed(2)} {currency}</span>
            </div>
          </div>
        </div>
      </div>

      {/* Reverse Charge or Tax Exemption Text */}
      {invoiceData.reverseChargeText && (
        <div className="mb-3 p-2.5 bg-yellow-50 border border-yellow-200 rounded print:break-inside-avoid">
          <p className="text-[10pt] font-semibold text-yellow-800 leading-relaxed">{invoiceData.reverseChargeText}</p>
        </div>
      )}

      {invoiceData.taxExemptionText && (
        <div className="mb-3 p-2.5 bg-blue-50 border border-blue-200 rounded print:break-inside-avoid">
          <p className="text-[10pt] font-semibold text-blue-800 leading-relaxed">{invoiceData.taxExemptionText}</p>
        </div>
      )}

      {/* Footer Note */}
      <div className="border-t border-gray-200 pt-2">
        <p className="text-[9pt] text-gray-600 leading-relaxed">
          Fakturu je potrebne uhradit do datumu splatnosti.
        </p>
      </div>

      {/* Footer */}
      <div className="mt-3 text-center text-[9pt] text-gray-500 border-t border-gray-200 pt-2">
        <p>Dakujeme za vasu doveru!</p>
      </div>
    </div>
  );
};
