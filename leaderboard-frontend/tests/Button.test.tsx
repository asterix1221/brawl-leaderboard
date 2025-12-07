import React from 'react';
import { describe, it, expect, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import Button from '../src/components/presentational/Common/Button';

describe('Button компонент', () => {
  it('должен рендерить базовую кнопку', () => {
    render(
      <Button>
        Нажми меня
      </Button>
    );
    
    const button = screen.getByRole('button', { name: /нажми меня/i });
    expect(button).toBeTruthy();
    expect(button.getAttribute('type')).toBe('button');
  });

  it('должен применять вариант primary по умолчанию', () => {
    render(
      <Button>
        Primary кнопка
      </Button>
    );
    
    const button = screen.getByRole('button');
    expect(button.className).toContain('bg-blue-600');
    expect(button.className).toContain('hover:bg-blue-700');
  });

  it('должен применять вариант secondary', () => {
    render(
      <Button variant="secondary">
        Secondary кнопка
      </Button>
    );
    
    const button = screen.getByRole('button');
    expect(button.className).toContain('bg-gray-600');
    expect(button.className).toContain('hover:bg-gray-700');
  });

  it('должен применять вариант danger', () => {
    render(
      <Button variant="danger">
        Danger кнопка
      </Button>
    );
    
    const button = screen.getByRole('button');
    expect(button.className).toContain('bg-red-600');
    expect(button.className).toContain('hover:bg-red-700');
  });

  it('должен вызывать onClick при клике', async () => {
    const handleClick = vi.fn();
    const user = userEvent.setup();
    
    render(
      <Button onClick={handleClick}>
        Кликни
      </Button>
    );
    
    const button = screen.getByRole('button');
    await user.click(button);
    
    expect(handleClick).toHaveBeenCalledTimes(1);
  });

  it('должен быть отключен когда disabled=true', () => {
    render(
      <Button disabled>
        Отключенная кнопка
      </Button>
    );
    
    const button = screen.getByRole('button') as HTMLButtonElement;
    expect(button.disabled).toBe(true);
    expect(button.className).toContain('opacity-50');
    expect(button.className).toContain('cursor-not-allowed');
  });

  it('должен применять правильные размеры', () => {
    render(
      <Button size="lg">
        Большая кнопка
      </Button>
    );
    
    const button = screen.getByRole('button');
    expect(button.className).toContain('px-6');
    expect(button.className).toContain('py-3');
    expect(button.className).toContain('text-base');
  });

  it('должен применяться пользовательский класс', () => {
    render(
      <Button className="custom-class">
        Кнопка с классом
      </Button>
    );
    
    const button = screen.getByRole('button');
    expect(button.className).toContain('custom-class');
  });

  it('должен рендерить разные типы кнопок', () => {
    const { rerender } = render(
      <Button type="submit">
        Submit кнопка
      </Button>
    );
    
    let button = screen.getByRole('button');
    expect(button.getAttribute('type')).toBe('submit');
    
    rerender(
      <Button type="reset">
        Reset кнопка
      </Button>
    );
    
    button = screen.getByRole('button');
    expect(button.getAttribute('type')).toBe('reset');
  });
});