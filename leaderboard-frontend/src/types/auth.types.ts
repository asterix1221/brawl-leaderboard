// Authentication Types for Brawl Stars Leaderboard

export interface User {
  id: string;
  email: string;
  nickname: string;
  createdAt: string;
  updatedAt: string;
}

export interface Tokens {
  accessToken: string;
  refreshToken: string;
}

export interface AuthState {
  user: User | null;
  token: string | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  error: string | null;
}

export interface AuthActions {
  setUser: (user: User | null) => void;
  setToken: (token: string | null) => void;
  setLoading: (loading: boolean) => void;
  setError: (error: string | null) => void;
  login: (email: string, password: string) => Promise<void>;
  register: (email: string, password: string, nickname: string) => Promise<void>;
  logout: () => void;
  refreshToken: () => Promise<void>;
}

export interface LoginCredentials {
  email: string;
  password: string;
}

export interface RegisterData {
  email: string;
  password: string;
  nickname: string;
  confirmPassword?: string;
}

export interface AuthFormData {
  email: string;
  password: string;
  nickname?: string;
  confirmPassword?: string;
  rememberMe?: boolean;
}

export interface ValidationError {
  field: string;
  message: string;
}

export interface AuthValidation {
  isValid: boolean;
  errors: ValidationError[];
}