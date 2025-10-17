import { useState } from "react";
import { DashboardLayout } from "@/components/dashboard/DashboardLayout";
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
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
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { Badge } from "@/components/ui/badge";
import {
  Car,
  Plus,
  Search,
  Edit,
  Trash2,
  Gauge,
  Calendar,
  FileText,
} from "lucide-react";
import { useToast } from "@/hooks/use-toast";

const vehiclesData = [
  {
    id: "1",
    name: "BMW 5 Series",
    plate: "BA 123 AB",
    type: "Osobné",
    vin: "WBA12345678901234",
    year: 2020,
    currentKm: 45640,
    totalTrips: 15,
    status: "active",
  },
  {
    id: "2",
    name: "Mercedes Sprinter",
    plate: "BA 456 CD",
    type: "Nákladné",
    vin: "WDB98765432109876",
    year: 2019,
    currentKm: 78520,
    totalTrips: 23,
    status: "active",
  },
  {
    id: "3",
    name: "Škoda Octavia",
    plate: "BA 789 EF",
    type: "Osobné",
    vin: "TMB55555555555555",
    year: 2021,
    currentKm: 32310,
    totalTrips: 12,
    status: "active",
  },
  {
    id: "4",
    name: "Volkswagen Transporter",
    plate: "BA 321 GH",
    type: "Dodávka",
    vin: "WVW11111111111111",
    year: 2018,
    currentKm: 95200,
    totalTrips: 34,
    status: "maintenance",
  },
];

