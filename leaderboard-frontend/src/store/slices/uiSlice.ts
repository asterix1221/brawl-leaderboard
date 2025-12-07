import { create } from 'zustand';

export interface Notification {
  id: string;
  type: 'success' | 'error' | 'warning' | 'info';
  title: string;
  message: string;
  duration?: number; // in milliseconds, 0 for persistent
  createdAt: string;
}

interface UIState {
  // State
  theme: 'light' | 'dark' | 'system';
  sidebarOpen: boolean;
  notifications: Notification[];
  isLoading: boolean;
  loadingMessage: string;

  // Actions
  toggleTheme: () => void;
  setTheme: (theme: 'light' | 'dark' | 'system') => void;
  toggleSidebar: () => void;
  setSidebarOpen: (open: boolean) => void;
  addNotification: (notification: Omit<Notification, 'id' | 'createdAt'>) => string;
  removeNotification: (id: string) => void;
  clearNotifications: () => void;
  setLoading: (loading: boolean, message?: string) => void;
}

export const useUIStore = create<UIState>((set, get) => ({
  // Initial state
  theme: 'system',
  sidebarOpen: false,
  notifications: [],
  isLoading: false,
  loadingMessage: '',

  // Actions
  toggleTheme: () => {
    const currentTheme = get().theme;
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    set({ theme: newTheme });
    
    // Apply theme to document
    if (typeof document !== 'undefined') {
      document.documentElement.classList.toggle('dark', newTheme === 'dark');
    }
  },

  setTheme: (theme: 'light' | 'dark' | 'system') => {
    set({ theme });
    
    // Apply theme to document
    if (typeof document !== 'undefined') {
      const isDark = theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
      document.documentElement.classList.toggle('dark', isDark);
    }
  },

  toggleSidebar: () => set((state) => ({ sidebarOpen: !state.sidebarOpen })),

  setSidebarOpen: (open: boolean) => set({ sidebarOpen: open }),

  addNotification: (notification: Omit<Notification, 'id' | 'createdAt'>) => {
    const id = Date.now().toString() + Math.random().toString(36).substr(2, 9);
    const newNotification: Notification = {
      ...notification,
      id,
      createdAt: new Date().toISOString(),
      duration: notification.duration ?? 5000, // Default 5 seconds
    };

    set((state) => ({
      notifications: [...state.notifications, newNotification],
    }));

    // Auto-remove notification after duration
    if (newNotification.duration && newNotification.duration > 0) {
      setTimeout(() => {
        get().removeNotification(id);
      }, newNotification.duration);
    }

    return id;
  },

  removeNotification: (id: string) => {
    set((state) => ({
      notifications: state.notifications.filter((notif) => notif.id !== id),
    }));
  },

  clearNotifications: () => set({ notifications: [] }),

  setLoading: (loading: boolean, message = '') => {
    set({ isLoading: loading, loadingMessage: message });
  },
}));

// Selectors for common use cases
export const useUI = () => {
  const store = useUIStore();
  
  return {
    // State
    theme: store.theme,
    sidebarOpen: store.sidebarOpen,
    notifications: store.notifications,
    isLoading: store.isLoading,
    loadingMessage: store.loadingMessage,
    
    // Actions
    toggleTheme: store.toggleTheme,
    setTheme: store.setTheme,
    toggleSidebar: store.toggleSidebar,
    setSidebarOpen: store.setSidebarOpen,
    addNotification: store.addNotification,
    removeNotification: store.removeNotification,
    clearNotifications: store.clearNotifications,
    setLoading: store.setLoading,
  };
};

// Convenience selectors
export const useTheme = () => useUIStore((state) => state.theme);
export const useSidebarOpen = () => useUIStore((state) => state.sidebarOpen);
export const useNotifications = () => useUIStore((state) => state.notifications);
export const useIsLoading = () => useUIStore((state) => state.isLoading);
export const useLoadingMessage = () => useUIStore((state) => state.loadingMessage);

// Helper functions for common notification types
export const useNotificationHelpers = () => {
  const { addNotification } = useUIStore();
  
  return {
    success: (title: string, message: string, duration?: number) =>
      addNotification({ type: 'success', title, message, duration }),
    
    error: (title: string, message: string, duration?: number) =>
      addNotification({ type: 'error', title, message, duration }),
    
    warning: (title: string, message: string, duration?: number) =>
      addNotification({ type: 'warning', title, message, duration }),
    
    info: (title: string, message: string, duration?: number) =>
      addNotification({ type: 'info', title, message, duration }),
  };
};