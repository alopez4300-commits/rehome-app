import React, { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import { ActivityFeed, MyHomeEntry } from '../components/MyHome/ActivityFeed';
import { LoadingSpinner } from '../components/common/LoadingSpinner';
import { Button } from '../components/Button/Button';

export const ActivityFeedPage: React.FC = () => {
  const { workspaceId, projectId } = useParams<{ workspaceId: string; projectId: string }>();
  const { canAccessWorkspace } = useAuth();
  const [entries, setEntries] = useState<MyHomeEntry[]>([]);
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

    // Fetch MyHome entries
    const fetchEntries = async () => {
      try {
        // TODO: Replace with actual API call
        // const response = await axios.get(`/api/workspaces/${workspaceId}/projects/${projectId}/myhome/feed`);
        
        // Mock data for now
        setEntries([
          {
            id: 1,
            ts: '2024-01-20T14:30:00Z',
            author: 1,
            author_name: 'Alice Johnson',
            kind: 'note',
            text: 'Client approved the new homepage design. Moving forward with implementation.',
          },
          {
            id: 2,
            ts: '2024-01-20T13:15:00Z',
            author: 2,
            author_name: 'Bob Smith',
            kind: '/task',
            title: 'Design Homepage Layout',
            status: 'in_progress',
          },
          {
            id: 3,
            ts: '2024-01-20T12:00:00Z',
            author: 3,
            author_name: 'Charlie Brown',
            kind: '/time',
            hours: 2.5,
            task: 'Implement User Authentication',
            description: 'Worked on login form validation and error handling',
          },
          {
            id: 4,
            ts: '2024-01-20T10:45:00Z',
            author: 1,
            author_name: 'Alice Johnson',
            kind: '/file',
            path: 'assets/documents/design-specs.pdf',
            size: 1024000,
            type: 'application/pdf',
          },
          {
            id: 5,
            ts: '2024-01-20T09:30:00Z',
            author: 2,
            author_name: 'Bob Smith',
            kind: '/ai.prompt',
            prompt: 'What are the best practices for responsive web design?',
          },
          {
            id: 6,
            ts: '2024-01-20T09:30:05Z',
            author: 2,
            author_name: 'Bob Smith',
            kind: '/ai.response',
            text: 'Here are the key best practices for responsive web design:\n\n1. Mobile-first approach\n2. Flexible grid systems\n3. Responsive images\n4. Touch-friendly interfaces\n5. Performance optimization',
            metadata: {
              provider: 'OpenAI',
              model: 'gpt-4o-mini',
              tokens_used: 150,
              response_time: 1200,
            },
          },
          {
            id: 7,
            ts: '2024-01-19T16:20:00Z',
            author: 3,
            author_name: 'Charlie Brown',
            kind: 'note',
            text: 'Completed API documentation for authentication endpoints. Ready for review.',
          },
          {
            id: 8,
            ts: '2024-01-19T14:10:00Z',
            author: 1,
            author_name: 'Alice Johnson',
            kind: '/task',
            title: 'Write API Documentation',
            status: 'completed',
          },
        ]);
      } catch (err) {
        setError('Failed to load activity feed');
      } finally {
        setLoading(false);
      }
    };

    fetchEntries();
  }, [workspaceId, projectId, canAccessWorkspace]);

  const handleLoadMore = () => {
    // TODO: Implement pagination
    console.log('Load more entries');
  };

  const handleEntryClick = (entry: MyHomeEntry) => {
    // TODO: Handle entry click (e.g., open task details, file preview, etc.)
    console.log('Entry clicked:', entry);
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

  return (
    <div className="space-y-6">
      {/* Activity Feed Header */}
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-xl font-semibold text-gray-900">Activity Feed</h2>
          <p className="text-sm text-gray-600">
            Recent project activity and updates
          </p>
        </div>
        <div className="flex space-x-3">
          <Button variant="secondary" size="sm" disabled>
            Filter
          </Button>
          <Button variant="primary" size="sm" disabled>
            Add Note
          </Button>
        </div>
      </div>

      {/* Activity Feed */}
      <div className="bg-white rounded-lg shadow">
        <div className="p-6">
          <ActivityFeed
            entries={entries}
            loading={loading}
            onLoadMore={handleLoadMore}
            onEntryClick={handleEntryClick}
          />
        </div>
      </div>
    </div>
  );
};
