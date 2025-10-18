import { Toaster } from "@/components/ui/toaster";
import { Toaster as Sonner } from "@/components/ui/sonner";
import { TooltipProvider } from "@/components/ui/tooltip";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { BrowserRouter, Routes, Route } from "react-router-dom";
import { ThemeProvider } from "@/components/ThemeProvider";
import { AuthProvider } from "@/contexts/AuthContext";
import { CompanyProvider } from "@/contexts/CompanyContext";
import { ProtectedRoute } from "@/components/ProtectedRoute";
import Index from "./pages/Index";
import Login from "./pages/Login";
import Register from "./pages/Register";
import Dashboard from "./pages/Dashboard";
import Invoices from "./pages/Invoices";
import NewInvoice from "./pages/NewInvoice";
import Clients from "./pages/Clients";
import Reports from "./pages/Reports";
import Attendance from "./pages/Attendance";
import VehicleLog from "./pages/VehicleLog";
import Vehicles from "./pages/Vehicles";
import Settings from "./pages/Settings";
import NotFound from "./pages/NotFound";

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      refetchOnWindowFocus: false,
      retry: 1,
    },
  },
});

const App = () => (
  <QueryClientProvider client={queryClient}>
    <ThemeProvider attribute="class" defaultTheme="system" enableSystem>
      <AuthProvider>
        <CompanyProvider>
          <TooltipProvider>
            <Toaster />
            <Sonner />
            <BrowserRouter
              future={{
                v7_startTransition: true,
                v7_relativeSplatPath: true,
              }}
            >
          <Routes>
            {/* Public routes */}
            <Route path="/" element={<Index />} />
            <Route path="/login" element={<Login />} />
            <Route path="/register" element={<Register />} />

            {/* Protected routes - require authentication */}
            <Route path="/app/dashboard" element={<ProtectedRoute><Dashboard /></ProtectedRoute>} />
            <Route path="/app/invoices" element={<ProtectedRoute><Invoices /></ProtectedRoute>} />
            <Route path="/app/invoices/new" element={<ProtectedRoute><NewInvoice /></ProtectedRoute>} />
            <Route path="/app/invoices/edit/:id" element={<ProtectedRoute><NewInvoice /></ProtectedRoute>} />
            <Route path="/app/clients" element={<ProtectedRoute><Clients /></ProtectedRoute>} />
            <Route path="/app/reports" element={<ProtectedRoute><Reports /></ProtectedRoute>} />
            <Route path="/app/attendance" element={<ProtectedRoute><Attendance /></ProtectedRoute>} />
            <Route path="/app/vehicle-log" element={<ProtectedRoute><VehicleLog /></ProtectedRoute>} />
            <Route path="/app/vehicles" element={<ProtectedRoute><Vehicles /></ProtectedRoute>} />
            <Route path="/app/settings" element={<ProtectedRoute><Settings /></ProtectedRoute>} />

            {/* ADD ALL CUSTOM ROUTES ABOVE THE CATCH-ALL "*" ROUTE */}
            <Route path="*" element={<NotFound />} />
            </Routes>
          </BrowserRouter>
          </TooltipProvider>
        </CompanyProvider>
      </AuthProvider>
    </ThemeProvider>
  </QueryClientProvider>
);

export default App;

