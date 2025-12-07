// Конфигурация API для переключения между реальным бэкендом и моками
import { mockService } from './mockService';

// Определяем, использовать ли моковые данные
const USE_MOCK_DATA = import.meta.env.VITE_USE_MOCK_DATA === 'true' || 
                     !import.meta.env.VITE_API_URL ||
                     import.meta.env.VITE_API_URL.includes('mock') ||
                     // В тестовой среде всегда используем моки
                     import.meta.env.MODE === 'test';

console.log('API Mode:', USE_MOCK_DATA ? 'MOCK' : 'REAL');
console.log('API URL:', import.meta.env.VITE_API_URL);
console.log('MODE:', import.meta.env.MODE);

// Экспортируем соответствующий сервис
export const apiService = USE_MOCK_DATA ? mockService : null;

// Helper функция для проверки режима
export const isMockMode = () => USE_MOCK_DATA;
