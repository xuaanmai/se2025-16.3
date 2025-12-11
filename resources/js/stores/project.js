import { defineStore } from 'pinia';
import api from '../services/api';

export const useProjectStore = defineStore('project', {
  state: () => ({
    projects: [],
    pagination: {
      currentPage: 1,
      totalPages: 1,
      total: 0,
    },
    currentProject: null,
    loading: false,
    error: null,
  }),

  actions: {
    async fetchProjects(page = 1) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.get(`/projects?page=${page}`);
        this.projects = response.data.data;
        this.pagination.currentPage = response.data.meta.current_page;
        this.pagination.totalPages = response.data.meta.last_page;
        this.pagination.total = response.data.meta.total;
      } catch (err) {
        this.error = 'Failed to fetch projects.';
        console.error(err);
      } finally {
        this.loading = false;
      }
    },
    
    async fetchProject(projectId) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.get(`/projects/${projectId}`);
        this.currentProject = response.data.data;
        return this.currentProject;
      } catch (err) {
        this.error = `Failed to fetch project ${projectId}.`;
        console.error(err);
      } finally {
        this.loading = false;
      }
    },

    async createProject(data) {
      this.loading = true;
      this.error = null;
      try {
        await api.post('/projects', data);
        await this.fetchProjects(); // Refresh the list
      } catch (err) {
        this.error = 'Failed to create project.';
        console.error(err);
      } finally {
        this.loading = false;
      }
    },

    async updateProject(id, data) {
      this.loading = true;
      this.error = null;
      try {
        await api.put(`/projects/${id}`, data);
        await this.fetchProjects(this.pagination.currentPage); // Refresh the list
      } catch (err) {
        this.error = 'Failed to update project.';
        console.error(err);
      } finally {
        this.loading = false;
      }
    },

    async deleteProject(id) {
      this.loading = true;
      this.error = null;
      try {
        await api.delete(`/projects/${id}`);
        await this.fetchProjects(); // Refresh the list
      } catch (err) {
        this.error = 'Failed to delete project.';
        console.error(err);
      } finally {
        this.loading = false;
      }
    },

    async toggleFavorite(id) {
      try {
        const response = await api.post(`/projects/${id}/favorite`);
        // Update the specific project in the list to reflect the change
        const project = this.projects.find(p => p.id === id);
        if (project) {
          project.is_favorite = response.data.is_favorite;
        }
      } catch (err) {
        console.error('Failed to toggle favorite status.', err);
        alert('Error updating favorite status.');
      }
    }
  },
});
