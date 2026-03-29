import { Document, Page, Text, View, StyleSheet, Image } from '@react-pdf/renderer';

interface InvoiceItem {
  description: string;
  quantity: number;
  unit_price?: number;
  unit_price_without_tax?: number;
  total_price: number;
}

interface InvoicePartySnapshotBank {
  iban?: string;
}

interface Party {
  name: string;
  street?: string;
  postal_code?: string;
  city?: string;
  ico?: string;
  dic?: string;
  ic_dph?: string;
  registration_office?: string;
  registration_number?: string;
}

interface Invoice {
  invoice_number: string;
  issue_date: string;
  due_date: string;
  variable_symbol?: string;
  total_amount: number;
  currency: string;
  notes?: string;
  qr_code?: string;
  party_snapshot: {
    supplier: Party & { bank: InvoicePartySnapshotBank };
    customer: Party;
  };
  items?: InvoiceItem[];
}

interface InvoicePDFDocumentProps {
  invoice: Invoice;
}

const styles = StyleSheet.create({
  page: {
    padding: 30,
    fontSize: 10,
    fontFamily: 'Helvetica',
    backgroundColor: '#ffffff',
  },
  header: {
    marginBottom: 20,
    paddingBottom: 15,
    borderBottom: '2 solid #9333ea',
  },
  headerRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 15,
  },
  logo: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#9333ea',
  },
  invoiceTitle: {
    fontSize: 14,
    fontWeight: 'bold',
    color: '#9333ea',
  },
  partiesRow: {
    flexDirection: 'row',
    gap: 10,
  },
  partyBox: {
    flex: 1,
    padding: 10,
    backgroundColor: '#fefcff',
    borderRadius: 5,
    border: '1 solid #e9d5ff',
  },
  partyBoxClient: {
    flex: 1,
    padding: 10,
    backgroundColor: '#fafafa',
    borderRadius: 5,
    border: '1 solid #e5e7eb',
  },
  partyTitle: {
    fontSize: 8,
    fontWeight: 'bold',
    color: '#9333ea',
    textTransform: 'uppercase',
    marginBottom: 5,
    letterSpacing: 0.5,
  },
  partyTitleClient: {
    fontSize: 8,
    fontWeight: 'bold',
    color: '#374151',
    textTransform: 'uppercase',
    marginBottom: 5,
    letterSpacing: 0.5,
  },
  partyName: {
    fontSize: 11,
    fontWeight: 'bold',
    marginBottom: 3,
  },
  partyDetail: {
    fontSize: 8,
    color: '#4b5563',
    marginBottom: 2,
  },
  partyDetailSection: {
    marginTop: 5,
    paddingTop: 5,
    borderTop: '1 solid #e9d5ff',
  },
  paymentSection: {
    marginTop: 15,
    marginBottom: 15,
    padding: 10,
    backgroundColor: '#fefcff',
    borderRadius: 5,
    border: '2 solid #e9d5ff',
  },
  paymentTitle: {
    fontSize: 9,
    fontWeight: 'bold',
    color: '#9333ea',
    marginBottom: 8,
    paddingBottom: 5,
    borderBottom: '1 solid #e9d5ff',
  },
  paymentContent: {
    flexDirection: 'row',
    gap: 10,
  },
  paymentDetails: {
    flex: 1,
  },
  dateRow: {
    flexDirection: 'row',
    gap: 8,
    marginBottom: 8,
  },
  dateBox: {
    flex: 1,
    padding: 6,
    backgroundColor: '#fefeff',
    borderRadius: 4,
    border: '1 solid #e9d5ff',
  },
  dateLabel: {
    fontSize: 7,
    color: '#6b7280',
    textTransform: 'uppercase',
    marginBottom: 2,
  },
  dateValue: {
    fontSize: 9,
    fontWeight: 'bold',
    color: '#111827',
  },
  bankDetails: {
    padding: 8,
    backgroundColor: '#ffffff',
    borderRadius: 4,
    border: '1 solid #e9d5ff',
  },
  bankRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingBottom: 4,
    marginBottom: 4,
    borderBottom: '1 solid #f3f4f6',
  },
  bankLabel: {
    fontSize: 7,
    color: '#6b7280',
    textTransform: 'uppercase',
  },
  bankValue: {
    fontSize: 8,
    fontWeight: 'bold',
    color: '#111827',
  },
  totalRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingTop: 4,
  },
  totalLabel: {
    fontSize: 9,
    fontWeight: 'bold',
    color: '#374151',
  },
  totalValue: {
    fontSize: 12,
    fontWeight: 'bold',
    color: '#9333ea',
  },
  qrSection: {
    width: 100,
    alignItems: 'center',
    padding: 8,
    backgroundColor: '#ffffff',
    borderRadius: 8,
    border: '2 solid #c084fc',
  },
  qrCode: {
    width: 80,
    height: 80,
  },
  qrLabel: {
    fontSize: 7,
    fontWeight: 'bold',
    color: '#9333ea',
    marginTop: 5,
  },
  table: {
    marginBottom: 20,
  },
  tableHeader: {
    flexDirection: 'row',
    borderBottom: '2 solid #d1d5db',
    paddingBottom: 8,
    paddingTop: 8,
    fontWeight: 'bold',
  },
  tableRow: {
    flexDirection: 'row',
    borderBottom: '1 solid #e5e7eb',
    paddingTop: 8,
    paddingBottom: 8,
  },
  tableColDescription: {
    flex: 3,
    paddingLeft: 5,
  },
  tableColQuantity: {
    width: 60,
    textAlign: 'right',
    paddingRight: 5,
  },
  tableColPrice: {
    width: 80,
    textAlign: 'right',
    paddingRight: 5,
  },
  tableColTotal: {
    width: 80,
    textAlign: 'right',
    paddingRight: 5,
  },
  totalSection: {
    flexDirection: 'row',
    justifyContent: 'flex-end',
    marginBottom: 20,
  },
  totalBox: {
    width: 250,
    padding: 10,
    backgroundColor: '#faf5ff',
    borderRadius: 5,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  totalBoxLabel: {
    fontSize: 12,
    fontWeight: 'bold',
  },
  totalBoxValue: {
    fontSize: 12,
    fontWeight: 'bold',
    color: '#9333ea',
  },
  footer: {
    marginTop: 15,
    paddingTop: 10,
    borderTop: '1 solid #e5e7eb',
    fontSize: 8,
    color: '#6b7280',
  },
  footerCenter: {
    marginTop: 20,
    paddingTop: 10,
    borderTop: '1 solid #e5e7eb',
    textAlign: 'center',
    fontSize: 7,
    color: '#9ca3af',
  },
});

