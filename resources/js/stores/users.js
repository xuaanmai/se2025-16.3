
import { defineStore } from 'pinia';
import api from '../services/api';

export const useUsersStore = defineStore('users', {
  state: () => ({
    users: [],
    user: null,
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
    async fetchUsers(page = 1, filters = {}) {
      this.loading = true;
      this.error = null;
      try {
        const params = { page, ...filters };
        const response = await api.get('/users', { params });
        
        this.users = response.data.data;
        this.pagination = {
          currentPage: response.data.meta.current_page,
          totalPages: response.data.meta.last_page,
          total: response.data.meta.total,
          perPage: response.data.meta.per_page,
        };
      } catch (err) {
        this.error = 'Failed to fetch users.';
        console.error(err);
      } finally {
        this.loading = false;
      }
    },

    async createUser(userData) {
        this.loading = true;
        this.error = null;
        try {
            const response = await api.post('/users', userData);
            await this.fetchUsers(); 
            return response.data.data;
        } catch (err) {
            this.error = err.response?.data?.message || 'Failed to create user.';
            if (err.response?.data?.errors) {
              this.error = Object.values(err.response.data.errors).flat().join(' ');
            }
            console.error(err);
            throw err;
        } finally {
            this.loading = false;
        }
    },

    async updateUser(id, userData) {
        this.loading = true;
        this.error = null;
        try {
            const response = await api.put(`/users/${id}`, userData);
            await this.fetchUsers(this.pagination.currentPage);
            return response.data.data;
        } catch (err) {
            this.error = err.response?.data?.message || 'Failed to update user.';
             if (err.response?.data?.errors) {
              this.error = Object.values(err.response.data.errors).flat().join(' ');
            }
            console.error(err);
            throw err;
        } finally {
            this.loading = false;
        }
    },

     async deleteUser(id) {
        this.loading = true;
        this.error = null;
        try {
            await api.delete(`/users/${id}`);
            await this.fetchUsers(this.pagination.currentPage);
        } catch (err) {
            this.error = 'Failed to delete user.';
            console.error(err);
            throw err;
        } finally {
            this.loading = false;
        }
    },
  },
});
