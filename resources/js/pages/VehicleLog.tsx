import { useState } from "react";
import { DashboardLayout } from "@/components/dashboard/DashboardLayout";
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import {
  Car,
  Plus,
  Search,
  Download,
  MapPin,
  Gauge,
  Calendar,
} from "lucide-react";
import { useToast } from "@/hooks/use-toast";

const vehicles = [
  { id: "1", name: "BMW 5 Series", plate: "BA 123 AB", type: "Osobné" },
  { id: "2", name: "Mercedes Sprinter", plate: "BA 456 CD", type: "Nákladné" },
  { id: "3", name: "Škoda Octavia", plate: "BA 789 EF", type: "Osobné" },
];

const tripHistory = [
  {
    id: "1",
    date: "17.10.2025",
    vehicle: "BMW 5 Series",
    plate: "BA 123 AB",
    driver: "Ján Novák",
    from: "Bratislava",
    to: "Košice",
    purpose: "Obchodná cesta",
    kmStart: 45200,
    kmEnd: 45640,
    kmTotal: 440,
  },
  {
    id: "2",
    date: "16.10.2025",
    vehicle: "Škoda Octavia",
    plate: "BA 789 EF",
    driver: "Peter Horváth",
    from: "Bratislava",
    to: "Žilina",
    purpose: "Stretnutie s klientom",
    kmStart: 32100,
    kmEnd: 32310,
    kmTotal: 210,
  },
  {
    id: "3",
    date: "15.10.2025",
    vehicle: "Mercedes Sprinter",
    plate: "BA 456 CD",
    driver: "Milan Kováč",
    from: "Bratislava",
    to: "Trnava",
    purpose: "Dovoz materiálu",
    kmStart: 78450,
    kmEnd: 78520,
    kmTotal: 70,
  },
  {
    id: "4",
    date: "14.10.2025",
    vehicle: "BMW 5 Series",
    plate: "BA 123 AB",
    driver: "Ján Novák",
    from: "Bratislava",
    to: "Nitra",
    purpose: "Kontrola pobočky",
    kmStart: 45080,
    kmEnd: 45200,
    kmTotal: 120,
  },
];

