import apiClient from './apiClient';
import { LoginRequest, LoginResponse, RegisterRequest, ApiResponse } from '../types/api.types';

export class AuthService {
  /**
   * Register a new user
   */
  async register(data: RegisterRequest): Promise<ApiResponse<LoginResponse['data']>> {
    return apiClient.post<LoginResponse['data']>('/auth/register', data);
  }

  /**
   * Login user
   */
  async login(data: LoginRequest): Promise<ApiResponse<LoginResponse['data']>> {
    const response = await apiClient.post<LoginResponse['data']>('/auth/login', data);
    
    // Store tokens in localStorage if login successful
    if (response.success && response.data) {
      localStorage.setItem('accessToken', response.data.accessToken);
      localStorage.setItem('refreshToken', response.data.refreshToken);
      localStorage.setItem('user', JSON.stringify(response.data.user));
    }
    
    return response;
  }

  /**
   * Logout user
   */
  async logout(): Promise<void> {
    try {
      await apiClient.post('/auth/logout');
    } catch (error) {
      // Even if logout request fails, clear local storage
      console.warn('Logout request failed:', error);
    } finally {
      // Clear local storage
      localStorage.removeItem('accessToken');
      localStorage.removeItem('refreshToken');
      localStorage.removeItem('user');
    }
  }

  /**
   * Refresh access token
   */
  async refreshToken(): Promise<ApiResponse<{ accessToken: string; refreshToken?: string }>> {
    const refreshToken = localStorage.getItem('refreshToken');
    if (!refreshToken) {
      throw new Error('No refresh token available');
    }

    const response = await apiClient.post<{ accessToken: string; refreshToken?: string }>('/auth/refresh', {
      refreshToken,
    });

    // Update tokens in localStorage if refresh successful
    if (response.success && response.data) {
      localStorage.setItem('accessToken', response.data.accessToken);
      if (response.data.refreshToken) {
        localStorage.setItem('refreshToken', response.data.refreshToken);
      }
    }

    return response;
  }

  /**
   * Get current user from localStorage
   */
  getCurrentUser(): any {
    const userStr = localStorage.getItem('user');
    return userStr ? JSON.parse(userStr) : null;
  }

  /**
   * Check if user is authenticated
   */
  isAuthenticated(): boolean {
    return !!localStorage.getItem('accessToken') && !!localStorage.getItem('user');
  }

  /**
   * Get access token
   */
  getAccessToken(): string | null {
    return localStorage.getItem('accessToken');
  }

  /**
   * Get refresh token
   */
  getRefreshToken(): string | null {
    return localStorage.getItem('refreshToken');
  }

  /**
   * Validate email format
   */
  validateEmail(email: string): boolean {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  }

  /**
   * Validate password strength
   */
  validatePassword(password: string): { isValid: boolean; errors: string[] } {
    const errors: string[] = [];

    if (password.length < 8) {
      errors.push('Password must be at least 8 characters long');
    }

    if (password.length > 128) {
      errors.push('Password must be less than 128 characters');
    }

    if (!/[A-Z]/.test(password)) {
      errors.push('Password must contain at least one uppercase letter');
    }

    if (!/[a-z]/.test(password)) {
      errors.push('Password must contain at least one lowercase letter');
    }

    if (!/\d/.test(password)) {
      errors.push('Password must contain at least one number');
    }

    if (!/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
      errors.push('Password must contain at least one special character');
    }

    return {
      isValid: errors.length === 0,
      errors,
    };
  }

  /**
   * Validate registration data
   */
  validateRegistrationData(data: RegisterRequest): { isValid: boolean; errors: Record<string, string[]> } {
    const errors: Record<string, string[]> = {};

    // Email validation
    if (!data.email) {
      errors.email = ['Email is required'];
    } else if (!this.validateEmail(data.email)) {
      errors.email = ['Please enter a valid email address'];
    }

    // Password validation
    if (!data.password) {
      errors.password = ['Password is required'];
    } else {
      const passwordValidation = this.validatePassword(data.password);
      if (!passwordValidation.isValid) {
        errors.password = passwordValidation.errors;
      }
    }

    // Nickname validation
    if (!data.nickname) {
      errors.nickname = ['Nickname is required'];
    } else if (data.nickname.length < 3) {
      errors.nickname = ['Nickname must be at least 3 characters long'];
    } else if (data.nickname.length > 30) {
      errors.nickname = ['Nickname must be less than 30 characters'];
    } else if (!/^[a-zA-Z0-9_-]+$/.test(data.nickname)) {
      errors.nickname = ['Nickname can only contain letters, numbers, underscores, and hyphens'];
    }

    return {
      isValid: Object.keys(errors).length === 0,
      errors,
    };
  }
}

// Create singleton instance
const authService = new AuthService();

export default authService;