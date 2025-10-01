import { api } from './api';

export interface LoginRequest {
  email: string;
  password: string;
}

export interface LoginResponse {
  success: boolean;
  data: {
    user: {
      id: number;
      name: string;
      email: string;
      role: string;
      has_admin_role: boolean;
    };
    token: string;
    expires_at: string;
  };
  message: string;
}

export interface User {
  id: number;
  name: string;
  email: string;
  role: string;
  has_admin_role: boolean;
}

export interface Workspace {
  id: number;
  name: string;
  description?: string;
  pivot: {
    role: string;
  };
  projects_count?: number;
  members_count?: number;
  created_at: string;
  updated_at: string;
}

export interface WorkspacesResponse {
  success: boolean;
  data: {
    workspaces: Workspace[];
  };
}

export const authService = {
  async login(credentials: LoginRequest): Promise<LoginResponse> {
    const response = await api.post('/api/login', credentials);
    return response.data;
  },

  async getCurrentUser(): Promise<{ success: boolean; data: { user: User } }> {
    const response = await api.get('/api/me');
    return response.data;
  },

  async getWorkspaces(): Promise<WorkspacesResponse> {
    const response = await api.get('/api/workspaces');
    return response.data;
  },

  async logout(): Promise<void> {
    await api.post('/api/logout');
  },
};
