import React, { useState, useEffect, useMemo } from 'react';
import { useNavigate } from 'react-router-dom';
import { useLeaderboardStore } from '../../store/slices/leaderboardSlice';
import Input from '../presentational/Common/Input';
import Button from '../presentational/Common/Button';
import LeaderboardTable from '../presentational/Leaderboard/LeaderboardTable';
import type { LeaderboardEntry } from '../../types/domain.types';

interface SearchContainerProps {
  className?: string;
}

const SearchContainer: React.FC<SearchContainerProps> = ({ className }) => {
  const navigate = useNavigate();
  const {
    searchQuery,
    searchResults,
    searchLoading,
    searchError,
    searchPlayers,
    clearSearch,
    setSearchQuery,
  } = useLeaderboardStore();

  const [localQuery, setLocalQuery] = useState(searchQuery);
  const [debouncedQuery, setDebouncedQuery] = useState('');

  // Debounce search query
  useEffect(() => {
    const timer = setTimeout(() => {
      setDebouncedQuery(localQuery);
    }, 500);

    return () => clearTimeout(timer);
  }, [localQuery]);

  // Perform search when debounced query changes
  useEffect(() => {
    if (debouncedQuery.trim()) {
      searchPlayers(debouncedQuery);
    } else {
      clearSearch();
    }
  }, [debouncedQuery, searchPlayers, clearSearch]);

  // Sync local query with store query
  useEffect(() => {
    setLocalQuery(searchQuery);
  }, [searchQuery]);

  const handleInputChange = (value: string) => {
    setLocalQuery(value);
    setSearchQuery(value);
  };

  const handleClearSearch = () => {
    setLocalQuery('');
    setSearchQuery('');
    clearSearch();
  };

  const handlePlayerClick = (player: LeaderboardEntry) => {
    navigate(`/player/${player.playerId}`);
  };

  const hasSearched = debouncedQuery.trim().length > 0;
  const hasResults = searchResults.length > 0;

  const noResultsMessage = useMemo(() => {
    if (!hasSearched) {
      return 'Enter a player name to search';
    }
    if (searchLoading) {
      return 'Searching players...';
    }
    if (searchError) {
      return searchError;
    }
    if (!hasResults) {
      return `No players found for "${debouncedQuery}"`;
    }
    return null;
  }, [hasSearched, searchLoading, searchError, hasResults, debouncedQuery]);

  return (
    <div className={`space-y-6 ${className || ''}`}>
      {/* Search Header */}
      <div className="bg-white rounded-lg shadow p-6">
        <div className="max-w-2xl mx-auto">
          <h1 className="text-2xl font-bold text-gray-900 text-center mb-6">
            Search Players
          </h1>
          
          <div className="relative">
            <Input
              type="text"
              placeholder="Search by player name..."
              value={localQuery}
              onChange={(e) => handleInputChange(e.target.value)}
              leftIcon={
                <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              }
              rightIcon={
                localQuery && (
                  <button
                    onClick={handleClearSearch}
                    className="text-gray-400 hover:text-gray-600"
                  >
                    <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                )
              }
              className="text-lg py-3"
            />
            
            {searchLoading && (
              <div className="absolute right-3 top-1/2 transform -translate-y-1/2">
                <svg className="animate-spin h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24">
                  <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                  <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </div>
            )}
          </div>
        </div>
      </div>

      {/* Search Results */}
      {hasSearched && (
        <div className="bg-white rounded-lg shadow">
          <div className="px-6 py-4 border-b border-gray-200">
            <div className="flex items-center justify-between">
              <h2 className="text-lg font-medium text-gray-900">
                Search Results
              </h2>
              {hasResults && (
                <span className="text-sm text-gray-500">
                  Found {searchResults.length} players
                </span>
              )}
            </div>
          </div>

          <div className="p-6">
            {noResultsMessage ? (
              <div className="text-center py-12">
                {searchLoading ? (
                  <div className="flex flex-col items-center">
                    <svg className="animate-spin h-8 w-8 text-blue-500 mb-4" fill="none" viewBox="0 0 24 24">
                      <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                      <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p className="text-gray-500">{noResultsMessage}</p>
                  </div>
                ) : (
                  <div className="flex flex-col items-center">
                    <svg className="h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <p className="text-gray-500">{noResultsMessage}</p>
                    {hasSearched && !searchLoading && !searchError && (
                      <Button
                        onClick={() => searchPlayers(debouncedQuery)}
                        variant="outline"
                        size="sm"
                        className="mt-4"
                      >
                        Try Again
                      </Button>
                    )}
                  </div>
                )}
              </div>
            ) : (
              <LeaderboardTable
                players={searchResults}
                loading={searchLoading}
                error={searchError}
                className="w-full"
              />
            )}
          </div>
        </div>
      )}

      {/* Popular Searches / Suggestions */}
      {!hasSearched && (
        <div className="bg-white rounded-lg shadow p-6">
          <h2 className="text-lg font-medium text-gray-900 mb-4">
            Popular Searches
          </h2>
          <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
            {[
              'Legendary',
              'Pro',
              'MVP',
              'Gamer',
              'Player',
              'Star',
              'Brawl',
              'Hero'
            ].map((suggestion) => (
              <button
                key={suggestion}
                onClick={() => handleInputChange(suggestion)}
                className="text-left p-3 rounded-lg border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-colors"
              >
                <span className="text-sm text-gray-700">{suggestion}</span>
              </button>
            ))}
          </div>
        </div>
      )}
    </div>
  );
};

export default SearchContainer;