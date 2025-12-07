import apiClient from './apiClient';
import { apiService, isMockMode } from './apiConfig';
import type { LeaderboardResponse, SearchPlayersRequest, SearchPlayersResponse } from '../../types/api.types';

export class LeaderboardService {
  /**
   * Get global leaderboard
   */
  async getGlobalLeaderboard(params?: {
    limit?: number;
    offset?: number;
    region?: string;
  }): Promise<LeaderboardResponse> {
    if (isMockMode() && apiService) {
      try {
        const result = await apiService.getGlobalLeaderboard(
          params?.limit || 10, 
          params?.offset || 0, 
          params?.region
        );
        
        return {
          success: true,
          data: {
            entries: result.players,
            total: result.total,
            page: Math.floor((params?.offset || 0) / (params?.limit || 10)) + 1,
            limit: params?.limit || 10,
            hasMore: false
          }
        };
      } catch (error) {
        return {
          success: false,
          error: error instanceof Error ? error.message : 'Failed to load leaderboard'
        };
      }
    }

    const queryParams = new URLSearchParams();
    
    if (params?.limit) queryParams.append('limit', params.limit.toString());
    if (params?.offset) queryParams.append('offset', params.offset.toString());
    if (params?.region) queryParams.append('region', params.region);

    const url = queryParams.toString() 
      ? `/leaderboards/global?${queryParams.toString()}`
      : '/leaderboards/global';

    const response = await apiClient.get(url);
    return {
      success: response.success,
      data: response.data || {
        entries: [],
        total: 0,
        page: 1,
        limit: params?.limit || 10,
        hasMore: false
      },
      error: response.error
    };
  }

  /**
   * Get regional leaderboard
   */
  async getRegionalLeaderboard(
    region: string,
    params?: {
      limit?: number;
      offset?: number;
    }
  ): Promise<LeaderboardResponse> {
    const queryParams = new URLSearchParams();
    
    if (params?.limit) queryParams.append('limit', params.limit.toString());
    if (params?.offset) queryParams.append('offset', params.offset.toString());

    const url = queryParams.toString() 
      ? `/leaderboards/regional/${region}?${queryParams.toString()}`
      : `/leaderboards/regional/${region}`;

    const response = await apiClient.get(url);
    return {
      success: response.success,
      data: response.data || {
        entries: [],
        total: 0,
        page: 1,
        limit: params?.limit || 10,
        hasMore: false
      },
      error: response.error
    };
  }

  /**
   * Search players
   */
  async searchPlayers(params: SearchPlayersRequest): Promise<SearchPlayersResponse> {
    if (isMockMode() && apiService) {
      try {
        const result = await apiService.searchPlayers(params.q, params.limit || 10);
        
        return {
          success: true,
          data: {
            players: result.map((player: any) => ({
              id: player.playerId,
              nickname: player.nickname,
              totalTrophies: player.totalTrophies,
              region: player.region,
              level: player.level
            })),
            total: result.length
          }
        };
      } catch (error) {
        return {
          success: false,
          error: error instanceof Error ? error.message : 'Failed to search players'
        };
      }
    }

    const queryParams = new URLSearchParams();
    
    queryParams.append('q', params.q);
    if (params.limit) queryParams.append('limit', params.limit.toString());
    if (params.offset) queryParams.append('offset', params.offset.toString());

    const response = await apiClient.get(`/players/search?${queryParams.toString()}`);
    return {
      success: response.success,
      data: response.data || {
        players: [],
        total: 0
      },
      error: response.error
    };
  }

  /**
   * Get player profile
   */
  async getPlayerProfile(playerId: string): Promise<any> {
    return apiClient.get(`/players/${playerId}`);
  }

  /**
   * Get current user's profile (protected)
   */
  async getMyProfile(): Promise<any> {
    return apiClient.get('/players/me');
  }

  /**
   * Link Brawl Stars account (protected)
   */
  async linkBrawlStarsAccount(brawlStarsPlayerId: string): Promise<any> {
    return apiClient.post('/players/link', { brawlStarsPlayerId });
  }

  /**
   * Get current user's stats (protected)
   */
  async getMyStats(): Promise<any> {
    return apiClient.get('/players/me/stats');
  }

  /**
   * Get current user's score history (protected)
   */
  async getMyHistory(params?: {
    limit?: number;
    offset?: number;
  }): Promise<any> {
    const queryParams = new URLSearchParams();
    
    if (params?.limit) queryParams.append('limit', params.limit.toString());
    if (params?.offset) queryParams.append('offset', params.offset.toString());

    const url = queryParams.toString() 
      ? `/players/me/history?${queryParams.toString()}`
      : '/players/me/history';

    return apiClient.get(url);
  }

  /**
   * Get available regions
   */
  async getRegions(): Promise<{ success: boolean; data: string[] }> {
    const result = await apiClient.get('/regions');
    return {
      success: result.success,
      data: result.data || []
    };
  }

  /**
   * Get available seasons
   */
  async getSeasons(): Promise<{ success: boolean; data: any[] }> {
    const result = await apiClient.get('/seasons');
    return {
      success: result.success,
      data: result.data || []
    };
  }

  /**
   * Get leaderboard for specific season
   */
  async getSeasonLeaderboard(
    seasonId: string,
    params?: {
      limit?: number;
      offset?: number;
      region?: string;
    }
  ): Promise<LeaderboardResponse> {
    const queryParams = new URLSearchParams();
    
    if (params?.limit) queryParams.append('limit', params.limit.toString());
    if (params?.offset) queryParams.append('offset', params.offset.toString());
    if (params?.region) queryParams.append('region', params.region);

    const url = queryParams.toString() 
      ? `/leaderboards/season/${seasonId}?${queryParams.toString()}`
      : `/leaderboards/season/${seasonId}`;

    const response = await apiClient.get(url);
    return {
      success: response.success,
      data: response.data || {
        entries: [],
        total: 0,
        page: 1,
        limit: params?.limit || 10,
        hasMore: false
      },
      error: response.error
    };
  }

  /**
   * Get cached data (if available)
   */
  getCachedData<T>(key: string): T | null {
    try {
      const cached = localStorage.getItem(key);
      if (!cached) return null;

      const { data, timestamp } = JSON.parse(cached);
      const now = Date.now();
      
      // Cache for 5 minutes (300000 ms)
      if (now - timestamp > 300000) {
        localStorage.removeItem(key);
        return null;
      }

      return data;
    } catch {
      return null;
    }
  }

  /**
   * Set cached data
   */
  setCachedData<T>(key: string, data: T): void {
    try {
      const cacheData = {
        data,
        timestamp: Date.now(),
      };
      localStorage.setItem(key, JSON.stringify(cacheData));
    } catch (error) {
      console.warn('Failed to cache data:', error);
    }
  }

  /**
   * Clear cached data
   */
  clearCachedData(pattern?: string): void {
    const keys = Object.keys(localStorage);
    
    keys.forEach(key => {
      if (pattern && key.includes(pattern)) {
        localStorage.removeItem(key);
      } else if (!pattern && key.startsWith('leaderboard_')) {
        localStorage.removeItem(key);
      }
    });
  }
}

// Create singleton instance
const leaderboardService = new LeaderboardService();

export default leaderboardService;