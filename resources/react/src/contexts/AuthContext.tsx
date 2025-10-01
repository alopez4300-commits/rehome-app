import React, { createContext, useContext, useEffect, useState } from 'react'
import axios from 'axios'

export interface User {
  id: number
  name: string
  email: string
  role: 'admin' | 'team' | 'consultant' | 'client'
  has_admin_role: boolean
}

interface AuthContextType {
  user: User | null
  isLoading: boolean
  login: (email: string, password: string) => Promise<void>
  logout: () => Promise<void>
  isAdmin: () => boolean
  canAccess: (requiredRole?: string) => boolean
}

const AuthContext = createContext<AuthContextType | undefined>(undefined)

// Configure axios defaults
axios.defaults.baseURL = 'http://localhost:8000'
axios.defaults.withCredentials = true

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<User | null>(null)
  const [isLoading, setIsLoading] = useState(true)

  // Check for stored token and validate it
  useEffect(() => {
    const checkAuth = async () => {
      console.log('AuthContext: Checking authentication...')
      const token = localStorage.getItem('auth_token')
      console.log('AuthContext: Token found:', !!token)
      
      if (token) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
        try {
          console.log('AuthContext: Making API call to /api/me')
          const response = await axios.get('/api/me')
          console.log('AuthContext: API response:', response.data)
          setUser(response.data.data.user)
        } catch (error) {
          console.log('AuthContext: API call failed:', error)
          // Token is invalid, remove it
          localStorage.removeItem('auth_token')
          delete axios.defaults.headers.common['Authorization']
        }
      }
      console.log('AuthContext: Setting loading to false')
      setIsLoading(false)
    }
    
    checkAuth()
  }, [])

  const login = async (email: string, password: string) => {
    const response = await axios.post('/api/login', { email, password })
    const { user: userData, token } = response.data.data
    
    setUser(userData)
    localStorage.setItem('auth_token', token)
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
  }

  const logout = async () => {
    try {
      await axios.post('/api/logout')
    } catch (error) {
      // Continue with logout even if API call fails
    }
    
    setUser(null)
    localStorage.removeItem('auth_token')
    delete axios.defaults.headers.common['Authorization']
  }

  const isAdmin = () => {
    return user?.has_admin_role ?? false
  }

  const canAccess = (requiredRole?: string) => {
    if (!user) return false
    if (user.has_admin_role) return true // Admins can access everything
    
    if (!requiredRole) return true // No specific role required
    
    // Role hierarchy: admin > team > consultant > client
    const roleHierarchy = ['client', 'consultant', 'team', 'admin']
    const userRoleIndex = roleHierarchy.indexOf(user.role)
    const requiredRoleIndex = roleHierarchy.indexOf(requiredRole)
    
    return userRoleIndex >= requiredRoleIndex
  }

  const value = {
    user,
    isLoading,
    login,
    logout,
    isAdmin,
    canAccess,
  }

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}

export function useAuth() {
  const context = useContext(AuthContext)
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider')
  }
  return context
}