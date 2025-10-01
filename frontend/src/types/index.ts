// User types
export interface User {
  id: number;
  name: string;
  email: string;
  role: string;
  has_admin_role: boolean;
}

// Workspace types
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

// Project types
export interface Project {
  id: number;
  name: string;
  description?: string;
  status: string;
  created_at: string;
  updated_at: string;
}

// Task types
export interface Task {
  id: number;
  name: string;
  description?: string;
  status: 'pending' | 'in_progress' | 'completed';
  priority: 'low' | 'medium' | 'high';
  due_date?: string;
  assigned_to: Array<{
    id: number;
    name: string;
    email: string;
  }>;
  created_by: {
    id: number;
    name: string;
  };
  created_at: string;
  updated_at: string;
}

// MyHome types
export interface MyHomeEntry {
  id: number;
  ts: string;
  author: number;
  author_name: string;
  kind: string;
  text?: string;
  title?: string;
  status?: string;
  hours?: number;
  task?: string;
  path?: string;
  size?: number;
  type?: string;
  prompt?: string;
  metadata?: {
    provider?: string;
    model?: string;
    tokens_used?: number;
    response_time?: number;
  };
}

// API Response types
export interface ApiResponse<T> {
  success: boolean;
  data: T;
  message?: string;
}

export interface PaginatedResponse<T> {
  success: boolean;
  data: {
    items: T[];
    pagination: {
      current_page: number;
      last_page: number;
      per_page: number;
      total: number;
    };
  };
}

// Auth types
export interface LoginRequest {
  email: string;
  password: string;
}

export interface LoginResponse {
  success: boolean;
  data: {
    user: User;
    token: string;
    expires_at: string;
  };
  message: string;
}

// Role types
export type UserRole = 'admin' | 'owner' | 'member' | 'consultant' | 'client';
export type TaskStatus = 'pending' | 'in_progress' | 'completed';
export type TaskPriority = 'low' | 'medium' | 'high';
export type ProjectStatus = 'planning' | 'in_progress' | 'completed' | 'on_hold';
