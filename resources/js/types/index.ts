export interface User {
  id: number;
  first_name: string;
  last_name: string;
  email: string;
  email_verified_at: string | null;
  current_company_id: number | null;
  created_at: string;
  updated_at: string;
}

export interface Company {
  id: number;
  name: string;
  ico: string;
  dic: string | null;
  ic_dph: string | null;
  street: string | null;
  address: string;
  city: string;
  postal_code: string;
  country: string;

  // Banking information (§ 74 ods. 1)
  iban: string | null;
  swift: string | null;
  bank_name: string | null;

  // Contact information
  phone: string | null;
  email: string | null;
  website: string | null;

  // Company details
  company_type: string | null; // s.r.o., a.s., živnosť, etc.
  registration_office: string | null;
  registration_number: string | null;

  // Legacy
  bank_account: string | null;

  created_at: string;
  updated_at: string;
}

export interface UserCompany {
  id: number;
  name: string;
  ico: string;
  dic: string | null;
  ic_dph: string | null;
  vat_payer_status: 'not_vat_payer' | 'vat_payer' | 'vat_payer_paragraph_7' | null;
  vat_payer_status_label?: string;
  street: string;
  address: string; // Formatted: "street, postal_code city"
  city: string;
  postal_code: string;
  country: string;

  // Banking information (§ 74 ods. 1)
  iban: string | null;
  swift: string | null;

  // Contact information
  phone: string | null;
  email: string | null;
  website: string | null;

  // Company details
  company_type: string; // živnosť or s.r.o.
  registration_number: string; // Registration number in business or trade register

  status: 'active' | 'inactive';
  vehicles: number;
  clients: number;
  created_at: string;
  updated_at: string;
}

export interface BusinessEntity {
  id: number;
  company_id: number;
  name: string;
  ico: string;
  dic: string | null;
  ic_dph: string | null;
  address: string;
  city: string;
  postal_code: string;
  country: string;
  phone: string | null;
  email: string | null;
  created_at: string;
  updated_at: string;
}

export interface Invoice {
  id: number;
  company_id: number;
  business_entity_id: number;
  invoice_number: string;
  issue_date: string;
  due_date: string;
  delivery_date: string;
  variable_symbol: string | null;
  constant_symbol: string | null;
  specific_symbol: string | null;

  // VAT and totals (§ 74 ods. 1 Slovak invoice compliance)
  subtotal: number; // Total without VAT
  tax_amount: number; // Total VAT amount
  tax_rate: number; // Default VAT rate (20%, 10%, 0%)
  total_amount: number; // Total with VAT
  discount_amount?: number | null;
  discount_percentage?: number | null;
  reverse_charge_text?: string | null; // For reverse charge invoices
  tax_exemption_text?: string | null; // For tax-exempt invoices

  // Legacy fields (for backwards compatibility)
  total_amount_without_vat?: number;
  vat_amount?: number;

  currency: string;
  notes: string | null;
  status: 'draft' | 'sent' | 'paid' | 'overdue' | 'cancelled';
  created_at: string;
  updated_at: string;

  // Snapshot fields for customer company (stored at invoice creation time)
  company_ico?: string;
  company_dic?: string | null;
  company_ic_dph?: string | null;
  company_name?: string;
  company_address?: string;
  company_city?: string;
  company_zip?: string;
  company_country?: string;

  // Relationships
  business_entity?: BusinessEntity;
  supplier_company?: UserCompany;
  items?: InvoiceItem[];
  qr_code?: string;

  // Supplier VAT payer status for conditional display
  supplier_vat_payer_status?: 'not_vat_payer' | 'vat_payer' | 'vat_payer_paragraph_7' | null;
  supplier_is_vat_payer?: boolean;
}

export interface InvoiceItem {
  id: number;
  invoice_id: number;
  description: string;
  quantity: number;

  // VAT fields (§ 74 ods. 1 Slovak invoice compliance)
  unit_price_without_tax: number; // Price per unit without VAT
  tax_rate: number; // VAT rate for this item (20%, 10%, 0%)
  tax_amount: number; // Calculated VAT amount for this item
  subtotal: number; // quantity * unit_price_without_tax
  discount_amount?: number | null; // Optional discount
  total_price: number; // subtotal + tax_amount

  // Legacy field (for backwards compatibility)
  unit_price?: number;

  created_at: string;
  updated_at: string;
}

