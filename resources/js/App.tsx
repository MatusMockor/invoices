import { Toaster } from "@/components/ui/toaster";
import { Toaster as Sonner } from "@/components/ui/sonner";
import { TooltipProvider } from "@/components/ui/tooltip";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { BrowserRouter, Routes, Route } from "react-router-dom";
import { ThemeProvider } from "@/components/ThemeProvider";
import { AuthProvider } from "@/contexts/AuthContext";
import { CompanyProvider } from "@/contexts/CompanyContext";
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
            <Route path="/" element={<Index />} />
            <Route path="/login" element={<Login />} />
            <Route path="/register" element={<Register />} />
            <Route path="/app/dashboard" element={<Dashboard />} />
            <Route path="/app/invoices" element={<Invoices />} />
            <Route path="/app/invoices/new" element={<NewInvoice />} />
            <Route path="/app/invoices/edit/:id" element={<NewInvoice />} />
            <Route path="/app/clients" element={<Clients />} />
            <Route path="/app/reports" element={<Reports />} />
            <Route path="/app/attendance" element={<Attendance />} />
            <Route path="/app/vehicle-log" element={<VehicleLog />} />
            <Route path="/app/vehicles" element={<Vehicles />} />
            <Route path="/app/settings" element={<Settings />} />
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

