import React from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import { Button } from '../components/Button/Button';

export const DashboardPage: React.FC = () => {
  const { user, workspaces, isAdmin } = useAuth();

  const getRoleDescription = (role: string) => {
    switch (role) {
      case 'admin':
        return 'Full system access with admin privileges';
      case 'owner':
        return 'Full workspace control and project management';
      case 'member':
        return 'Standard team member with task and project access';
      case 'consultant':
        return 'Limited access to assigned projects and tasks';
      case 'client':
        return 'Read-only access to project progress and deliverables';
      default:
        return 'Standard user access';
    }
  };

  return (
    <div className="space-y-6">
      {/* Welcome Section */}
      <div className="bg-white rounded-lg shadow p-6">
        <h1 className="text-2xl font-bold text-gray-900">
          Welcome back, {user?.name}!
        </h1>
        <p className="mt-2 text-gray-600">
          {getRoleDescription(user?.role || '')}
        </p>
        {isAdmin() && (
          <div className="mt-4 p-4 bg-red-50 border border-red-200 rounded-md">
            <p className="text-sm text-red-800">
              <strong>System Admin:</strong> You have full access to all workspaces and can manage users, workspaces, and projects from the admin panel.
            </p>
          </div>
        )}
      </div>

      {/* Workspaces Section */}
      <div className="bg-white rounded-lg shadow">
        <div className="p-6 border-b border-gray-200">
          <h2 className="text-lg font-semibold text-gray-900">
            Your Workspaces
          </h2>
          <p className="mt-1 text-sm text-gray-600">
            {isAdmin() 
              ? 'As an admin, you can access all workspaces in the system.'
              : 'Select a workspace to view and manage your projects.'
            }
          </p>
        </div>

        <div className="p-6">
          {!workspaces || workspaces.length === 0 ? (
            <div className="text-center py-8">
              <p className="text-gray-500">No workspaces available</p>
              {isAdmin() && (
                <p className="text-sm text-gray-400 mt-2">
                  Create workspaces from the admin panel
                </p>
              )}
            </div>
          ) : (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              {workspaces?.map((workspace) => (
                <Link
                  key={workspace.id}
                  to={`/workspaces/${workspace.id}`}
                  className="block p-4 border border-gray-200 rounded-lg hover:border-amber-300 hover:shadow-md transition-all"
                >
                  <h3 className="font-medium text-gray-900">{workspace.name}</h3>
                  {workspace.description && (
                    <p className="mt-1 text-sm text-gray-600">{workspace.description}</p>
                  )}
                  <div className="mt-3 flex items-center justify-between text-sm text-gray-500">
                    <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                      {workspace.pivot.role}
                    </span>
                    <div className="flex space-x-4">
                      {workspace.projects_count !== undefined && (
                        <span>{workspace.projects_count} projects</span>
                      )}
                      {workspace.members_count !== undefined && (
                        <span>{workspace.members_count} members</span>
                      )}
                    </div>
                  </div>
                </Link>
              ))}
            </div>
          )}
        </div>
      </div>

      {/* Quick Actions */}
      <div className="bg-white rounded-lg shadow p-6">
        <h2 className="text-lg font-semibold text-gray-900 mb-4">
          Quick Actions
        </h2>
        <div className="flex flex-wrap gap-3">
          {isAdmin() && (
            <Button
              variant="danger"
              size="sm"
              onClick={() => window.open('/system', '_blank')}
            >
              Open Admin Panel
            </Button>
          )}
          <Button variant="primary" size="sm" disabled>
            Create Project
          </Button>
          <Button variant="secondary" size="sm" disabled>
            Invite Team Member
          </Button>
        </div>
      </div>
    </div>
  );
};
