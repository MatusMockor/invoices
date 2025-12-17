import { Link, useNavigate, useSearchParams } from "react-router-dom";
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
  const [isRedirecting, setIsRedirecting] = useState(false);
  const { login, isLoggingIn, isAuthenticated, user } = useAuthContext();
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();

  // Get redirect URL from query params (for OAuth flow)
  const redirectUrl = searchParams.get("redirect");

  // Redirect authenticated users to appropriate page
  useEffect(() => {
    if (isAuthenticated && user && !isRedirecting) {
      // If there's a redirect URL (OAuth flow), use it
      if (redirectUrl) {
        setIsRedirecting(true);
        // Full page redirect to OAuth authorize endpoint
        window.location.replace(redirectUrl);
        return;
      }
      // If user has company, go to dashboard, otherwise go to onboarding
      if (user.current_company_id) {
        navigate("/app/dashboard", { replace: true });
      } else {
        navigate("/app/onboarding", { replace: true });
      }
    }
  }, [isAuthenticated, user, navigate, redirectUrl, isRedirecting]);

  // Show loading screen while redirecting to OAuth
  if (isRedirecting) {
    return (
      <div className="min-h-screen bg-gradient-to-br from-background via-background to-secondary/20 flex items-center justify-center p-4">
        <div className="text-center">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto mb-4"></div>
          <p className="text-muted-foreground">Presmerovanie na autorizáciu...</p>
        </div>
      </div>
    );
  }

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
