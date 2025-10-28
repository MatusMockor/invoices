import { Link, useNavigate } from "react-router-dom";
import { useEffect } from "react";
import { Card } from "@/components/ui/card";
import { ArrowLeft } from "lucide-react";
import { toast } from "sonner";
import { useAuthContext } from "@/contexts/AuthContext";
import { UserRegistrationStep, type UserFormData } from "@/components/auth/UserRegistrationStep";

const Register = () => {
  const { register: registerUser, isAuthenticated } = useAuthContext();
  const navigate = useNavigate();

  // Redirect to dashboard if already authenticated
  useEffect(() => {
    if (isAuthenticated) {
      navigate("/app/dashboard", { replace: true });
    }
  }, [isAuthenticated, navigate]);

  const handleUserSubmit = async (data: UserFormData) => {
    try {
      await registerUser({
        name: data.name,
        email: data.email,
        password: data.password,
        password_confirmation: data.confirmPassword,
      });

      toast.success("Registrácia úspešná!");
      navigate("/app/onboarding");
    } catch (error: any) {
      toast.error(error.response?.data?.message || "Registrácia zlyhala");
    }
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-background via-background to-secondary/20 flex items-center justify-center p-4">
      <div className="w-full max-w-2xl">
        <Link to="/" className="inline-flex items-center gap-2 text-muted-foreground hover:text-foreground mb-8 transition-colors">
          <ArrowLeft className="h-4 w-4" />
          <span>Späť na hlavnú stránku</span>
        </Link>

        <Card className="shadow-xl border-border/50 bg-card/95 backdrop-blur animate-scale-in">
          <UserRegistrationStep onSubmit={handleUserSubmit} />
        </Card>
      </div>
    </div>
  );
};

export default Register;
