// Интеграционные тесты для проверки работы с моковыми данными
import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest';
import { mockService } from '../services/api/mockService';
import authService from '../services/api/authService';
import leaderboardService from '../services/api/leaderboardService';

// Мокаем localStorage
const localStorageMock = {
  getItem: vi.fn(),
  setItem: vi.fn(),
  removeItem: vi.fn(),
  clear: vi.fn(),
};

describe('Интеграционные тесты с моковыми данными', () => {
  beforeEach(() => {
    mockService.resetData();
    vi.clearAllMocks();
    Object.defineProperty(window, 'localStorage', {
      value: localStorageMock,
      writable: true,
    });
  });

  afterEach(() => {
    vi.clearAllMocks();
  });

  describe('Полный цикл аутентификации', () => {
    it('должен выполнять полный цикл регистрация → вход → получение данных', async () => {
      // 1. Регистрация
      const registerResult = await authService.register({
        email: 'integration@example.com',
        password: 'Password123!',
        nickname: 'IntegrationUser'
      });

      expect(registerResult.success).toBe(true);
      expect(registerResult.data?.user.email).toBe('integration@example.com');

      // 2. Проверяем, что токены сохранены в localStorage
      expect(localStorageMock.setItem).toHaveBeenCalledWith('accessToken', expect.any(String));
      expect(localStorageMock.setItem).toHaveBeenCalledWith('refreshToken', expect.any(String));
      expect(localStorageMock.setItem).toHaveBeenCalledWith('user', expect.any(String));

      // 3. Выход
      await authService.logout();
      
      // 4. Проверяем, что токены удалены
      expect(localStorageMock.removeItem).toHaveBeenCalledWith('accessToken');
      expect(localStorageMock.removeItem).toHaveBeenCalledWith('refreshToken');
      expect(localStorageMock.removeItem).toHaveBeenCalledWith('user');

      // 5. Вход
      const loginResult = await authService.login({
        email: 'integration@example.com',
        password: 'Password123!'
      });

      expect(loginResult.success).toBe(true);
      expect(loginResult.data?.user.email).toBe('integration@example.com');
    });
  });

  describe('Работа с лидербордом', () => {
    it('должен получать и фильтровать данные лидерборда', async () => {
      // Получение глобального лидерборда
      const globalResult = await leaderboardService.getGlobalLeaderboard({
        limit: 5,
        offset: 0
      });

      expect(globalResult.success).toBe(true);
      expect(globalResult.data?.entries?.length).toBeGreaterThan(0);
      expect(globalResult.data?.total).toBeGreaterThan(0);

      // Фильтрация по региону
      const regionalResult = await leaderboardService.getGlobalLeaderboard({
        limit: 10,
        offset: 0,
        region: 'RU'
      });

      expect(regionalResult.success).toBe(true);
      
      // Проверяем, что все результаты из региона RU
      if (regionalResult.data?.entries && regionalResult.data.entries.length > 0) {
        regionalResult.data.entries.forEach((player: any) => {
          expect(player.region).toBe('RU');
        });
      }
    });

    it('должен выполнять поиск игроков', async () => {
      const searchResult = await leaderboardService.searchPlayers({
        q: 'Pro',
        limit: 10
      });

      expect(searchResult.success).toBe(true);
      expect(searchResult.data?.players?.length).toBeGreaterThan(0);
      
      // Проверяем, что все результаты содержат 'Pro' в никнейме
      if (searchResult.data?.players && searchResult.data.players.length > 0) {
        searchResult.data.players.forEach((player: any) => {
          expect(player.nickname).toContain('Pro');
        });
      }
    });
  });

  describe('Кеширование данных', () => {
    it('должен кешировать и извлекать данные лидерборда', () => {
      const cacheKey = 'test_leaderboard_global';
      const testData = [
        { rank: 1, nickname: 'TestPlayer', totalTrophies: 15000 }
      ];

      // Мокаем localStorage для кеширования
      localStorageMock.getItem.mockReturnValue(JSON.stringify({
        data: testData,
        timestamp: Date.now()
      }));

      // Сохраняем в кеш
      leaderboardService.setCachedData(cacheKey, testData);
      
      // Проверяем, что данные были сохранены
      expect(localStorageMock.setItem).toHaveBeenCalledWith(
        cacheKey,
        expect.stringContaining('"data"')
      );

      // Получаем из кеша
      const cachedData = leaderboardService.getCachedData(cacheKey);
      expect(cachedData).toEqual(testData);
    });

    it('должен очищать кеш', () => {
      const cacheKey = 'test_leaderboard_global';
      const testData = [{ rank: 1, nickname: 'TestPlayer' }];

      // Мокаем localStorage
      localStorageMock.getItem.mockReturnValue(JSON.stringify({
        data: testData,
        timestamp: Date.now()
      }));

      // Сохраняем в кеш
      leaderboardService.setCachedData(cacheKey, testData);
      
      // Проверяем, что данные были сохранены
      expect(localStorageMock.setItem).toHaveBeenCalled();

      // Мокаем удаление
      localStorageMock.removeItem.mockImplementation(() => {});

      // Очищаем кеш
      leaderboardService.clearCachedData('leaderboard');
      
      // Проверяем, что был вызван removeItem
      expect(localStorageMock.removeItem).toHaveBeenCalled();
    });
  });

  describe('Работа с профилем игрока', () => {
    it('должен получать полный профиль игрока', async () => {
      const playerId = '1';
      
      // Получаем профиль
      const profile = await mockService.getPlayerProfile(playerId);
      expect(profile.id).toBe(playerId);
      expect(profile.nickname).toBeTruthy();
      expect(profile.totalTrophies).toBeGreaterThan(0);

      // Получаем статистику
      const stats = await mockService.getPlayerStats(playerId);
      expect(stats.totalScore).toBeGreaterThan(0);
      expect(stats.wins).toBeGreaterThanOrEqual(0);
      expect(stats.losses).toBeGreaterThanOrEqual(0);
      expect(stats.winRate).toBeGreaterThanOrEqual(0);
      expect(stats.winRate).toBeLessThanOrEqual(100);

      // Получаем историю
      const history = await mockService.getPlayerHistory(playerId);
      expect(history).toHaveLength(7); // 7 дней
      expect(history[0]).toHaveProperty('date');
      expect(history[0]).toHaveProperty('score');
      expect(history[0]).toHaveProperty('change');
    });
  });

  describe('Обработка ошибок', () => {
    it('должен корректно обрабатывать ошибки регистрации', async () => {
      // Регистрируем пользователя
      await authService.register({
        email: 'error@example.com',
        password: 'Password123!',
        nickname: 'ErrorUser'
      });

      // Пытаемся зарегистрировать того же пользователя
      const duplicateResult = await authService.register({
        email: 'error@example.com',
        password: 'AnotherPass123!',
        nickname: 'AnotherUser'
      });

      expect(duplicateResult.success).toBe(false);
      expect(duplicateResult.error).toContain('уже существует');
    });

    it('должен корректно обрабатывать ошибки входа', async () => {
      const wrongLoginResult = await authService.login({
        email: 'nonexistent@example.com',
        password: 'WrongPassword123!'
      });

      expect(wrongLoginResult.success).toBe(false);
      expect(wrongLoginResult.error).toContain('Неверный email или пароль');
    });

    it('должен корректно обрабатывать поиск несуществующего игрока', async () => {
      const searchResult = await leaderboardService.searchPlayers({
        q: 'NonexistentPlayer12345',
        limit: 10
      });

      expect(searchResult.success).toBe(true);
      expect(searchResult.data?.players?.length).toBe(0);
    });
  });

  describe('Health check', () => {
    it('должен возвращать статус системы', async () => {
      const health = await mockService.healthCheck();
      
      expect(health.status).toBe('healthy');
      expect(health.database).toBe('connected');
      expect(health.redis).toBe('connected');
      expect(health.api).toBe('mock_mode');
    });
  });
});
