# ReHome v2 - React SPA Authentication Guide for Codespaces

## Overview

ReHome v2 uses **Laravel Sanctum** for SPA authentication with cookie-based sessions. This guide provides all the information needed to implement React SPA authentication in Codespaces.

## Authentication Architecture

### Backend (Laravel)
- **Primary Auth**: Laravel Sanctum (cookie-based for SPA)
- **Admin Panel**: Filament with session auth at `/admin`
- **API Routes**: Protected with `auth:sanctum` middleware
- **User Model**: `App\Models\User` with `HasApiTokens` trait

### Frontend (React SPA)
- **Authentication**: Cookie-based via Sanctum
- **CSRF Protection**: Required for stateful requests
- **Session Management**: Automatic via cookies

## Key Configuration

### Environment Variables
```env
# Backend URLs
APP_URL=http://localhost:8000
SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost:8000

# Session Configuration
SESSION_DRIVER=file
SESSION_DOMAIN=localhost
SESSION_LIFETIME=120

# CORS (if needed)
CORS_ALLOWED_ORIGINS=http://localhost:3000
```

### Sanctum Configuration
- **Stateful Domains**: `localhost:3000,localhost:8000`
- **Guard**: `web` (session-based)
- **CSRF Cookie**: Available at `/sanctum/csrf-cookie`

## API Endpoints

### Authentication Endpoints
```javascript
// Get CSRF cookie (required for stateful requests)
GET /sanctum/csrf-cookie

// Get current user
GET /api/user

// Logout (if implemented)
POST /api/logout
```

### Protected API Endpoints
All API routes require `auth:sanctum` middleware:

```javascript
// MyHome System
GET /api/projects/{id}/myhome/feed
POST /api/projects/{id}/myhome/notes
GET /api/projects/{id}/myhome/stats

// AI Agent
POST /api/projects/{id}/agent/chat
GET /api/projects/{id}/agent/stats
GET /api/agent/user-stats
```

## User Model Structure

```typescript
interface User {
  id: number;
  name: string;
  email: string;
  has_admin_role: boolean;
  role: string;
  email_verified_at: string | null;
  created_at: string;
  updated_at: string;
  
  // Relationships
  workspaces: Workspace[];
}

interface Workspace {
  id: number;
  name: string;
  owner_id: number;
  pivot: {
    role: 'owner' | 'member' | 'consultant' | 'client';
  };
}
```

## React SPA Implementation

### 1. Authentication Setup

```typescript
// auth.ts
import axios from 'axios';

const API_BASE_URL = 'http://localhost:8000';

// Configure axios defaults
axios.defaults.baseURL = API_BASE_URL;
axios.defaults.withCredentials = true; // Important for cookies

// Get CSRF cookie
export const getCsrfCookie = async () => {
  await axios.get('/sanctum/csrf-cookie');
};

// Get current user
export const getCurrentUser = async (): Promise<User | null> => {
  try {
    const response = await axios.get('/api/user');
    return response.data;
  } catch (error) {
    return null;
  }
};

// Login (if implementing custom login)
export const login = async (email: string, password: string) => {
  await getCsrfCookie();
  
  const response = await axios.post('/login', {
    email,
    password,
  });
  
  return response.data;
};
```

### 2. Authentication Context

```typescript
// AuthContext.tsx
import React, { createContext, useContext, useEffect, useState } from 'react';
import { getCurrentUser, getCsrfCookie } from './auth';

interface AuthContextType {
  user: User | null;
  loading: boolean;
  isAuthenticated: boolean;
  isAdmin: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => void;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    initializeAuth();
  }, []);

  const initializeAuth = async () => {
    try {
      await getCsrfCookie();
      const userData = await getCurrentUser();
      setUser(userData);
    } catch (error) {
      console.error('Auth initialization failed:', error);
    } finally {
      setLoading(false);
    }
  };

  const login = async (email: string, password: string) => {
    // Implementation depends on your login flow
    // Could redirect to /admin for Filament login
    // Or implement custom login endpoint
  };

  const logout = () => {
    setUser(null);
    // Redirect to login or clear session
  };

  const value = {
    user,
    loading,
    isAuthenticated: !!user,
    isAdmin: user?.has_admin_role || false,
    login,
    logout,
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};
```

### 3. Protected Routes

```typescript
// ProtectedRoute.tsx
import React from 'react';
import { useAuth } from './AuthContext';
import { Navigate } from 'react-router-dom';

interface ProtectedRouteProps {
  children: React.ReactNode;
  requireAdmin?: boolean;
}

export const ProtectedRoute: React.FC<ProtectedRouteProps> = ({ 
  children, 
  requireAdmin = false 
}) => {
  const { user, loading, isAuthenticated, isAdmin } = useAuth();

  if (loading) {
    return <div>Loading...</div>;
  }

  if (!isAuthenticated) {
    return <Navigate to="/login" replace />;
  }

  if (requireAdmin && !isAdmin) {
    return <Navigate to="/unauthorized" replace />;
  }

  return <>{children}</>;
};
```

### 4. API Service

