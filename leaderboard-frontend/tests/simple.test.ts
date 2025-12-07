import { describe, it, expect } from 'vitest';

describe('Простой тест', () => {
  it('должен работать', () => {
    expect(1 + 1).toBe(2);
  });

  it('должен работать с текстом', () => {
    expect('hello').toBe('hello');
  });
});