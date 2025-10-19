import { Navigate } from "react-router-dom";
import { useAuthContext } from "@/contexts/AuthContext";
import { authService } from "@/services/authService";

interface ProtectedRouteProps {
  children: React.ReactNode;
}

export const ProtectedRoute = ({ children }: ProtectedRouteProps) => {
  const { isAuthenticated, isLoading } = useAuthContext();
  const hasToken = !!authService.getToken();

  console.log('[ProtectedRoute]', { hasToken, isAuthenticated, isLoading });

  // If we have a token, allow access immediately
  // The token will be validated on API calls, and user will be logged out if invalid
  if (hasToken) {
    console.log('[ProtectedRoute] Has token - allowing access');
    return <>{children}</>;
  }

  // If loading and no token yet, show spinner briefly
  // This handles the edge case during login flow
  if (isLoading) {
    console.log('[ProtectedRoute] Loading - showing spinner');
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary"></div>
      </div>
    );
  }

  // No token and not authenticated - redirect to login
  console.log('[ProtectedRoute] No token - redirecting to login');
  return <Navigate to="/login" replace />;
};

