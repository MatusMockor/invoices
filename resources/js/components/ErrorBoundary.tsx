import React, { Component, ErrorInfo, ReactNode } from 'react';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { AlertCircle } from 'lucide-react';

interface Props {
  children: ReactNode;
  fallback?: ReactNode;
  onError?: (error: Error, errorInfo: ErrorInfo) => void;
}

interface State {
  hasError: boolean;
  error?: Error;
}

/**
 * Error Boundary component to catch and handle React errors gracefully
 *
 * @example
 * ```tsx
 * <ErrorBoundary>
 *   <MyComponent />
 * </ErrorBoundary>
 * ```
 *
 * @example With custom fallback
 * ```tsx
 * <ErrorBoundary fallback={<CustomErrorUI />}>
 *   <MyComponent />
 * </ErrorBoundary>
 * ```
 */
export class ErrorBoundary extends Component<Props, State> {
  constructor(props: Props) {
    super(props);
    this.state = { hasError: false };
  }

  static getDerivedStateFromError(error: Error): State {
    return { hasError: true, error };
  }

  componentDidCatch(error: Error, errorInfo: ErrorInfo) {
    console.error('Error boundary caught:', error, errorInfo);

    // Call optional error handler
    this.props.onError?.(error, errorInfo);

    // TODO: Log to error tracking service (Sentry, LogRocket, etc.)
    // Example:
    // Sentry.captureException(error, { contexts: { react: { componentStack: errorInfo.componentStack } } });
  }

  private handleReset = () => {
    this.setState({ hasError: false, error: undefined });
  };

  private handleReload = () => {
    window.location.reload();
  };

  render() {
    if (this.state.hasError) {
      // Use custom fallback if provided
      if (this.props.fallback) {
        return this.props.fallback;
      }

      // Default error UI
      return (
        <Card className="m-8 border-destructive/50 shadow-lg">
          <CardContent className="p-12 text-center">
            <div className="flex flex-col items-center">
              <AlertCircle className="w-16 h-16 text-destructive mb-4" aria-hidden="true" />
              <h2 className="text-2xl font-bold mb-2 text-foreground">
                Niečo sa pokazilo
              </h2>
              <p className="text-muted-foreground mb-6 max-w-md">
                Vyskytla sa neočakávaná chyba. Skúste prosím obnoviť stránku alebo sa vráťte späť.
              </p>

              {process.env.NODE_ENV === 'development' && this.state.error && (
                <details className="mb-6 text-left w-full max-w-2xl">
                  <summary className="cursor-pointer text-sm text-muted-foreground hover:text-foreground mb-2">
                    Detaily chyby (iba v development režime)
                  </summary>
                  <pre className="text-xs bg-muted p-4 rounded-lg overflow-auto max-h-96">
                    <code>
                      {this.state.error.name}: {this.state.error.message}
                      {'\n\n'}
                      {this.state.error.stack}
                    </code>
                  </pre>
                </details>
              )}

              <div className="flex gap-3">
                <Button
                  onClick={this.handleReset}
                  variant="outline"
                >
                  Skúsiť znova
                </Button>
                <Button
                  onClick={this.handleReload}
                  className="bg-gradient-primary hover:opacity-90"
                >
                  Obnoviť stránku
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>
      );
    }

    return this.props.children;
  }
}
