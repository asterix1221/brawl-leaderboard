/**
 * Утилита для объединения CSS классов
 * Простая реализация без внешних зависимостей
 */
export function cn(...classes: (string | undefined | null | false)[]): string {
  return classes
    .filter(Boolean)
    .join(' ')
    .trim();
}
