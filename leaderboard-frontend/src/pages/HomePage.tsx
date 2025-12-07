import React, { useEffect } from 'react';
import { useLeaderboard } from '../../store/store';
import LeaderboardTable from '../presentational/Leaderboard/LeaderboardTable';
import Button from '../presentational/Common/Button';

const HomePage: React.FC = () => {
  const { 
    players, 
    loading, 
    error, 
    fetchLeaderboard, 
    refreshLeaderboard,
    hasMore,
    fetchNextPage 
  } = useLeaderboard();

  useEffect(() => {
    // Fetch initial leaderboard data
    fetchLeaderboard({ limit: 100, offset: 0 });
  }, [fetchLeaderboard]);

  const handleRefresh = () => {
    refreshLeaderboard();
  };

  const handleLoadMore = () => {
    if (hasMore && !loading) {
      fetchNextPage();
    }
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Header */}
        <div className="text-center mb-8">
          <h1 className="text-4xl font-bold text-gray-900 mb-4">
            Brawl Stars Leaderboard
          </h1>
          <p className="text-lg text-gray-600 max-w-2xl mx-auto">
            Track your progress, compare with friends, and climb the ranks in the ultimate Brawl Stars competition.
          </p>
        </div>

        {/* Controls */}
        <div className="flex justify-center items-center space-x-4 mb-8">
          <Button 
            onClick={handleRefresh} 
            loading={loading}
            variant="outline"
          >
            <svg className="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Refresh
          </Button>
        </div>

        {/* Leaderboard Table */}
        <div className="mb-8">
          <LeaderboardTable 
            players={players}
            loading={loading}
            error={error}
          />
        </div>

        {/* Load More Button */}
        {hasMore && !loading && players.length > 0 && (
          <div className="text-center">
            <Button 
              onClick={handleLoadMore}
              variant="outline"
              size="lg"
            >
              Load More Players
            </Button>
          </div>
        )}

        {/* Loading indicator for load more */}
        {loading && players.length > 0 && (
          <div className="text-center py-4">
            <div className="inline-flex items-center">
              <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Loading more players...
            </div>
          </div>
        )}

        {/* Stats Section */}
        {players.length > 0 && !loading && !error && (
          <div className="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div className="bg-white overflow-hidden shadow rounded-lg">
              <div className="p-5">
                <div className="flex items-center">
                  <div className="flex-shrink-0">
                    <svg className="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                  </div>
                  <div className="ml-5 w-0 flex-1">
                    <dl>
                      <dt className="text-sm font-medium text-gray-500 truncate">Total Players</dt>
                      <dd className="text-lg font-medium text-gray-900">{players.length}</dd>
                    </dl>
                  </div>
                </div>
              </div>
            </div>

            <div className="bg-white overflow-hidden shadow rounded-lg">
              <div className="p-5">
                <div className="flex items-center">
                  <div className="flex-shrink-0">
                    <svg className="h-6 w-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                  </div>
                  <div className="ml-5 w-0 flex-1">
                    <dl>
                      <dt className="text-sm font-medium text-gray-500 truncate">Top Player</dt>
                      <dd className="text-lg font-medium text-gray-900">
                        {players[0]?.nickname || 'N/A'}
                      </dd>
                    </dl>
                  </div>
                </div>
              </div>
            </div>

            <div className="bg-white overflow-hidden shadow rounded-lg">
              <div className="p-5">
                <div className="flex items-center">
                  <div className="flex-shrink-0">
                    <svg className="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                  </div>
                  <div className="ml-5 w-0 flex-1">
                    <dl>
                      <dt className="text-sm font-medium text-gray-500 truncate">Highest Trophies</dt>
                      <dd className="text-lg font-medium text-gray-900">
                        {players.length > 0 
                          ? Math.max(...players.map(p => p.totalTrophies)).toLocaleString()
                          : '0'
                        }
                      </dd>
                    </dl>
                  </div>
                </div>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default HomePage;