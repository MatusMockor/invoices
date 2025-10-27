import { Link, useNavigate } from "react-router-dom";
import { useState, useEffect } from "react";
import { Card } from "@/components/ui/card";
import { ArrowLeft } from "lucide-react";
import { toast } from "sonner";
import { useAuthContext } from "@/contexts/AuthContext";
import { UserRegistrationStep, type UserFormData } from "@/components/auth/UserRegistrationStep";
import { CompanyRegistrationStep } from "@/components/auth/CompanyRegistrationStep";

const Register = () => {
  const [step, setStep] = useState<"user" | "company">("user");
  const [userData, setUserData] = useState<UserFormData | null>(null);
  const { isAuthenticated } = useAuthContext();
  const navigate = useNavigate();

  // Redirect to dashboard if already authenticated
  useEffect(() => {
    if (isAuthenticated) {
      navigate("/app/dashboard", { replace: true });
    }
  }, [isAuthenticated, navigate]);

  const handleUserSubmit = (data: UserFormData) => {
    setUserData(data);
    toast.success("Teraz zadajte údaje o firme");
    setStep("company");
  };

  const handleBack = () => {
    setStep("user");
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-background via-background to-secondary/20 flex items-center justify-center p-4">
      <div className="w-full max-w-2xl">
        <Link to="/" className="inline-flex items-center gap-2 text-muted-foreground hover:text-foreground mb-8 transition-colors">
          <ArrowLeft className="h-4 w-4" />
          <span>Späť na hlavnú stránku</span>
        </Link>

        <Card className="shadow-xl border-border/50 bg-card/95 backdrop-blur animate-scale-in">
          {step === "user" ? (
            <UserRegistrationStep onSubmit={handleUserSubmit} />
          ) : (
            <CompanyRegistrationStep userData={userData!} onBack={handleBack} />
          )}
        </Card>
      </div>
    </div>
  );
};

export default Register;
