import { useState, useEffect } from "react";

/**
 * Custom hook that debounces a value
 *
 * @template T - The type of the value to debounce
 * @param {T} value - The value to debounce
 * @param {number} delay - The delay in milliseconds before the value is updated
 * @returns {T} The debounced value
 *
 * @example
 * ```tsx
 * const [searchQuery, setSearchQuery] = useState("");
 * const debouncedSearch = useDebounce(searchQuery, 500);
 *
 * // debouncedSearch will update 500ms after the user stops typing
 * ```
 */
export function useDebounce<T>(value: T, delay: number): T {
  const [debouncedValue, setDebouncedValue] = useState<T>(value);

  useEffect(() => {
    const handler = setTimeout(() => {
      setDebouncedValue(value);
    }, delay);

    return () => {
      clearTimeout(handler);
    };
  }, [value, delay]);

  return debouncedValue;
}
