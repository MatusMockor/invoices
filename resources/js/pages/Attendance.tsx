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
import { Badge } from "@/components/ui/badge";
import { Input } from "@/components/ui/input";
import {
  Clock,
  LogIn,
  LogOut,
  Calendar,
  TrendingUp,
  Search,
  Download,
  Coffee,
  Utensils,
  PauseCircle,
} from "lucide-react";
import { useToast } from "@/hooks/use-toast";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";

const attendanceHistory = [
  {
    id: "1",
    date: "17.10.2025",
    checkIn: "08:15",
    breakStart: "10:30",
    breakEnd: "10:45",
    lunchStart: "12:00",
    lunchEnd: "12:30",
    checkOut: "17:30",
    totalHours: "9:15",
    totalBreaks: "0:45",
    status: "present",
  },
  {
    id: "2",
    date: "16.10.2025",
    checkIn: "08:00",
    breakStart: "10:15",
    breakEnd: "10:30",
    lunchStart: "12:00",
    lunchEnd: "13:00",
    checkOut: "17:00",
    totalHours: "9:00",
    totalBreaks: "1:15",
    status: "present",
  },
  {
    id: "3",
    date: "15.10.2025",
    checkIn: "08:30",
    breakStart: "10:45",
    breakEnd: "11:00",
    lunchStart: "12:30",
    lunchEnd: "13:00",
    checkOut: "18:00",
    totalHours: "9:30",
    totalBreaks: "0:45",
    status: "present",
  },
];

