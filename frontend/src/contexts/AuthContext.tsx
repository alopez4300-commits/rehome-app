import { createContext, useContext, useEffect, useState, ReactNode } from 'react';
import { authService, User, Workspace } from '../services/auth';

// Types are now imported from ../types

interface AuthContextType {
  user: User | null;
  workspaces: Workspace[];
  loading: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => void;
  refreshUser: () => Promise<void>;
  isAdmin: () => boolean;
  canAccessWorkspace: (workspaceId: number) => boolean;
  getWorkspaceRole: (workspaceId: number) => string | null;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [workspaces, setWorkspaces] = useState<Workspace[]>([]);
  const [loading, setLoading] = useState(true);

  // Token is now handled by the API service

  const login = async (email: string, password: string) => {
    try {
      const response = await authService.login({ email, password });
      const { user: userData, token } = response.data;
      
      // Store token
      localStorage.setItem('auth_token', token);
      
      // Set user
      setUser(userData);
      
      // Fetch workspaces
      await fetchWorkspaces();
    } catch (error) {
      console.error('Login failed:', error);
      throw error;
    }
  };

  const logout = async () => {
    try {
      await authService.logout();
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      localStorage.removeItem('auth_token');
      setUser(null);
      setWorkspaces([]);
    }
  };

  const refreshUser = async () => {
    try {
      const response = await authService.getCurrentUser();
      setUser(response.data.user);
      await fetchWorkspaces();
    } catch (error) {
      console.error('Failed to refresh user:', error);
      logout();
    }
  };

  const fetchWorkspaces = async () => {
    try {
      const response = await authService.getWorkspaces();
      setWorkspaces(response.data.workspaces);
    } catch (error) {
      console.error('Failed to fetch workspaces:', error);
    }
  };

  const isAdmin = () => {
    return user?.has_admin_role || false;
  };

  const canAccessWorkspace = (workspaceId: number) => {
    if (isAdmin()) return true;
    return workspaces.some(ws => ws.id === workspaceId);
  };

  const getWorkspaceRole = (workspaceId: number) => {
    if (isAdmin()) return 'owner';
    const workspace = workspaces.find(ws => ws.id === workspaceId);
    return workspace?.pivot?.role || null;
  };

  // Check authentication on mount
  useEffect(() => {
    const checkAuth = async () => {
      const token = localStorage.getItem('auth_token');
      if (token) {
        try {
          await refreshUser();
        } catch (error) {
          await logout();
        }
      }
      setLoading(false);
    };

    checkAuth();
  }, []);

  const value: AuthContextType = {
    user,
    workspaces,
    loading,
    login,
    logout,
    refreshUser,
    isAdmin,
    canAccessWorkspace,
    getWorkspaceRole,
  };

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
}
