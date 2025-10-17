import api from '@/lib/axios';
import type { Vehicle, Trip, ApiResponse, PaginatedResponse } from '@/types';

export interface VehicleCreateData {
  name: string;
  license_plate: string;
  brand?: string;
  model?: string;
  year?: number;
  vin?: string;
}

export type VehicleUpdateData = Partial<VehicleCreateData>;

export interface TripCreateData {
  vehicle_id: number;
  start_date: string;
  end_date?: string;
  start_location: string;
  end_location?: string;
  start_odometer: number;
  end_odometer?: number;
  distance?: number;
  purpose: string;
  notes?: string;
}

export type TripUpdateData = Partial<TripCreateData>;

export interface TripFilters {
  vehicle_id?: number;
  user_id?: number;
  date_from?: string;
  date_to?: string;
  page?: number;
  per_page?: number;
}

export const vehicleService = {
  async getAllVehicles(): Promise<ApiResponse<Vehicle[]>> {
    const response = await api.get('/vehiclelogbook/vehicles');
    return response.data;
  },

  async getVehicleById(id: number): Promise<ApiResponse<Vehicle>> {
    const response = await api.get(`/vehiclelogbook/vehicles/${id}`);
    return response.data;
  },

  async createVehicle(data: VehicleCreateData): Promise<ApiResponse<Vehicle>> {
    const response = await api.post('/vehiclelogbook/vehicles', data);
    return response.data;
  },

  async updateVehicle(id: number, data: VehicleUpdateData): Promise<ApiResponse<Vehicle>> {
    const response = await api.put(`/vehiclelogbook/vehicles/${id}`, data);
    return response.data;
  },

  async deleteVehicle(id: number): Promise<void> {
    await api.delete(`/vehiclelogbook/vehicles/${id}`);
  },

  async getAllTrips(filters?: TripFilters): Promise<PaginatedResponse<Trip>> {
    const response = await api.get('/vehiclelogbook/trips', { params: filters });
    return response.data;
  },

  async getTripById(id: number): Promise<ApiResponse<Trip>> {
    const response = await api.get(`/vehiclelogbook/trips/${id}`);
    return response.data;
  },

  async createTrip(data: TripCreateData): Promise<ApiResponse<Trip>> {
    const response = await api.post('/vehiclelogbook/trips', data);
    return response.data;
  },

  async updateTrip(id: number, data: TripUpdateData): Promise<ApiResponse<Trip>> {
    const response = await api.put(`/vehiclelogbook/trips/${id}`, data);
    return response.data;
  },

  async deleteTrip(id: number): Promise<void> {
    await api.delete(`/vehiclelogbook/trips/${id}`);
  },
};

