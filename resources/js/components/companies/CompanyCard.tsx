import { memo } from 'react';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Building2, Mail, Phone, MapPin, CheckCircle2, RefreshCw, Edit, Trash2 } from 'lucide-react';
import type { UserCompany } from '@/types';

interface CompanyCardProps {
  company: UserCompany;
  isCurrentCompany: boolean;
  onSwitch: (id: number) => Promise<void>;
  onEdit: (company: UserCompany) => void;
  onDelete: (company: UserCompany) => void;
  isSwitching: boolean;
  isUpdating: boolean;
  isDeleting: boolean;
}

/**
 * Company card component that displays company information and actions
 *
 * Features:
 * - Visual indicator for currently selected company
 * - Switch, edit, and delete actions
 * - Accessibility support with ARIA labels and keyboard navigation
 * - Memoized to prevent unnecessary re-renders
 */
export const CompanyCard = memo<CompanyCardProps>(
  ({
    company,
    isCurrentCompany,
    onSwitch,
    onEdit,
    onDelete,
    isSwitching,
    isUpdating,
    isDeleting,
  }) => {
    return (
      <Card
        tabIndex={0}
        role="article"
        aria-label={`Firma ${company.name}${isCurrentCompany ? ', aktuálne zvolená' : ''}`}
        className={`bg-gradient-card hover:shadow-xl transition-all duration-300 hover:-translate-y-1 overflow-hidden group focus-within:ring-2 focus-within:ring-primary/50 outline-none ${
          isCurrentCompany
            ? 'border-primary/50 ring-2 ring-primary/20'
            : 'border-border/50'
        }`}
        onKeyDown={(e) => {
          if (e.key === 'Enter' && e.target === e.currentTarget) {
            const firstButton = e.currentTarget.querySelector('button');
            firstButton?.focus();
          }
        }}
      >
        <CardHeader className="pb-3 bg-gradient-to-br from-primary/5 to-transparent">
          <div className="flex items-start justify-between">
            <div className="flex items-center gap-3">
              <div className="w-12 h-12 rounded-xl bg-gradient-primary flex items-center justify-center shadow-md">
                <Building2 className="w-6 h-6 text-primary-foreground" aria-hidden="true" />
              </div>
              <div>
                <div className="flex items-center gap-2">
                  <h3 className="font-bold text-lg text-foreground group-hover:text-primary transition-colors line-clamp-1">
                    {company.name}
                  </h3>
                  {isCurrentCompany && (
                    <CheckCircle2
                      className="w-5 h-5 text-primary flex-shrink-0"
                      aria-label="Aktuálne zvolená firma"
                    />
                  )}
                </div>
                {isCurrentCompany && (
                  <Badge className="bg-primary/10 text-primary border-primary/30 text-xs mt-1">
                    Aktuálna firma
                  </Badge>
                )}
              </div>
            </div>
          </div>
        </CardHeader>
        <CardContent className="space-y-4 pt-4">
          <div className="space-y-3">
            <div className="flex items-center gap-2 text-sm" title={company.address}>
              <MapPin className="w-4 h-4 text-muted-foreground flex-shrink-0" aria-hidden="true" />
              <span className="text-muted-foreground line-clamp-1">{company.address}</span>
            </div>
            {company.email && (
              <div className="flex items-center gap-2 text-sm" title={company.email}>
                <Mail className="w-4 h-4 text-muted-foreground flex-shrink-0" aria-hidden="true" />
                <span className="text-muted-foreground line-clamp-1">{company.email}</span>
              </div>
            )}
            {company.phone && (
              <div className="flex items-center gap-2 text-sm">
                <Phone className="w-4 h-4 text-muted-foreground flex-shrink-0" aria-hidden="true" />
                <span className="text-muted-foreground">{company.phone}</span>
              </div>
            )}
          </div>

          <div className="grid grid-cols-2 gap-3 pt-3 border-t border-border/50">
            <div className="text-center p-3 bg-background/50 rounded-lg">
              <p className="text-xs text-muted-foreground mb-1">IČO</p>
              <p className="font-semibold text-foreground">{company.ico}</p>
            </div>
            <div className="text-center p-3 bg-background/50 rounded-lg">
              <p className="text-xs text-muted-foreground mb-1">DIČ</p>
              <p className="font-semibold text-foreground">{company.dic || 'N/A'}</p>
            </div>
          </div>

          <div className="flex gap-2 pt-3">
            {!isCurrentCompany && (
              <Button
                variant="default"
                className="flex-1 bg-gradient-primary hover:opacity-90 transition-opacity"
                onClick={() => onSwitch(company.id)}
                disabled={isSwitching}
                aria-label={`Prepnúť na firmu ${company.name}`}
                aria-busy={isSwitching}
              >
                <RefreshCw
                  className={`h-4 w-4 mr-2 ${isSwitching ? 'animate-spin' : ''}`}
                  aria-hidden="true"
                />
                {isSwitching ? 'Prepínanie...' : 'Prepnúť'}
              </Button>
            )}
            <Button
              variant="outline"
              className={`${isCurrentCompany ? 'flex-1' : ''} hover:bg-primary hover:text-primary-foreground transition-colors`}
              onClick={() => onEdit(company)}
              disabled={isUpdating}
            >
              <Edit className="h-4 w-4 mr-2" aria-hidden="true" />
              Upraviť
            </Button>
            <Button
              variant="outline"
              className="hover:bg-destructive hover:text-destructive-foreground transition-colors"
              onClick={() => onDelete(company)}
              disabled={isDeleting}
              aria-label={`Zmazať firmu ${company.name}`}
            >
              <Trash2 className="h-4 w-4" aria-hidden="true" />
            </Button>
          </div>
        </CardContent>
      </Card>
    );
  },
  (prevProps, nextProps) => {
    // Custom comparison function to optimize re-renders
    // Only re-render if these specific props change
    return (
      prevProps.company.id === nextProps.company.id &&
      prevProps.company.name === nextProps.company.name &&
      prevProps.company.email === nextProps.company.email &&
      prevProps.company.phone === nextProps.company.phone &&
      prevProps.company.address === nextProps.company.address &&
      prevProps.company.ico === nextProps.company.ico &&
      prevProps.company.dic === nextProps.company.dic &&
      prevProps.isCurrentCompany === nextProps.isCurrentCompany &&
      prevProps.isSwitching === nextProps.isSwitching &&
      prevProps.isUpdating === nextProps.isUpdating &&
      prevProps.isDeleting === nextProps.isDeleting
    );
  }
);

CompanyCard.displayName = 'CompanyCard';