export const InvoicePDFDocument = ({ invoice }: InvoicePDFDocumentProps) => (
  <Document>
    <Page size="A4" style={styles.page}>
      {(() => {
        const supplier = invoice.party_snapshot.supplier;
        const customer = invoice.party_snapshot.customer;

        return (
          <>
      {/* Header */}
      <View style={styles.header}>
        <View style={styles.headerRow}>
          <Text style={styles.logo}>InvoiceHub</Text>
          <Text style={styles.invoiceTitle}>FAKTÚRA {invoice.invoice_number}</Text>
        </View>

        {/* Supplier and Client */}
        <View style={styles.partiesRow}>
          {/* Supplier */}
          <View style={styles.partyBox}>
            <Text style={styles.partyTitle}>Dodávateľ</Text>
            <Text style={styles.partyName}>{supplier?.name || 'N/A'}</Text>
            <Text style={styles.partyDetail}>{supplier?.street || ''}</Text>
            <Text style={styles.partyDetail}>
              {supplier?.postal_code} {supplier?.city}
            </Text>
            <View style={styles.partyDetailSection}>
              <Text style={styles.partyDetail}>IČO: {supplier?.ico || 'N/A'}</Text>
              <Text style={styles.partyDetail}>DIČ: {supplier?.dic || 'N/A'}</Text>
              {supplier?.ic_dph && (
                <Text style={styles.partyDetail}>IČ DPH: {supplier.ic_dph}</Text>
              )}
            </View>
          </View>

          {/* Client */}
          <View style={styles.partyBoxClient}>
            <Text style={styles.partyTitleClient}>Odberateľ</Text>
            <Text style={styles.partyName}>{customer?.name || 'N/A'}</Text>
            <Text style={styles.partyDetail}>{customer?.street || ''}</Text>
            <Text style={styles.partyDetail}>
              {customer?.postal_code} {customer?.city}
            </Text>
            <View style={styles.partyDetailSection}>
              <Text style={styles.partyDetail}>IČO: {customer?.ico || 'N/A'}</Text>
              <Text style={styles.partyDetail}>DIČ: {customer?.dic || 'N/A'}</Text>
              {customer?.ic_dph && (
                <Text style={styles.partyDetail}>IČ DPH: {customer.ic_dph}</Text>
              )}
            </View>
          </View>
        </View>
      </View>

      {/* Payment Info */}
      <View style={styles.paymentSection}>
        <Text style={styles.paymentTitle}>Platobné údaje</Text>
        <View style={styles.paymentContent}>
          <View style={styles.paymentDetails}>
            {/* Dates */}
            <View style={styles.dateRow}>
              <View style={styles.dateBox}>
                <Text style={styles.dateLabel}>Dátum vystavenia</Text>
                <Text style={styles.dateValue}>
                  {new Date(invoice.issue_date).toLocaleDateString('sk-SK')}
                </Text>
              </View>
              <View style={styles.dateBox}>
                <Text style={styles.dateLabel}>Dátum splatnosti</Text>
                <Text style={styles.dateValue}>
                  {new Date(invoice.due_date).toLocaleDateString('sk-SK')}
                </Text>
              </View>
            </View>

            {/* Bank Details */}
            <View style={styles.bankDetails}>
              {supplier?.bank?.iban && (
                <View style={styles.bankRow}>
                  <Text style={styles.bankLabel}>Číslo účtu</Text>
                  <Text style={styles.bankValue}>{supplier.bank.iban}</Text>
                </View>
              )}
              {invoice.variable_symbol && (
                <View style={styles.bankRow}>
                  <Text style={styles.bankLabel}>Variabilný symbol</Text>
                  <Text style={styles.bankValue}>{invoice.variable_symbol}</Text>
                </View>
              )}
              <View style={styles.totalRow}>
                <Text style={styles.totalLabel}>Suma k úhrade</Text>
                <Text style={styles.totalValue}>
                  {Number(invoice.total_amount).toFixed(2)} {invoice.currency}
                </Text>
              </View>
            </View>
          </View>

          {/* QR Code */}
          {invoice.qr_code && (
            <View style={styles.qrSection}>
              <Image src={invoice.qr_code} style={styles.qrCode} />
              <Text style={styles.qrLabel}>Pay by Square</Text>
            </View>
          )}
        </View>
      </View>

      {/* Items Table */}
      <View style={styles.table}>
        <View style={styles.tableHeader}>
          <Text style={styles.tableColDescription}>Popis</Text>
          <Text style={styles.tableColQuantity}>Počet</Text>
          <Text style={styles.tableColPrice}>Cena/ks</Text>
          <Text style={styles.tableColTotal}>Celkom</Text>
        </View>
        {invoice.items?.map((item, index) => (
          <View key={index} style={styles.tableRow}>
            <Text style={styles.tableColDescription}>{item.description}</Text>
            <Text style={styles.tableColQuantity}>{Number(item.quantity)}</Text>
            <Text style={styles.tableColPrice}>
              {Number(item.unit_price ?? item.unit_price_without_tax ?? 0).toFixed(2)} {invoice.currency}
            </Text>
            <Text style={styles.tableColTotal}>
              {Number(item.total_price).toFixed(2)} {invoice.currency}
            </Text>
          </View>
        ))}
      </View>

      {/* Total */}
      <View style={styles.totalSection}>
        <View style={styles.totalBox}>
          <Text style={styles.totalBoxLabel}>Celkom k úhrade:</Text>
          <Text style={styles.totalBoxValue}>
            {Number(invoice.total_amount).toFixed(2)} {invoice.currency}
          </Text>
        </View>
      </View>

      {/* Footer Note */}
      <View style={styles.footer}>
        <Text>
          Faktúru je potrebné uhradiť do dátumu splatnosti.
          {invoice.notes && ` Poznámka: ${invoice.notes}`}
        </Text>
      </View>

      {/* Footer */}
      <View style={styles.footerCenter}>
        <Text>Ďakujeme za vašu dôveru!</Text>
      </View>
          </>
        );
      })()}
    </Page>
  </Document>
);
