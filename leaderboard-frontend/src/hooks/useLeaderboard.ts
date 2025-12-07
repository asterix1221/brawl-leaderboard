import { useState, useEffect } from 'react';
import leaderboardService from '../services/api/leaderboardService';
import { useLeaderboardStore } from '../store/slices/leaderboardSlice';
import type { LeaderboardEntry } from '../types/domain.types';

export const useLeaderboard = () => {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  
  const {
    players,
    total,
    page,
    region,
    loading: storeLoading,
    error: storeError,
    setPlayers,
    setPage,
    setRegion,
    setError: setStoreError
  } = useLeaderboardStore();

  const fetchLeaderboard = async (params?: {
    limit?: number;
    region?: string;
    page?: number;
  }) => {
    setLoading(true);
    setError(null);
    setStoreError(null);
    
    try {
      const response = await leaderboardService.getGlobalLeaderboard({
        limit: params?.limit,
        offset: params?.page ? (params.page - 1) * (params?.limit || 20) : 0,
        region: params?.region
      });
      
      if (response.success && response.data) {
        setPlayers(response.data.entries || [], response.data.total || 0);
        return response.data;
      } else {
        const errorMessage = response.error || 'Failed to fetch leaderboard';
        setError(errorMessage);
        setStoreError(errorMessage);
        throw new Error(errorMessage);
      }
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : 'An error occurred while fetching leaderboard';
      setError(errorMessage);
      setStoreError(errorMessage);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const changeRegion = async (newRegion: string) => {
    setRegion(newRegion);
    setPage(1);
    
    // Fetch with new region
    try {
      await fetchLeaderboard({ 
        region: newRegion,
        limit: 20,
        page: 1 
      });
    } catch (err) {
      // Error is already handled in fetchLeaderboard
    }
  };

  const changePage = async (newPage: number) => {
    setPage(newPage);
    
    try {
      await fetchLeaderboard({ 
        region,
        limit: 20,
        page: newPage 
      });
    } catch (err) {
      // Error is already handled in fetchLeaderboard
    }
  };

  const refresh = async () => {
    try {
      await fetchLeaderboard({ 
        region,
        limit: 20,
        page 
      });
    } catch (err) {
      // Error is already handled in fetchLeaderboard
    }
  };

  // Auto-fetch on mount
  useEffect(() => {
    if (players.length === 0 && !loading && !storeLoading) {
      fetchLeaderboard({ 
        region,
        limit: 20,
        page 
      }).catch(() => {
        // Error is already handled in fetchLeaderboard
      });
    }
  }, []); // eslint-disable-line react-hooks/exhaustive-deps

  return {
    // State
    players,
    total,
    page,
    region,
    loading: loading || storeLoading,
    error: error || storeError,
    
    // Actions
    fetchLeaderboard,
    changeRegion,
    changePage,
    refresh,
    setError,
    setStoreError,
  };
};