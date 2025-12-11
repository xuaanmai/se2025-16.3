
import { defineStore } from 'pinia';
import api from '../services/api';

export const useReferentialStore = defineStore('referentials', {
  state: () => ({
    projects: [], // For filter dropdowns
    ticketStatuses: [],
    ticketTypes: [],
    ticketPriorities: [],
    users: [],
    roles: [], // Added roles
    loading: false,
  }),

  actions: {
    async fetchAllReferentials() {
      if (this.projects.length > 0) return; // Don't refetch if already loaded

      this.loading = true;
      try {
        const [
          projectsRes,
          statusesRes,
          typesRes,
          prioritiesRes,
          usersRes,
          rolesRes, // Added roles fetch
        ] = await Promise.all([
          api.get('/projects', { params: { per_page: -1 } }), // Fetch all projects
          api.get('/referential/ticket-statuses'),
          api.get('/referential/ticket-types'),
          api.get('/referential/ticket-priorities'),
          api.get('/users', { params: { per_page: -1 } }), // Fetch all users
          api.get('/roles'), // Fetch roles
        ]);

        this.projects = projectsRes.data.data;
        this.ticketStatuses = statusesRes.data.data;
        this.ticketTypes = typesRes.data.data;
        this.ticketPriorities = prioritiesRes.data.data;
        this.users = usersRes.data.data;
        this.roles = rolesRes.data.data; // Store roles

      } catch (error) {
        console.error('Failed to fetch referential data:', error);
      } finally {
        this.loading = false;
      }
    },
  },
});
