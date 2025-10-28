# API Integration Documentation

Tento dokument popisuje, ako sú integrované API endpointy v React SPA aplikácii.

## Štruktúra

```
resources/js/
├── lib/
│   └── axios.ts              # Axios konfigurácia s CSRF tokenom
├── types/
│   └── index.ts              # TypeScript typy pre všetky entity
├── services/                 # API service layer
│   ├── authService.ts        # Autentifikácia
│   ├── companyService.ts     # Správa firiem
│   ├── invoiceService.ts     # Faktúry
│   ├── businessEntityService.ts  # Obchodné entity
│   ├── attendanceService.ts  # Dochádzka
│   ├── contactService.ts     # CRM kontakty
│   ├── taskService.ts        # Task management
│   ├── vehicleService.ts     # Kniha jázd
│   └── index.ts              # Centrálny export
├── hooks/                    # React hooks pre API
│   ├── useAuth.tsx
│   ├── useInvoices.tsx
│   └── useCompanies.tsx
└── contexts/
    └── AuthContext.tsx       # Auth context provider
```

## Axios Konfigurácia

Axios je nakonfigurovaný s:
- Base URL: `/api`
- CSRF token automaticky pridávaný z meta tagu
- Credentials (withCredentials): true
- Automatic redirect na /login pri 401 chybe

```typescript
import api from '@/lib/axios';
```

## API Services

Každý modul má svoj vlastný service súbor s metódami pre CRUD operácie.

### Auth Service

```typescript
import { authService } from '@/services';

// Login
await authService.login({ email, password });

// Register
await authService.register({ name, email, password, password_confirmation });

// Logout
await authService.logout();

// Get current user
const user = await authService.getCurrentUser();
```

### Invoice Service

```typescript
import { invoiceService } from '@/services';

// Get all invoices
const invoices = await invoiceService.getAll({ status: 'paid' });

// Create invoice
await invoiceService.create({
  business_entity_id: 1,
  invoice_number: 'INV-001',
  issue_date: '2025-10-17',
  due_date: '2025-11-17',
  currency: 'EUR',
  items: [...]
});

// Download PDF
const blob = await invoiceService.downloadPdf(1);
```

### Company Service

```typescript
import { companyService } from '@/services';

// Get all companies
const companies = await companyService.getAll();

// Switch company
await companyService.switchCompany(1);
```

### Attendance Service

```typescript
import { attendanceService } from '@/services';

// Check in
await attendanceService.checkIn({
  work_type: 'office',
  notes: 'Test'
});

// Get today's attendance
const today = await attendanceService.getToday();

// Start break
await attendanceService.startBreak(attendanceId, {
  break_type: 'lunch'
});
```

### Contact Service (CRM)

```typescript
import { contactService } from '@/services';

// Get all contacts
const contacts = await contactService.getAll({
  status: 'active',
  search: 'John'
});

// Create contact
await contactService.create({
  first_name: 'John',
  last_name: 'Doe',
  email: 'john@example.com',
  status: 'active'
});

// Import contacts
await contactService.import(file);
```

### Task Service

```typescript
import { taskService } from '@/services';

// Get all tasks
const tasks = await taskService.getAll({
  status: 'in_progress',
  priority: 'high'
});

// Mark as completed
await taskService.markAsCompleted(taskId);

// Add follow-up
await taskService.addFollowUp(taskId, {
  description: 'Follow up call',
  scheduled_at: '2025-10-20'
});
```

### Vehicle Service

```typescript
import { vehicleService } from '@/services';

// Get all vehicles
const vehicles = await vehicleService.getAllVehicles();

// Create trip
await vehicleService.createTrip({
  vehicle_id: 1,
  start_date: '2025-10-17',
  start_location: 'Bratislava',
  start_odometer: 50000,
  purpose: 'Business meeting'
});
```

## React Hooks

Pre jednoduchšiu integráciu s React Query sú vytvorené custom hooks.

### useAuth

