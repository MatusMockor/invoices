import { Navigate, useLocation } from "react-router-dom";
import { useAuthContext } from "@/contexts/AuthContext";
import { authService } from "@/services/authService";

interface ProtectedRouteProps {
  children: React.ReactNode;
  requireCompany?: boolean;
}

export const ProtectedRoute = ({ children, requireCompany = true }: ProtectedRouteProps) => {
  const { user, isLoading, isError, refetch } = useAuthContext();
  const hasToken = !!authService.getToken();
  const location = useLocation();

  // No token - redirect to login immediately (don't wait for loading)
  if (!hasToken) {
    return <Navigate to="/login" replace />;
  }

  // Only show loading spinner if we have a token and are loading user data
  if (isLoading && hasToken) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary"></div>
      </div>
    );
  }

  // If we have token but no user after loading - check if error occurred
  if (!isLoading && hasToken && !user) {
    // If there was an error (all retries failed), show error message with retry option
    if (isError) {
      return (
        <div className="min-h-screen flex items-center justify-center p-4">
          <div className="text-center space-y-6 max-w-md">
            <div className="p-4 bg-destructive/10 rounded-lg border border-destructive/20">
              <p className="text-lg font-semibold text-destructive mb-2">
                Nepodarilo sa načítať používateľské údaje
              </p>
              <p className="text-sm text-muted-foreground">
                Skontrolujte prosím internetové pripojenie a skúste to znovu.
              </p>
            </div>
            <div className="space-y-3">
              <button
                onClick={() => refetch()}
                className="w-full px-6 py-3 bg-primary text-primary-foreground rounded-lg hover:opacity-90 transition-opacity font-medium"
              >
                Skúsiť znovu
              </button>
              <button
                onClick={() => {
                  authService.removeToken();
                  window.location.href = '/login';
                }}
                className="w-full px-6 py-3 bg-secondary text-secondary-foreground rounded-lg hover:opacity-90 transition-opacity"
              >
                Odhlásiť sa
              </button>
            </div>
          </div>
        </div>
      );
    }

    // No error flag but still no user - shouldn't happen but show loading as fallback
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary"></div>
      </div>
    );
  }

  // If company is required and user doesn't have one, redirect to onboarding
  if (requireCompany && user && !user.current_company_id) {
    return <Navigate to="/app/onboarding" replace />;
  }

  // All checks passed - render children
  return <>{children}</>;
};

