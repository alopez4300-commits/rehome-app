import React, { useState } from 'react';
import { Navigate } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import { Button } from '../components/Button/Button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { LoadingSpinner } from '../components/common/LoadingSpinner';

export const LoginPage: React.FC = () => {
  const { user, login, loading } = useAuth();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState('');

  // Redirect if already logged in
  if (user) {
    return <Navigate to="/dashboard" replace />;
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsLoading(true);
    setError('');

    try {
      await login(email, password);
    } catch (err: any) {
      setError(err.response?.data?.message || 'Login failed');
    } finally {
      setIsLoading(false);
    }
  };

  const handleQuickLogin = async (testEmail: string) => {
    setIsLoading(true);
    setError('');

    try {
      await login(testEmail, 'password');
    } catch (err: any) {
      setError(err.response?.data?.message || 'Login failed');
    } finally {
      setIsLoading(false);
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <LoadingSpinner size="lg" />
      </div>
    );
  }

  return (
    <div className="min-h-screen flex items-center justify-center bg-background py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-md w-full space-y-8">
        <Card>
          <CardHeader className="text-center">
            <CardTitle className="text-3xl font-extrabold">
              ReHome v2
            </CardTitle>
            <CardDescription>
              Sign in to your account
            </CardDescription>
          </CardHeader>
          <CardContent>
            <form className="space-y-6" onSubmit={handleSubmit}>
              <div className="space-y-4">
                <div className="space-y-2">
                  <Label htmlFor="email">Email address</Label>
                  <Input
                    id="email"
                    name="email"
                    type="email"
                    autoComplete="email"
                    required
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    placeholder="Enter your email"
                  />
                </div>

                <div className="space-y-2">
                  <Label htmlFor="password">Password</Label>
                  <Input
                    id="password"
                    name="password"
                    type="password"
                    autoComplete="current-password"
                    required
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    placeholder="Enter your password"
                  />
                </div>
              </div>

              {error && (
                <div className="text-destructive text-sm text-center">
                  {error}
                </div>
              )}

              <div>
                <Button
                  type="submit"
                  variant="primary"
                  size="lg"
                  loading={isLoading}
                  className="w-full"
                >
                  Sign in
                </Button>
              </div>
            </form>

            {/* Quick Login Buttons for Development */}
            <div className="mt-8">
              <div className="text-center text-sm text-muted-foreground mb-4">
                Quick Login (Development)
              </div>
              <div className="grid grid-cols-2 gap-2">
                <Button
                  variant="destructive"
                  size="sm"
                  onClick={() => handleQuickLogin('alice@admin.com')}
                  loading={isLoading}
                >
                  Admin
                </Button>
                <Button
                  variant="primary"
                  size="sm"
                  onClick={() => handleQuickLogin('bob@team.com')}
                  loading={isLoading}
                >
                  Team
                </Button>
                <Button
                  variant="secondary"
                  size="sm"
                  onClick={() => handleQuickLogin('john@consulting.com')}
                  loading={isLoading}
                >
                  Consultant
                </Button>
                <Button
                  variant="ghost"
                  size="sm"
                  onClick={() => handleQuickLogin('jane@client.com')}
                  loading={isLoading}
                >
                  Client
                </Button>
              </div>
              <div className="text-center text-xs text-muted-foreground mt-2">
                All use password: password
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
};