```typescript
import { useAuthContext } from '@/contexts/AuthContext';

const { user, isAuthenticated, login, logout, isLoggingIn } = useAuthContext();
```

### useInvoices

```typescript
import { useInvoices } from '@/hooks/useInvoices';

const {
  invoices,
  isLoading,
  createInvoice,
  updateInvoice,
  deleteInvoice,
  downloadPdf
} = useInvoices({ status: 'paid' });
```

### useCompanies

```typescript
import { useCompanies } from '@/hooks/useCompanies';

const {
  companies,
  isLoading,
  createCompany,
  switchCompany
} = useCompanies();
```

## TypeScript Typy

Všetky entity majú definované TypeScript typy v `types/index.ts`:

- `User`
- `Company`
- `BusinessEntity`
- `Invoice` & `InvoiceItem`
- `Attendance` & `AttendanceBreak`
- `Contact` & related types
- `Task` & `FollowUp`
- `Vehicle` & `Trip`
- `PaginatedResponse<T>`
- `ApiResponse<T>`

## Používanie v Komponentách

```typescript
import { useInvoices } from '@/hooks/useInvoices';
import { toast } from 'sonner';

const InvoicesPage = () => {
  const { invoices, isLoading, downloadPdf } = useInvoices();

  const handleDownload = async (id: number) => {
    try {
      await downloadPdf(id);
      toast.success('PDF stiahnuté!');
    } catch (error) {
      toast.error('Chyba pri sťahovaní PDF');
    }
  };

  if (isLoading) return <div>Loading...</div>;

  return (
    <div>
      {invoices.map((invoice) => (
        <div key={invoice.id}>
          <h3>{invoice.invoice_number}</h3>
          <button onClick={() => handleDownload(invoice.id)}>
            Download PDF
          </button>
        </div>
      ))}
    </div>
  );
};
```

## Laravel API Routes

### Main API Routes (`routes/api.php`)
- `POST /api/login` - Login
- `POST /api/register` - Register
- `POST /api/logout` - Logout
- `GET /api/user` - Get current user
- `GET /api/companies` - Get companies

### Module Routes

#### Attendance (`/api/attendances/`)
- `GET /` - List attendances
- `GET /today` - Today's attendance
- `POST /check-in` - Check in
- `POST /{id}/check-out` - Check out
- `POST /{id}/start-break` - Start break
- `POST /breaks/{id}/end` - End break
- `GET /monthly-report` - Monthly report

#### CRM (`/api/contacts/`)
- `GET /` - List contacts
- `POST /` - Create contact
- `GET /{id}` - Get contact
- `PUT /{id}` - Update contact
- `DELETE /{id}` - Delete contact
- `POST /bulk-update` - Bulk update
- `POST /import` - Import contacts
- `POST /export` - Export contacts

#### Tasks (`/api/tasks/`)
- `GET /` - List tasks
- `GET /calendar` - Calendar view
- `POST /` - Create task
- `PUT /{id}` - Update task
- `POST /{id}/complete` - Mark as completed
- `POST /{id}/assign` - Assign task
- `POST /{id}/follow-ups` - Add follow-up

#### Vehicle Logbook (`/api/vehiclelogbook/`)
- `GET /vehicles` - List vehicles
- `POST /vehicles` - Create vehicle
- `GET /trips` - List trips
- `POST /trips` - Create trip

## CSRF Protection

Aplikácia automaticky získa CSRF cookie a pridá token do všetkých requestov:

```typescript
// Axios automaticky pridá CSRF token z meta tagu
const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
```

Uistite sa, že máte CSRF token v blade šablóne:
```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```

## Error Handling

Všetky errory sú automaticky spracované:
- 401 → redirect na /login
- Ostatné errory → toast notifikácia

```typescript
try {
  await someService.action();
  toast.success('Úspešne!');
} catch (error: any) {
  toast.error(error.response?.data?.message || 'Nastala chyba');
}
```