const Attendance = () => {
  const { toast } = useToast();
  const [isCheckedIn, setIsCheckedIn] = useState(false);
  const [isOnBreak, setIsOnBreak] = useState(false);
  const [isOnLunch, setIsOnLunch] = useState(false);
  const [checkInTime, setCheckInTime] = useState<string | null>(null);
  const [breakStartTime, setBreakStartTime] = useState<string | null>(null);
  const [lunchStartTime, setLunchStartTime] = useState<string | null>(null);
  const [searchQuery, setSearchQuery] = useState("");

  const getCurrentTime = () => {
    return new Date().toLocaleTimeString("sk-SK", {
      hour: "2-digit",
      minute: "2-digit",
    });
  };

  const handleCheckIn = () => {
    const time = getCurrentTime();
    setCheckInTime(time);
    setIsCheckedIn(true);
    toast({
      title: "Príchod zaznamenaný",
      description: `Príchod o ${time}`,
    });
  };

  const handleBreakStart = () => {
    const time = getCurrentTime();
    setBreakStartTime(time);
    setIsOnBreak(true);
    toast({
      title: "Prestávka začala",
      description: `Prestávka začala o ${time}`,
    });
  };

  const handleBreakEnd = () => {
    const time = getCurrentTime();
    toast({
      title: "Prestávka ukončená",
      description: `Prestávka skončila o ${time}`,
    });
    setIsOnBreak(false);
    setBreakStartTime(null);
  };

  const handleLunchStart = () => {
    const time = getCurrentTime();
    setLunchStartTime(time);
    setIsOnLunch(true);
    toast({
      title: "Obedňajšia prestávka",
      description: `Obed začal o ${time}`,
    });
  };

  const handleLunchEnd = () => {
    const time = getCurrentTime();
    toast({
      title: "Obed ukončený",
      description: `Obed skončil o ${time}`,
    });
    setIsOnLunch(false);
    setLunchStartTime(null);
  };

  const handleCheckOut = () => {
    const time = getCurrentTime();
    toast({
      title: "Odchod zaznamenaný",
      description: `Odchod o ${time}`,
    });
    setIsCheckedIn(false);
    setCheckInTime(null);
    setIsOnBreak(false);
    setIsOnLunch(false);
  };

  const getStatusBadge = (status: string) => {
    const variants = {
      present: "bg-success/10 text-success border-success/30",
      late: "bg-warning/10 text-warning border-warning/30",
      absent: "bg-destructive/10 text-destructive border-destructive/30",
    };

    const labels = {
      present: "Prítomný",
      late: "Oneskorenie",
      absent: "Neprítomný",
    };

    return (
      <Badge className={variants[status as keyof typeof variants]}>
        {labels[status as keyof typeof labels]}
      </Badge>
    );
  };

  const filteredAttendance = attendanceHistory.filter((record) =>
    record.date.toLowerCase().includes(searchQuery.toLowerCase())
  );

  const totalWorkHours = attendanceHistory
    .filter((r) => r.totalHours !== "-")
    .reduce((sum, record) => {
      const [hours, minutes] = record.totalHours.split(":").map(Number);
      return sum + hours + minutes / 60;
    }, 0);

  const presentDays = attendanceHistory.filter((r) => r.status === "present" || r.status === "late").length;

  return (
    <DashboardLayout>
      <div className="space-y-6 animate-fade-in">
        <div className="flex justify-between items-center">
          <div>
            <h1 className="text-3xl font-bold text-foreground">Dochádzka</h1>
            <p className="text-muted-foreground mt-1">
              Sledujte pracovný čas a dochádzku
            </p>
          </div>
          <Button variant="outline" className="gap-2">
            <Download className="h-4 w-4" />
            Exportovať
          </Button>
        </div>

        {/* Check In/Out Card */}
        <Card className="bg-gradient-card p-8 border-2 border-primary/30 shadow-elegant-md">
          <div className="text-center space-y-6">
            <div className="inline-flex items-center justify-center w-20 h-20 rounded-full bg-primary/10">
              <Clock className="w-10 h-10 text-primary" />
            </div>
            
            <div>
              <h2 className="text-2xl font-bold text-foreground mb-2">
                {new Date().toLocaleDateString("sk-SK", {
                  weekday: "long",
                  year: "numeric",
                  month: "long",
                  day: "numeric",
                })}
              </h2>
              <p className="text-4xl font-bold text-primary">
                {new Date().toLocaleTimeString("sk-SK", {
                  hour: "2-digit",
                  minute: "2-digit",
                })}
              </p>
            </div>

            {isCheckedIn && checkInTime && (
              <div className="space-y-3">
                <div className="py-4 px-6 bg-success/10 rounded-lg border border-success/30">
                  <p className="text-sm text-success mb-1">Príchod zaznamenaný</p>
                  <p className="text-2xl font-bold text-success">{checkInTime}</p>
                </div>

                {isOnBreak && breakStartTime && (
                  <div className="py-3 px-6 bg-warning/10 rounded-lg border border-warning/30">
                    <p className="text-sm text-warning mb-1">
                      Na prestávke od
                    </p>
                    <p className="text-xl font-bold text-warning">
                      {breakStartTime}
                    </p>
                  </div>
                )}

                {isOnLunch && lunchStartTime && (
                  <div className="py-3 px-6 bg-accent/10 rounded-lg border border-accent/30">
                    <p className="text-sm text-accent mb-1">Na obede od</p>
                    <p className="text-xl font-bold text-accent">
                      {lunchStartTime}
                    </p>
                  </div>
                )}
              </div>
            )}

            <div className="flex flex-wrap gap-4 justify-center">
              {!isCheckedIn ? (
                <Button
                  onClick={handleCheckIn}
                  size="lg"
                  className="bg-success hover:bg-success/90 text-success-foreground px-8 py-6 text-lg"
                >
                  <LogIn className="h-6 w-6 mr-2" />
                  Zaznamenať príchod
                </Button>
              ) : (
                <>
                  {!isOnBreak && !isOnLunch && (
                    <DropdownMenu>
                      <DropdownMenuTrigger asChild>
                        <Button
                          size="lg"
                          variant="outline"
                          className="px-8 py-6 text-lg border-warning/30 text-warning hover:bg-warning/10"
                        >
                          <PauseCircle className="h-6 w-6 mr-2" />
                          Prestávka
                        </Button>
                      </DropdownMenuTrigger>
                      <DropdownMenuContent className="w-56">
                        <DropdownMenuItem onClick={handleBreakStart}>
                          <Coffee className="h-4 w-4 mr-2" />
                          Krátka prestávka
                        </DropdownMenuItem>
                        <DropdownMenuItem onClick={handleLunchStart}>
                          <Utensils className="h-4 w-4 mr-2" />
                          Obed
                        </DropdownMenuItem>
                      </DropdownMenuContent>
                    </DropdownMenu>
                  )}

                  {isOnBreak && (
                    <Button
                      onClick={handleBreakEnd}
                      size="lg"
                      className="bg-warning hover:bg-warning/90 text-warning-foreground px-8 py-6 text-lg"
                    >
                      <Coffee className="h-6 w-6 mr-2" />
                      Ukončiť prestávku
                    </Button>
                  )}

                  {isOnLunch && (
                    <Button
                      onClick={handleLunchEnd}
                      size="lg"
                      className="bg-accent hover:bg-accent/90 text-accent-foreground px-8 py-6 text-lg"
                    >
                      <Utensils className="h-6 w-6 mr-2" />
                      Ukončiť obed
                    </Button>
                  )}

                  <Button
                    onClick={handleCheckOut}
                    size="lg"
                    className="bg-destructive hover:bg-destructive/90 text-destructive-foreground px-8 py-6 text-lg"
                  >
                    <LogOut className="h-6 w-6 mr-2" />
                    Zaznamenať odchod
                  </Button>
                </>
              )}
            </div>
          </div>
        </Card>

        {/* Stats Cards */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <Card className="bg-gradient-card p-6 border border-border shadow-elegant-sm">
            <div className="flex items-center justify-between mb-2">
              <p className="text-sm text-muted-foreground">Tento mesiac</p>
              <Calendar className="h-5 w-5 text-primary" />
            </div>
            <p className="text-2xl font-bold text-foreground">{presentDays} dní</p>
            <div className="flex items-center gap-1 mt-2">
              <TrendingUp className="h-4 w-4 text-success" />
              <p className="text-sm text-success">100% dochádzka</p>
            </div>
          </Card>

          <Card className="bg-gradient-card p-6 border border-border shadow-elegant-sm">
            <div className="flex items-center justify-between mb-2">
              <p className="text-sm text-muted-foreground">Odpracované hodiny</p>
              <Clock className="h-5 w-5 text-accent" />
            </div>
            <p className="text-2xl font-bold text-foreground">
              {totalWorkHours.toFixed(1)}h
            </p>
            <p className="text-sm text-muted-foreground mt-2">
              Za posledných {attendanceHistory.length} dní
            </p>
          </Card>

          <Card className="bg-gradient-card p-6 border border-border shadow-elegant-sm">
            <div className="flex items-center justify-between mb-2">
              <p className="text-sm text-muted-foreground">Priemerný príchod</p>
              <LogIn className="h-5 w-5 text-success" />
            </div>
            <p className="text-2xl font-bold text-foreground">08:22</p>
            <p className="text-sm text-muted-foreground mt-2">
              Tento mesiac
            </p>
          </Card>
        </div>

        {/* Search */}
        <div className="bg-gradient-card rounded-xl p-6 border border-border shadow-elegant-sm">
          <div className="flex gap-4 items-center">
            <div className="relative flex-1">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input
                placeholder="Hľadať podľa dátumu..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="pl-10"
              />
            </div>
          </div>
        </div>

        {/* Attendance History */}
        <Card className="bg-gradient-card border border-border shadow-elegant-sm overflow-hidden">
          <div className="p-6 border-b border-border">
            <h3 className="text-lg font-bold text-foreground">História dochádzky</h3>
            <p className="text-sm text-muted-foreground mt-1">
              Prehľad vašej dochádzky
            </p>
          </div>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Dátum</TableHead>
                <TableHead>Príchod</TableHead>
                <TableHead>Prestávka</TableHead>
                <TableHead>Obed</TableHead>
                <TableHead>Odchod</TableHead>
                <TableHead>Odpracované</TableHead>
                <TableHead>Stav</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {filteredAttendance.map((record) => (
                <TableRow key={record.id}>
                  <TableCell className="font-medium">{record.date}</TableCell>
                  <TableCell>
                    <span className="text-success font-semibold">
                      {record.checkIn}
                    </span>
                  </TableCell>
                  <TableCell>
                    <div className="text-sm">
                      <span className="text-warning font-semibold">
                        {record.breakStart}
                      </span>
                      <span className="text-muted-foreground mx-1">→</span>
                      <span className="text-warning font-semibold">
                        {record.breakEnd}
                      </span>
                    </div>
                  </TableCell>
                  <TableCell>
                    <div className="text-sm">
                      <span className="text-accent font-semibold">
                        {record.lunchStart}
                      </span>
                      <span className="text-muted-foreground mx-1">→</span>
                      <span className="text-accent font-semibold">
                        {record.lunchEnd}
                      </span>
                    </div>
                  </TableCell>
                  <TableCell>
                    <span className="text-destructive font-semibold">
                      {record.checkOut}
                    </span>
                  </TableCell>
                  <TableCell>
                    <div>
                      <p className="font-semibold">{record.totalHours}</p>
                      <p className="text-xs text-muted-foreground">
                        Prestávky: {record.totalBreaks}
                      </p>
                    </div>
                  </TableCell>
                  <TableCell>{getStatusBadge(record.status)}</TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </Card>
      </div>
    </DashboardLayout>
  );
};

export default Attendance;
