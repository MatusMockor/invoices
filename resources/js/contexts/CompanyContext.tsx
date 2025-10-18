import { createContext, useContext, useState, useEffect, ReactNode } from "react";

interface CompanyContextType {
  selectedCompanyId: string | null;
  setSelectedCompanyId: (id: string) => void;
  isLoading: boolean;
}

const CompanyContext = createContext<CompanyContextType | undefined>(undefined);

export const CompanyProvider = ({ children }: { children: ReactNode }) => {
  const [selectedCompanyId, setSelectedCompanyId] = useState<string | null>(() => {
    return localStorage.getItem("selectedCompanyId") || null;
  });
  const [isLoading, setIsLoading] = useState(false);

  const handleCompanyChange = async (id: string) => {
    setIsLoading(true);
    setSelectedCompanyId(id);
    localStorage.setItem("selectedCompanyId", id);
    
    // Simulate API request delay
    await new Promise(resolve => setTimeout(resolve, 800));
    
    setIsLoading(false);
  };

  return (
    <CompanyContext.Provider value={{ selectedCompanyId, setSelectedCompanyId: handleCompanyChange, isLoading }}>
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