const Vehicles = () => {
  const { toast } = useToast();
  const [isAddVehicleOpen, setIsAddVehicleOpen] = useState(false);
  const [isEditVehicleOpen, setIsEditVehicleOpen] = useState(false);
  const [selectedVehicle, setSelectedVehicle] = useState<typeof vehiclesData[0] | null>(null);
  const [searchQuery, setSearchQuery] = useState("");

  const handleAddVehicle = (e: React.FormEvent) => {
    e.preventDefault();
    toast({
      title: "Vozidlo pridané",
      description: "Nové vozidlo bolo úspešne pridané do systému.",
    });
    setIsAddVehicleOpen(false);
  };

  const handleEditVehicle = (e: React.FormEvent) => {
    e.preventDefault();
    toast({
      title: "Vozidlo upravené",
      description: "Údaje vozidla boli úspešne aktualizované.",
    });
    setIsEditVehicleOpen(false);
  };

  const handleDeleteVehicle = (vehicle: typeof vehiclesData[0]) => {
    if (confirm(`Naozaj chcete vymazať vozidlo ${vehicle.name}?`)) {
      toast({
        title: "Vozidlo vymazané",
        description: `Vozidlo ${vehicle.name} bolo vymazané.`,
        variant: "destructive",
      });
    }
  };

  const getStatusBadge = (status: string) => {
    if (status === "active") {
      return (
        <Badge className="bg-success/10 text-success border-success/30">
          Aktívne
        </Badge>
      );
    }
    return (
      <Badge className="bg-warning/10 text-warning border-warning/30">
        Servis
      </Badge>
    );
  };

  const filteredVehicles = vehiclesData.filter((vehicle) =>
    vehicle.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
    vehicle.plate.toLowerCase().includes(searchQuery.toLowerCase())
  );

  const totalVehicles = vehiclesData.length;
  const activeVehicles = vehiclesData.filter((v) => v.status === "active").length;
  const totalKm = vehiclesData.reduce((sum, v) => sum + v.currentKm, 0);
  const totalTrips = vehiclesData.reduce((sum, v) => sum + v.totalTrips, 0);

  return (
    <DashboardLayout>
      <div className="space-y-6 animate-fade-in">
        <div className="flex justify-between items-center">
          <div>
            <h1 className="text-3xl font-bold text-foreground">Vozidlá</h1>
            <p className="text-muted-foreground mt-1">
              Správa vozového parku
            </p>
          </div>
          <Button
            onClick={() => setIsAddVehicleOpen(true)}
            className="bg-primary hover:bg-primary/90 gap-2"
          >
            <Plus className="h-4 w-4" />
            Pridať vozidlo
          </Button>
        </div>

        {/* Stats Cards */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
          <Card className="bg-gradient-card p-6 border border-border shadow-elegant-sm">
            <div className="flex items-center justify-between mb-2">
              <p className="text-sm text-muted-foreground">Celkom vozidiel</p>
              <Car className="h-5 w-5 text-primary" />
            </div>
            <p className="text-2xl font-bold text-foreground">{totalVehicles}</p>
            <p className="text-sm text-success mt-2">
              {activeVehicles} aktívnych
            </p>
          </Card>

          <Card className="bg-gradient-card p-6 border border-border shadow-elegant-sm">
            <div className="flex items-center justify-between mb-2">
              <p className="text-sm text-muted-foreground">Celkom km</p>
              <Gauge className="h-5 w-5 text-accent" />
            </div>
            <p className="text-2xl font-bold text-foreground">
              {totalKm.toLocaleString()}
            </p>
            <p className="text-sm text-muted-foreground mt-2">Súčet všetkých</p>
          </Card>

          <Card className="bg-gradient-card p-6 border border-border shadow-elegant-sm">
            <div className="flex items-center justify-between mb-2">
              <p className="text-sm text-muted-foreground">Celkom jázd</p>
              <FileText className="h-5 w-5 text-success" />
            </div>
            <p className="text-2xl font-bold text-foreground">{totalTrips}</p>
            <p className="text-sm text-muted-foreground mt-2">Všetky vozidlá</p>
          </Card>

          <Card className="bg-gradient-card p-6 border border-border shadow-elegant-sm">
            <div className="flex items-center justify-between mb-2">
              <p className="text-sm text-muted-foreground">Priemerný rok</p>
              <Calendar className="h-5 w-5 text-warning" />
            </div>
            <p className="text-2xl font-bold text-foreground">
              {Math.round(
                vehiclesData.reduce((sum, v) => sum + v.year, 0) / totalVehicles
              )}
            </p>
            <p className="text-sm text-muted-foreground mt-2">Vek vozidiel</p>
          </Card>
        </div>

        {/* Search */}
        <div className="bg-gradient-card rounded-xl p-6 border border-border shadow-elegant-sm">
          <div className="relative">
            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
            <Input
              placeholder="Hľadať vozidlá..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="pl-10"
            />
          </div>
        </div>

        {/* Vehicles Table */}
        <Card className="bg-gradient-card border border-border shadow-elegant-sm overflow-hidden">
          <div className="p-6 border-b border-border">
            <h3 className="text-lg font-bold text-foreground">Zoznam vozidiel</h3>
            <p className="text-sm text-muted-foreground mt-1">
              Prehľad všetkých vozidiel
            </p>
          </div>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Vozidlo</TableHead>
                <TableHead>ŠPZ</TableHead>
                <TableHead>Typ</TableHead>
                <TableHead>Rok</TableHead>
                <TableHead>VIN</TableHead>
                <TableHead>Aktuálne KM</TableHead>
                <TableHead>Počet jázd</TableHead>
                <TableHead>Stav</TableHead>
                <TableHead className="text-right">Akcie</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {filteredVehicles.map((vehicle) => (
                <TableRow key={vehicle.id}>
                  <TableCell>
                    <div className="flex items-center gap-3">
                      <div className="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                        <Car className="w-5 h-5 text-primary" />
                      </div>
                      <span className="font-semibold">{vehicle.name}</span>
                    </div>
                  </TableCell>
                  <TableCell className="font-mono font-semibold">
                    {vehicle.plate}
                  </TableCell>
                  <TableCell>{vehicle.type}</TableCell>
                  <TableCell>{vehicle.year}</TableCell>
                  <TableCell className="font-mono text-sm text-muted-foreground">
                    {vehicle.vin}
                  </TableCell>
                  <TableCell className="font-semibold text-accent">
                    {vehicle.currentKm.toLocaleString()} km
                  </TableCell>
                  <TableCell className="font-semibold">{vehicle.totalTrips}</TableCell>
                  <TableCell>{getStatusBadge(vehicle.status)}</TableCell>
                  <TableCell className="text-right">
                    <div className="flex justify-end gap-2">
                      <Button
                        variant="ghost"
                        size="icon"
                        onClick={() => {
                          setSelectedVehicle(vehicle);
                          setIsEditVehicleOpen(true);
                        }}
                      >
                        <Edit className="h-4 w-4 text-accent" />
                      </Button>
                      <Button
                        variant="ghost"
                        size="icon"
                        onClick={() => handleDeleteVehicle(vehicle)}
                      >
                        <Trash2 className="h-4 w-4 text-destructive" />
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </Card>
      </div>

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

      {/* Edit Vehicle Dialog */}
      <Dialog open={isEditVehicleOpen} onOpenChange={setIsEditVehicleOpen}>
        <DialogContent className="max-w-xl">
          <DialogHeader>
            <DialogTitle className="text-2xl font-bold text-primary">
              Upraviť vozidlo
            </DialogTitle>
          </DialogHeader>
          {selectedVehicle && (
            <form onSubmit={handleEditVehicle} className="space-y-6">
              <div className="bg-gradient-card rounded-xl p-6 border-2 border-primary/30">
                <h3 className="text-lg font-bold text-primary mb-4">
                  Informácie o vozidle
                </h3>
                <div className="space-y-4">
                  <div className="space-y-2">
                    <Label htmlFor="editVehicleName">Značka a model *</Label>
                    <Input
                      id="editVehicleName"
                      defaultValue={selectedVehicle.name}
                      required
                      className="border-primary/30"
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="editVehiclePlate">ŠPZ *</Label>
                    <Input
                      id="editVehiclePlate"
                      defaultValue={selectedVehicle.plate}
                      required
                      className="border-primary/30"
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="editVehicleType">Typ vozidla *</Label>
                    <Select defaultValue={selectedVehicle.type.toLowerCase()} required>
                      <SelectTrigger className="border-primary/30">
                        <SelectValue />
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
                    <Label htmlFor="editVehicleVin">VIN číslo</Label>
                    <Input
                      id="editVehicleVin"
                      defaultValue={selectedVehicle.vin}
                      className="border-primary/30"
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="editVehicleYear">Rok výroby</Label>
                    <Input
                      id="editVehicleYear"
                      type="number"
                      defaultValue={selectedVehicle.year}
                      min="1900"
                      max="2025"
                      className="border-primary/30"
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="editVehicleKm">Aktuálny stav KM</Label>
                    <Input
                      id="editVehicleKm"
                      type="number"
                      defaultValue={selectedVehicle.currentKm}
                      className="border-primary/30"
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="editVehicleStatus">Stav</Label>
                    <Select defaultValue={selectedVehicle.status}>
                      <SelectTrigger className="border-primary/30">
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="active">Aktívne</SelectItem>
                        <SelectItem value="maintenance">Servis</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                </div>
              </div>

              <div className="flex justify-end gap-3">
                <Button
                  type="button"
                  variant="outline"
                  onClick={() => setIsEditVehicleOpen(false)}
                >
                  Zrušiť
                </Button>
                <Button type="submit" className="bg-primary hover:bg-primary/90">
                  Uložiť zmeny
                </Button>
              </div>
            </form>
          )}
        </DialogContent>
      </Dialog>
    </DashboardLayout>
  );
};

export default Vehicles;
