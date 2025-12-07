import { useState } from 'react';
import authService from '../services/api/authService';
import { useAuthStore } from '../store/slices/authSlice';
import type { RegisterRequest, LoginRequest } from '../types/api.types';

export const useAuth = () => {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const { setUser, setToken, logout } = useAuthStore();

  const register = async (data: RegisterRequest) => {
    setLoading(true);
    setError(null);
    
    try {
      const response = await authService.register(data);
      setUser(response.user);
      setToken(response.accessToken);
      return response;
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : 'Registration failed';
      setError(errorMessage);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const login = async (data: LoginRequest) => {
    setLoading(true);
    setError(null);
    
    try {
      const response = await authService.login(data);
      setUser(response.user);
      setToken(response.accessToken);
      return response;
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : 'Login failed';
      setError(errorMessage);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const logoutUser = () => {
    logout();
    setError(null);
  };

  const refresh = async () => {
    try {
      const response = await authService.refreshToken();
      if (response.success && response.data) {
        setToken(response.data.accessToken);
        return response.data;
      }
      throw new Error('Refresh failed');
    } catch (err) {
      logoutUser();
      throw err;
    }
  };

  return {
    loading,
    error,
    register,
    login,
    logout: logoutUser,
    refresh,
  };
};