export interface Attendance {
  id: number;
  user_id: number;
  check_in: string;
  check_out: string | null;
  work_type: 'office' | 'remote' | 'hybrid' | 'field';
  status: 'pending' | 'approved' | 'rejected';
  notes: string | null;
  approved_by: number | null;
  approved_at: string | null;
  created_at: string;
  updated_at: string;
  user?: User;
  breaks?: AttendanceBreak[];
  total_work_hours?: number;
  total_break_hours?: number;
}

export interface AttendanceBreak {
  id: number;
  attendance_id: number;
  break_type: 'lunch' | 'short' | 'other';
  start_time: string;
  end_time: string | null;
  created_at: string;
  updated_at: string;
}

export interface Note {
  id: number;
  user_id: number;
  company_id: number;
  noteable_type: string;
  noteable_id: number;
  body: string;
  created_at: string;
  updated_at: string;
  user?: User;
}

export interface SimpleContact {
  id: number;
  user_id: number;
  company_id: number;
  first_name: string | null;
  last_name: string | null;
  email: string | null;
  phone: string | null;
  position: string | null;
  notes: string | null;
  created_at: string;
  updated_at: string;
}

export interface Contact {
  id: number;
  company_id: number;
  first_name: string;
  last_name: string;
  email: string | null;
  status: 'active' | 'inactive' | 'lead' | 'customer' | 'archived';
  notes: string | null;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
  full_name?: string;
  phones?: ContactPhone[];
  emails?: ContactEmail[];
  addresses?: ContactAddress[];
  tags?: ContactTag[];
  activities?: ContactActivity[];
}

export interface ContactPhone {
  id: number;
  contact_id: number;
  phone: string;
  type: 'mobile' | 'work' | 'home' | 'other';
  is_primary: boolean;
  created_at: string;
  updated_at: string;
}

export interface ContactEmail {
  id: number;
  contact_id: number;
  email: string;
  type: 'work' | 'personal' | 'other';
  is_primary: boolean;
  created_at: string;
  updated_at: string;
}

export interface ContactAddress {
  id: number;
  contact_id: number;
  address: string;
  city: string;
  postal_code: string;
  country: string;
  type: 'home' | 'work' | 'other';
  is_primary: boolean;
  created_at: string;
  updated_at: string;
}

export interface ContactTag {
  id: number;
  name: string;
  color: string | null;
  created_at: string;
  updated_at: string;
}

export interface ContactActivity {
  id: number;
  contact_id: number;
  type: string;
  description: string;
  created_by: number;
  created_at: string;
  updated_at: string;
}

export interface Task {
  id: number;
  company_id: number;
  title: string;
  description: string | null;
  status: 'todo' | 'in_progress' | 'completed' | 'cancelled';
  priority: 'low' | 'medium' | 'high' | 'urgent';
  due_date: string | null;
  assigned_to: number | null;
  created_by: number;
  contact_id: number | null;
  created_at: string;
  updated_at: string;
  assigned_user?: User;
  created_user?: User;
  contact?: Contact;
  follow_ups?: FollowUp[];
}

export interface FollowUp {
  id: number;
  task_id: number;
  description: string;
  scheduled_at: string;
  completed_at: string | null;
  created_by: number;
  created_at: string;
  updated_at: string;
}

export interface Vehicle {
  id: number;
  company_id: number;
  name: string;
  license_plate: string;
  brand: string | null;
  model: string | null;
  year: number | null;
  vin: string | null;
  created_at: string;
  updated_at: string;
}

export interface Trip {
  id: number;
  vehicle_id: number;
  user_id: number;
  start_date: string;
  end_date: string | null;
  start_location: string;
  end_location: string | null;
  start_odometer: number;
  end_odometer: number | null;
  distance: number | null;
  purpose: string;
  notes: string | null;
  created_at: string;
  updated_at: string;
  vehicle?: Vehicle;
  user?: User;
}

export interface PaginatedResponse<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
}

export interface ApiResponse<T> {
  data: T;
  message?: string;
}

export interface UserCompanyFormData {
  name: string;
  ico: string;
  dic: string;
  ic_dph?: string;
  email: string;
  phone?: string;
  street: string;
  city: string;
  postal_code: string;
  country: string;
  iban?: string;
  swift?: string;
  status?: 'active' | 'inactive';
}

export interface CompanyStats {
  total: number;
}

export interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
}

export interface AxiosErrorResponse {
  response?: {
    status?: number;
    data?: ApiError;
  };
  message?: string;
}

