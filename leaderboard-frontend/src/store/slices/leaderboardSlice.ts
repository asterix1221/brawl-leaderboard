import { create } from 'zustand';
import { LeaderboardEntry } from '../../types/domain.types';
import leaderboardService from '../../services/api/leaderboardService';

interface LeaderboardState {
  // State
  players: LeaderboardEntry[];
  total: number;
  loading: boolean;
  error: string | null;
  page: number;
  limit: number;
  region: string;
  hasMore: boolean;
  searchQuery: string;
  searchResults: LeaderboardEntry[];
  searchLoading: boolean;
  searchError: string | null;

  // Actions
  setPlayers: (players: LeaderboardEntry[], total: number) => void;
  setLoading: (loading: boolean) => void;
  setError: (error: string | null) => void;
  setPage: (page: number) => void;
  setLimit: (limit: number) => void;
  setRegion: (region: string) => void;
  setHasMore: (hasMore: boolean) => void;
  setSearchQuery: (query: string) => void;
  setSearchResults: (results: LeaderboardEntry[]) => void;
  setSearchLoading: (loading: boolean) => void;
  setSearchError: (error: string | null) => void;
  
  // Async actions
  fetchLeaderboard: (params?: { limit?: number; offset?: number; region?: string }) => Promise<void>;
  fetchNextPage: () => Promise<void>;
  searchPlayers: (query: string, limit?: number) => Promise<void>;
  clearSearch: () => void;
  refreshLeaderboard: () => Promise<void>;
}

export const useLeaderboardStore = create<LeaderboardState>((set, get) => ({
  // Initial state
  players: [],
  total: 0,
  loading: false,
  error: null,
  page: 0,
  limit: 100,
  region: '',
  hasMore: true,
  searchQuery: '',
  searchResults: [],
  searchLoading: false,
  searchError: null,

  // Actions
  setPlayers: (players, total) => set({ players, total }),
  setLoading: (loading) => set({ loading }),
  setError: (error) => set({ error }),
  setPage: (page) => set({ page }),
  setLimit: (limit) => set({ limit }),
  setRegion: (region) => set({ region }),
  setHasMore: (hasMore) => set({ hasMore }),
  setSearchQuery: (searchQuery) => set({ searchQuery }),
  setSearchResults: (searchResults) => set({ searchResults }),
  setSearchLoading: (searchLoading) => set({ searchLoading }),
  setSearchError: (searchError) => set({ searchError }),

  // Async actions
  fetchLeaderboard: async (params) => {
    const { limit, region, page } = get();
    const requestParams = {
      limit: params?.limit || limit,
      offset: params?.offset || (page * limit),
      region: params?.region || region,
    };

    set({ loading: true, error: null });

    try {
      const response = await leaderboardService.getGlobalLeaderboard(requestParams);

      if (response.success && response.data) {
        const { entries, total, hasMore } = response.data;
        
        // If this is the first page or new region, replace players
        // Otherwise, append (for infinite scroll)
        const currentPlayers = requestParams.offset === 0 ? [] : get().players;
        const newPlayers = requestParams.offset === 0 ? entries : [...currentPlayers, ...entries];

        set({
          players: newPlayers,
          total,
          hasMore,
          loading: false,
          error: null,
        });

        // Cache the results
        const cacheKey = `leaderboard_global_${JSON.stringify(requestParams)}`;
        leaderboardService.setCachedData(cacheKey, { entries, total, hasMore });
      } else {
        set({
          loading: false,
          error: response.error || 'Failed to fetch leaderboard',
        });
      }
    } catch (error: any) {
      set({
        loading: false,
        error: error.error || 'An unexpected error occurred',
      });
    }
  },

  fetchNextPage: async () => {
    const { page, limit, hasMore, region } = get();
    
    if (!hasMore || get().loading) return;

    const nextPage = page + 1;
    set({ page: nextPage });

    await get().fetchLeaderboard({
      offset: nextPage * limit,
      limit,
      region,
    });
  },

  searchPlayers: async (query, limit = 20) => {
    if (!query.trim()) {
      set({ searchResults: [], searchQuery: '' });
      return;
    }

    set({ 
      searchLoading: true, 
      searchError: null, 
      searchQuery: query 
    });

    try {
      const response = await leaderboardService.searchPlayers({ q: query, limit });

      if (response.success && response.data) {
        set({
          searchResults: response.data.players.map((player, index) => ({
            rank: index + 1,
            playerId: player.id,
            nickname: player.nickname,
            totalTrophies: player.totalTrophies,
            region: player.region,
            level: player.level,
          })),
          searchLoading: false,
        });
      } else {
        set({
          searchLoading: false,
          searchError: response.error || 'Search failed',
        });
      }
    } catch (error: any) {
      set({
        searchLoading: false,
        searchError: error.error || 'An unexpected error occurred during search',
      });
    }
  },

  clearSearch: () => {
    set({
      searchQuery: '',
      searchResults: [],
      searchError: null,
    });
  },

  refreshLeaderboard: async () => {
    const { limit, region } = get();
    
    // Clear cache
    leaderboardService.clearCachedData('leaderboard_global');
    
    // Reset to first page and fetch
    set({ page: 0 });
    await get().fetchLeaderboard({ limit, offset: 0, region });
  },
}));

// Selectors for common use cases
export const useLeaderboard = () => {
  const store = useLeaderboardStore();
  
  return {
    // State
    players: store.players,
    total: store.total,
    loading: store.loading,
    error: store.error,
    page: store.page,
    limit: store.limit,
    region: store.region,
    hasMore: store.hasMore,
    searchQuery: store.searchQuery,
    searchResults: store.searchResults,
    searchLoading: store.searchLoading,
    searchError: store.searchError,
    
    // Actions
    fetchLeaderboard: store.fetchLeaderboard,
    fetchNextPage: store.fetchNextPage,
    searchPlayers: store.searchPlayers,
    clearSearch: store.clearSearch,
    refreshLeaderboard: store.refreshLeaderboard,
    setPage: store.setPage,
    setLimit: store.setLimit,
    setRegion: store.setRegion,
    setPlayers: store.setPlayers,
    setLoading: store.setLoading,
    setError: store.setError,
  };
};

// Convenience selectors
export const useLeaderboardPlayers = () => useLeaderboardStore((state) => state.players);
export const useLeaderboardLoading = () => useLeaderboardStore((state) => state.loading);
export const useLeaderboardError = () => useLeaderboardStore((state) => state.error);
export const useSearchResults = () => useLeaderboardStore((state) => state.searchResults);
export const useSearchLoading = () => useLeaderboardStore((state) => state.searchLoading);