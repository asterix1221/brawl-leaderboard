// Main store file that exports all store slices
export { useAuthStore, useAuth, useCurrentUser, useIsAuthenticated, useAuthLoading, useAuthError } from './slices/authSlice';
export { useLeaderboardStore, useLeaderboard, useLeaderboardPlayers, useLeaderboardLoading, useLeaderboardError, useSearchResults, useSearchLoading } from './slices/leaderboardSlice';

// Re-export types for convenience
export type { AuthState, AuthActions } from '../types/auth.types';
export type { LeaderboardEntry } from '../types/domain.types';