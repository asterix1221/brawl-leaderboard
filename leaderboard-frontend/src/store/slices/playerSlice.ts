import { create } from 'zustand';
import type { Player, PlayerStats, ScoreHistory } from '../../types/domain.types';
// TODO: Import playerService when created
// import playerService from '../../services/api/playerService';

interface PlayerState {
  // State
  selectedPlayer: Player | null;
  stats: PlayerStats | null;
  history: ScoreHistory[];
  loading: boolean;
  error: string | null;

  // Actions
  setSelectedPlayer: (player: Player | null) => void;
  setStats: (stats: PlayerStats | null) => void;
  setHistory: (history: ScoreHistory[]) => void;
  setLoading: (loading: boolean) => void;
  setError: (error: string | null) => void;
  clearPlayer: () => void;
  
  // Async actions
  fetchPlayerProfile: (playerId: string) => Promise<void>;
  fetchPlayerStats: (playerId: string) => Promise<void>;
  fetchPlayerHistory: (playerId: string) => Promise<void>;
  linkBrawlStarsAccount: (brawlStarsPlayerId: string) => Promise<void>;
}

export const usePlayerStore = create<PlayerState>((set, get) => ({
  // Initial state
  selectedPlayer: null,
  stats: null,
  history: [],
  loading: false,
  error: null,

  // Actions
  setSelectedPlayer: (selectedPlayer) => set({ selectedPlayer }),
  setStats: (stats) => set({ stats }),
  setHistory: (history) => set({ history }),
  setLoading: (loading) => set({ loading }),
  setError: (error) => set({ error }),

  // Async actions
  fetchPlayerProfile: async (playerId: string) => {
    set({ loading: true, error: null });

    try {
      // TODO: Replace with actual playerService call
      // const response = await playerService.getPlayerProfile(playerId);
      
      // Mock response for now
      const response = {
        success: true,
        data: {
          player: {
            id: playerId,
            nickname: 'Mock Player',
            totalTrophies: 1000,
            region: 'global',
            level: 10,
            lastUpdated: new Date().toISOString(),
          }
        }
      };

      if (response.success && response.data) {
        const { player } = response.data;
        set({
          selectedPlayer: {
            id: player.id,
            nickname: player.nickname,
            totalTrophies: player.totalTrophies,
            region: player.region,
            level: player.level,
            lastUpdated: player.lastUpdated,
            createdAt: new Date().toISOString(), // Fallback if not provided
          },
          loading: false,
        });
      } else {
        set({
          loading: false,
          error: 'Failed to fetch player profile',
        });
      }
    } catch (error: any) {
      set({
        loading: false,
        error: error.error || 'An unexpected error occurred while fetching player profile',
      });
    }
  },

  fetchPlayerStats: async (playerId: string) => {
    set({ loading: true, error: null });

    try {
      // TODO: Replace with actual playerService call
      // const response = await playerService.getPlayerStats(playerId);
      
      // Mock response for now
      const response = {
        success: true,
        data: {
          stats: {
            playerId,
            totalTrophies: 1000,
            wins: 150,
            losses: 50,
            winRate: 75,
            rank: 42,
            region: 'global',
            level: 10,
          }
        }
      };

      if (response.success && response.data) {
        const { stats } = response.data;
        set({
          stats: {
            playerId: stats.playerId || playerId,
            totalTrophies: stats.totalTrophies,
            wins: stats.wins,
            losses: stats.losses,
            winRate: stats.winRate,
            rank: stats.rank,
            region: stats.region || '',
            level: stats.level,
          },
          loading: false,
        });
      } else {
        set({
          loading: false,
          error: 'Failed to fetch player stats',
        });
      }
    } catch (error: any) {
      set({
        loading: false,
        error: error.error || 'An unexpected error occurred while fetching player stats',
      });
    }
  },

  fetchPlayerHistory: async (playerId: string) => {
    set({ loading: true, error: null });

    try {
      // TODO: Replace with actual playerService call
      // const response = await playerService.getPlayerHistory(playerId);
      
      // Mock response for now
      const response = {
        success: true,
        data: {
          history: [
            {
              id: '1',
              scoreId: 'score1',
              oldScore: 900,
              newScore: 1000,
              changeReason: 'Victory in match',
              createdAt: new Date().toISOString(),
            }
          ]
        }
      };

      if (response.success && response.data) {
        const { history } = response.data;
        set({
          history: history.map((item: any) => ({
            id: item.id,
            scoreId: item.scoreId || '',
            playerId: playerId,
            oldScore: item.oldScore,
            newScore: item.newScore,
            changeReason: item.changeReason,
            createdAt: item.createdAt,
          })),
          loading: false,
        });
      } else {
        set({
          loading: false,
          error: 'Failed to fetch player history',
        });
      }
    } catch (error: any) {
      set({
        loading: false,
        error: error.error || 'An unexpected error occurred while fetching player history',
      });
    }
  },

  linkBrawlStarsAccount: async (brawlStarsPlayerId: string) => {
    set({ loading: true, error: null });

    try {
      // TODO: Replace with actual playerService call
      // const response = await playerService.linkBrawlStarsAccount({ brawlStarsPlayerId });
      
      // Mock response for now
      const response = {
        success: true,
      };

      if (response.success) {
        set({ loading: false });
        // Refresh the current player profile after linking
        const { selectedPlayer } = get();
        if (selectedPlayer) {
          await get().fetchPlayerProfile(selectedPlayer.id);
        }
      } else {
        set({
          loading: false,
          error: 'Failed to link Brawl Stars account',
        });
      }
    } catch (error: any) {
      set({
        loading: false,
        error: error.error || 'An unexpected error occurred while linking Brawl Stars account',
      });
    }
  },

  clearPlayer: () => {
    set({
      selectedPlayer: null,
      stats: null,
      history: [],
      error: null,
    });
  },
}));

// Selectors for common use cases
export const usePlayer = () => {
  const store = usePlayerStore();
  
  return {
    // State
    selectedPlayer: store.selectedPlayer,
    stats: store.stats,
    history: store.history,
    loading: store.loading,
    error: store.error,
    
    // Actions
    fetchPlayerProfile: store.fetchPlayerProfile,
    fetchPlayerStats: store.fetchPlayerStats,
    fetchPlayerHistory: store.fetchPlayerHistory,
    linkBrawlStarsAccount: store.linkBrawlStarsAccount,
    clearPlayer: store.clearPlayer,
    setSelectedPlayer: store.setSelectedPlayer,
    setStats: store.setStats,
    setHistory: store.setHistory,
    setLoading: store.setLoading,
    setError: store.setError,
  };
};

// Convenience selectors
export const useSelectedPlayer = () => usePlayerStore((state) => state.selectedPlayer);
export const usePlayerStats = () => usePlayerStore((state) => state.stats);
export const usePlayerHistory = () => usePlayerStore((state) => state.history);
export const usePlayerLoading = () => usePlayerStore((state) => state.loading);
export const usePlayerError = () => usePlayerStore((state) => state.error);