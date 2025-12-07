// Main store file that exports all store slices
export { 
  useAuthStore, 
  useAuth, 
  useCurrentUser, 
  useIsAuthenticated, 
  useAuthLoading, 
  useAuthError 
} from './slices/authSlice';

export { 
  useLeaderboardStore, 
  useLeaderboard, 
  useLeaderboardPlayers, 
  useLeaderboardLoading, 
  useLeaderboardError, 
  useSearchResults, 
  useSearchLoading 
} from './slices/leaderboardSlice';

export { 
  usePlayerStore, 
  usePlayer, 
  useSelectedPlayer, 
  usePlayerStats, 
  usePlayerHistory, 
  usePlayerLoading, 
  usePlayerError 
} from './slices/playerSlice';

export { 
  useUIStore, 
  useUI, 
  useTheme, 
  useSidebarOpen, 
  useNotifications, 
  useIsLoading, 
  useLoadingMessage, 
  useNotificationHelpers 
} from './slices/uiSlice';

// Re-export types for convenience
export type { AuthState, AuthActions } from '../types/auth.types';
export type { LeaderboardEntry } from '../types/domain.types';
export type { Player, PlayerStats, ScoreHistory } from '../types/domain.types';
export type { Notification } from './slices/uiSlice';