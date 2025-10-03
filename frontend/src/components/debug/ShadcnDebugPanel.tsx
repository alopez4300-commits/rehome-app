import React, { useState } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '../Button/Button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';

export const ShadcnDebugPanel: React.FC = () => {
  const [isVisible, setIsVisible] = useState(false);

  if (!isVisible) {
    return (
      <div className="fixed bottom-4 right-4 z-50">
        <Button 
          onClick={() => setIsVisible(true)}
          variant="secondary"
          size="sm"
          className="bg-purple-600 text-white hover:bg-purple-700"
        >
          🎨 Debug shadcn/ui
        </Button>
      </div>
    );
  }

  return (
    <div className="fixed bottom-4 right-4 z-50 w-96">
      <Card className="border-purple-200 bg-purple-50">
        <CardHeader className="pb-3">
          <div className="flex items-center justify-between">
            <CardTitle className="text-lg text-purple-900">shadcn/ui Debug Panel</CardTitle>
            <Button 
              onClick={() => setIsVisible(false)}
              variant="ghost"
              size="sm"
              className="h-6 w-6 p-0 text-purple-600 hover:text-purple-800"
            >
              ✕
            </Button>
          </div>
          <CardDescription className="text-purple-700">
            Testing shadcn/ui components and Tailwind CSS
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          {/* Button variants */}
          <div>
            <h4 className="text-sm font-medium text-purple-900 mb-2">Button Variants</h4>
            <div className="flex flex-wrap gap-2">
              <Button variant="primary" size="sm">Primary</Button>
              <Button variant="secondary" size="sm">Secondary</Button>
              <Button variant="outline" size="sm">Outline</Button>
              <Button variant="ghost" size="sm">Ghost</Button>
              <Button variant="danger" size="sm">Danger</Button>
            </div>
          </div>

          {/* Badge variants */}
          <div>
            <h4 className="text-sm font-medium text-purple-900 mb-2">Badge Variants</h4>
            <div className="flex flex-wrap gap-2">
              <Badge variant="default">Default</Badge>
              <Badge variant="secondary">Secondary</Badge>
              <Badge variant="destructive">Destructive</Badge>
              <Badge variant="outline">Outline</Badge>
            </div>
          </div>

          {/* Input */}
          <div>
            <h4 className="text-sm font-medium text-purple-900 mb-2">Input</h4>
            <Input placeholder="Test input field" className="w-full" />
          </div>

          {/* Nested Card */}
          <div>
            <h4 className="text-sm font-medium text-purple-900 mb-2">Nested Card</h4>
            <Card className="bg-white">
              <CardHeader className="pb-2">
                <CardTitle className="text-sm">Test Card</CardTitle>
                <CardDescription className="text-xs">This is a nested card component</CardDescription>
              </CardHeader>
              <CardContent className="pt-0">
                <p className="text-xs text-muted-foreground">
                  Card content with proper spacing and typography.
                </p>
              </CardContent>
            </Card>
          </div>

          {/* CSS Variables Test */}
          <div>
            <h4 className="text-sm font-medium text-purple-900 mb-2">CSS Variables</h4>
            <div className="grid grid-cols-2 gap-2 text-xs">
              <div className="bg-primary text-primary-foreground p-2 rounded">Primary</div>
              <div className="bg-secondary text-secondary-foreground p-2 rounded">Secondary</div>
              <div className="bg-muted text-muted-foreground p-2 rounded">Muted</div>
              <div className="bg-accent text-accent-foreground p-2 rounded">Accent</div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
};