```typescript
// api.ts
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  withCredentials: true,
});

// Request interceptor for CSRF
api.interceptors.request.use(async (config) => {
  if (config.method !== 'get') {
    await axios.get('http://localhost:8000/sanctum/csrf-cookie');
  }
  return config;
});

// Response interceptor for auth errors
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Handle unauthorized - redirect to login
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default api;
```

## Authentication Flow

### 1. Initial Setup
```typescript
// App.tsx
import { AuthProvider } from './AuthContext';
import { BrowserRouter } from 'react-router-dom';

function App() {
  return (
    <BrowserRouter>
      <AuthProvider>
        <Routes>
          <Route path="/login" element={<LoginPage />} />
          <Route path="/" element={
            <ProtectedRoute>
              <Dashboard />
            </ProtectedRoute>
          } />
          <Route path="/admin" element={
            <ProtectedRoute requireAdmin>
              <AdminPanel />
            </ProtectedRoute>
          } />
        </Routes>
      </AuthProvider>
    </BrowserRouter>
  );
}
```

### 2. Login Options

**Option A: Redirect to Filament Admin Panel**
```typescript
const login = () => {
  window.location.href = 'http://localhost:8000/admin';
};
```

**Option B: Custom Login Form**
```typescript
const login = async (email: string, password: string) => {
  await getCsrfCookie();
  
  const response = await axios.post('/login', {
    email,
    password,
  });
  
  // Refresh user data
  const user = await getCurrentUser();
  setUser(user);
};
```

## User Roles and Permissions

### Role System
- **Admin**: `has_admin_role = true` - Full access to everything
- **Workspace Roles**: `owner`, `member`, `consultant`, `client`
- **Admin Bypass**: Admins act as owners in all workspaces

### Permission Checking
```typescript
// Check if user can access project
const canAccessProject = (project: Project, user: User): boolean => {
  if (user.has_admin_role) return true;
  
  return user.workspaces.some(workspace => 
    workspace.id === project.workspace_id
  );
};

// Get user's role in workspace
const getWorkspaceRole = (workspaceId: number, user: User): string | null => {
  if (user.has_admin_role) return 'owner';
  
  const workspace = user.workspaces.find(w => w.id === workspaceId);
  return workspace?.pivot.role || null;
};
```

## MyHome System Integration

### API Usage
```typescript
// Get project activity feed
const getActivityFeed = async (projectId: number) => {
  const response = await api.get(`/projects/${projectId}/myhome/feed`);
  return response.data;
};

// Create a note
const createNote = async (projectId: number, text: string) => {
  const response = await api.post(`/projects/${projectId}/myhome/notes`, {
    text
  });
  return response.data;
};

// AI Chat
const sendAIMessage = async (projectId: number, query: string) => {
  const response = await api.post(`/projects/${projectId}/agent/chat`, {
    query
  });
  return response.data;
};
```

## Development Setup

### 1. Environment Variables
```env
# React App (.env)
REACT_APP_API_URL=http://localhost:8000
REACT_APP_ADMIN_URL=http://localhost:8000/admin
```

### 2. Package Dependencies
```json
{
  "dependencies": {
    "react": "^18.0.0",
    "react-router-dom": "^6.0.0",
    "axios": "^1.6.0",
    "@types/react": "^18.0.0",
    "@types/react-dom": "^18.0.0"
  }
}
```

### 3. Development Server
```json
{
  "scripts": {
    "dev": "react-scripts start",
    "build": "react-scripts build",
    "test": "react-scripts test"
  }
}
```

## Testing Authentication

### 1. Test User
- **Email**: `admin@rehome.com`
- **Password**: `password`
- **Role**: Admin (has_admin_role = true)

### 2. Test Endpoints
```bash
# Get CSRF cookie
curl -c cookies.txt http://localhost:8000/sanctum/csrf-cookie

# Get current user (after login)
curl -b cookies.txt http://localhost:8000/api/user

# Test MyHome API
curl -b cookies.txt http://localhost:8000/api/projects/1/myhome/feed
```

## Common Issues and Solutions

### 1. CORS Issues
- Ensure `SANCTUM_STATEFUL_DOMAINS` includes your frontend URL
- Set `withCredentials: true` in axios
- Check CORS middleware configuration

### 2. CSRF Token Issues
- Always call `/sanctum/csrf-cookie` before POST requests
- Ensure cookies are being sent with requests
- Check session configuration

### 3. Authentication Failures
- Verify user exists in database
- Check session storage and cookies
- Ensure API routes use `auth:sanctum` middleware

## Next Steps

1. **Implement Login Flow**: Choose between Filament redirect or custom form
2. **Build Dashboard**: Create main SPA interface
3. **Integrate MyHome**: Connect to activity logging system
4. **Add Task Management**: Implement Phase 3 features
5. **Test Authentication**: Verify all flows work correctly

## Resources

- [Laravel Sanctum Documentation](https://laravel.com/docs/sanctum)
- [React Router Documentation](https://reactrouter.com/)
- [Axios Documentation](https://axios-http.com/)
- [ReHome v2 README](./README.md)
