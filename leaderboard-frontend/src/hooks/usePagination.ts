import { useState, useCallback } from 'react';

interface UsePaginationProps {
  totalItems: number;
  initialPage?: number;
  initialLimit?: number;
  maxPages?: number;
}

interface PaginationState {
  currentPage: number;
  limit: number;
  totalPages: number;
  startIndex: number;
  endIndex: number;
  hasNextPage: boolean;
  hasPrevPage: boolean;
}

interface PaginationActions {
  goToPage: (page: number) => void;
  nextPage: () => void;
  prevPage: () => void;
  setLimit: (limit: number) => void;
  reset: () => void;
}

export const usePagination = ({
  totalItems,
  initialPage = 1,
  initialLimit = 10,
  maxPages = 100,
}: UsePaginationProps): PaginationState & PaginationActions => {
  const [currentPage, setCurrentPage] = useState(initialPage);
  const [limit, setLimit] = useState(initialLimit);

  const totalPages = Math.min(Math.ceil(totalItems / limit), maxPages);
  const startIndex = (currentPage - 1) * limit;
  const endIndex = Math.min(startIndex + limit, totalItems);
  const hasNextPage = currentPage < totalPages;
  const hasPrevPage = currentPage > 1;

  const goToPage = useCallback((page: number) => {
    const clampedPage = Math.max(1, Math.min(page, totalPages));
    setCurrentPage(clampedPage);
  }, [totalPages]);

  const nextPage = useCallback(() => {
    if (hasNextPage) {
      setCurrentPage(prev => prev + 1);
    }
  }, [hasNextPage]);

  const prevPage = useCallback(() => {
    if (hasPrevPage) {
      setCurrentPage(prev => prev - 1);
    }
  }, [hasPrevPage]);

  const setPageLimit = useCallback((newLimit: number) => {
    const clampedLimit = Math.max(1, Math.min(newLimit, 100)); // Limit between 1 and 100
    setLimit(clampedLimit);
    
    // Reset to first page when changing limit to avoid out-of-bounds
    setCurrentPage(1);
  }, []);

  const reset = useCallback(() => {
    setCurrentPage(initialPage);
    setLimit(initialLimit);
  }, [initialPage, initialLimit]);

  return {
    // State
    currentPage,
    limit,
    totalPages,
    startIndex,
    endIndex,
    hasNextPage,
    hasPrevPage,
    
    // Actions
    goToPage,
    nextPage,
    prevPage,
    setLimit: setPageLimit,
    reset,
  };
};