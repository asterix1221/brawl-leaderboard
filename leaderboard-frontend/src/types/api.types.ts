// API Types for Brawl Stars Leaderboard

export interface RegisterRequest {
  email: string;
  password: string;
  nickname: string;
}

export interface LoginRequest {
  email: string;
  password: string;
}

export interface LoginResponse {
  success: boolean;
  data: {
    accessToken: string;
    refreshToken: string;
    user: {
      id: string;
      email: string;
      nickname: string;
    };
  };
}

export interface ApiResponse<T = any> {
  success: boolean;
  data?: T;
  error?: string;
  code?: string;
}

export interface ErrorResponse {
  success: false;
  error: string;
  code: string;
}

export interface LeaderboardResponse extends ApiResponse<{
    entries: Array<{
      rank: number;
      playerId: string;
      nickname: string;
      totalTrophies: number;
      region: string;
      level: number;
    }>;
    total: number;
    page: number;
    limit: number;
    hasMore: boolean;
  }> {}

export interface SearchPlayersRequest {
  q: string;
  limit?: number;
  offset?: number;
}

export interface SearchPlayersResponse extends ApiResponse<{
    players: Array<{
      id: string;
      nickname: string;
      totalTrophies: number;
      region: string;
      level: number;
    }>;
    total: number;
  }> {}

export interface PlayerProfileResponse {
  success: boolean;
  data: {
    player: {
      id: string;
      nickname: string;
      totalTrophies: number;
      region: string;
      level: number;
      lastUpdated: string;
    };
    stats: {
      wins: number;
      losses: number;
      winRate: number;
      rank: number;
    };
    history: Array<{
      id: string;
      oldScore: number | null;
      newScore: number;
      changeReason: string;
      createdAt: string;
    }>;
  };
}

export interface LinkBrawlStarsRequest {
  brawlStarsPlayerId: string;
}

export interface HealthResponse {
  success: boolean;
  data: {
    status: 'healthy' | 'unhealthy';
    services: {
      database: 'connected' | 'disconnected';
      redis: 'connected' | 'disconnected';
      brawlStarsApi: 'available' | 'unavailable';
    };
    timestamp: string;
  };
}