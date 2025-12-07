import React, { useState, useEffect } from 'react';
import { useLeaderboard } from '../../hooks/useLeaderboard';
import { usePagination } from '../../hooks/usePagination';
import { useLeaderboardStore } from '../../store/slices/leaderboardSlice';
import LeaderboardTable from '../presentational/Leaderboard/LeaderboardTable';
import Button from '../presentational/Common/Button';
import Input from '../presentational/Common/Input';
import type { LeaderboardEntry } from '../../types/domain.types';

interface LeaderboardContainerProps {
  className?: string;
}

const LeaderboardContainer: React.FC<LeaderboardContainerProps> = ({ className }) => {
  const {
    players,
    total,
    page,
    region,
    loading,
    error,
    fetchLeaderboard,
    setRegion,
    setPage,
    refreshLeaderboard,
  } = useLeaderboardStore();

  const {
    currentPage,
    limit,
    totalPages,
    hasNextPage,
    hasPrevPage,
    goToPage,
    nextPage,
    prevPage,
    setLimit,
  } = usePagination({
    totalItems: total,
    initialPage: page + 1,
    initialLimit: 20,
    maxPages: 50,
  });

  const [searchQuery, setSearchQuery] = useState('');
  const [selectedRegion, setSelectedRegion] = useState(region || 'global');
  const [isRefreshing, setIsRefreshing] = useState(false);

  const availableRegions = [
    { value: 'global', label: 'Global' },
    { value: 'us', label: 'United States' },
    { value: 'eu', label: 'Europe' },
    { value: 'asia', label: 'Asia' },
    { value: 'ru', label: 'Russia' },
  ];

  // Fetch leaderboard when component mounts or parameters change
  useEffect(() => {
    fetchLeaderboard({
      limit,
      offset: (currentPage - 1) * limit,
      region: selectedRegion,
    });
  }, [fetchLeaderboard, currentPage, limit, selectedRegion]);

  // Handle pagination
  const handlePageChange = async (newPage: number) => {
    goToPage(newPage);
    setPage(newPage - 1);
    await fetchLeaderboard({
      limit,
      offset: (newPage - 1) * limit,
      region: selectedRegion,
    });
  };

  // Handle region change
  const handleRegionChange = async (newRegion: string) => {
    setSelectedRegion(newRegion);
    goToPage(1); // Reset to first page
    setRegion(newRegion);
    setPage(0);
    await fetchLeaderboard({
      limit,
      offset: 0,
      region: newRegion,
    });
  };

  // Handle limit change
  const handleLimitChange = (newLimit: number) => {
    setLimit(newLimit);
    goToPage(1); // Reset to first page
  };

  // Handle refresh
  const handleRefresh = async () => {
    setIsRefreshing(true);
    try {
      await refreshLeaderboard();
    } finally {
      setIsRefreshing(false);
    }
  };

  // Handle search
  const handleSearch = (query: string) => {
    setSearchQuery(query);
    // Search will be handled by SearchContainer or separate search functionality
  };

  return (
    <div className={`space-y-6 ${className || ''}`}>
      {/* Header and Controls */}
      <div className="bg-white rounded-lg shadow p-6">
        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 className="text-2xl font-bold text-gray-900">Leaderboard</h1>
            <p className="text-gray-600">
              Showing {((currentPage - 1) * limit) + 1}-{Math.min(currentPage * limit, total)} of {total} players
            </p>
          </div>
          
          <div className="flex items-center gap-3">
            {/* Region Filter */}
            <select
              value={selectedRegion}
              onChange={(e) => handleRegionChange(e.target.value)}
              className="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
            >
              {availableRegions.map((region) => (
                <option key={region.value} value={region.value}>
                  {region.label}
                </option>
              ))}
            </select>

            {/* Limit Selector */}
            <select
              value={limit}
              onChange={(e) => handleLimitChange(Number(e.target.value))}
              className="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
            >
              <option value={10}>10 per page</option>
              <option value={20}>20 per page</option>
              <option value={50}>50 per page</option>
              <option value={100}>100 per page</option>
            </select>

            {/* Refresh Button */}
            <Button
              onClick={handleRefresh}
              loading={isRefreshing}
              disabled={loading || isRefreshing}
              variant="outline"
              size="sm"
            >
              <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
            </Button>
          </div>
        </div>

        {/* Search Bar */}
        <div className="mt-4">
          <Input
            type="text"
            placeholder="Search players..."
            value={searchQuery}
            onChange={(e) => handleSearch(e.target.value)}
            leftIcon={
              <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            }
          />
        </div>
      </div>

      {/* Leaderboard Table */}
      <LeaderboardTable
        players={players}
        loading={loading}
        error={error}
        className="w-full"
      />

      {/* Pagination */}
      {totalPages > 1 && (
        <div className="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 rounded-lg shadow">
          <div className="flex-1 flex justify-between sm:hidden">
            <Button
              onClick={() => handlePageChange(currentPage - 1)}
              disabled={!hasPrevPage || loading}
              variant="outline"
            >
              Previous
            </Button>
            <Button
              onClick={() => handlePageChange(currentPage + 1)}
              disabled={!hasNextPage || loading}
              variant="outline"
            >
              Next
            </Button>
          </div>
          
          <div className="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p className="text-sm text-gray-700">
                Page <span className="font-medium">{currentPage}</span> of{' '}
                <span className="font-medium">{totalPages}</span>
              </p>
            </div>
            
            <div>
              <nav className="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                <button
                  onClick={() => handlePageChange(currentPage - 1)}
                  disabled={!hasPrevPage || loading}
                  className="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span className="sr-only">Previous</span>
                  <svg className="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fillRule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clipRule="evenodd" />
                  </svg>
                </button>
                
                {/* Page numbers */}
                {[...Array(Math.min(5, totalPages))].map((_, index) => {
                  let pageNum;
                  if (totalPages <= 5) {
                    pageNum = index + 1;
                  } else if (currentPage <= 3) {
                    pageNum = index + 1;
                  } else if (currentPage >= totalPages - 2) {
                    pageNum = totalPages - 4 + index;
                  } else {
                    pageNum = currentPage - 2 + index;
                  }
                  
                  return (
                    <button
                      key={pageNum}
                      onClick={() => handlePageChange(pageNum)}
                      disabled={loading}
                      className={`relative inline-flex items-center px-4 py-2 border text-sm font-medium ${
                        pageNum === currentPage
                          ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                          : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                      }`}
                    >
                      {pageNum}
                    </button>
                  );
                })}
                
                <button
                  onClick={() => handlePageChange(currentPage + 1)}
                  disabled={!hasNextPage || loading}
                  className="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span className="sr-only">Next</span>
                  <svg className="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fillRule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clipRule="evenodd" />
                  </svg>
                </button>
              </nav>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default LeaderboardContainer;