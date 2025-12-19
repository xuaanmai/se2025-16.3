
import { defineStore } from 'pinia';
import api from '../services/api';

export const useReferentialStore = defineStore('referentials', {
  state: () => ({
    projects: [], // For filter dropdowns
    ticketStatuses: [],
    ticketTypes: [],
    ticketPriorities: [],
    users: [],
    roles: [],
    loading: false,
    loaded: false, // Add a flag to track if data has been loaded
  }),

  actions: {
    async fetchAllReferentials() {
      // Only fetch if data hasn't been loaded yet.
      if (this.loaded) return;

      this.loading = true;
      try {
        const [
          projectsRes,
          statusesRes,
          typesRes,
          prioritiesRes,
          usersRes,
          rolesRes,
        ] = await Promise.all([
          api.get('/projects', { params: { per_page: -1 } }), // Fetch all projects
          api.get('/referential/ticket-statuses'),
          api.get('/referential/ticket-types'),
          api.get('/referential/ticket-priorities'),
          api.get('/users', { params: { per_page: -1 } }), // Fetch all users
          api.get('/roles'), // Fetch roles
        ]);

        this.projects = projectsRes.data.data;
        this.ticketStatuses = statusesRes.data;
        this.ticketTypes = typesRes.data;
        this.ticketPriorities = prioritiesRes.data;
        this.users = usersRes.data.data;
        this.roles = rolesRes.data.data;

        this.loaded = true; // Set the flag to true after successful fetch

      } catch (error) {
        console.error('Failed to fetch referential data:', error);
        this.loaded = false; // Reset flag on error to allow retrying
      } finally {
        this.loading = false;
      }
    },
  },
});
