import { useState } from "react";
import axios from "axios";

interface BusinessEntity {
  id: number;
  ico: string;
  name: string;
  dic?: string | null;
  ic_dph?: string | null;
  street?: string;
  city?: string;
  postal_code?: string;
  country?: string;
}

export const useBusinessEntitySearch = () => {
  const [isSearching, setIsSearching] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const searchByIco = async (ico: string): Promise<BusinessEntity | null> => {
    if (!ico || ico.trim().length < 3) {
      return null;
    }

    setIsSearching(true);
    setError(null);

    try {
      const response = await axios.get("/api/business-entities-fetch-by-ico", {
        params: { ico: ico.trim() },
      });

      if (response.data.data) {
        return response.data.data;
      }

      return null;
    } catch (err: any) {
      if (err.response?.status === 404) {
        setError("Firma s týmto IČO nebola nájdená");
      } else {
        setError("Chyba pri vyhľadávaní firmy");
      }
      return null;
    } finally {
      setIsSearching(false);
    }
  };

  return {
    searchByIco,
    isSearching,
    error,
  };
};
