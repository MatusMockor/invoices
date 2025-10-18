import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { profileService, type ProfileUpdateData, type ProfileDeleteData } from '@/services/profileService';

export const useProfile = () => {
  const queryClient = useQueryClient();

  const { data, isLoading, error } = useQuery({
    queryKey: ['profile'],
    queryFn: () => profileService.get(),
  });

  const updateMutation = useMutation({
    mutationFn: (data: ProfileUpdateData) => profileService.update(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['profile'] });
      queryClient.invalidateQueries({ queryKey: ['user'] });
    },
  });

  const deleteMutation = useMutation({
    mutationFn: (data: ProfileDeleteData) => profileService.delete(data),
  });

  return {
    profile: data?.data,
    isLoading,
    error,
    updateProfile: updateMutation.mutateAsync,
    deleteProfile: deleteMutation.mutateAsync,
    isUpdating: updateMutation.isPending,
    isDeleting: deleteMutation.isPending,
  };
};
