import api from '@/lib/axios';
import type { Note, ApiResponse } from '@/types';

export interface NoteCreateData {
  noteable_type: string;
  noteable_id: number;
  body: string;
}

export const noteService = {
  async getAll(noteableType: string, noteableId: number): Promise<ApiResponse<Note[]>> {
    const response = await api.get('/notes', {
      params: {
        noteable_type: noteableType,
        noteable_id: noteableId,
      },
    });
    return response.data;
  },

  async create(data: NoteCreateData): Promise<ApiResponse<Note>> {
    const response = await api.post('/notes', data);
    return response.data;
  },

  async delete(id: number): Promise<void> {
    await api.delete(`/notes/${id}`);
  },
};
