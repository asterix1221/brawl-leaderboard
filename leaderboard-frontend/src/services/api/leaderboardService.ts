import apiClient from './apiClient';
import { LeaderboardResponse, SearchPlayersRequest, SearchPlayersResponse } from '../types/api.types';

export class LeaderboardService {
  /**
   * Get global leaderboard
   */
  async getGlobalLeaderboard(params?: {
    limit?: number;
    offset?: number;
    region?: string;
  }): Promise<LeaderboardResponse> {
    const queryParams = new URLSearchParams();
    
    if (params?.limit) queryParams.append('limit', params.limit.toString());
    if (params?.offset) queryParams.append('offset', params.offset.toString());
    if (params?.region) queryParams.append('region', params.region);

    const url = queryParams.toString() 
      ? `/leaderboards/global?${queryParams.toString()}`
      : '/leaderboards/global';

    return apiClient.get(url);
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

    return apiClient.get(url);
  }

  /**
   * Search players
   */
  async searchPlayers(params: SearchPlayersRequest): Promise<SearchPlayersResponse> {
    const queryParams = new URLSearchParams();
    
    queryParams.append('q', params.q);
    if (params.limit) queryParams.append('limit', params.limit.toString());
    if (params.offset) queryParams.append('offset', params.offset.toString());

    return apiClient.get(`/players/search?${queryParams.toString()}`);
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
    return apiClient.get('/regions');
  }

  /**
   * Get available seasons
   */
  async getSeasons(): Promise<{ success: boolean; data: any[] }> {
    return apiClient.get('/seasons');
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

    return apiClient.get(url);
  }

  /**
   * Cache management helpers
   */
  private cacheKey(type: string, params: Record<string, any> = {}): string {
    const sortedParams = Object.keys(params)
      .sort()
      .reduce((result, key) => {
        result[key] = params[key];
        return result;
      }, {} as Record<string, any>);

    return `leaderboard_${type}_${JSON.stringify(sortedParams)}`;
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