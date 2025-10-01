import React, { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import { LoadingSpinner } from '../components/common/LoadingSpinner';
import { Button } from '../components/Button/Button';

interface Project {
  id: number;
  name: string;
  description?: string;
  status: string;
  created_at: string;
  updated_at: string;
}

export const WorkspacePage: React.FC = () => {
  const { workspaceId } = useParams<{ workspaceId: string }>();
  const { user, canAccessWorkspace, getWorkspaceRole } = useAuth();
  const [workspace, setWorkspace] = useState<any>(null);
  const [projects, setProjects] = useState<Project[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    if (!workspaceId) return;

    // Check access
    if (!canAccessWorkspace(parseInt(workspaceId))) {
      setError('You do not have access to this workspace');
      setLoading(false);
      return;
    }

    // Fetch workspace and projects
    const fetchData = async () => {
      try {
        // TODO: Replace with actual API calls
        // const workspaceResponse = await axios.get(`/api/workspaces/${workspaceId}`);
        // const projectsResponse = await axios.get(`/api/workspaces/${workspaceId}/projects`);
        
        // Mock data for now
        setWorkspace({
          id: parseInt(workspaceId),
          name: 'Demo Workspace',
          description: 'A demonstration workspace for the ReHome v2 platform',
        });
        
        setProjects([
          {
            id: 1,
            name: 'Website Redesign',
            description: 'Complete redesign of the company website',
            status: 'in_progress',
            created_at: '2024-01-15T10:00:00Z',
            updated_at: '2024-01-20T14:30:00Z',
          },
          {
            id: 2,
            name: 'Mobile App Development',
            description: 'New mobile application for iOS and Android',
            status: 'planning',
            created_at: '2024-01-10T09:00:00Z',
            updated_at: '2024-01-18T16:45:00Z',
          },
        ]);
      } catch (err) {
        setError('Failed to load workspace data');
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, [workspaceId, canAccessWorkspace]);

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'completed':
        return 'bg-green-100 text-green-800';
      case 'in_progress':
        return 'bg-blue-100 text-blue-800';
      case 'planning':
        return 'bg-yellow-100 text-yellow-800';
      case 'on_hold':
        return 'bg-gray-100 text-gray-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString();
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <LoadingSpinner size="lg" />
      </div>
    );
  }

  if (error) {
    return (
      <div className="text-center py-8">
        <p className="text-red-600">{error}</p>
      </div>
    );
  }

  const userRole = getWorkspaceRole(parseInt(workspaceId!));

  return (
    <div className="space-y-6">
      {/* Workspace Header */}
      <div className="bg-white rounded-lg shadow p-6">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-2xl font-bold text-gray-900">{workspace?.name}</h1>
            {workspace?.description && (
              <p className="mt-1 text-gray-600">{workspace.description}</p>
            )}
            <div className="mt-2">
              <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                Your role: {userRole}
              </span>
            </div>
          </div>
          <div className="flex space-x-3">
            <Button variant="primary" size="sm" disabled>
              Create Project
            </Button>
            {userRole === 'owner' && (
              <Button variant="secondary" size="sm" disabled>
                Manage Workspace
              </Button>
            )}
          </div>
        </div>
      </div>

      {/* Projects Section */}
      <div className="bg-white rounded-lg shadow">
        <div className="p-6 border-b border-gray-200">
          <h2 className="text-lg font-semibold text-gray-900">
            Projects ({projects.length})
          </h2>
          <p className="mt-1 text-sm text-gray-600">
            Manage and track your workspace projects
          </p>
        </div>

        <div className="p-6">
          {projects.length === 0 ? (
            <div className="text-center py-8">
              <p className="text-gray-500">No projects yet</p>
              <Button variant="primary" size="sm" className="mt-4" disabled>
                Create Your First Project
              </Button>
            </div>
          ) : (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              {projects.map((project) => (
                <Link
                  key={project.id}
                  to={`/workspaces/${workspaceId}/projects/${project.id}`}
                  className="block p-4 border border-gray-200 rounded-lg hover:border-amber-300 hover:shadow-md transition-all"
                >
                  <h3 className="font-medium text-gray-900">{project.name}</h3>
                  {project.description && (
                    <p className="mt-1 text-sm text-gray-600 line-clamp-2">
                      {project.description}
                    </p>
                  )}
                  <div className="mt-3 flex items-center justify-between">
                    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusColor(project.status)}`}>
                      {project.status.replace('_', ' ')}
                    </span>
                    <span className="text-xs text-gray-500">
                      Updated {formatDate(project.updated_at)}
                    </span>
                  </div>
                </Link>
              ))}
            </div>
          )}
        </div>
      </div>

      {/* Workspace Stats */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div className="bg-white rounded-lg shadow p-6">
          <div className="flex items-center">
            <div className="flex-shrink-0">
              <div className="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center">
                <span className="text-blue-600 text-sm font-medium">📊</span>
              </div>
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-500">Total Projects</p>
              <p className="text-2xl font-semibold text-gray-900">{projects.length}</p>
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow p-6">
          <div className="flex items-center">
            <div className="flex-shrink-0">
              <div className="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center">
                <span className="text-green-600 text-sm font-medium">✅</span>
              </div>
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-500">Active Projects</p>
              <p className="text-2xl font-semibold text-gray-900">
                {projects.filter(p => p.status === 'in_progress').length}
              </p>
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow p-6">
          <div className="flex items-center">
            <div className="flex-shrink-0">
              <div className="w-8 h-8 bg-yellow-100 rounded-md flex items-center justify-center">
                <span className="text-yellow-600 text-sm font-medium">⏳</span>
              </div>
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-500">Planning</p>
              <p className="text-2xl font-semibold text-gray-900">
                {projects.filter(p => p.status === 'planning').length}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
