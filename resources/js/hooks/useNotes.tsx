import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { noteService, type NoteCreateData } from '@/services/noteService';

export const useNotes = (noteableType: string, noteableId: number) => {
  const queryClient = useQueryClient();

  const { data, isLoading, error } = useQuery({
    queryKey: ['notes', noteableType, noteableId],
    queryFn: () => noteService.getAll(noteableType, noteableId),
    enabled: !!noteableType && !!noteableId,
  });

  const createMutation = useMutation({
    mutationFn: (data: NoteCreateData) => noteService.create(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['notes', noteableType, noteableId] });
    },
  });

  const deleteMutation = useMutation({
    mutationFn: (id: number) => noteService.delete(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['notes', noteableType, noteableId] });
    },
  });

  return {
    notes: data?.data || [],
    isLoading,
    error,
    createNote: createMutation.mutateAsync,
    deleteNote: deleteMutation.mutateAsync,
    isCreating: createMutation.isPending,
    isDeleting: deleteMutation.isPending,
  };
};
