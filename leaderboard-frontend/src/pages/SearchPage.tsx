import React, { useState, useEffect } from 'react';
import { useLeaderboard } from '../../store/store';
import Button from '../presentational/Common/Button';
import Input from '../presentational/Common/Input';

const SearchPage: React.FC = () => {
  const { 
    searchResults, 
    searchLoading, 
    searchError, 
    searchPlayers, 
    clearSearch 
  } = useLeaderboard();
  
  const [query, setQuery] = useState('');
  const [debouncedQuery, setDebouncedQuery] = useState('');
  const [searchTimeout, setSearchTimeout] = useState<NodeJS.Timeout | null>(null);

  // Debounce search query
  useEffect(() => {
    if (searchTimeout) {
      clearTimeout(searchTimeout);
    }

    const timeout = setTimeout(() => {
      setDebouncedQuery(query);
    }, 300); // 300ms debounce

    setSearchTimeout(timeout);

    return () => {
      if (timeout) {
        clearTimeout(timeout);
      }
    };
  }, [query, 300]);

  // Perform search when debounced query changes
  useEffect(() => {
    if (debouncedQuery.trim()) {
      searchPlayers(debouncedQuery.trim());
    } else {
      clearSearch();
    }
  }, [debouncedQuery, searchPlayers, clearSearch]);

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setQuery(e.target.value);
  };

  const handleClearSearch = () => {
    setQuery('');
    setDebouncedQuery('');
    clearSearch();
  };

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    if (query.trim()) {
      searchPlayers(query.trim());
    }
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Header */}
        <div className="text-center mb-8">
          <h1 className="text-4xl font-bold text-gray-900 mb-4">
            Search Players
          </h1>
          <p className="text-lg text-gray-600 max-w-2xl mx-auto">
            Find your friends or competitors by their nickname
          </p>
        </div>

        {/* Search Form */}
        <div className="max-w-2xl mx-auto mb-8">
          <form onSubmit={handleSearch} className="space-y-4">
            <div className="relative">
              <Input
                type="text"
                value={query}
                onChange={handleInputChange}
                placeholder="Enter player nickname..."
                leftIcon={
                  <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                }
                rightIcon={
                  query && (
                    <button
                      type="button"
                      onClick={handleClearSearch}
                      className="text-gray-400 hover:text-gray-600 focus:outline-none"
                    >
                      <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  )
                }
                disabled={searchLoading}
              />
            </div>
            
            <Button
              type="submit"
              loading={searchLoading}
              disabled={!query.trim() || searchLoading}
              className="w-full"
            >
              {searchLoading ? 'Searching...' : 'Search Players'}
            </Button>
          </form>
        </div>

        {/* Search Results */}
        <div className="max-w-4xl mx-auto">
          {searchError && (
            <div className="mb-6 rounded-md bg-red-50 p-4">
              <div className="flex">
                <svg className="h-5 w-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
                </svg>
                <span className="text-red-800 font-medium">Search failed</span>
              </div>
              <p className="mt-2 text-red-700 text-sm">{searchError}</p>
            </div>
          )}

          {searchLoading && (
            <div className="text-center py-12">
              <div className="inline-flex items-center">
                <svg className="animate-spin -ml-1 mr-3 h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                  <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span className="text-lg text-gray-600">Searching for players...</span>
              </div>
            </div>
          )}

          {!searchLoading && !searchError && debouncedQuery && searchResults.length === 0 && (
            <div className="text-center py-12">
              <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
              <h3 className="mt-4 text-lg font-medium text-gray-900">No players found</h3>
              <p className="mt-2 text-gray-500">
                We couldn't find any players matching "{debouncedQuery}".
              </p>
              <div className="mt-6">
                <Button variant="outline" onClick={handleClearSearch}>
                  Clear search
                </Button>
              </div>
            </div>
          )}

          {!searchLoading && !searchError && searchResults.length > 0 && (
            <div>
              <div className="mb-6">
                <h2 className="text-lg font-medium text-gray-900">
                  Search Results ({searchResults.length} players found)
                </h2>
                {debouncedQuery && (
                  <p className="text-sm text-gray-600 mt-1">
                    Showing results for "{debouncedQuery}"
                  </p>
                )}
              </div>

              <div className="bg-white shadow overflow-hidden sm:rounded-md">
                <ul className="divide-y divide-gray-200">
                  {searchResults.map((player) => (
                    <li key={player.playerId}>
                      <div className="px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <div className="flex items-center space-x-4">
                          {/* Rank Badge */}
                          <div className="flex-shrink-0">
                            <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-300">
                              #{player.rank}
                            </span>
                          </div>

                          {/* Player Avatar */}
                          <div className="flex-shrink-0">
                            <div className="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                              <span className="text-white font-medium">
                                {player.nickname.charAt(0).toUpperCase()}
                              </span>
                            </div>
                          </div>

                          {/* Player Info */}
                          <div>
                            <div className="text-sm font-medium text-gray-900">
                              {player.nickname}
                            </div>
                            <div className="text-sm text-gray-500">
                              Level {player.level} • {player.region}
                            </div>
                          </div>
                        </div>

                        {/* Trophies */}
                        <div className="flex items-center">
                          <svg className="h-4 w-4 text-yellow-400 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                          </svg>
                          <span className="text-sm font-medium text-gray-900">
                            {player.totalTrophies.toLocaleString()}
                          </span>
                        </div>
                      </div>
                    </li>
                  ))}
                </ul>
              </div>
            </div>
          )}

          {!debouncedQuery && !searchLoading && (
            <div className="text-center py-12">
              <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
              <h3 className="mt-4 text-lg font-medium text-gray-900">Start searching</h3>
              <p className="mt-2 text-gray-500">
                Enter a player nickname above to search for players in the leaderboard.
              </p>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default SearchPage;