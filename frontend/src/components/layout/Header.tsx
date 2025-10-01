import React from 'react';
import { useAuth } from '../../contexts/AuthContext';
import { Button } from '../Button/Button';

export const Header: React.FC = () => {
  const { user, logout, isAdmin } = useAuth();

  const getRoleColor = (role: string) => {
    switch (role) {
      case 'admin':
        return 'bg-red-100 text-red-800';
      case 'owner':
        return 'bg-purple-100 text-purple-800';
      case 'member':
        return 'bg-green-100 text-green-800';
      case 'consultant':
        return 'bg-yellow-100 text-yellow-800';
      case 'client':
        return 'bg-blue-100 text-blue-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  return (
    <header className="bg-white shadow-sm border-b border-gray-200">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between items-center h-16">
          <div className="flex items-center">
            <h1 className="text-xl font-semibold text-gray-900">
              ReHome v2
            </h1>
            {isAdmin() && (
              <span className="ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                System Admin
              </span>
            )}
          </div>

          <div className="flex items-center space-x-4">
            <div className="flex items-center space-x-2">
              <span className="text-sm text-gray-700">
                {user?.name}
              </span>
              <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getRoleColor(user?.role || '')}`}>
                {user?.role}
              </span>
            </div>
            
            <Button
              variant="ghost"
              size="sm"
              onClick={logout}
            >
              Logout
            </Button>
          </div>
        </div>
      </div>
    </header>
  );
};
