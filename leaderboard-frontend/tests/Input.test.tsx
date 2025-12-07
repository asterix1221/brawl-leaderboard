import React from 'react';
import { describe, it, expect, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import Input from '../src/components/presentational/Common/Input';

describe('Input компонент', () => {
  it('должен рендерить базовый input', () => {
    render(<Input placeholder="Введите текст" />);
    
    const input = screen.getByPlaceholderText('Введите текст');
    expect(input).toBeTruthy();
    expect(input.tagName).toBe('INPUT');
  });

  it('должен отображать label', () => {
    render(<Input label="Имя пользователя" />);
    
    const label = screen.getByText('Имя пользователя');
    expect(label).toBeTruthy();
    expect(label.tagName).toBe('LABEL');
  });

  it('должен связывать label с input через htmlFor', () => {
    render(<Input label="Email" id="email-input" />);
    
    const label = screen.getByText('Email');
    const input = screen.getByLabelText('Email');
    
    expect(label.getAttribute('for')).toBe('email-input');
    expect(input.id).toBe('email-input');
  });

  it('должен отображать ошибку', () => {
    render(<Input error="Это поле обязательно" />);
    
    const error = screen.getByText('Это поле обязательно');
    expect(error).toBeTruthy();
    expect(error.className).toContain('text-red-600');
  });

  it('должен отображать helper text когда нет ошибки', () => {
    render(<Input helperText="Минимум 3 символа" />);
    
    const helper = screen.getByText('Минимум 3 символа');
    expect(helper).toBeTruthy();
    expect(helper.className).toContain('text-gray-500');
  });

  it('не должен отображать helper text когда есть ошибка', () => {
    render(<Input error="Ошибка валидации" helperText="Подсказка" />);
    
    const error = screen.getByText('Ошибка валидации');
    expect(error).toBeTruthy();
    
    const helper = screen.queryByText('Подсказка');
    expect(helper).toBeNull();
  });

  it('должен применять error классы к input', () => {
    render(<Input error="Ошибка" />);
    
    const input = screen.getByRole('textbox');
    expect(input.className).toContain('border-red-300');
    expect(input.className).toContain('text-red-900');
  });

  it('должен обрабатывать ввод текста', async () => {
    const user = userEvent.setup();
    render(<Input />);
    
    const input = screen.getByRole('textbox') as HTMLInputElement;
    await user.type(input, 'test input');
    
    expect(input.value).toBe('test input');
  });

  it('должен поддерживать разные типы input', () => {
    const { rerender } = render(<Input type="email" />);
    
    let input = screen.getByRole('textbox');
    expect(input.getAttribute('type')).toBe('email');
    
    rerender(<Input type="password" />);
    input = document.querySelector('input[type="password"]')!;
    expect(input.getAttribute('type')).toBe('password');
  });

  it('должен быть disabled когда передан disabled prop', () => {
    render(<Input disabled />);
    
    const input = screen.getByRole('textbox') as HTMLInputElement;
    expect(input.disabled).toBe(true);
  });

  it('должен применять пользовательский className', () => {
    render(<Input className="custom-input-class" />);
    
    const input = screen.getByRole('textbox');
    expect(input.className).toContain('custom-input-class');
  });

  it('должен генерировать id автоматически если не передан', () => {
    render(<Input label="Автоматический id" />);
    
    const label = screen.getByText('Автоматический id');
    const input = screen.getByLabelText('Автоматический id');
    
    expect(label.getAttribute('for')).toBeTruthy();
    expect(input.id).toBe(label.getAttribute('for'));
  });

  it('должен вызывать onChange при вводе', async () => {
    const user = userEvent.setup();
    const handleChange = vi.fn();
    
    render(<Input onChange={handleChange} />);
    
    const input = screen.getByRole('textbox');
    await user.type(input, 'a');
    
    expect(handleChange).toHaveBeenCalledTimes(1);
  });

  it('должен поддерживать left icon', () => {
    render(<Input leftIcon={<div data-testid="left-icon" />} />);
    
    const icon = screen.getByTestId('left-icon');
    expect(icon).toBeTruthy();
    
    const input = screen.getByRole('textbox');
    expect(input.className).toContain('pl-10');
  });

  it('должен поддерживать right icon', () => {
    render(<Input rightIcon={<div data-testid="right-icon" />} />);
    
    const icon = screen.getByTestId('right-icon');
    expect(icon).toBeTruthy();
    
    const input = screen.getByRole('textbox');
    expect(input.className).toContain('pr-10');
  });

  it('должен поддерживать оба иконки одновременно', () => {
    render(
      <Input 
        leftIcon={<div data-testid="left-icon" />}
        rightIcon={<div data-testid="right-icon" />}
      />
    );
    
    const leftIcon = screen.getByTestId('left-icon');
    const rightIcon = screen.getByTestId('right-icon');
    
    expect(leftIcon).toBeTruthy();
    expect(rightIcon).toBeTruthy();
    
    const input = screen.getByRole('textbox');
    expect(input.className).toContain('pl-10');
    expect(input.className).toContain('pr-10');
  });
});
