/**
 * Утилита для объединения CSS классов
 * Простая реализация без внешних зависимостей
 */
export function cn(...classes) {
  return classes
    .filter(Boolean)
    .join(' ')
    .trim();
}