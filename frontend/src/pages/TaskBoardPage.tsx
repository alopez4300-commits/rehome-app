import React, { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import { LoadingSpinner } from '../components/common/LoadingSpinner';
import { Button } from '../components/Button/Button';

interface Task {
  id: number;
  name: string;
  description?: string;
  status: 'pending' | 'in_progress' | 'completed';
  priority: 'low' | 'medium' | 'high';
  due_date?: string;
  assigned_to: Array<{
    id: number;
    name: string;
    email: string;
  }>;
  created_by: {
    id: number;
    name: string;
  };
  created_at: string;
  updated_at: string;
}

export const TaskBoardPage: React.FC = () => {
  const { workspaceId, projectId } = useParams<{ workspaceId: string; projectId: string }>();
  const { canAccessWorkspace } = useAuth();
  const [tasks, setTasks] = useState<Task[]>([]);
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

    // Fetch tasks
    const fetchTasks = async () => {
      try {
        // TODO: Replace with actual API call
        // const response = await axios.get(`/api/workspaces/${workspaceId}/projects/${projectId}/tasks`);
        
        // Mock data for now
        setTasks([
          {
            id: 1,
            name: 'Design Homepage Layout',
            description: 'Create wireframes and mockups for the new homepage',
            status: 'pending',
            priority: 'high',
            due_date: '2024-02-01',
            assigned_to: [
              { id: 1, name: 'Alice Johnson', email: 'alice@example.com' }
            ],
            created_by: { id: 2, name: 'Bob Smith' },
            created_at: '2024-01-15T10:00:00Z',
            updated_at: '2024-01-20T14:30:00Z',
          },
          {
            id: 2,
            name: 'Implement User Authentication',
            description: 'Set up login and registration functionality',
            status: 'in_progress',
            priority: 'medium',
            due_date: '2024-01-25',
            assigned_to: [
              { id: 3, name: 'Charlie Brown', email: 'charlie@example.com' }
            ],
            created_by: { id: 1, name: 'Alice Johnson' },
            created_at: '2024-01-10T09:00:00Z',
            updated_at: '2024-01-22T16:45:00Z',
          },
          {
            id: 3,
            name: 'Write API Documentation',
            description: 'Document all API endpoints and usage examples',
            status: 'completed',
            priority: 'low',
            assigned_to: [
              { id: 2, name: 'Bob Smith', email: 'bob@example.com' }
            ],
            created_by: { id: 1, name: 'Alice Johnson' },
            created_at: '2024-01-05T14:00:00Z',
            updated_at: '2024-01-18T11:20:00Z',
          },
        ]);
      } catch (err) {
        setError('Failed to load tasks');
      } finally {
        setLoading(false);
      }
    };

    fetchTasks();
  }, [workspaceId, projectId, canAccessWorkspace]);

  const getPriorityColor = (priority: string) => {
    switch (priority) {
      case 'high':
        return 'bg-red-100 text-red-800';
      case 'medium':
        return 'bg-yellow-100 text-yellow-800';
      case 'low':
        return 'bg-green-100 text-green-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'completed':
        return 'bg-green-100 text-green-800';
      case 'in_progress':
        return 'bg-blue-100 text-blue-800';
      case 'pending':
        return 'bg-gray-100 text-gray-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString();
  };

  const isOverdue = (dueDate: string) => {
    return new Date(dueDate) < new Date() && new Date(dueDate).toDateString() !== new Date().toDateString();
  };

  const groupTasksByStatus = (tasks: Task[]) => {
    return {
      pending: tasks.filter(task => task.status === 'pending'),
      in_progress: tasks.filter(task => task.status === 'in_progress'),
      completed: tasks.filter(task => task.status === 'completed'),
    };
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

  const groupedTasks = groupTasksByStatus(tasks);

  return (
    <div className="space-y-6">
      {/* Task Board Header */}
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-xl font-semibold text-gray-900">Task Board</h2>
          <p className="text-sm text-gray-600">
            Manage and track project tasks
          </p>
        </div>
        <Button variant="primary" size="sm" disabled>
          Create Task
        </Button>
      </div>

      {/* Kanban Board */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Pending Tasks */}
        <div className="bg-white rounded-lg shadow">
          <div className="p-4 border-b border-gray-200">
            <h3 className="font-medium text-gray-900">
              Pending ({groupedTasks.pending.length})
            </h3>
          </div>
          <div className="p-4 space-y-3 min-h-[400px]">
            {groupedTasks.pending.map((task) => (
              <div
                key={task.id}
                className="p-3 border border-gray-200 rounded-lg hover:shadow-md transition-shadow cursor-pointer"
              >
                <div className="flex items-start justify-between mb-2">
                  <h4 className="font-medium text-sm text-gray-900">{task.name}</h4>
                  <span className={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${getPriorityColor(task.priority)}`}>
                    {task.priority}
                  </span>
                </div>
                {task.description && (
                  <p className="text-xs text-gray-600 mb-2 line-clamp-2">
                    {task.description}
                  </p>
                )}
                <div className="flex items-center justify-between text-xs text-gray-500">
                  <div className="flex items-center space-x-1">
                    {task.assigned_to.map((user) => (
                      <span key={user.id} className="w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center text-xs">
                        {user.name.charAt(0)}
                      </span>
                    ))}
                  </div>
                  {task.due_date && (
                    <span className={isOverdue(task.due_date) ? 'text-red-600' : ''}>
                      Due {formatDate(task.due_date)}
                    </span>
                  )}
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* In Progress Tasks */}
        <div className="bg-white rounded-lg shadow">
          <div className="p-4 border-b border-gray-200">
            <h3 className="font-medium text-gray-900">
              In Progress ({groupedTasks.in_progress.length})
            </h3>
          </div>
          <div className="p-4 space-y-3 min-h-[400px]">
            {groupedTasks.in_progress.map((task) => (
              <div
                key={task.id}
                className="p-3 border border-gray-200 rounded-lg hover:shadow-md transition-shadow cursor-pointer"
              >
                <div className="flex items-start justify-between mb-2">
                  <h4 className="font-medium text-sm text-gray-900">{task.name}</h4>
                  <span className={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${getPriorityColor(task.priority)}`}>
                    {task.priority}
                  </span>
                </div>
                {task.description && (
                  <p className="text-xs text-gray-600 mb-2 line-clamp-2">
                    {task.description}
                  </p>
                )}
                <div className="flex items-center justify-between text-xs text-gray-500">
                  <div className="flex items-center space-x-1">
                    {task.assigned_to.map((user) => (
                      <span key={user.id} className="w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center text-xs">
                        {user.name.charAt(0)}
                      </span>
                    ))}
                  </div>
                  {task.due_date && (
                    <span className={isOverdue(task.due_date) ? 'text-red-600' : ''}>
                      Due {formatDate(task.due_date)}
                    </span>
                  )}
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Completed Tasks */}
        <div className="bg-white rounded-lg shadow">
          <div className="p-4 border-b border-gray-200">
            <h3 className="font-medium text-gray-900">
              Completed ({groupedTasks.completed.length})
            </h3>
          </div>
          <div className="p-4 space-y-3 min-h-[400px]">
            {groupedTasks.completed.map((task) => (
              <div
                key={task.id}
                className="p-3 border border-gray-200 rounded-lg hover:shadow-md transition-shadow cursor-pointer opacity-75"
              >
                <div className="flex items-start justify-between mb-2">
                  <h4 className="font-medium text-sm text-gray-900 line-through">{task.name}</h4>
                  <span className={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${getPriorityColor(task.priority)}`}>
                    {task.priority}
                  </span>
                </div>
                {task.description && (
                  <p className="text-xs text-gray-600 mb-2 line-clamp-2">
                    {task.description}
                  </p>
                )}
                <div className="flex items-center justify-between text-xs text-gray-500">
                  <div className="flex items-center space-x-1">
                    {task.assigned_to.map((user) => (
                      <span key={user.id} className="w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center text-xs">
                        {user.name.charAt(0)}
                      </span>
                    ))}
                  </div>
                  <span>Completed {formatDate(task.updated_at)}</span>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
};
