import React, { useEffect, useState } from 'react'
import { useAuth } from '../contexts/AuthContext'
import { Link } from 'react-router-dom'
import axios from 'axios'

interface Workspace {
  id: number
  name: string
  description?: string
  owner: {
    id: number
    name: string
  }
  projects_count: number
  users_count: number
  created_at: string
}

export default function DashboardPage() {
  const { user, logout, isAdmin } = useAuth()
  const [workspaces, setWorkspaces] = useState<Workspace[]>([])
  const [isLoading, setIsLoading] = useState(true)

  useEffect(() => {
    const fetchWorkspaces = async () => {
      try {
        const response = await axios.get('/api/workspaces')
        setWorkspaces(response.data.data)
      } catch (error) {
        console.error('Failed to fetch workspaces:', error)
      } finally {
        setIsLoading(false)
      }
    }

    fetchWorkspaces()
  }, [])

  const getRoleBadgeColor = (role: string) => {
    switch (role) {
      case 'admin': return 'bg-red-100 text-red-800'
      case 'team': return 'bg-green-100 text-green-800'
      case 'consultant': return 'bg-yellow-100 text-yellow-800'
      case 'client': return 'bg-blue-100 text-blue-800'
      default: return 'bg-gray-100 text-gray-800'
    }
  }

  const getRoleDisplayName = (role: string) => {
    switch (role) {
      case 'admin': return 'System Admin'
      case 'team': return 'Team Member'
      case 'consultant': return 'Consultant'
      case 'client': return 'Client'
      default: return role
    }
  }

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-white shadow">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center py-6">
            <div className="flex items-center">
              <h1 className="text-3xl font-bold text-gray-900">ReHome v2</h1>
              {isAdmin() && (
                <span className="ml-4 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                  System Admin
                </span>
              )}
            </div>
            <div className="flex items-center space-x-4">
              <div className="flex items-center space-x-3">
                <div className="text-sm">
                  <div className="font-medium text-gray-900">{user?.name}</div>
                  <div className="text-gray-500">{user?.email}</div>
                </div>
                <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getRoleBadgeColor(user?.role || '')}`}>
                  {getRoleDisplayName(user?.role || '')}
                </span>
              </div>
              <button
                onClick={logout}
                className="bg-gray-800 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-700"
              >
                Sign out
              </button>
            </div>
          </div>
        </div>
      </header>

      {/* Main Content */}
      <main className="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div className="px-4 py-6 sm:px-0">
          <div className="mb-8">
            <h2 className="text-2xl font-bold text-gray-900 mb-4">Welcome back, {user?.name}!</h2>
            <p className="text-gray-600">
              {isAdmin() 
                ? "You have system administrator access to all workspaces and projects."
                : `You have ${user?.role} access to your assigned workspaces.`
              }
            </p>
          </div>

          {/* Workspaces Grid */}
          <div>
            <div className="flex justify-between items-center mb-6">
              <h3 className="text-lg font-medium text-gray-900">
                {isAdmin() ? 'All Workspaces' : 'Your Workspaces'}
              </h3>
              <span className="text-sm text-gray-500">
                {workspaces.length} workspace{workspaces.length !== 1 ? 's' : ''}
              </span>
            </div>

            {isLoading ? (
              <div className="text-center py-12">
                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mx-auto"></div>
                <p className="mt-2 text-gray-500">Loading workspaces...</p>
              </div>
            ) : workspaces.length === 0 ? (
              <div className="text-center py-12">
                <p className="text-gray-500">No workspaces found.</p>
              </div>
            ) : (
              <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                {workspaces.map((workspace) => (
                  <Link
                    key={workspace.id}
                    to={`/workspaces/${workspace.id}`}
                    className="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow duration-200"
                  >
                    <div className="p-6">
                      <div className="flex items-center justify-between">
                        <h4 className="text-lg font-medium text-gray-900 truncate">
                          {workspace.name}
                        </h4>
                      </div>
                      {workspace.description && (
                        <p className="mt-2 text-sm text-gray-600 line-clamp-2">
                          {workspace.description}
                        </p>
                      )}
                      <div className="mt-4 flex items-center justify-between text-sm text-gray-500">
                        <span>{workspace.projects_count} projects</span>
                        <span>{workspace.users_count} members</span>
                      </div>
                      <div className="mt-2 text-xs text-gray-400">
                        Owner: {workspace.owner.name}
                      </div>
                    </div>
                  </Link>
                ))}
              </div>
            )}
          </div>
        </div>
      </main>
    </div>
  )
}