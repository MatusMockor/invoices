import { Link, useNavigate } from "react-router-dom";
import { useState, useEffect } from "react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { ArrowLeft } from "lucide-react";
import { useAuthContext } from "@/contexts/AuthContext";
import { toast } from "sonner";

const Login = () => {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const { login, isLoggingIn, isAuthenticated, user } = useAuthContext();
  const navigate = useNavigate();

  // Redirect authenticated users to appropriate page
  useEffect(() => {
    if (isAuthenticated && user) {
      // If user has company, go to dashboard, otherwise go to onboarding
      if (user.current_company_id) {
        navigate("/app/dashboard", { replace: true });
      } else {
        navigate("/app/onboarding", { replace: true });
      }
    }
  }, [isAuthenticated, user, navigate]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    try {
      await login({ email, password });
      toast.success("Úspešne prihlásený!");
      // Navigation is handled by useEffect after user data is loaded
    } catch (error: any) {
      toast.error(error.response?.data?.message || "Nesprávny email alebo heslo");
    }
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-background via-background to-secondary/20 flex items-center justify-center p-4">
      <div className="w-full max-w-md">
        <Link to="/" className="inline-flex items-center gap-2 text-muted-foreground hover:text-foreground mb-8 transition-colors">
          <ArrowLeft className="h-4 w-4" />
          <span>Späť na hlavnú stránku</span>
        </Link>
        
        <Card className="shadow-xl border-border/50 bg-card/95 backdrop-blur animate-scale-in">
          <CardHeader className="space-y-1">
            <CardTitle className="text-3xl font-bold">Prihlásenie</CardTitle>
            <CardDescription className="text-base">
              Prihláste sa do svojho účtu
            </CardDescription>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleSubmit} className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="email">Email</Label>
                <Input
                  id="email"
                  type="email"
                  placeholder="vas@email.sk"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  required
                />
              </div>
              <div className="space-y-2">
                <Label htmlFor="password">Heslo</Label>
                <Input
                  id="password"
                  type="password"
                  placeholder="••••••••"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  required
                />
              </div>
              <Button className="w-full" size="lg" type="submit" disabled={isLoggingIn}>
                {isLoggingIn ? "Prihlasovanie..." : "Prihlásiť sa"}
              </Button>
              <div className="text-center text-sm text-muted-foreground">
                Nemáte účet?{" "}
                <Link to="/register" className="text-primary hover:underline font-medium">
                  Zaregistrujte sa
                </Link>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </div>
  );
};

export default Login;
