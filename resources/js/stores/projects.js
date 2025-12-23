
import { defineStore } from 'pinia';
import api from '../services/api';
import { useAuthStore } from './index'; // Import auth store

export const useProjectsStore = defineStore('projects', {
  state: () => ({
    projects: [],
    project: null,
    shortcutProjects: JSON.parse(localStorage.getItem('shortcutProjects') || '[]'), // <-- khởi tạo từ localStorage
    pagination: {
      currentPage: 1,
      totalPages: 1,
      total: 0,
      perPage: 15,
    },
    loading: false,
    error: null,
  }),

  actions: {
    async fetchProjects(page = 1, filters = {}) {
      this.loading = true;
      this.error = null;
      try {
        const params = { page, ...filters };
        const response = await api.get('/projects', { params });
        
        // Defensive check for response structure
        const responseData = response.data || {};
        const meta = responseData.meta || {};
        const data = responseData.data || [];

        this.projects = data;
        this.pagination = {
          currentPage: meta.current_page || 1,
          totalPages: meta.last_page || 1,
          total: meta.total || 0,
          perPage: meta.per_page || 15,
        };
      } catch (err) {
        this.error = 'Failed to fetch projects.';
        console.error(err);
      } finally {
        this.loading = false;
      }
    },

    async fetchProject(id) {
      this.loading = true;
      this.error = null;
      this.project = null;
      try {
        const response = await api.get(`/projects/${id}`);
        this.project = response.data; // Correctly assign the response data
      } catch (err) {
        this.error = 'Failed to fetch project details.';
        console.error(err);
      } finally {
        this.loading = false;
      }
    },

    async createProject(formData) {
        this.loading = true;
        this.error = null;
        try {
            const response = await api.post('/projects', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });
            await this.fetchProjects();
            
            // **FIX: Refresh user permissions after creating a project**
            const authStore = useAuthStore();
            await authStore.fetchUser();

            return response.data.data;
        } catch (err) {
            this.error = err.response?.data?.message || 'Failed to create project.';
            console.error(err);
            throw err;
        } finally {
            this.loading = false;
        }
    },

    async updateProject(id, formData) {
        this.loading = true;
        this.error = null;
        try {
            // Use POST for FormData with file uploads, and spoof PUT method
            const response = await api.post(`/projects/${id}`, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });
            await this.fetchProjects(this.pagination.currentPage);
            if (this.project && this.project.id === id) {
                this.project = response.data.data;
            }
            return response.data.data;
        } catch (err) {
            this.error = err.response?.data?.message || 'Failed to update project.';
            console.error(err);
            throw err;
        } finally {
            this.loading = false;
        }
    },

     async deleteProject(id) {
        this.loading = true;
        this.error = null;
        try {
            await api.delete(`/projects/${id}`);
            // Refresh the list
            await this.fetchProjects(this.pagination.currentPage);
        } catch (err) {
            this.error = 'Failed to delete project.';
            console.error(err);
            throw err;
        } finally {
            this.loading = false;
        }
    },

    async toggleFavorite(projectId) {
        try {
            const response = await api.post(`/projects/${projectId}/favorite`);
            // Update the specific project in the list
            const projectIndex = this.projects.findIndex(p => p.id === projectId);
            if (projectIndex !== -1) {
                this.projects[projectIndex].is_favorite = response.data.is_favorite;
            }
        } catch (err) {
            console.error('Failed to toggle favorite status:', err);
            // Optionally set an error message
        }
    },

    async fetchProjectMembers(projectId) {
        try {
            const response = await api.get(`/projects/${projectId}/users`);
            // The API returns a simple array, so we return it directly.
            const members = response.data || [];
            
            // In edit mode of project detail, we might want to update the main project object
            if (this.project && this.project.id == projectId) {
                this.project.users = members;
            }
            return members;
        } catch (err) {
            console.error('Failed to fetch project members:', err);
            return [];
        }
    },

    async attachUser(projectId, userData) {
        try {
            await api.post(`/projects/${projectId}/users`, userData);
            await this.fetchProjectMembers(projectId); // Refresh member list
        } catch (err) {
            console.error('Failed to attach user:', err);
            this.error = err.response?.data?.message || 'Failed to attach user.';
            throw err;
        }
    },

    async updateUserRole(projectId, userId, role) {
        try {
            await api.put(`/projects/${projectId}/users/${userId}`, { role });
            await this.fetchProjectMembers(projectId); // Refresh member list
        } catch (err) {
            console.error('Failed to update user role:', err);
            this.error = err.response?.data?.message || 'Failed to update role.';
            throw err;
        }
    },

    async detachUser(projectId, userId) {
        try {
            await api.delete(`/projects/${projectId}/users/${userId}`);
            await this.fetchProjectMembers(projectId); // Refresh member list
        } catch (err) {
            console.error('Failed to detach user:', err);
            this.error = err.response?.data?.message || 'Failed to detach user.';
            throw err;
        }
    },

    async fetchSprints(projectId) {
        try {
            const response = await api.get(`/projects/${projectId}/sprints`);
            // Assuming the project object in the store should hold its sprints
            if (this.project && this.project.id == projectId) {
                this.project.sprints = response.data;
            }
            return response.data;
        } catch (err) {
            console.error('Failed to fetch sprints:', err);
            return [];
        }
    },

    async fetchActiveProjects(page = 1) {
        this.loading = true;
        this.error = null;
        try {
            const response = await api.get(`/projects/active?page=${page}`);
            this.projects = response.data.data;
            this.pagination.currentPage = response.data.current_page;
            this.pagination.totalPages = response.data.last_page;
            this.pagination.total = response.data.total;
        } catch (err) {
            this.error = 'Failed to fetch active projects.';
            console.error(err);
        } finally {
            this.loading = false;
        }
    },

    // Thêm project vào shortcut
    addShortcut(project) {
      if (!this.shortcutProjects.find(p => p.id === project.id)) {
        this.shortcutProjects.push(project);
        this.saveShortcutsToLocal();
      }
    },

    // Xóa project khỏi shortcut
    removeShortcut(projectId) {
      this.shortcutProjects = this.shortcutProjects.filter(p => p.id !== projectId);
      this.saveShortcutsToLocal();
    },

    // Lưu shortcut vào localStorage
    saveShortcutsToLocal() {
      localStorage.setItem('shortcutProjects', JSON.stringify(this.shortcutProjects));
    },

    // Tải shortcut từ localStorage (nếu muốn refresh thủ công)
    loadShortcutsFromLocal() {
      this.shortcutProjects = JSON.parse(localStorage.getItem('shortcutProjects') || '[]');
    },

  },
});