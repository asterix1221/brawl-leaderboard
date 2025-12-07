import { useState } from 'react';
import leaderboardService from '../services/api/leaderboardService';
import { usePlayerStore } from '../store/slices/playerSlice';
import { useAuthStore } from '../store/slices/authSlice';

export const usePlayer = () => {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  
  const {
    selectedPlayer,
    stats,
    history,
    loading: storeLoading,
    error: storeError,
    setSelectedPlayer,
    setStats,
    setHistory,
    setLoading: setStoreLoading,
    setError: setStoreError,
    clearPlayer,
  } = usePlayerStore();

  const { isAuthenticated } = useAuthStore();

  const fetchPlayer = async (playerId: string) => {
    setLoading(true);
    setError(null);
    setStoreError(null);
    
    try {
      const response = await leaderboardService.getPlayerProfile(playerId);
      
      if (response.success && response.data) {
        setSelectedPlayer(response.data);
        return response.data;
      } else {
        const errorMessage = response.error || 'Failed to fetch player profile';
        setError(errorMessage);
        setStoreError(errorMessage);
        throw new Error(errorMessage);
      }
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : 'An error occurred while fetching player profile';
      setError(errorMessage);
      setStoreError(errorMessage);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const linkBrawlStars = async (brawlStarsPlayerId: string) => {
    if (!isAuthenticated) {
      const errorMessage = 'You must be logged in to link your account';
      setError(errorMessage);
      setStoreError(errorMessage);
      throw new Error(errorMessage);
    }

    setLoading(true);
    setError(null);
    setStoreError(null);
    
    try {
      const response = await leaderboardService.linkBrawlStarsAccount(brawlStarsPlayerId);
      
      if (response.success && response.data) {
        // Refresh current user profile
        await fetchMyProfile();
        return response.data;
      } else {
        const errorMessage = response.error || 'Failed to link Brawl Stars account';
        setError(errorMessage);
        setStoreError(errorMessage);
        throw new Error(errorMessage);
      }
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : 'An error occurred while linking account';
      setError(errorMessage);
      setStoreError(errorMessage);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const fetchMyProfile = async () => {
    if (!isAuthenticated) {
      const errorMessage = 'You must be logged in to view your profile';
      setError(errorMessage);
      setStoreError(errorMessage);
      throw new Error(errorMessage);
    }

    setLoading(true);
    setError(null);
    setStoreError(null);
    
    try {
      const response = await leaderboardService.getMyProfile();
      
      if (response.success && response.data) {
        setSelectedPlayer(response.data);
        return response.data;
      } else {
        const errorMessage = response.error || 'Failed to fetch your profile';
        setError(errorMessage);
        setStoreError(errorMessage);
        throw new Error(errorMessage);
      }
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : 'An error occurred while fetching your profile';
      setError(errorMessage);
      setStoreError(errorMessage);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const fetchMyStats = async () => {
    if (!isAuthenticated) {
      const errorMessage = 'You must be logged in to view your stats';
      setError(errorMessage);
      setStoreError(errorMessage);
      throw new Error(errorMessage);
    }

    setLoading(true);
    setError(null);
    setStoreError(null);
    
    try {
      const response = await leaderboardService.getMyStats();
      
      if (response.success && response.data) {
        setStats(response.data);
        return response.data;
      } else {
        const errorMessage = response.error || 'Failed to fetch your stats';
        setError(errorMessage);
        setStoreError(errorMessage);
        throw new Error(errorMessage);
      }
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : 'An error occurred while fetching your stats';
      setError(errorMessage);
      setStoreError(errorMessage);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const fetchMyHistory = async (params?: { limit?: number; offset?: number }) => {
    if (!isAuthenticated) {
      const errorMessage = 'You must be logged in to view your history';
      setError(errorMessage);
      setStoreError(errorMessage);
      throw new Error(errorMessage);
    }

    setLoading(true);
    setError(null);
    setStoreError(null);
    
    try {
      const response = await leaderboardService.getMyHistory(params);
      
      if (response.success && response.data) {
        setHistory(response.data);
        return response.data;
      } else {
        const errorMessage = response.error || 'Failed to fetch your history';
        setError(errorMessage);
        setStoreError(errorMessage);
        throw new Error(errorMessage);
      }
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : 'An error occurred while fetching your history';
      setError(errorMessage);
      setStoreError(errorMessage);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const refreshProfile = async (playerId?: string) => {
    if (playerId) {
      return fetchPlayer(playerId);
    } else if (isAuthenticated) {
      return fetchMyProfile();
    } else {
      throw new Error('No player ID provided and user not authenticated');
    }
  };

  return {
    // State
    selectedPlayer,
    stats,
    history,
    loading: loading || storeLoading,
    error: error || storeError,
    
    // Actions
    fetchPlayer,
    linkBrawlStars,
    fetchMyProfile,
    fetchMyStats,
    fetchMyHistory,
    refreshProfile,
    setError,
    setStoreError,
    clearPlayer,
  };
};