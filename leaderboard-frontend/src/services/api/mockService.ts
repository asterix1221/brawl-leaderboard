// Моковые данные для тестирования фронтенда
import type { Player, LeaderboardEntry, User } from '../../types/domain.types';

// Тип для ответа логина
interface LoginResponse {
  accessToken: string;
  refreshToken: string;
  user: User;
}

// Моковые пользователи
let mockUsers: User[] = [
  {
    id: '1',
    email: 'test@example.com',
    nickname: 'TestPlayer',
    createdAt: new Date().toISOString(),
    updatedAt: new Date().toISOString()
  }
];

// Моковые игроки
const mockPlayers: Player[] = [
  {
    id: '1',
    nickname: 'ProPlayer2024',
    totalTrophies: 15420,
    region: 'RU',
    level: 85,
    lastUpdated: new Date().toISOString(),
    createdAt: new Date().toISOString()
  },
  {
    id: '2',
    nickname: 'StarGamer',
    totalTrophies: 14890,
    region: 'EU',
    level: 82,
    lastUpdated: new Date().toISOString(),
    createdAt: new Date().toISOString()
  },
  {
    id: '3',
    nickname: 'BrawlMaster',
    totalTrophies: 14150,
    region: 'US',
    level: 79,
    lastUpdated: new Date().toISOString(),
    createdAt: new Date().toISOString()
  },
  {
    id: '4',
    nickname: 'ChampionRU',
    totalTrophies: 13500,
    region: 'RU',
    level: 77,
    lastUpdated: new Date().toISOString(),
    createdAt: new Date().toISOString()
  },
  {
    id: '5',
    nickname: 'ElitePlayer',
    totalTrophies: 12800,
    region: 'AS',
    level: 75,
    lastUpdated: new Date().toISOString(),
    createdAt: new Date().toISOString()
  }
];

// Моковый лидерборд
const mockLeaderboard: LeaderboardEntry[] = mockPlayers.map((player, index) => ({
  rank: index + 1,
  playerId: player.id,
  nickname: player.nickname,
  totalTrophies: player.totalTrophies,
  region: player.region,
  level: player.level
}));

// Имитация задержки сети
const delay = (ms: number = 500) => new Promise(resolve => setTimeout(resolve, ms));

