import api from '@/lib/axios';
import type { Attendance, AttendanceBreak, ApiResponse, PaginatedResponse } from '@/types';

export interface AttendanceFilters {
  user_id?: number;
  status?: 'pending' | 'approved' | 'rejected';
  work_type?: 'office' | 'remote' | 'hybrid' | 'field';
  date_from?: string;
  date_to?: string;
  page?: number;
  per_page?: number;
}

export interface CheckInData {
  work_type: 'office' | 'remote' | 'hybrid' | 'field';
  notes?: string;
}

export interface CheckOutData {
  notes?: string;
}

export interface StartBreakData {
  break_type: 'lunch' | 'short' | 'other';
}

export interface AttendanceUpdateData {
  check_in?: string;
  check_out?: string;
  work_type?: 'office' | 'remote' | 'hybrid' | 'field';
  notes?: string;
}

export interface MonthlyReportParams {
  user_id?: number;
  year: number;
  month: number;
}

export const attendanceService = {
  async getAll(filters?: AttendanceFilters): Promise<PaginatedResponse<Attendance>> {
    const response = await api.get('/attendances', { params: filters });
    return response.data;
  },

  async getById(id: number): Promise<ApiResponse<Attendance>> {
    const response = await api.get(`/attendances/${id}`);
    return response.data;
  },

  async getToday(): Promise<ApiResponse<Attendance | null>> {
    const response = await api.get('/attendances/today');
    return response.data;
  },

  async checkIn(data: CheckInData): Promise<ApiResponse<Attendance>> {
    const response = await api.post('/attendances/check-in', data);
    return response.data;
  },

  async checkOut(id: number, data?: CheckOutData): Promise<ApiResponse<Attendance>> {
    const response = await api.post(`/attendances/${id}/check-out`, data);
    return response.data;
  },

  async startBreak(id: number, data: StartBreakData): Promise<ApiResponse<AttendanceBreak>> {
    const response = await api.post(`/attendances/${id}/start-break`, data);
    return response.data;
  },

  async endBreak(breakId: number): Promise<ApiResponse<AttendanceBreak>> {
    const response = await api.post(`/attendances/breaks/${breakId}/end`);
    return response.data;
  },

  async update(id: number, data: AttendanceUpdateData): Promise<ApiResponse<Attendance>> {
    const response = await api.put(`/attendances/${id}`, data);
    return response.data;
  },

  async approve(id: number): Promise<ApiResponse<Attendance>> {
    const response = await api.post(`/attendances/${id}/approve`);
    return response.data;
  },

  async reject(id: number): Promise<ApiResponse<Attendance>> {
    const response = await api.post(`/attendances/${id}/reject`);
    return response.data;
  },

  async delete(id: number): Promise<void> {
    await api.delete(`/attendances/${id}`);
  },

  async getMonthlyReport(params: MonthlyReportParams): Promise<ApiResponse<any>> {
    const response = await api.get('/attendances/monthly-report', { params });
    return response.data;
  },

  async getOptions(): Promise<ApiResponse<any>> {
    const response = await api.get('/attendances/options');
    return response.data;
  },
};

