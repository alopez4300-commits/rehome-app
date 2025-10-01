import React from 'react';
import { useParams } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import { Button } from '../components/Button/Button';

export const TimeTrackingPage: React.FC = () => {
  const { workspaceId, projectId } = useParams<{ workspaceId: string; projectId: string }>();
  const { canAccessWorkspace } = useAuth();

  if (!canAccessWorkspace(parseInt(workspaceId!))) {
    return (
      <div className="text-center py-8">
        <p className="text-red-600">You do not have access to this workspace</p>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-xl font-semibold text-gray-900">Time Tracking</h2>
          <p className="text-sm text-gray-600">
            Track time spent on project tasks
          </p>
        </div>
        <Button variant="primary" size="sm" disabled>
          Log Time
        </Button>
      </div>

      <div className="bg-white rounded-lg shadow p-6">
        <div className="text-center py-12">
          <div className="text-6xl mb-4">⏱️</div>
          <h3 className="text-lg font-medium text-gray-900 mb-2">Time Tracking</h3>
          <p className="text-gray-600 mb-4">
            Track and manage time spent on project tasks
          </p>
          <p className="text-sm text-gray-500">
            Coming soon - Time logging, reports, and analytics
          </p>
        </div>
      </div>
    </div>
  );
};
