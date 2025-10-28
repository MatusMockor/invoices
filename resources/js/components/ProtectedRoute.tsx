import { Navigate, useLocation } from "react-router-dom";
import { useAuthContext } from "@/contexts/AuthContext";
import { authService } from "@/services/authService";

interface ProtectedRouteProps {
  children: React.ReactNode;
  requireCompany?: boolean;
}

export const ProtectedRoute = ({ children, requireCompany = true }: ProtectedRouteProps) => {
  const { user, isLoading } = useAuthContext();
  const hasToken = !!authService.getToken();
  const location = useLocation();

  // If loading and no token yet, show spinner briefly
  // This handles the edge case during login flow
  if (isLoading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary"></div>
      </div>
    );
  }

  // No token - redirect to login
  if (!hasToken) {
    return <Navigate to="/login" replace />;
  }

  // If company is required and user doesn't have one, redirect to onboarding
  // But don't redirect if we're already on the onboarding page
  if (requireCompany && user && !user.current_company_id && location.pathname !== '/app/onboarding') {
    return <Navigate to="/app/onboarding" replace />;
  }

  // All checks passed - render children
  return <>{children}</>;
};

