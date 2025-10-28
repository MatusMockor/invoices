import api from '@/lib/axios';
import type { Task, FollowUp, ApiResponse, PaginatedResponse } from '@/types';

export interface TaskFilters {
  status?: 'todo' | 'in_progress' | 'completed' | 'cancelled';
  priority?: 'low' | 'medium' | 'high' | 'urgent';
  assigned_to?: number;
  contact_id?: number;
  date_from?: string;
  date_to?: string;
  search?: string;
  page?: number;
  per_page?: number;
}

export interface TaskCreateData {
  title: string;
  description?: string;
  status: 'todo' | 'in_progress' | 'completed' | 'cancelled';
  priority: 'low' | 'medium' | 'high' | 'urgent';
  due_date?: string;
  assigned_to?: number;
  contact_id?: number;
}

export type TaskUpdateData = Partial<TaskCreateData>;

export interface AssignTaskData {
  assigned_to: number;
}

export interface FollowUpCreateData {
  description: string;
  scheduled_at: string;
}

export interface CalendarParams {
  start_date: string;
  end_date: string;
}

export const taskService = {
  async getAll(filters?: TaskFilters): Promise<PaginatedResponse<Task>> {
    const response = await api.get('/tasks', { params: filters });
    return response.data;
  },

  async getCalendar(params: CalendarParams): Promise<ApiResponse<Task[]>> {
    const response = await api.get('/tasks/calendar', { params });
    return response.data;
  },

  async getById(id: number): Promise<ApiResponse<Task>> {
    const response = await api.get(`/tasks/${id}`);
    return response.data;
  },

  async create(data: TaskCreateData): Promise<ApiResponse<Task>> {
    const response = await api.post('/tasks', data);
    return response.data;
  },

  async update(id: number, data: TaskUpdateData): Promise<ApiResponse<Task>> {
    const response = await api.put(`/tasks/${id}`, data);
    return response.data;
  },

  async delete(id: number): Promise<void> {
    await api.delete(`/tasks/${id}`);
  },

  async markAsCompleted(id: number): Promise<ApiResponse<Task>> {
    const response = await api.post(`/tasks/${id}/complete`);
    return response.data;
  },

  async markAsInProgress(id: number): Promise<ApiResponse<Task>> {
    const response = await api.post(`/tasks/${id}/in-progress`);
    return response.data;
  },

  async markAsCancelled(id: number): Promise<ApiResponse<Task>> {
    const response = await api.post(`/tasks/${id}/cancel`);
    return response.data;
  },

  async assign(id: number, data: AssignTaskData): Promise<ApiResponse<Task>> {
    const response = await api.post(`/tasks/${id}/assign`, data);
    return response.data;
  },

  async addFollowUp(id: number, data: FollowUpCreateData): Promise<ApiResponse<FollowUp>> {
    const response = await api.post(`/tasks/${id}/follow-ups`, data);
    return response.data;
  },
};

