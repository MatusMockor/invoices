import { UseFormReturn } from "react-hook-form";
import { format } from "date-fns";
import { CalendarIcon } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Calendar } from "@/components/ui/calendar";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { cn } from "@/lib/utils";
import { InvoiceFormData } from "./ClientInformationSection";

interface InvoiceDateSectionProps {
  form: UseFormReturn<InvoiceFormData>;
  issueDate: Date | undefined;
  setIssueDate: (date: Date | undefined) => void;
  dueDate: Date | undefined;
  setDueDate: (date: Date | undefined) => void;
  deliveryDate: Date | undefined;
  setDeliveryDate: (date: Date | undefined) => void;
  dueDateDays: number;
  setDueDateDays: (days: number) => void;
}

export const InvoiceDateSection = ({
  form,
  issueDate,
  setIssueDate,
  dueDate,
  setDueDate,
  deliveryDate,
  setDeliveryDate,
  dueDateDays,
  setDueDateDays,
}: InvoiceDateSectionProps) => {
  const { setValue, formState: { errors } } = form;

  return (
    <div className="bg-gradient-card rounded-xl p-6 border-2 border-border shadow-elegant-sm">
      <h3 className="text-lg font-bold text-foreground mb-4 flex items-center gap-2">
        <span className="w-8 h-8 bg-accent text-accent-foreground rounded-full flex items-center justify-center text-sm">3</span>
        Dátumy
      </h3>
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div className="space-y-2">
          <Label>Dátum vystavenia *</Label>
          <Popover>
            <PopoverTrigger asChild>
              <Button
                type="button"
                variant="outline"
                className={cn(
                  "w-full justify-start text-left font-normal",
                  !issueDate && "text-muted-foreground"
                )}
              >
                <CalendarIcon className="mr-2 h-4 w-4" />
                {issueDate ? format(issueDate, "dd.MM.yyyy") : "Vyberte dátum"}
              </Button>
            </PopoverTrigger>
            <PopoverContent className="w-auto p-0" align="start">
              <Calendar
                mode="single"
                selected={issueDate}
                onSelect={(date) => {
                  setIssueDate(date);
                  setValue("issueDate", date as Date);
                }}
                initialFocus
                className="pointer-events-auto"
              />
            </PopoverContent>
          </Popover>
          {errors.issueDate && (
            <p className="text-sm text-destructive">{errors.issueDate.message}</p>
          )}
        </div>
        <div className="space-y-2">
          <Label>Dátum dodania *</Label>
          <Popover>
            <PopoverTrigger asChild>
              <Button
                type="button"
                variant="outline"
                className={cn(
                  "w-full justify-start text-left font-normal",
                  !deliveryDate && "text-muted-foreground"
                )}
              >
                <CalendarIcon className="mr-2 h-4 w-4" />
                {deliveryDate ? format(deliveryDate, "dd.MM.yyyy") : "Vyberte dátum"}
              </Button>
            </PopoverTrigger>
            <PopoverContent className="w-auto p-0" align="start">
              <Calendar
                mode="single"
                selected={deliveryDate}
                onSelect={(date) => {
                  setDeliveryDate(date);
                  setValue("deliveryDate", date as Date);
                }}
                initialFocus
                className="pointer-events-auto"
              />
            </PopoverContent>
          </Popover>
          {errors.deliveryDate && (
            <p className="text-sm text-destructive">{errors.deliveryDate.message}</p>
          )}
        </div>
        <div className="space-y-2 md:col-span-1">
          <Label>Dátum splatnosti *</Label>
          <div className="grid grid-cols-3 gap-2">
            <div className="col-span-1">
              <Input
                type="number"
                value={dueDateDays}
                onChange={(e) => setDueDateDays(Number(e.target.value))}
                className="border-primary/30 text-center"
              />
            </div>
            <div className="col-span-2">
              <Popover>
                <PopoverTrigger asChild>
                  <Button
                    type="button"
                    variant="outline"
                    className={cn(
                      "w-full justify-start text-left font-normal",
                      !dueDate && "text-muted-foreground"
                    )}
                  >
                    <CalendarIcon className="mr-2 h-4 w-4" />
                    {dueDate ? format(dueDate, "dd.MM.yyyy") : "Vyberte dátum"}
                  </Button>
                </PopoverTrigger>
                <PopoverContent className="w-auto p-0" align="start">
                  <Calendar
                    mode="single"
                    selected={dueDate}
                    onSelect={(date) => {
                      if (date && issueDate) {
                        const diffTime = Math.abs(date.getTime() - issueDate.getTime());
                        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                        setDueDateDays(diffDays);
                      }
                      setDueDate(date as Date);
                      setValue("dueDate", date as Date);
                    }}
                    initialFocus
                    className="pointer-events-auto"
                  />
                </PopoverContent>
              </Popover>
            </div>
          </div>
          {errors.dueDate && (
            <p className="text-sm text-destructive">{errors.dueDate.message}</p>
          )}
        </div>
      </div>
    </div>
  );
};
