import { InvoiceData } from '@/types/invoice';

interface InvoicePreviewMinimalProps {
  invoiceData: InvoiceData;
}

export const InvoicePreviewMinimal = ({ invoiceData }: InvoicePreviewMinimalProps) => {
  // Use actual VAT data from backend
  const isVatPayer = invoiceData.isVatPayer ?? false;
  const subtotal = invoiceData.subtotal ?? invoiceData.items.reduce((sum, item) => sum + item.quantity * item.price, 0);
  const taxRate = invoiceData.taxRate ?? 20;
  const vat = invoiceData.taxAmount ?? subtotal * (taxRate / 100);
  const total = invoiceData.totalAmount ?? subtotal + vat;
  const currency = invoiceData.currency ?? 'EUR';

  return (
    <div className="bg-white text-slate-900 p-5 rounded-lg print:p-0" id="invoice-content">
      {/* Minimal Header */}
      <div className="mb-3 print:break-inside-avoid">
        <div className="flex justify-between items-start pb-3 border-b border-slate-300">
          <div>
            <h1 className="text-[16pt] font-light tracking-wide text-slate-800 mb-0.5 leading-tight">InvoiceHub</h1>
            <p className="text-[9pt] text-slate-500 uppercase tracking-widest">Invoice Management</p>
          </div>
          <div className="text-right">
            <p className="text-[9pt] text-slate-500 uppercase tracking-wider mb-0.5">Invoice</p>
            <p className="text-[14pt] font-light text-slate-900 leading-tight">{invoiceData.id}</p>
          </div>
        </div>
      </div>

      {/* Supplier and Client - Side by Side */}
      <div className="grid grid-cols-2 gap-6 mb-3 print:break-inside-avoid">
        {/* Supplier */}
        <div>
          <p className="text-[9pt] font-semibold text-slate-500 uppercase tracking-wider mb-2">From</p>
          <p className="font-medium text-[11pt] text-slate-900 mb-0.5 leading-relaxed">{invoiceData.supplier?.name || 'N/A'}</p>
          {(invoiceData.supplier?.address ?? '').split(',').filter(Boolean).map((part, index) => (
            <p key={index} className="text-[10pt] text-slate-600 leading-relaxed">{part.trim()}</p>
          ))}
          <div className="space-y-0.5 mt-2">
            <p className="text-[9pt] text-slate-500 leading-relaxed">ICO: {invoiceData.supplier?.ico}</p>
            <p className="text-[9pt] text-slate-500 leading-relaxed">DIC: {invoiceData.supplier?.dic}</p>
            {invoiceData.supplier?.icDph && (
              <p className="text-[9pt] text-slate-500 leading-relaxed">IC DPH: {invoiceData.supplier?.icDph}</p>
            )}
            {(invoiceData.supplier?.registryOffice || invoiceData.supplier?.registryNumber) && (
              <p className="text-[9pt] text-slate-500 leading-relaxed mt-1.5">
                {invoiceData.supplier?.registryOffice}
                {invoiceData.supplier?.registryOffice && invoiceData.supplier?.registryNumber && ', registracia c. '}
                {!invoiceData.supplier?.registryOffice && invoiceData.supplier?.registryNumber && 'registracia c. '}
                {invoiceData.supplier?.registryNumber}
              </p>
            )}
          </div>
        </div>

        {/* Client */}
        <div>
          <p className="text-[9pt] font-semibold text-slate-500 uppercase tracking-wider mb-2">To</p>
          <p className="font-medium text-[11pt] text-slate-900 mb-0.5 leading-relaxed">{invoiceData.client.name}</p>
          {(invoiceData.client.address ?? '').split(',').filter(Boolean).map((part, index) => (
            <p key={index} className="text-[10pt] text-slate-600 leading-relaxed">{part.trim()}</p>
          ))}
          <div className="space-y-0.5 mt-2">
            <p className="text-[9pt] text-slate-500 leading-relaxed">ICO: {invoiceData.client.ico}</p>
            <p className="text-[9pt] text-slate-500 leading-relaxed">DIC: {invoiceData.client.dic}</p>
          </div>
        </div>
      </div>

      {/* Invoice Details & Payment */}
      <div className="grid grid-cols-[1fr_auto_100px] gap-4 mb-3 pb-3 border-b border-slate-200 print:break-inside-avoid">
        <div className="space-y-1.5">
          <div>
            <p className="text-[9pt] text-slate-500 uppercase tracking-wider">Issue Date</p>
            <p className="text-[11pt] font-medium text-slate-900 leading-relaxed">{invoiceData.date}</p>
          </div>
          <div>
            <p className="text-[9pt] text-slate-500 uppercase tracking-wider">Due Date</p>
            <p className="text-[11pt] font-medium text-slate-900 leading-relaxed">{invoiceData.dueDate}</p>
          </div>
          {invoiceData.deliveryDate && (
            <div>
              <p className="text-[9pt] text-slate-500 uppercase tracking-wider">Delivery Date</p>
              <p className="text-[11pt] font-medium text-slate-900 leading-relaxed">{invoiceData.deliveryDate}</p>
            </div>
          )}
          <div>
            <p className="text-[9pt] text-slate-500 uppercase tracking-wider">Payment Method</p>
            <p className="text-[11pt] font-medium text-slate-900 leading-relaxed">Bank Transfer</p>
          </div>
        </div>

        <div className="space-y-1.5">
          {invoiceData.supplier?.iban && (
            <div>
              <p className="text-[9pt] text-slate-500 uppercase tracking-wider">Account Number</p>
              <p className="text-[10pt] font-mono font-medium text-slate-900 leading-relaxed">{invoiceData.supplier.iban}</p>
            </div>
          )}
          <div>
            <p className="text-[9pt] text-slate-500 uppercase tracking-wider">Variable Symbol</p>
            <p className="text-[10pt] font-mono font-medium text-slate-900 leading-relaxed">{invoiceData.variableSymbol || invoiceData.id.replace("INV-", "")}</p>
          </div>
          {invoiceData.constantSymbol && (
            <div>
              <p className="text-[9pt] text-slate-500 uppercase tracking-wider">Constant Symbol</p>
              <p className="text-[10pt] font-mono font-medium text-slate-900 leading-relaxed">{invoiceData.constantSymbol}</p>
            </div>
          )}
          {invoiceData.specificSymbol && (
            <div>
              <p className="text-[9pt] text-slate-500 uppercase tracking-wider">Specific Symbol</p>
              <p className="text-[10pt] font-mono font-medium text-slate-900 leading-relaxed">{invoiceData.specificSymbol}</p>
            </div>
          )}
        </div>

        {/* QR Code */}
        {invoiceData.qrCode && (
          <div className="flex flex-col items-center">
            <div className="border border-slate-300 p-2 rounded">
              <img src={invoiceData.qrCode} alt="Pay by Square QR Code" className="w-[80px] h-[80px]" />
            </div>
            <p className="text-[9pt] text-slate-500 mt-1.5">Pay by Square</p>
          </div>
        )}
      </div>

      {/* Items Table - Minimal Style */}
      <div className="mb-3 print:break-inside-auto">
        <table className="w-full">
          <thead className="print:table-header-group">
            <tr className="border-b border-slate-300">
              <th className="text-left py-2 text-[10pt] font-semibold text-slate-500 uppercase tracking-wider">Popis</th>
              <th className="text-right py-2 w-16 text-[10pt] font-semibold text-slate-500 uppercase tracking-wider">Pocet</th>
              {isVatPayer ? (
                <>
                  <th className="text-right py-2 w-24 text-[10pt] font-semibold text-slate-500 uppercase tracking-wider">Cena/ks<br/><span className="text-[9pt] font-normal">(bez DPH)</span></th>
                  <th className="text-right py-2 w-16 text-[10pt] font-semibold text-slate-500 uppercase tracking-wider">DPH</th>
                  <th className="text-right py-2 w-24 text-[10pt] font-semibold text-slate-500 uppercase tracking-wider">Vyska DPH</th>
                  <th className="text-right py-2 w-28 text-[10pt] font-semibold text-slate-500 uppercase tracking-wider">Celkom</th>
                </>
              ) : (
                <>
                  <th className="text-right py-2 w-28 text-[10pt] font-semibold text-slate-500 uppercase tracking-wider">Cena/ks</th>
                  <th className="text-right py-2 w-28 text-[10pt] font-semibold text-slate-500 uppercase tracking-wider">Celkom</th>
                </>
              )}
            </tr>
          </thead>
          <tbody>
            {invoiceData.items.map((item, index) => (
              <tr key={index} className="border-b border-slate-100 print:break-inside-avoid">
                <td className="py-2 text-[11pt] text-slate-900 leading-relaxed">{item.description}</td>
                <td className="text-right py-2 text-[11pt] text-slate-600 leading-relaxed">{item.quantity}</td>
                {isVatPayer ? (
                  <>
                    <td className="text-right py-2 text-[11pt] text-slate-600 leading-relaxed">{(item.unitPriceWithoutTax ?? item.price).toFixed(2)} {currency}</td>
                    <td className="text-right py-2 text-[11pt] text-slate-600 leading-relaxed">{(item.taxRate ?? taxRate).toFixed(0)}%</td>
                    <td className="text-right py-2 text-[11pt] text-slate-600 leading-relaxed">{(item.taxAmount ?? 0).toFixed(2)} {currency}</td>
                    <td className="text-right py-2 text-[11pt] font-medium text-slate-900 leading-relaxed">{(item.totalPrice ?? item.quantity * item.price).toFixed(2)} {currency}</td>
                  </>
                ) : (
                  <>
                    <td className="text-right py-2 text-[11pt] text-slate-600 leading-relaxed">{item.price.toFixed(2)} {currency}</td>
                    <td className="text-right py-2 text-[11pt] font-medium text-slate-900 leading-relaxed">{(item.totalPrice ?? item.quantity * item.price).toFixed(2)} {currency}</td>
                  </>
                )}
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {/* Totals - Minimal */}
      <div className="flex justify-end print:break-inside-avoid print:break-before-avoid">
        <div className="w-72">
          <div className="space-y-1.5">
            {isVatPayer && (
              <>
                <div className="flex justify-between py-1.5 text-[11pt]">
                  <span className="text-slate-600 leading-relaxed">Zaklad dane (bez DPH):</span>
                  <span className="font-medium text-slate-900 leading-relaxed">{subtotal.toFixed(2)} {currency}</span>
                </div>
                <div className="flex justify-between py-1.5 text-[11pt]">
                  <span className="text-slate-600 leading-relaxed">DPH ({taxRate.toFixed(0)}%):</span>
                  <span className="font-medium text-slate-900 leading-relaxed">{vat.toFixed(2)} {currency}</span>
                </div>
              </>
            )}
            <div className="flex justify-between py-2 border-t-2 border-slate-900">
              <span className="font-semibold text-[14pt] text-slate-900 leading-tight">Celkom k uhrade:</span>
              <span className="font-bold text-[14pt] text-slate-900 leading-tight">{total.toFixed(2)} {currency}</span>
            </div>
          </div>
        </div>
      </div>

      {/* Reverse Charge or Tax Exemption Text */}
      {invoiceData.reverseChargeText && (
        <div className="mb-3 mt-4 p-2.5 bg-yellow-50 border border-yellow-200 rounded print:break-inside-avoid">
          <p className="text-[10pt] font-semibold text-yellow-800 leading-relaxed">{invoiceData.reverseChargeText}</p>
        </div>
      )}

      {invoiceData.taxExemptionText && (
        <div className="mb-3 mt-4 p-2.5 bg-blue-50 border border-blue-200 rounded print:break-inside-avoid">
          <p className="text-[10pt] font-semibold text-blue-800 leading-relaxed">{invoiceData.taxExemptionText}</p>
        </div>
      )}

      {/* Footer */}
      <div className="mt-3 pt-2 border-t border-slate-200">
        <p className="text-[9pt] text-slate-500 text-center leading-relaxed">
          Payment is due by the due date. For any questions, please contact us at email@invoicehub.sk
        </p>
      </div>

      <div className="mt-2 text-center">
        <p className="text-[9pt] text-slate-400">Thank you for your business</p>
      </div>
    </div>
  );
};
