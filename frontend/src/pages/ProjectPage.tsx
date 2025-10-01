import React, { useEffect, useState } from 'react';
import { useParams, Outlet } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import { LoadingSpinner } from '../components/common/LoadingSpinner';

export const ProjectPage: React.FC = () => {
  const { workspaceId, projectId } = useParams<{ workspaceId: string; projectId: string }>();
  const { canAccessWorkspace } = useAuth();
  const [project, setProject] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    if (!workspaceId || !projectId) return;

    // Check access
    if (!canAccessWorkspace(parseInt(workspaceId))) {
      setError('You do not have access to this workspace');
      setLoading(false);
      return;
    }

    // Fetch project data
    const fetchProject = async () => {
      try {
        // TODO: Replace with actual API call
        // const response = await axios.get(`/api/workspaces/${workspaceId}/projects/${projectId}`);
        
        // Mock data for now
        setProject({
          id: parseInt(projectId),
          name: 'Website Redesign',
          description: 'Complete redesign of the company website with modern UI/UX',
          status: 'in_progress',
          created_at: '2024-01-15T10:00:00Z',
          updated_at: '2024-01-20T14:30:00Z',
        });
      } catch (err) {
        setError('Failed to load project data');
      } finally {
        setLoading(false);
      }
    };

    fetchProject();
  }, [workspaceId, projectId, canAccessWorkspace]);

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

  return (
    <div className="space-y-6">
      {/* Project Header */}
      <div className="bg-white rounded-lg shadow p-6">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-2xl font-bold text-gray-900">{project?.name}</h1>
            {project?.description && (
              <p className="mt-1 text-gray-600">{project.description}</p>
            )}
            <div className="mt-2 flex items-center space-x-4">
              <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                {project?.status?.replace('_', ' ')}
              </span>
              <span className="text-sm text-gray-500">
                Updated {new Date(project?.updated_at).toLocaleDateString()}
              </span>
            </div>
          </div>
        </div>
      </div>

      {/* Project Content */}
      <Outlet />
    </div>
  );
};
