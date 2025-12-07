import { describe, it, expect } from 'vitest';

describe('Математические операции', () => {
  it('должно складывать числа', () => {
    expect(1 + 1).toBe(2);
  });

  it('должно работать с массивами', () => {
    const arr = [1, 2, 3];
    expect(arr).toHaveLength(3);
    expect(arr).toContain(2);
  });
});