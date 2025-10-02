import React from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import { Button } from '../components/Button/Button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

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
      <Card>
        <CardHeader>
          <CardTitle className="text-2xl">
            Welcome back, {user?.name}!
          </CardTitle>
          <CardDescription>
            {getRoleDescription(user?.role || '')}
          </CardDescription>
        </CardHeader>
        {isAdmin() && (
          <CardContent>
            <div className="p-4 bg-destructive/10 border border-destructive/20 rounded-md">
              <p className="text-sm text-destructive">
                <strong>System Admin:</strong> You have full access to all workspaces and can manage users, workspaces, and projects from the admin panel.
              </p>
            </div>
          </CardContent>
        )}
      </Card>

      {/* Workspaces Section */}
      <Card>
        <CardHeader>
          <CardTitle>Your Workspaces</CardTitle>
          <CardDescription>
            {isAdmin() 
              ? 'As an admin, you can access all workspaces in the system.'
              : 'Select a workspace to view and manage your projects.'
            }
          </CardDescription>
        </CardHeader>
        <CardContent>
          {!workspaces || workspaces.length === 0 ? (
            <div className="text-center py-8">
              <p className="text-muted-foreground">No workspaces available</p>
              {isAdmin() && (
                <p className="text-sm text-muted-foreground mt-2">
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
                  className="block"
                >
                  <Card className="hover:shadow-md transition-all cursor-pointer">
                    <CardHeader>
                      <CardTitle className="text-lg">{workspace.name}</CardTitle>
                      {workspace.description && (
                        <CardDescription>{workspace.description}</CardDescription>
                      )}
                    </CardHeader>
                    <CardContent>
                      <div className="flex items-center justify-between">
                        <Badge variant="secondary">
                          {workspace.pivot.role}
                        </Badge>
                        <div className="flex space-x-4 text-sm text-muted-foreground">
                          {workspace.projects_count !== undefined && (
                            <span>{workspace.projects_count} projects</span>
                          )}
                          {workspace.members_count !== undefined && (
                            <span>{workspace.members_count} members</span>
                          )}
                        </div>
                      </div>
                    </CardContent>
                  </Card>
                </Link>
              ))}
            </div>
          )}
        </CardContent>
      </Card>

      {/* Quick Actions */}
      <Card>
        <CardHeader>
          <CardTitle>Quick Actions</CardTitle>
        </CardHeader>
        <CardContent>
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
        </CardContent>
      </Card>
    </div>
  );
};
