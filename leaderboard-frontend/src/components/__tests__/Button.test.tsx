import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';
import Button from '../presentational/Common/Button';

describe('Button компонент', () => {
  it('должен рендерить базовую кнопку', () => {
    render(
      <Button>
        Нажми меня
      </Button>
    );
    
    const button = screen.getByRole('button', { name: /нажми меня/i });
    expect(button).toBeTruthy();
  });

  it('должен применять вариант primary по умолчанию', () => {
    render(
      <Button>
        Primary кнопка
      </Button>
    );
    
    const button = screen.getByRole('button');
    expect(button.className).toContain('bg-blue-600');
  });
});