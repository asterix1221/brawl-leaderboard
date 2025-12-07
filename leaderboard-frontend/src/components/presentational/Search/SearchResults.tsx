import React from 'react';
import type { LeaderboardEntry } from '../../../types/domain.types';
import { cn } from '../../../utils/cn';

interface SearchResultsProps {
  results: LeaderboardEntry[];
  isLoading?: boolean;
  error?: string | null;
  query?: string;
  onPlayerClick?: (player: LeaderboardEntry) => void;
  className?: string;
}

const SearchResults: React.FC<SearchResultsProps> = ({
  results,
  isLoading = false,
  error = null,
  query = '',
  onPlayerClick,
  className
}) => {
  const highlightText = (text: string, query: string) => {
    if (!query.trim()) return text;
    
    const regex = new RegExp(`(${query.trim().replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
    const parts = text.split(regex);
    
    return parts.map((part, index) => 
      regex.test(part) ? (
        <mark key={index} className="bg-yellow-200 px-1 rounded">
          {part}
        </mark>
      ) : (
        part
      )
    );
  };

  const getRankBadgeClass = (rank: number) => {
    if (rank === 1) return 'bg-yellow-100 text-yellow-800 border-yellow-300';
    if (rank <= 3) return 'bg-orange-100 text-orange-800 border-orange-300';
    if (rank <= 10) return 'bg-purple-100 text-purple-800 border-purple-300';
    return 'bg-gray-100 text-gray-800 border-gray-300';
  };

  const getRankIcon = (rank: number) => {
    if (rank === 1) return '🥇';
    if (rank === 2) return '🥈';
    if (rank === 3) return '🥉';
    return null;
  };

  // Loading state
  if (isLoading) {
    return (
      <div className={cn('w-full', className)}>
        <div className="space-y-3">
          {[...Array(5)].map((_, index) => (
            <div key={index} className="animate-pulse">
              <div className="flex items-center space-x-4 p-4 bg-white rounded-lg border border-gray-200">
                <div className="h-12 w-12 bg-gray-200 rounded-full"></div>
                <div className="flex-1 space-y-2">
                  <div className="h-4 bg-gray-200 rounded w-1/3"></div>
                  <div className="h-3 bg-gray-200 rounded w-1/4"></div>
                </div>
                <div className="text-right">
                  <div className="h-4 bg-gray-200 rounded w-16"></div>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>
    );
  }

  // Error state
  if (error) {
    return (
      <div className={cn('w-full', className)}>
        <div className="rounded-lg border border-red-200 bg-red-50 p-6 text-center">
          <svg className="mx-auto h-12 w-12 text-red-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
          </svg>
          <h3 className="text-lg font-medium text-red-800 mb-2">Search Error</h3>
          <p className="text-red-700">{error}</p>
          <button 
            onClick={() => window.location.reload()}
            className="mt-4 px-4 py-2 bg-red-100 text-red-800 rounded-md hover:bg-red-200 transition-colors"
          >
            Try Again
          </button>
        </div>
      </div>
    );
  }

  // No results state
  if (!isLoading && !error && results.length === 0 && query) {
    return (
      <div className={cn('w-full', className)}>
        <div className="text-center py-12">
          <svg className="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <h3 className="text-lg font-medium text-gray-900 mb-2">No players found</h3>
          <p className="text-gray-500 mb-4">
            No players match your search for "<span className="font-medium">{query}</span>"
          </p>
          <div className="text-sm text-gray-400">
            <p>Try:</p>
            <ul className="mt-2 space-y-1">
              <li>• Checking your spelling</li>
              <li>• Using different keywords</li>
              <li>• Searching for partial names</li>
            </ul>
          </div>
        </div>
      </div>
    );
  }

  // No search query state
  if (!isLoading && !error && results.length === 0 && !query) {
    return (
      <div className={cn('w-full', className)}>
        <div className="text-center py-12">
          <svg className="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <h3 className="text-lg font-medium text-gray-900 mb-2">Start searching</h3>
          <p className="text-gray-500">
            Enter a player nickname above to find players in the leaderboard.
          </p>
        </div>
      </div>
    );
  }

  // Results list
  return (
    <div className={cn('w-full', className)}>
      {/* Results header */}
      {query && (
        <div className="mb-4">
          <h3 className="text-lg font-medium text-gray-900">
            Search results for "{query}"
          </h3>
          <p className="text-sm text-gray-500">
            Found {results.length} player{results.length !== 1 ? 's' : ''}
          </p>
        </div>
      )}

      {/* Results list */}
      <div className="space-y-3">
        {results.map((player) => (
          <div
            key={player.playerId}
            onClick={() => onPlayerClick?.(player)}
            className={cn(
              'flex items-center space-x-4 p-4 bg-white rounded-lg border border-gray-200 transition-all duration-200',
              onPlayerClick && 'cursor-pointer hover:shadow-md hover:border-blue-300 hover:bg-blue-50'
            )}
          >
            {/* Rank */}
            <div className="flex-shrink-0">
              <span className={cn(
                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border',
                getRankBadgeClass(player.rank)
              )}>
                {getRankIcon(player.rank) && (
                  <span className="mr-1">{getRankIcon(player.rank)}</span>
                )}
                #{player.rank}
              </span>
            </div>

            {/* Player avatar and info */}
            <div className="flex items-center flex-1 min-w-0">
              <div className="h-12 w-12 flex-shrink-0">
                <div className="h-12 w-12 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                  <span className="text-white font-medium">
                    {player.nickname.charAt(0).toUpperCase()}
                  </span>
                </div>
              </div>
              <div className="ml-4 flex-1 min-w-0">
                <div className="text-sm font-semibold text-gray-900 truncate">
                  {highlightText(player.nickname, query)}
                </div>
                <div className="text-sm text-gray-500 flex items-center space-x-2">
                  <span>{player.region}</span>
                  <span>•</span>
                  <span>Level {player.level}</span>
                </div>
              </div>
            </div>

            {/* Trophies */}
            <div className="flex items-center flex-shrink-0">
              <svg className="h-4 w-4 text-yellow-400 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
              <span className="text-sm font-medium text-gray-900">
                {player.totalTrophies.toLocaleString()}
              </span>
            </div>

            {/* Arrow indicator for clickable items */}
            {onPlayerClick && (
              <div className="flex-shrink-0">
                <svg className="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                </svg>
              </div>
            )}
          </div>
        ))}
      </div>
    </div>
  );
};

export default SearchResults;