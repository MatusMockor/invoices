import { createContext, useContext, useState, ReactNode } from "react";

interface CompanyContextType {
  selectedCompanyId: string | null;
  setSelectedCompanyId: (id: string) => void;
  isLoading: boolean;
  setIsLoading: (loading: boolean) => void;
}

const CompanyContext = createContext<CompanyContextType | undefined>(undefined);

export const CompanyProvider = ({ children }: { children: ReactNode }) => {
  const [selectedCompanyId, setSelectedCompanyIdState] = useState<string | null>(() => {
    return localStorage.getItem("selectedCompanyId") || null;
  });
  const [isLoading, setIsLoading] = useState(false);

  const setSelectedCompanyId = (id: string) => {
    setSelectedCompanyIdState(id);
    localStorage.setItem("selectedCompanyId", id);
  };

  return (
    <CompanyContext.Provider value={{ selectedCompanyId, setSelectedCompanyId, isLoading, setIsLoading }}>
      {children}
    </CompanyContext.Provider>
  );
};

export const useCompanyContext = () => {
  const context = useContext(CompanyContext);
  if (!context) {
    throw new Error("useCompanyContext must be used within CompanyProvider");
  }
  return context;
};

