import { Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider } from './contexts/AuthContext';
import { ProtectedRoute } from './components/auth/ProtectedRoute';
import { Layout } from './components/layout/Layout';
import { LoginPage } from './pages/LoginPage';
import { DashboardPage } from './pages/DashboardPage';
import { WorkspacePage } from './pages/WorkspacePage';
import { ProjectPage } from './pages/ProjectPage';
import { TaskBoardPage } from './pages/TaskBoardPage';
import { ActivityFeedPage } from './pages/ActivityFeedPage';
import { TimeTrackingPage } from './pages/TimeTrackingPage';
import { FileBrowserPage } from './pages/FileBrowserPage';
import { AIChatPage } from './pages/AIChatPage';

function App() {
  return (
    <AuthProvider>
      <div className="min-h-screen bg-gray-50">
        <Routes>
          {/* Public routes */}
          <Route path="/login" element={<LoginPage />} />
          
          {/* Protected routes */}
          <Route
            path="/"
            element={
              <ProtectedRoute>
                <Layout />
              </ProtectedRoute>
            }
          >
            <Route index element={<Navigate to="/dashboard" replace />} />
            <Route path="dashboard" element={<DashboardPage />} />
            <Route path="workspaces/:workspaceId" element={<WorkspacePage />} />
            <Route path="workspaces/:workspaceId/projects/:projectId" element={<ProjectPage />}>
              <Route index element={<Navigate to="overview" replace />} />
              <Route path="overview" element={<DashboardPage />} />
              <Route path="tasks" element={<TaskBoardPage />} />
              <Route path="activity" element={<ActivityFeedPage />} />
              <Route path="time" element={<TimeTrackingPage />} />
              <Route path="files" element={<FileBrowserPage />} />
              <Route path="chat" element={<AIChatPage />} />
            </Route>
          </Route>
          
          {/* Catch all route */}
          <Route path="*" element={<Navigate to="/dashboard" replace />} />
        </Routes>
      </div>
    </AuthProvider>
  );
}

export default App;