// Моковый API сервис
export const mockService = {
  // Функция для очистки данных (для тестов)
  resetData() {
    mockUsers = [
      {
        id: '1',
        email: 'test@example.com',
        nickname: 'TestPlayer',
        createdAt: new Date().toISOString(),
        updatedAt: new Date().toISOString()
      }
    ];
  },

  // Auth методы
  async register(email: string, _password: string, nickname: string): Promise<LoginResponse> {
    await delay(800);
    
    // Проверка на дубликат email
    if (mockUsers.some(user => user.email === email)) {
      throw new Error('Пользователь с таким email уже существует');
    }
    
    const newUser: User = {
      id: String(mockUsers.length + 1),
      email,
      nickname,
      createdAt: new Date().toISOString(),
      updatedAt: new Date().toISOString()
    };
    
    mockUsers.push(newUser);
    
    return {
      accessToken: 'mock-access-token-' + Date.now(),
      refreshToken: 'mock-refresh-token-' + Date.now(),
      user: newUser
    };
  },

  async login(email: string, _password: string): Promise<LoginResponse> {
    await delay(600);
    
    const user = mockUsers.find(u => u.email === email);
    if (!user) {
      throw new Error('Неверный email или пароль');
    }
    
    return {
      accessToken: 'mock-access-token-' + Date.now(),
      refreshToken: 'mock-refresh-token-' + Date.now(),
      user
    };
  },

  async refreshToken(_refreshToken: string): Promise<LoginResponse> {
    await delay(300);
    
    const user = mockUsers[0]; // В реальности здесь была бы проверка токена
    
    return {
      accessToken: 'mock-access-token-' + Date.now(),
      refreshToken: 'mock-refresh-token-' + Date.now(),
      user
    };
  },

  // Leaderboard методы
  async getGlobalLeaderboard(limit: number = 10, offset: number = 0, region?: string): Promise<{
    players: LeaderboardEntry[];
    total: number;
  }> {
    await delay(400);
    
    let filteredLeaderboard = mockLeaderboard;
    
    if (region && region !== 'all') {
      filteredLeaderboard = mockLeaderboard.filter(player => player.region === region);
    }
    
    const total = filteredLeaderboard.length;
    const players = filteredLeaderboard.slice(offset, offset + limit);
    
    return { players, total };
  },

  async searchPlayers(query: string, limit: number = 10): Promise<LeaderboardEntry[]> {
    await delay(300);
    
    if (!query.trim()) {
      return [];
    }
    
    const filtered = mockLeaderboard.filter(player =>
      player.nickname.toLowerCase().includes(query.toLowerCase())
    ).slice(0, limit);
    
    return filtered;
  },

  // Player методы
  async getPlayerProfile(playerId: string): Promise<Player> {
    await delay(500);
    
    const player = mockPlayers.find(p => p.id === playerId);
    if (!player) {
      throw new Error('Игрок не найден');
    }
    
    return player;
  },

  async linkBrawlStarsPlayer(userId: string, brawlStarsId: string): Promise<Player> {
    await delay(700);
    
    // В реальности здесь был бы запрос к Brawl Stars API
    const mockBrawlStarsPlayer: Player = {
      id: brawlStarsId,
      nickname: `BS_${mockUsers.find(u => u.id === userId)?.nickname || 'Player'}`,
      totalTrophies: Math.floor(Math.random() * 5000) + 10000,
      region: 'RU',
      level: Math.floor(Math.random() * 30) + 50,
      lastUpdated: new Date().toISOString(),
      createdAt: new Date().toISOString()
    };
    
    // Добавляем в моковые данные, если его еще нет
    if (!mockPlayers.find(p => p.id === brawlStarsId)) {
      mockPlayers.push(mockBrawlStarsPlayer);
    }
    
    return mockBrawlStarsPlayer;
  },

  async getPlayerStats(playerId: string): Promise<{
    totalScore: number;
    wins: number;
    losses: number;
    winRate: number;
    rank: number;
  }> {
    await delay(300);
    
    const player = mockPlayers.find(p => p.id === playerId);
    if (!player) {
      throw new Error('Игрок не найден');
    }
    
    const rank = mockLeaderboard.findIndex(p => p.playerId === playerId) + 1;
    const wins = Math.floor(Math.random() * 500) + 100;
    const losses = Math.floor(Math.random() * 200) + 50;
    const totalScore = player.totalTrophies;
    
    return {
      totalScore,
      wins,
      losses,
      winRate: Math.round((wins / (wins + losses)) * 100),
      rank
    };
  },

  async getPlayerHistory(playerId: string): Promise<Array<{
    date: string;
    score: number;
    change: number;
  }>> {
    await delay(400);
    
    const history = [];
    const player = mockPlayers.find(p => p.id === playerId);
    if (!player) {
      throw new Error('Игрок не найден');
    }
    
    // Генерируем историю за последние 7 дней
    for (let i = 6; i >= 0; i--) {
      const date = new Date();
      date.setDate(date.getDate() - i);
      
      const score = player.totalTrophies - (i * Math.floor(Math.random() * 100));
      const change: number = i === 6 ? 0 : score - (history[history.length - 1]?.score || 0);
      
      history.push({
        date: date.toISOString().split('T')[0],
        score,
        change
      });
    }
    
    return history;
  },

  // Health check
  async healthCheck(): Promise<{
    status: string;
    database: string;
    redis: string;
    api: string;
  }> {
    await delay(200);
    
    return {
      status: 'healthy',
      database: 'connected',
      redis: 'connected',
      api: 'mock_mode'
    };
  }
};
