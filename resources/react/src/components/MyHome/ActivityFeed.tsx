import React from 'react'
import { formatDistanceToNow } from 'date-fns'

export interface Activity {
  id: string
  kind: 'note' | 'task' | 'time_log' | 'file' | 'ai_interaction'
  content: string
  author: string
  timestamp: string
  project: string
}

export interface ActivityFeedProps {
  activities: Activity[]
  loading?: boolean
}

export const ActivityFeed: React.FC<ActivityFeedProps> = ({
  activities,
  loading = false,
}) => {
  const getActivityIcon = (kind: Activity['kind']) => {
    switch (kind) {
      case 'note':
        return '📝'
      case 'task':
        return '✅'
      case 'time_log':
        return '⏱️'
      case 'file':
        return '📁'
      case 'ai_interaction':
        return '🤖'
      default:
        return '📄'
    }
  }

  const getActivityColor = (kind: Activity['kind']) => {
    switch (kind) {
      case 'note':
        return 'bg-blue-100 text-blue-800'
      case 'task':
        return 'bg-green-100 text-green-800'
      case 'time_log':
        return 'bg-purple-100 text-purple-800'
      case 'file':
        return 'bg-orange-100 text-orange-800'
      case 'ai_interaction':
        return 'bg-indigo-100 text-indigo-800'
      default:
        return 'bg-gray-100 text-gray-800'
    }
  }

  if (loading) {
    return (
      <div className="space-y-4">
        {[...Array(3)].map((_, i) => (
          <div key={i} className="animate-pulse">
            <div className="flex items-start space-x-3">
              <div className="w-8 h-8 bg-gray-200 rounded-full"></div>
              <div className="flex-1 space-y-2">
                <div className="h-4 bg-gray-200 rounded w-3/4"></div>
                <div className="h-3 bg-gray-200 rounded w-1/2"></div>
              </div>
            </div>
          </div>
        ))}
      </div>
    )
  }

  if (activities.length === 0) {
    return (
      <div className="text-center py-8">
        <div className="text-gray-400 text-4xl mb-4">📭</div>
        <h3 className="text-lg font-medium text-gray-900 mb-2">No activities yet</h3>
        <p className="text-gray-500">Start by creating a note, task, or logging time.</p>
      </div>
    )
  }

  return (
    <div className="space-y-4">
      {activities.map((activity) => (
        <div key={activity.id} className="flex items-start space-x-3 p-4 bg-white rounded-lg border border-gray-200 hover:border-gray-300 transition-colors">
          <div className="flex-shrink-0">
            <div className={`w-8 h-8 rounded-full flex items-center justify-center text-sm ${getActivityColor(activity.kind)}`}>
              {getActivityIcon(activity.kind)}
            </div>
          </div>
          <div className="flex-1 min-w-0">
            <div className="flex items-center justify-between">
              <p className="text-sm font-medium text-gray-900">{activity.author}</p>
              <p className="text-xs text-gray-500">
                {formatDistanceToNow(new Date(activity.timestamp), { addSuffix: true })}
              </p>
            </div>
            <p className="text-sm text-gray-700 mt-1">{activity.content}</p>
            <div className="flex items-center mt-2">
              <span className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                {activity.project}
              </span>
            </div>
          </div>
        </div>
      ))}
    </div>
  )
}
