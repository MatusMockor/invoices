import { UseFormReturn } from "react-hook-form";
import { MessageSquare } from "lucide-react";
import { Card } from "@/components/ui/card";
import { Textarea } from "@/components/ui/textarea";
import { InvoiceFormData } from "./ClientInformationSection";
import { SectionHeader } from "@/components/ui/SectionHeader";

interface NotesSectionProps {
  form: UseFormReturn<InvoiceFormData>;
}

const MAX_NOTES_LENGTH = 500;

export const NotesSection = ({ form }: NotesSectionProps) => {
  const { register, watch } = form;
  const notesLength = watch("notes")?.length || 0;

  return (
    <Card className="p-4 sm:p-5 h-full">
      <SectionHeader icon={MessageSquare} title="Poznamky" />
      <Textarea
        {...register("notes")}
        placeholder="Doplnujuce informacie k fakture, platobne podmienky..."
        rows={4}
        className="resize-none"
        maxLength={MAX_NOTES_LENGTH}
      />
      <p className="text-xs text-muted-foreground mt-1.5 text-right">
        {notesLength} / {MAX_NOTES_LENGTH}
      </p>
    </Card>
  );
};
