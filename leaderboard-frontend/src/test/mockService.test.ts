// Тесты для мокового сервиса
import { describe, it, expect, beforeEach } from 'vitest';
import { mockService } from '../services/api/mockService';

describe('MockService', () => {
  beforeEach(() => {
    // Очищаем и сбрасываем данные перед каждым тестом
    mockService.resetData();
    localStorage.clear();
  });

  describe('Authentication', () => {
    it('должен регистрировать нового пользователя', async () => {
      const result = await mockService.register('newuser@example.com', 'password123', 'TestUser');
      
      expect(result).toHaveProperty('accessToken');
      expect(result).toHaveProperty('refreshToken');
      expect(result).toHaveProperty('user');
      expect(result.user.email).toBe('newuser@example.com');
      expect(result.user.nickname).toBe('TestUser');
    });

    it('должен выполнять вход существующего пользователя', async () => {
      // Сначала регистрируем пользователя
      await mockService.register('login@example.com', 'password123', 'LoginUser');
      
      // Затем выполняем вход
      const result = await mockService.login('login@example.com', 'password123');
      
      expect(result).toHaveProperty('accessToken');
      expect(result).toHaveProperty('refreshToken');
      expect(result.user.email).toBe('login@example.com');
    });

    it('должен возвращать ошибку при неверном логине', async () => {
      await expect(mockService.login('wrong@example.com', 'password123')).rejects.toThrow('Неверный email или пароль');
    });

    it('должен возвращать ошибку при дубликате email', async () => {
      await mockService.register('duplicate@example.com', 'password123', 'User1');
      
      await expect(mockService.register('duplicate@example.com', 'password456', 'User2')).rejects.toThrow('Пользователь с таким email уже существует');
    });
  });

  describe('Leaderboard', () => {
    it('должен возвращать глобальный лидерборд', async () => {
      const result = await mockService.getGlobalLeaderboard(10, 0);
      
      expect(result).toHaveProperty('players');
      expect(result).toHaveProperty('total');
      expect(result.players).toHaveLength(5); // У нас 5 моковых игроков
      expect(result.total).toBe(5);
      expect(result.players[0].rank).toBe(1);
    });

    it('должен фильтровать по региону', async () => {
      const result = await mockService.getGlobalLeaderboard(10, 0, 'RU');
      
      expect(result.players.length).toBeGreaterThan(0);
      result.players.forEach(player => {
        expect(player.region).toBe('RU');
      });
    });

    it('должен поддерживать пагинацию', async () => {
      const firstPage = await mockService.getGlobalLeaderboard(2, 0);
      const secondPage = await mockService.getGlobalLeaderboard(2, 2);
      
      expect(firstPage.players).toHaveLength(2);
      expect(secondPage.players).toHaveLength(2);
      expect(firstPage.players[0].rank).toBe(1);
      expect(secondPage.players[0].rank).toBe(3);
    });
  });

  describe('Search', () => {
    it('должен находить игроков по нику', async () => {
      const results = await mockService.searchPlayers('Pro', 10);
      
      expect(results.length).toBeGreaterThan(0);
      expect(results[0].nickname).toContain('Pro');
    });

    it('должен возвращать пустой результат при пустом запросе', async () => {
      const results = await mockService.searchPlayers('', 10);
      
      expect(results).toHaveLength(0);
    });

    it('должен ограничивать количество результатов', async () => {
      const results = await mockService.searchPlayers('Player', 2);
      
      expect(results.length).toBeLessThanOrEqual(2);
    });
  });

  describe('Player Profile', () => {
    it('должен возвращать профиль игрока', async () => {
      const player = await mockService.getPlayerProfile('1');
      
      expect(player).toHaveProperty('id', '1');
      expect(player).toHaveProperty('nickname');
      expect(player).toHaveProperty('totalTrophies');
      expect(player).toHaveProperty('region');
      expect(player).toHaveProperty('level');
    });

    it('должен возвращать ошибку для несуществующего игрока', async () => {
      await expect(mockService.getPlayerProfile('999')).rejects.toThrow('Игрок не найден');
    });

    it('должен возвращать статистику игрока', async () => {
      const stats = await mockService.getPlayerStats('1');
      
      expect(stats).toHaveProperty('totalScore');
      expect(stats).toHaveProperty('wins');
      expect(stats).toHaveProperty('losses');
      expect(stats).toHaveProperty('winRate');
      expect(stats).toHaveProperty('rank');
      expect(stats.winRate).toBeGreaterThanOrEqual(0);
      expect(stats.winRate).toBeLessThanOrEqual(100);
    });

    it('должен возвращать историю игрока', async () => {
      const history = await mockService.getPlayerHistory('1');
      
      expect(history).toHaveLength(7); // 7 дней истории
      expect(history[0]).toHaveProperty('date');
      expect(history[0]).toHaveProperty('score');
      expect(history[0]).toHaveProperty('change');
    });
  });

  describe('Brawl Stars Integration', () => {
    it('должен связывать Brawl Stars аккаунт', async () => {
      const player = await mockService.linkBrawlStarsPlayer('1', 'BS_Player123');
      
      expect(player).toHaveProperty('id', 'BS_Player123');
      expect(player.nickname).toContain('BS_');
      expect(player.totalTrophies).toBeGreaterThan(0);
    });
  });

  describe('Health Check', () => {
    it('должен возвращать статус системы', async () => {
      const health = await mockService.healthCheck();
      
      expect(health).toHaveProperty('status', 'healthy');
      expect(health).toHaveProperty('database', 'connected');
      expect(health).toHaveProperty('redis', 'connected');
      expect(health).toHaveProperty('api', 'mock_mode');
    });
  });
});
