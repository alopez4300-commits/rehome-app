import React from 'react';
import { NavLink, useParams } from 'react-router-dom';
import { clsx } from 'clsx';
import { useAuth } from '../../contexts/AuthContext';

export const Sidebar: React.FC = () => {
  const { user, isAdmin } = useAuth();
  const { workspaceId, projectId } = useParams();

  const navigation = [
    {
      name: 'Dashboard',
      href: '/dashboard',
      icon: '🏠',
    },
  ];

  const workspaceNavigation = workspaceId ? [
    {
      name: 'Workspace Overview',
      href: `/workspaces/${workspaceId}`,
      icon: '🏢',
    },
  ] : [];

  const projectNavigation = projectId ? [
    {
      name: 'Task Board',
      href: `/workspaces/${workspaceId}/projects/${projectId}/tasks`,
      icon: '📋',
    },
    {
      name: 'Activity Feed',
      href: `/workspaces/${workspaceId}/projects/${projectId}/activity`,
      icon: '📝',
    },
    {
      name: 'Time Tracking',
      href: `/workspaces/${workspaceId}/projects/${projectId}/time`,
      icon: '⏱️',
    },
    {
      name: 'File Browser',
      href: `/workspaces/${workspaceId}/projects/${projectId}/files`,
      icon: '📁',
    },
    {
      name: 'AI Chat',
      href: `/workspaces/${workspaceId}/projects/${projectId}/chat`,
      icon: '🤖',
    },
  ] : [];

  const adminNavigation = isAdmin() ? [
    {
      name: 'Admin Panel',
      href: '/admin',
      icon: '⚙️',
      external: true,
    },
  ] : [];

  const allNavigation = [
    ...navigation,
    ...workspaceNavigation,
    ...projectNavigation,
    ...adminNavigation,
  ];

  return (
    <div className="w-64 bg-white shadow-sm border-r border-gray-200 min-h-screen">
      <nav className="mt-8 px-4">
        <div className="space-y-2">
          {allNavigation.map((item) => {
            const isActive = window.location.pathname === item.href;
            
            if (item.external) {
              return (
                <a
                  key={item.name}
                  href={item.href}
                  target="_blank"
                  rel="noopener noreferrer"
                  className={clsx(
                    'group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors',
                    isActive
                      ? 'bg-amber-100 text-amber-900'
                      : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'
                  )}
                >
                  <span className="mr-3 text-lg">{item.icon}</span>
                  {item.name}
                </a>
              );
            }

            return (
              <NavLink
                key={item.name}
                to={item.href}
                className={({ isActive }) =>
                  clsx(
                    'group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors',
                    isActive
                      ? 'bg-amber-100 text-amber-900'
                      : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'
                  )
                }
              >
                <span className="mr-3 text-lg">{item.icon}</span>
                {item.name}
              </NavLink>
            );
          })}
        </div>
      </nav>
    </div>
  );
};