const VehicleLog = () => {
  const { toast } = useToast();
  const [isAddTripOpen, setIsAddTripOpen] = useState(false);
  const [isAddVehicleOpen, setIsAddVehicleOpen] = useState(false);
  const [searchQuery, setSearchQuery] = useState("");
  const [selectedVehicle, setSelectedVehicle] = useState("all");

  const handleAddTrip = (e: React.FormEvent) => {
    e.preventDefault();
    toast({
      title: "Jazda pridaná",
      description: "Záznam o jazde bol úspešne pridaný.",
    });
    setIsAddTripOpen(false);
  };

  const handleAddVehicle = (e: React.FormEvent) => {
    e.preventDefault();
    toast({
      title: "Vozidlo pridané",
      description: "Nové vozidlo bolo úspešne pridané do systému.",
    });
    setIsAddVehicleOpen(false);
  };

  const filteredTrips = tripHistory.filter((trip) => {
    const matchesSearch =
      trip.vehicle.toLowerCase().includes(searchQuery.toLowerCase()) ||
      trip.from.toLowerCase().includes(searchQuery.toLowerCase()) ||
      trip.to.toLowerCase().includes(searchQuery.toLowerCase()) ||
      trip.driver.toLowerCase().includes(searchQuery.toLowerCase());
    
    const matchesVehicle =
      selectedVehicle === "all" || trip.plate === selectedVehicle;
    
    return matchesSearch && matchesVehicle;
  });

  const totalKm = tripHistory.reduce((sum, trip) => sum + trip.kmTotal, 0);
  const totalTrips = tripHistory.length;
  const averageKm = totalKm / totalTrips;

  return (
    <DashboardLayout>
      <div className="space-y-6 animate-fade-in">
        <div className="flex justify-between items-center">
          <div>
            <h1 className="text-3xl font-bold text-foreground">Kniha jázd</h1>
            <p className="text-muted-foreground mt-1">
              Evidencia jázd a správa vozidiel
            </p>
          </div>
          <div className="flex gap-3">
            <Button variant="outline" className="gap-2">
              <Download className="h-4 w-4" />
              Exportovať
            </Button>
            <Button
              onClick={() => setIsAddTripOpen(true)}
              className="bg-primary hover:bg-primary/90 gap-2"
            >
              <Plus className="h-4 w-4" />
              Nová jazda
            </Button>
          </div>
        </div>

        {/* Stats Cards */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
          <Card className="bg-gradient-card p-6 border border-border shadow-elegant-sm">
            <div className="flex items-center justify-between mb-2">
              <p className="text-sm text-muted-foreground">Vozidlá</p>
              <Car className="h-5 w-5 text-primary" />
            </div>
            <p className="text-2xl font-bold text-foreground">{vehicles.length}</p>
            <p className="text-sm text-muted-foreground mt-2">V prevádzke</p>
          </Card>

          <Card className="bg-gradient-card p-6 border border-border shadow-elegant-sm">
            <div className="flex items-center justify-between mb-2">
              <p className="text-sm text-muted-foreground">Celkom km</p>
              <Gauge className="h-5 w-5 text-accent" />
            </div>
            <p className="text-2xl font-bold text-foreground">
              {totalKm.toLocaleString()}
            </p>
            <p className="text-sm text-muted-foreground mt-2">Tento mesiac</p>
          </Card>

          <Card className="bg-gradient-card p-6 border border-border shadow-elegant-sm">
            <div className="flex items-center justify-between mb-2">
              <p className="text-sm text-muted-foreground">Počet jázd</p>
              <MapPin className="h-5 w-5 text-success" />
            </div>
            <p className="text-2xl font-bold text-foreground">{totalTrips}</p>
            <p className="text-sm text-muted-foreground mt-2">Tento mesiac</p>
          </Card>

          <Card className="bg-gradient-card p-6 border border-border shadow-elegant-sm">
            <div className="flex items-center justify-between mb-2">
              <p className="text-sm text-muted-foreground">Priemer/jazda</p>
              <Calendar className="h-5 w-5 text-warning" />
            </div>
            <p className="text-2xl font-bold text-foreground">
              {Math.round(averageKm)} km
            </p>
            <p className="text-sm text-muted-foreground mt-2">Priemer</p>
          </Card>
        </div>

        {/* Vehicles Overview */}
        <Card className="bg-gradient-card p-6 border border-border shadow-elegant-sm">
          <div className="flex items-center justify-between mb-4">
            <h3 className="text-lg font-bold text-foreground flex items-center gap-2">
              <Car className="h-5 w-5 text-primary" />
              Vozidlá
            </h3>
            <Button
              onClick={() => setIsAddVehicleOpen(true)}
              variant="outline"
              size="sm"
              className="gap-2 border-primary/30 text-primary hover:bg-primary/10"
            >
              <Plus className="h-4 w-4" />
              Pridať vozidlo
            </Button>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            {vehicles.map((vehicle) => (
              <div
                key={vehicle.id}
                className="p-4 bg-card rounded-lg border-2 border-primary/30 hover:border-primary/50 transition-colors"
              >
                <div className="flex items-center gap-3 mb-2">
                  <div className="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                    <Car className="w-6 h-6 text-primary" />
                  </div>
                  <div>
                    <p className="font-semibold text-foreground">{vehicle.name}</p>
                    <p className="text-sm text-muted-foreground">{vehicle.plate}</p>
                  </div>
                </div>
                <div className="flex items-center justify-between mt-3 pt-3 border-t border-border">
                  <span className="text-xs text-muted-foreground">{vehicle.type}</span>
                  <Button variant="ghost" size="sm">
                    Detail
                  </Button>
                </div>
              </div>
            ))}
          </div>
        </Card>

        {/* Search and Filter */}
        <div className="bg-gradient-card rounded-xl p-6 border border-border shadow-elegant-sm">
          <div className="flex flex-col md:flex-row gap-4">
            <div className="relative flex-1">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input
                placeholder="Hľadať jazdy..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="pl-10"
              />
            </div>
            <Select value={selectedVehicle} onValueChange={setSelectedVehicle}>
              <SelectTrigger className="w-full md:w-[250px]">
                <SelectValue placeholder="Filtrovať podľa vozidla" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">Všetky vozidlá</SelectItem>
                {vehicles.map((vehicle) => (
                  <SelectItem key={vehicle.id} value={vehicle.plate}>
                    {vehicle.name} ({vehicle.plate})
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
        </div>

        {/* Trip History */}
        <Card className="bg-gradient-card border border-border shadow-elegant-sm overflow-hidden">
          <div className="p-6 border-b border-border">
            <h3 className="text-lg font-bold text-foreground">História jázd</h3>
            <p className="text-sm text-muted-foreground mt-1">
              Prehľad všetkých jázd
            </p>
          </div>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Dátum</TableHead>
                <TableHead>Vozidlo</TableHead>
                <TableHead>Vodič</TableHead>
                <TableHead>Trasa</TableHead>
                <TableHead>Účel</TableHead>
                <TableHead>Stav KM</TableHead>
                <TableHead className="text-right">Km celkom</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {filteredTrips.map((trip) => (
                <TableRow key={trip.id}>
                  <TableCell className="font-medium">{trip.date}</TableCell>
                  <TableCell>
                    <div>
                      <p className="font-semibold">{trip.vehicle}</p>
                      <p className="text-sm text-muted-foreground">{trip.plate}</p>
                    </div>
                  </TableCell>
                  <TableCell>{trip.driver}</TableCell>
                  <TableCell>
                    <div className="flex items-center gap-2">
                      <span>{trip.from}</span>
                      <span className="text-muted-foreground">→</span>
                      <span>{trip.to}</span>
                    </div>
                  </TableCell>
                  <TableCell className="text-sm">{trip.purpose}</TableCell>
                  <TableCell>
                    <div className="text-sm">
                      <p>
                        <span className="text-muted-foreground">Start:</span>{" "}
                        {trip.kmStart.toLocaleString()}
                      </p>
                      <p>
                        <span className="text-muted-foreground">Koniec:</span>{" "}
                        {trip.kmEnd.toLocaleString()}
                      </p>
                    </div>
                  </TableCell>
                  <TableCell className="text-right">
                    <span className="text-lg font-bold text-primary">
                      {trip.kmTotal}
                    </span>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </Card>
      </div>

      {/* Add Trip Dialog */}
      <Dialog open={isAddTripOpen} onOpenChange={setIsAddTripOpen}>
        <DialogContent className="max-w-2xl">
          <DialogHeader>
            <DialogTitle className="text-2xl font-bold text-primary">
              Pridať jazdu
            </DialogTitle>
          </DialogHeader>
          <form onSubmit={handleAddTrip} className="space-y-6">
            <div className="bg-gradient-card rounded-xl p-6 border-2 border-primary/30">
              <h3 className="text-lg font-bold text-primary mb-4">
                Informácie o jazde
              </h3>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label htmlFor="tripDate">Dátum *</Label>
                  <Input
                    id="tripDate"
                    type="date"
                    required
                    className="border-primary/30"
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="tripVehicle">Vozidlo *</Label>
                  <Select required>
                    <SelectTrigger className="border-primary/30">
                      <SelectValue placeholder="Vyberte vozidlo" />
                    </SelectTrigger>
                    <SelectContent>
                      {vehicles.map((vehicle) => (
                        <SelectItem key={vehicle.id} value={vehicle.id}>
                          {vehicle.name} ({vehicle.plate})
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>
                <div className="space-y-2">
                  <Label htmlFor="tripDriver">Vodič *</Label>
                  <Input
                    id="tripDriver"
                    placeholder="Meno vodiča"
                    required
                    className="border-primary/30"
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="tripPurpose">Účel jazdy *</Label>
                  <Input
                    id="tripPurpose"
                    placeholder="Obchodná cesta"
                    required
                    className="border-primary/30"
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="tripFrom">Z (miesto) *</Label>
                  <Input
                    id="tripFrom"
                    placeholder="Bratislava"
                    required
                    className="border-primary/30"
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="tripTo">Do (miesto) *</Label>
                  <Input
                    id="tripTo"
                    placeholder="Košice"
                    required
                    className="border-primary/30"
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="tripKmStart">Stav KM - začiatok *</Label>
                  <Input
                    id="tripKmStart"
                    type="number"
                    placeholder="45200"
                    required
                    className="border-primary/30"
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="tripKmEnd">Stav KM - koniec *</Label>
                  <Input
                    id="tripKmEnd"
                    type="number"
                    placeholder="45640"
                    required
                    className="border-primary/30"
                  />
                </div>
                <div className="space-y-2 md:col-span-2">
                  <Label htmlFor="tripNotes">Poznámky</Label>
                  <Textarea
                    id="tripNotes"
                    placeholder="Dodatočné poznámky..."
                    className="border-primary/30"
                  />
                </div>
              </div>
            </div>

            <div className="flex justify-end gap-3">
              <Button
                type="button"
                variant="outline"
                onClick={() => setIsAddTripOpen(false)}
              >
                Zrušiť
              </Button>
              <Button type="submit" className="bg-primary hover:bg-primary/90">
                Pridať jazdu
              </Button>
            </div>
          </form>
        </DialogContent>
      </Dialog>

      {/* Add Vehicle Dialog */}
      <Dialog open={isAddVehicleOpen} onOpenChange={setIsAddVehicleOpen}>
        <DialogContent className="max-w-xl">
          <DialogHeader>
            <DialogTitle className="text-2xl font-bold text-primary">
              Pridať vozidlo
            </DialogTitle>
          </DialogHeader>
          <form onSubmit={handleAddVehicle} className="space-y-6">
            <div className="bg-gradient-card rounded-xl p-6 border-2 border-primary/30">
              <h3 className="text-lg font-bold text-primary mb-4">
                Informácie o vozidle
              </h3>
              <div className="space-y-4">
                <div className="space-y-2">
                  <Label htmlFor="vehicleName">Značka a model *</Label>
                  <Input
                    id="vehicleName"
                    placeholder="BMW 5 Series"
                    required
                    className="border-primary/30"
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="vehiclePlate">ŠPZ *</Label>
                  <Input
                    id="vehiclePlate"
                    placeholder="BA 123 AB"
                    required
                    className="border-primary/30"
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="vehicleType">Typ vozidla *</Label>
                  <Select required>
                    <SelectTrigger className="border-primary/30">
                      <SelectValue placeholder="Vyberte typ" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="osobne">Osobné</SelectItem>
                      <SelectItem value="nakladne">Nákladné</SelectItem>
                      <SelectItem value="dodavka">Dodávka</SelectItem>
                      <SelectItem value="motocykel">Motocykel</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
                <div className="space-y-2">
                  <Label htmlFor="vehicleVin">VIN číslo</Label>
                  <Input
                    id="vehicleVin"
                    placeholder="WBA12345678901234"
                    className="border-primary/30"
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="vehicleYear">Rok výroby</Label>
                  <Input
                    id="vehicleYear"
                    type="number"
                    placeholder="2020"
                    min="1900"
                    max="2025"
                    className="border-primary/30"
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="vehicleKm">Aktuálny stav KM</Label>
                  <Input
                    id="vehicleKm"
                    type="number"
                    placeholder="45200"
                    className="border-primary/30"
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="vehicleNotes">Poznámky</Label>
                  <Textarea
                    id="vehicleNotes"
                    placeholder="Dodatočné informácie o vozidle..."
                    className="border-primary/30"
                  />
                </div>
              </div>
            </div>

            <div className="flex justify-end gap-3">
              <Button
                type="button"
                variant="outline"
                onClick={() => setIsAddVehicleOpen(false)}
              >
                Zrušiť
              </Button>
              <Button type="submit" className="bg-primary hover:bg-primary/90">
                <Plus className="h-4 w-4 mr-2" />
                Pridať vozidlo
              </Button>
            </div>
          </form>
        </DialogContent>
      </Dialog>
    </DashboardLayout>
  );
};

export default VehicleLog;
