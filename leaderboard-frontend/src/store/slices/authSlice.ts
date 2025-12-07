import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import { AuthState, AuthActions, User } from '../../types/auth.types';
import authService from '../../services/api/authService';

interface AuthStore extends AuthState, AuthActions {}

export const useAuthStore = create<AuthStore>()(
  persist(
    (set, get) => ({
      // Initial state
      user: null,
      token: null,
      isAuthenticated: false,
      isLoading: false,
      error: null,

      // Actions
      setUser: (user: User | null) => {
        set({ user });
        if (user) {
          set({ isAuthenticated: true });
        } else {
          set({ isAuthenticated: false, token: null });
        }
      },

      setToken: (token: string | null) => {
        set({ token });
        if (token) {
          set({ isAuthenticated: !!get().user });
        } else {
          set({ isAuthenticated: false, user: null });
        }
      },

      setLoading: (isLoading: boolean) => {
        set({ isLoading });
      },

      setError: (error: string | null) => {
        set({ error });
      },

      login: async (email: string, password: string) => {
        set({ isLoading: true, error: null });

        try {
          const response = await authService.login({ email, password });

          if (response.success && response.data) {
            const { user, accessToken } = response.data;
            set({
              user,
              token: accessToken,
              isAuthenticated: true,
              isLoading: false,
              error: null,
            });
          } else {
            set({
              isLoading: false,
              error: response.error || 'Login failed',
            });
          }
        } catch (error: any) {
          set({
            isLoading: false,
            error: error.error || 'An unexpected error occurred during login',
          });
        }
      },

      register: async (email: string, password: string, nickname: string) => {
        set({ isLoading: true, error: null });

        try {
          const response = await authService.register({ email, password, nickname });

          if (response.success && response.data) {
            const { user, accessToken } = response.data;
            set({
              user,
              token: accessToken,
              isAuthenticated: true,
              isLoading: false,
              error: null,
            });
          } else {
            set({
              isLoading: false,
              error: response.error || 'Registration failed',
            });
          }
        } catch (error: any) {
          set({
            isLoading: false,
            error: error.error || 'An unexpected error occurred during registration',
          });
        }
      },

      logout: async () => {
        set({ isLoading: true });

        try {
          await authService.logout();
        } catch (error) {
          console.warn('Logout request failed:', error);
        } finally {
          // Clear state regardless of request success
          set({
            user: null,
            token: null,
            isAuthenticated: false,
            isLoading: false,
            error: null,
          });
        }
      },

      refreshToken: async () => {
        try {
          const response = await authService.refreshToken();

          if (response.success && response.data) {
            set({
              token: response.data.accessToken,
            });
          } else {
            // Refresh failed, logout user
            get().logout();
          }
        } catch (error) {
          // Refresh failed, logout user
          get().logout();
        }
      },
    }),
    {
      name: 'auth-storage',
      partialize: (state) => ({
        user: state.user,
        token: state.token,
        isAuthenticated: state.isAuthenticated,
      }),
    }
  )
);

// Selectors for common use cases
export const useAuth = () => {
  const store = useAuthStore();
  
  return {
    // State
    user: store.user,
    token: store.token,
    isAuthenticated: store.isAuthenticated,
    isLoading: store.isLoading,
    error: store.error,
    
    // Actions
    login: store.login,
    register: store.register,
    logout: store.logout,
    setUser: store.setUser,
    setToken: store.setToken,
    setLoading: store.setLoading,
    setError: store.setError,
    refreshToken: store.refreshToken,
  };
};

// Convenience selectors
export const useCurrentUser = () => useAuthStore((state) => state.user);
export const useIsAuthenticated = () => useAuthStore((state) => state.isAuthenticated);
export const useAuthLoading = () => useAuthStore((state) => state.isLoading);
export const useAuthError = () => useAuthStore((state) => state.error);