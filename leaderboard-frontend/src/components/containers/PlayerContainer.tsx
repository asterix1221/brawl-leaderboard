import React, { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { usePlayer } from '../../hooks/usePlayer';
import { useAuthStore } from '../../store/slices/authSlice';
import Button from '../presentational/Common/Button';
import Input from '../presentational/Common/Input';
import type { Player, PlayerStats, ScoreHistory } from '../../types/domain.types';

interface PlayerContainerProps {
  className?: string;
}

const PlayerContainer: React.FC<PlayerContainerProps> = ({ className }) => {
  const { playerId } = useParams<{ playerId: string }>();
  const navigate = useNavigate();
  const { isAuthenticated } = useAuthStore();
  const {
    selectedPlayer,
    stats,
    history,
    loading,
    error,
    fetchPlayer,
    fetchMyProfile,
    fetchMyStats,
    fetchMyHistory,
    linkBrawlStars,
    refreshProfile,
  } = usePlayer();

  const [activeTab, setActiveTab] = useState<'profile' | 'stats' | 'history'>('profile');
  const [isLinking, setIsLinking] = useState(false);
  const [brawlStarsId, setBrawlStarsId] = useState('');
  const [showLinkForm, setShowLinkForm] = useState(false);

  // Load player profile
  useEffect(() => {
    if (playerId) {
      fetchPlayer(playerId);
    } else if (isAuthenticated) {
      // Load current user's profile
      fetchMyProfile();
      fetchMyStats();
      fetchMyHistory();
    }
  }, [playerId, isAuthenticated]);

  // Load additional data when profile loads
  useEffect(() => {
    if (selectedPlayer && !playerId) {
      // This is the current user's profile
      fetchMyStats();
      fetchMyHistory();
    }
  }, [selectedPlayer, playerId]);

  const handleLinkBrawlStars = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!brawlStarsId.trim()) {
      return;
    }

    setIsLinking(true);
    try {
      await linkBrawlStars(brawlStarsId.trim());
      setBrawlStarsId('');
      setShowLinkForm(false);
      // Refresh profile to show linked account
      await refreshProfile();
    } catch (err) {
      // Error is handled by the hook
    } finally {
      setIsLinking(false);
    }
  };

  const handleRefresh = async () => {
    await refreshProfile(playerId);
  };

  if (loading && !selectedPlayer) {
    return (
      <div className={`space-y-6 ${className || ''}`}>
        <div className="bg-white rounded-lg shadow p-6">
          <div className="animate-pulse">
            <div className="flex items-center space-x-4">
              <div className="h-16 w-16 bg-gray-200 rounded-full"></div>
              <div className="flex-1 space-y-2">
                <div className="h-4 bg-gray-200 rounded w-1/4"></div>
                <div className="h-3 bg-gray-200 rounded w-1/2"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className={`space-y-6 ${className || ''}`}>
        <div className="bg-red-50 border border-red-200 rounded-lg p-6">
          <div className="flex">
            <svg className="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
              <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
            </svg>
            <div className="ml-3">
              <h3 className="text-sm font-medium text-red-800">Error loading player profile</h3>
              <p className="mt-2 text-sm text-red-700">{error}</p>
              <div className="mt-4">
                <Button onClick={handleRefresh} variant="outline" size="sm">
                  Try Again
                </Button>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }

  if (!selectedPlayer) {
    return (
      <div className={`space-y-6 ${className || ''}`}>
        <div className="bg-white rounded-lg shadow p-6 text-center">
          <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          <h3 className="mt-4 text-lg font-medium text-gray-900">Player not found</h3>
          <p className="mt-2 text-gray-500">The player you're looking for doesn't exist.</p>
          <div className="mt-6">
            <Button onClick={() => navigate('/leaderboard')} variant="outline">
              Back to Leaderboard
            </Button>
          </div>
        </div>
      </div>
    );
  }

  const isOwnProfile = !playerId;
  const canLinkAccount = isAuthenticated;

  return (
    <div className={`space-y-6 ${className || ''}`}>
      {/* Player Header */}
      <div className="bg-white rounded-lg shadow p-6">
        <div className="flex items-start justify-between">
          <div className="flex items-center space-x-4">
            <div className="h-16 w-16 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
              <span className="text-white text-xl font-bold">
                {selectedPlayer.nickname.charAt(0).toUpperCase()}
              </span>
            </div>
            <div>
              <h1 className="text-2xl font-bold text-gray-900">{selectedPlayer.nickname}</h1>
              <div className="flex items-center space-x-4 mt-1">
                <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                  {selectedPlayer.region}
                </span>
                <span className="text-sm text-gray-600">
                  Level {selectedPlayer.level}
                </span>
                <span className="flex items-center text-sm text-gray-600">
                  <svg className="h-4 w-4 text-yellow-400 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 1.118L00-.364-2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                  </svg>
                  {selectedPlayer.totalTrophies.toLocaleString()}
                </span>
              </div>
            </div>
          </div>
          
          <div className="flex items-center space-x-3">
            <Button onClick={handleRefresh} variant="outline" size="sm" loading={loading}>
              <svg className="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              Refresh
            </Button>
            
            {isOwnProfile && (
              <Button 
                onClick={() => setShowLinkForm(!showLinkForm)} 
                variant="outline" 
                size="sm"
              >
                Link Brawl Stars
              </Button>
            )}
          </div>
        </div>

        {/* Brawl Stars Link Form */}
        {showLinkForm && (
          <div className="mt-6 border-t pt-6">
            <h3 className="text-lg font-medium text-gray-900 mb-4">
              Link Brawl Stars Account
            </h3>
            
            <form onSubmit={handleLinkBrawlStars} className="space-y-4">
              <Input
                type="text"
                label="Brawl Stars Player ID"
                value={brawlStarsId}
                onChange={(e) => setBrawlStarsId(e.target.value)}
                placeholder="Enter your Brawl Stars player ID"
                helperText="Find your player ID in the Brawl Stars app under Settings > Support"
                required
              />
              
              <div className="flex space-x-3">
                <Button type="submit" loading={isLinking} disabled={isLinking}>
                  Link Account
                </Button>
                <Button 
                  type="button" 
                  variant="outline" 
                  onClick={() => setShowLinkForm(false)}
                  disabled={isLinking}
                >
                  Cancel
                </Button>
              </div>
            </form>
          </div>
        )}
      </div>

      {/* Tabs */}
      <div className="bg-white rounded-lg shadow">
        <div className="border-b border-gray-200">
          <nav className="-mb-px flex space-x-8 px-6" aria-label="Tabs">
            {[
              { id: 'profile', name: 'Profile', count: null },
              { id: 'stats', name: 'Statistics', count: null },
              { id: 'history', name: 'History', count: history.length },
            ].map((tab) => (
              <button
                key={tab.id}
                onClick={() => setActiveTab(tab.id as any)}
                className={`${
                  activeTab === tab.id
                    ? 'border-blue-500 text-blue-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                } whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm`}
              >
                {tab.name}
                {tab.count !== null && (
                  <span className="ml-2 bg-gray-100 text-gray-900 py-0.5 px-2.5 rounded-full text-xs">
                    {tab.count}
                  </span>
                )}
              </button>
            ))}
          </nav>
        </div>

        <div className="p-6">
          {activeTab === 'profile' && (
            <div className="space-y-4">
              <h3 className="text-lg font-medium text-gray-900">Player Profile</h3>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <dt className="text-sm font-medium text-gray-500">Player ID</dt>
                  <dd className="mt-1 text-sm text-gray-900">{selectedPlayer.id}</dd>
                </div>
                <div>
                  <dt className="text-sm font-medium text-gray-500">Nickname</dt>
                  <dd className="mt-1 text-sm text-gray-900">{selectedPlayer.nickname}</dd>
                </div>
                <div>
                  <dt className="text-sm font-medium text-gray-500">Region</dt>
                  <dd className="mt-1 text-sm text-gray-900">{selectedPlayer.region}</dd>
                </div>
                <div>
                  <dt className="text-sm font-medium text-gray-500">Level</dt>
                  <dd className="mt-1 text-sm text-gray-900">{selectedPlayer.level}</dd>
                </div>
                <div>
                  <dt className="text-sm font-medium text-gray-500">Total Trophies</dt>
                  <dd className="mt-1 text-sm text-gray-900">{selectedPlayer.totalTrophies.toLocaleString()}</dd>
                </div>
                {selectedPlayer.createdAt && (
                  <div>
                    <dt className="text-sm font-medium text-gray-500">Member Since</dt>
                    <dd className="mt-1 text-sm text-gray-900">
                      {new Date(selectedPlayer.createdAt).toLocaleDateString()}
                    </dd>
                  </div>
                )}
              </div>
            </div>
          )}

          {activeTab === 'stats' && (
            <div className="space-y-4">
              <h3 className="text-lg font-medium text-gray-900">Player Statistics</h3>
              {stats ? (
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div className="bg-blue-50 rounded-lg p-4">
                    <dt className="text-sm font-medium text-blue-600">Total Wins</dt>
                    <dd className="mt-1 text-2xl font-semibold text-blue-900">{stats.wins}</dd>
                  </div>
                  <div className="bg-red-50 rounded-lg p-4">
                    <dt className="text-sm font-medium text-red-600">Total Losses</dt>
                    <dd className="mt-1 text-2xl font-semibold text-red-900">{stats.losses}</dd>
                  </div>
                  <div className="bg-green-50 rounded-lg p-4">
                    <dt className="text-sm font-medium text-green-600">Win Rate</dt>
                    <dd className="mt-1 text-2xl font-semibold text-green-900">{stats.winRate}%</dd>
                  </div>
                </div>
              ) : (
                <p className="text-gray-500">No statistics available.</p>
              )}
            </div>
          )}

          {activeTab === 'history' && (
            <div className="space-y-4">
              <h3 className="text-lg font-medium text-gray-900">Score History</h3>
              {history.length > 0 ? (
                <div className="space-y-3">
                  {history.map((entry) => (
                    <div key={entry.id} className="border rounded-lg p-4">
                      <div className="flex items-center justify-between">
                        <div className="flex items-center space-x-3">
                          <div className="flex items-center">
                            <span className="text-sm text-gray-600">
                              {entry.oldScore} → {entry.newScore}
                            </span>
                            <svg className="h-4 w-4 text-green-500 ml-2" fill="currentColor" viewBox="0 0 20 20">
                              <path fillRule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clipRule="evenodd" />
                            </svg>
                          </div>
                        </div>
                        <span className="text-sm text-gray-500">
                          {new Date(entry.createdAt).toLocaleDateString()}
                        </span>
                      </div>
                      <p className="mt-1 text-sm text-gray-600">{entry.changeReason}</p>
                    </div>
                  ))}
                </div>
              ) : (
                <p className="text-gray-500">No score history available.</p>
              )}
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default PlayerContainer;