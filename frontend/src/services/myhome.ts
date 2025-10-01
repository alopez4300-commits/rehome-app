import { api } from './api';
import { MyHomeEntry } from '../components/MyHome/ActivityFeed';

export interface MyHomeStats {
  total_entries: number;
  entries_by_kind: Record<string, number>;
  total_time_logged: number;
  recent_activity: MyHomeEntry[];
}

export interface MyHomeFeedResponse {
  success: boolean;
  data: {
    entries: MyHomeEntry[];
    pagination: {
      current_page: number;
      last_page: number;
      per_page: number;
      total: number;
    };
  };
}

export interface CreateNoteRequest {
  text: string;
}

export interface CreateNoteResponse {
  success: boolean;
  data: {
    entry: MyHomeEntry;
  };
  message: string;
}

export const myhomeService = {
  async getFeed(
    workspaceId: number,
    projectId: number,
    page: number = 1,
    limit: number = 50
  ): Promise<MyHomeFeedResponse> {
    const response = await api.get(
      `/api/workspaces/${workspaceId}/projects/${projectId}/myhome/feed`,
      {
        params: { page, limit },
      }
    );
    return response.data;
  },

  async getStats(
    workspaceId: number,
    projectId: number
  ): Promise<{ success: boolean; data: MyHomeStats }> {
    const response = await api.get(
      `/api/workspaces/${workspaceId}/projects/${projectId}/myhome/stats`
    );
    return response.data;
  },

  async createNote(
    workspaceId: number,
    projectId: number,
    note: CreateNoteRequest
  ): Promise<CreateNoteResponse> {
    const response = await api.post(
      `/api/workspaces/${workspaceId}/projects/${projectId}/myhome/notes`,
      note
    );
    return response.data;
  },

  async search(
    workspaceId: number,
    projectId: number,
    query: string
  ): Promise<MyHomeFeedResponse> {
    const response = await api.get(
      `/api/workspaces/${workspaceId}/projects/${projectId}/myhome/search`,
      {
        params: { q: query },
      }
    );
    return response.data;
  },
};
