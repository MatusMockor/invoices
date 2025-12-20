import { LucideIcon } from "lucide-react";
import { cn } from "@/lib/utils";

interface SectionHeaderProps {
  icon: LucideIcon;
  title: string;
  className?: string;
}

export const SectionHeader = ({ icon: Icon, title, className }: SectionHeaderProps) => {
  return (
    <div className={cn("flex items-center gap-2 mb-4", className)}>
      <div className="p-1.5 rounded-md bg-primary/10">
        <Icon className="h-4 w-4 text-primary" />
      </div>
      <h2 className="font-semibold text-foreground">{title}</h2>
    </div>
  );
};
