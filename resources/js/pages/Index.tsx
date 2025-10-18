import { Link } from "react-router-dom";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { FileText, BarChart3, Users, Calendar, CheckCircle2, Zap, Shield } from "lucide-react";

const Index = () => {

  const features = [
    {
      icon: FileText,
      title: "Správa faktúr",
      description: "Vytváranie a správa faktúr jednoducho a rýchlo"
    },
    {
      icon: Users,
      title: "Klienti",
      description: "Prehľadná databáza všetkých vašich klientov"
    },
    {
      icon: Calendar,
      title: "Dochádzka",
      description: "Sledovanie dochádzky zamestnancov"
    },
    {
      icon: BarChart3,
      title: "Reporty",
      description: "Detailné prehľady a štatistiky"
    },
    {
      icon: Zap,
      title: "Moderný dizajn",
      description: "Intuitívne a responzívne rozhranie"
    },
    {
      icon: Shield,
      title: "Bezpečnosť",
      description: "Vaše dáta sú v bezpečí"
    }
  ];

  return (
    <div className="min-h-screen bg-gradient-to-br from-background via-background to-secondary/20">
      {/* Header with Auth Buttons */}
      <header className="container mx-auto px-4 py-6">
        <div className="flex justify-between items-center">
          <h2 className="text-2xl font-bold text-foreground">Faktura Flow</h2>
          <div className="flex gap-3">
            <Link to="/login">
              <Button variant="outline" size="lg">
                Prihlásenie
              </Button>
            </Link>
            <Link to="/register">
              <Button size="lg">
                Registrácia
              </Button>
            </Link>
          </div>
        </div>
      </header>

      {/* Hero Section */}
      <section className="container mx-auto px-4 pt-12 pb-32">
        <div className="grid lg:grid-cols-2 gap-12 items-center">
          {/* Left side - Hero content */}
          <div className="space-y-8 animate-fade-in">
            <div className="space-y-4">
              <h1 className="text-5xl lg:text-6xl font-bold text-foreground leading-tight">
                Faktura Flow
              </h1>
              <p className="text-2xl text-primary font-semibold">
                Moderný a prehľadný systém
              </p>
              <p className="text-lg text-muted-foreground">
                Kompletné riešenie pre správu faktúr, klientov, dochádzky a vozidiel. 
                Všetko na jednom mieste, jednoducho a efektívne.
              </p>
            </div>

            <div className="flex flex-wrap gap-4 pt-4">
              <div className="flex items-center gap-2 text-sm">
                <CheckCircle2 className="h-5 w-5 text-primary" />
                <span className="text-foreground">Intuitívne ovládanie</span>
              </div>
              <div className="flex items-center gap-2 text-sm">
                <CheckCircle2 className="h-5 w-5 text-primary" />
                <span className="text-foreground">Responzívny dizajn</span>
              </div>
              <div className="flex items-center gap-2 text-sm">
                <CheckCircle2 className="h-5 w-5 text-primary" />
                <span className="text-foreground">Rýchle spracovanie</span>
              </div>
            </div>

            <div className="flex gap-4">
              <Link to="/register">
                <Button size="lg" className="mt-4">
                  Začať teraz
                </Button>
              </Link>
              <Link to="/app/dashboard">
                <Button variant="outline" size="lg" className="mt-4">
                  Prejsť na Dashboard
                </Button>
              </Link>
            </div>
          </div>

          {/* Right side - Feature Highlight */}
          <div className="animate-scale-in">
            <Card className="shadow-xl border-border/50 bg-card/95 backdrop-blur p-8">
              <div className="space-y-6">
                <div className="flex items-start gap-4">
                  <div className="h-12 w-12 rounded-lg bg-primary/10 flex items-center justify-center flex-shrink-0">
                    <FileText className="h-6 w-6 text-primary" />
                  </div>
                  <div>
                    <h3 className="font-semibold text-lg mb-2">Automatizácia faktúr</h3>
                    <p className="text-muted-foreground">Vytvárajte faktúry za sekundy s inteligentným systémom</p>
                  </div>
                </div>
                <div className="flex items-start gap-4">
                  <div className="h-12 w-12 rounded-lg bg-primary/10 flex items-center justify-center flex-shrink-0">
                    <BarChart3 className="h-6 w-6 text-primary" />
                  </div>
                  <div>
                    <h3 className="font-semibold text-lg mb-2">Pokročilé reporty</h3>
                    <p className="text-muted-foreground">Sledujte výkonnosť vašej firmy v reálnom čase</p>
                  </div>
                </div>
                <div className="flex items-start gap-4">
                  <div className="h-12 w-12 rounded-lg bg-primary/10 flex items-center justify-center flex-shrink-0">
                    <Shield className="h-6 w-6 text-primary" />
                  </div>
                  <div>
                    <h3 className="font-semibold text-lg mb-2">Bezpečnosť na prvom mieste</h3>
                    <p className="text-muted-foreground">Šifrované dáta s zálohou v cloude</p>
                  </div>
                </div>
              </div>
            </Card>
          </div>
        </div>
      </section>

      {/* Features Section */}
      <section className="container mx-auto px-4 py-20 bg-secondary/10">
        <div className="text-center mb-16 animate-fade-in">
          <h2 className="text-4xl font-bold text-foreground mb-4">
            Prečo Faktura Flow?
          </h2>
          <p className="text-lg text-muted-foreground max-w-2xl mx-auto">
            Náš systém je navrhnutý pre maximálnu efektivitu a jednoduchosť používania
          </p>
        </div>

        <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
          {features.map((feature, index) => (
            <Card 
              key={index} 
              className="border-border/50 bg-card hover:shadow-lg transition-shadow duration-300 animate-slide-up"
              style={{ animationDelay: `${index * 100}ms` }}
            >
              <CardHeader>
                <div className="h-12 w-12 rounded-lg bg-primary/10 flex items-center justify-center mb-4">
                  <feature.icon className="h-6 w-6 text-primary" />
                </div>
                <CardTitle className="text-xl">{feature.title}</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-muted-foreground">{feature.description}</p>
              </CardContent>
            </Card>
          ))}
        </div>
      </section>

      {/* Footer */}
      <footer className="container mx-auto px-4 py-8 text-center text-muted-foreground border-t border-border/50">
        <p>© 2024 Faktura Flow. Všetky práva vyhradené.</p>
      </footer>
    </div>
  );
};

export default Index;
