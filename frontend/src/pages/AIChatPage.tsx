import React from 'react';
import { useParams } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import { Button } from '../components/Button/Button';

export const AIChatPage: React.FC = () => {
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
          <h2 className="text-xl font-semibold text-gray-900">AI Chat Assistant</h2>
          <p className="text-sm text-gray-600">
            Get help and insights from the AI assistant
          </p>
        </div>
        <Button variant="primary" size="sm" disabled>
          New Chat
        </Button>
      </div>

      <div className="bg-white rounded-lg shadow p-6">
        <div className="text-center py-12">
          <div className="text-6xl mb-4">🤖</div>
          <h3 className="text-lg font-medium text-gray-900 mb-2">AI Assistant</h3>
          <p className="text-gray-600 mb-4">
            Chat with the AI assistant for project insights and help
          </p>
          <p className="text-sm text-gray-500">
            Coming soon - AI-powered project assistance and insights
          </p>
        </div>
      </div>
    </div>
  );
};
