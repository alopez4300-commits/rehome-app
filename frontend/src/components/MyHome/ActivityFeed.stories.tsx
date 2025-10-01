import type { Meta, StoryObj } from '@storybook/react';
import { ActivityFeed } from './ActivityFeed';

const mockEntries = [
  {
    id: 1,
    ts: '2025-01-15T14:23:10Z',
    author: 1,
    author_name: 'John Doe',
    kind: 'note',
    text: 'Project kickoff meeting completed successfully.',
  },
  {
    id: 2,
    ts: '2025-01-15T14:24:15Z',
    author: 1,
    author_name: 'John Doe',
    kind: '/task',
    title: 'Setup development environment',
    status: 'completed',
  },
  {
    id: 3,
    ts: '2025-01-15T14:30:00Z',
    author: 2,
    author_name: 'Jane Smith',
    kind: '/time',
    hours: 2.5,
    task: 'Development setup',
  },
  {
    id: 4,
    ts: '2025-01-15T15:00:00Z',
    author: 1,
    author_name: 'John Doe',
    kind: '/file',
    path: 'assets/documents/project-brief.pdf',
    size: 1024000,
    type: 'application/pdf',
  },
  {
    id: 5,
    ts: '2025-01-15T15:30:00Z',
    author: 1,
    author_name: 'John Doe',
    kind: '/ai.prompt',
    prompt: 'What are the key requirements for the MyHome system?',
  },
  {
    id: 6,
    ts: '2025-01-15T15:30:05Z',
    author: 1,
    author_name: 'John Doe',
    kind: '/ai.response',
    text: 'The MyHome system should provide append-only activity logging with NDJSON format, support for multiple entry types (notes, tasks, time logs, files, AI interactions), and efficient querying capabilities.',
    metadata: {
      provider: 'claude',
      model: 'claude-3-sonnet-20240229',
      tokens_used: 150,
      response_time: 1250,
    },
  },
];

const meta: Meta<typeof ActivityFeed> = {
  title: 'MyHome/ActivityFeed',
  component: ActivityFeed,
  parameters: {
    layout: 'padded',
    docs: {
      description: {
        component: 'Activity feed displaying MyHome entries in chronological order.',
      },
    },
  },
  argTypes: {
    entries: { control: 'object' },
    loading: { control: 'boolean' },
    onLoadMore: { action: 'load-more' },
    onEntryClick: { action: 'entry-click' },
  },
  tags: ['autodocs'],
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
  args: {
    entries: mockEntries,
    loading: false,
  },
};

export const Loading: Story = {
  args: {
    entries: [],
    loading: true,
  },
};

export const Empty: Story = {
  args: {
    entries: [],
    loading: false,
  },
};

export const LongList: Story = {
  args: {
    entries: Array.from({ length: 50 }, (_, i) => ({
      ...mockEntries[0],
      id: i + 1,
      text: `Entry ${i + 1}: This is a longer entry to test scrolling behavior and how the component handles many items.`,
      ts: new Date(Date.now() - (50 - i) * 60000).toISOString(),
    })),
    loading: false,
  },
};

export const Mobile: Story = {
  args: {
    entries: mockEntries,
    loading: false,
  },
  parameters: {
    viewport: { defaultViewport: 'mobile' },
  },
};

export const Tablet: Story = {
  args: {
    entries: mockEntries,
    loading: false,
  },
  parameters: {
    viewport: { defaultViewport: 'tablet' },
  },
};
