import { UseFormReturn } from "react-hook-form";
import { format } from "date-fns";
import { CalendarIcon } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Calendar } from "@/components/ui/calendar";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
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
    <div className="grid grid-cols-2 gap-3">
      {/* Dátum vystavenia */}
      <div>
        <Label className="text-xs">Dátum vystavenia</Label>
        <Popover>
          <PopoverTrigger asChild>
            <Button type="button" variant="outline" className="w-full mt-1 justify-start font-normal">
              <CalendarIcon className="mr-2 h-4 w-4" />
              {issueDate ? format(issueDate, "dd.MM.yyyy") : "Vyberte"}
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
              className="pointer-events-auto"
            />
          </PopoverContent>
        </Popover>
        {errors.issueDate && (
          <p className="text-xs text-destructive mt-1">{errors.issueDate.message}</p>
        )}
      </div>

      {/* Dátum dodania */}
      <div>
        <Label className="text-xs">Dátum dodania</Label>
        <Popover>
          <PopoverTrigger asChild>
            <Button type="button" variant="outline" className="w-full mt-1 justify-start font-normal">
              <CalendarIcon className="mr-2 h-4 w-4" />
              {deliveryDate ? format(deliveryDate, "dd.MM.yyyy") : "Vyberte"}
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
              className="pointer-events-auto"
            />
          </PopoverContent>
        </Popover>
        {errors.deliveryDate && (
          <p className="text-xs text-destructive mt-1">{errors.deliveryDate.message}</p>
        )}
      </div>

      {/* Splatnosť - full width */}
      <div className="col-span-2">
        <Label className="text-xs">Splatnosť (dní)</Label>
        <div className="grid grid-cols-3 gap-2 mt-1">
          <Input
            type="number"
            value={dueDateDays}
            onChange={(e) => setDueDateDays(Number(e.target.value))}
            className="text-center"
          />
          <Popover>
            <PopoverTrigger asChild>
              <Button type="button" variant="outline" className="col-span-2 justify-start font-normal">
                <CalendarIcon className="mr-2 h-4 w-4" />
                {dueDate ? format(dueDate, "dd.MM.yyyy") : "Vyberte"}
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
                className="pointer-events-auto"
              />
            </PopoverContent>
          </Popover>
        </div>
        {errors.dueDate && (
          <p className="text-xs text-destructive mt-1">{errors.dueDate.message}</p>
        )}
      </div>
    </div>
  );
};
