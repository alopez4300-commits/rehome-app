import React from 'react';
import { clsx } from 'clsx';

export interface MyHomeEntry {
  id: number;
  ts: string;
  author: number;
  author_name: string;
  kind: string;
  text?: string;
  title?: string;
  status?: string;
  hours?: number;
  task?: string;
  path?: string;
  size?: number;
  type?: string;
  prompt?: string;
  metadata?: {
    provider?: string;
    model?: string;
    tokens_used?: number;
    response_time?: number;
  };
}

export interface ActivityFeedProps {
  entries: MyHomeEntry[];
  loading?: boolean;
  onLoadMore?: () => void;
  onEntryClick?: (entry: MyHomeEntry) => void;
}

export const ActivityFeed: React.FC<ActivityFeedProps> = ({
  entries,
  loading = false,
  onLoadMore,
  onEntryClick,
}) => {
  const formatTimestamp = (ts: string) => {
    return new Date(ts).toLocaleString();
  };

  const getEntryIcon = (kind: string) => {
    switch (kind) {
      case 'note':
        return '📝';
      case '/task':
        return '📋';
      case '/time':
        return '⏱️';
      case '/file':
        return '📁';
      case '/ai.prompt':
        return '🤖';
      case '/ai.response':
        return '💬';
      default:
        return '📄';
    }
  };

  const getEntryContent = (entry: MyHomeEntry) => {
    switch (entry.kind) {
      case 'note':
        return entry.text;
      case '/task':
        return `${entry.title} (${entry.status})`;
      case '/time':
        return `${entry.hours}h on ${entry.task}`;
      case '/file':
        return `Uploaded ${entry.path}`;
      case '/ai.prompt':
        return `AI: ${entry.prompt}`;
      case '/ai.response':
        return entry.text;
      default:
        return 'Unknown entry type';
    }
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center p-8">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-amber-600"></div>
      </div>
    );
  }

  if (entries.length === 0) {
    return (
      <div className="text-center p-8 text-gray-500">
        <p>No activity yet</p>
        <p className="text-sm">Start by creating a note or task</p>
      </div>
    );
  }

  return (
    <div className="space-y-4">
      {entries.map((entry) => (
        <div
          key={entry.id}
          className={clsx(
            'p-4 border rounded-lg hover:bg-gray-50 cursor-pointer transition-colors',
            entry.kind === '/ai.response' && 'bg-blue-50 border-blue-200',
            entry.kind === '/ai.prompt' && 'bg-purple-50 border-purple-200'
          )}
          onClick={() => onEntryClick?.(entry)}
        >
          <div className="flex items-start space-x-3">
            <div className="text-lg">{getEntryIcon(entry.kind)}</div>
            <div className="flex-1 min-w-0">
              <div className="flex items-center space-x-2">
                <span className="font-medium text-sm text-gray-900">
                  {entry.author_name}
                </span>
                <span className="text-xs text-gray-500">
                  {formatTimestamp(entry.ts)}
                </span>
                <span className="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">
                  {entry.kind}
                </span>
              </div>
              <p className="mt-1 text-sm text-gray-700">
                {getEntryContent(entry)}
              </p>
              {entry.metadata && (
                <div className="mt-2 text-xs text-gray-500">
                  {entry.metadata.provider} • {entry.metadata.tokens_used} tokens • {entry.metadata.response_time}ms
                </div>
              )}
            </div>
          </div>
        </div>
      ))}
      
      {onLoadMore && (
        <div className="text-center pt-4">
          <button
            onClick={onLoadMore}
            className="text-amber-600 hover:text-amber-700 text-sm font-medium"
          >
            Load more
          </button>
        </div>
      )}
    </div>
  );
};
