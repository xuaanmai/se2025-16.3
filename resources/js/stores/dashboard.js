
import { defineStore } from 'pinia';
import api from '../services/api'; // Assuming you have an api service configured

export const useDashboardStore = defineStore('dashboard', {
  state: () => ({
    stats: null,
    favoriteProjects: [],
    latestActivities: [],
    latestComments: [],
    latestProjects: [],
    latestTickets: [],
    ticketsByPriority: null,
    ticketsByType: null,
    ticketTimeLogged: null,
    userTimeLogged: null,
    loading: false,
    error: null,
  }),

  actions: {
    async fetchAllDashboardData() {
      this.loading = true;
      this.error = null;
      try {
        // Fetch all data in parallel
        const [
          statsRes,
          favProjectsRes,
          latestActivitiesRes,
          latestCommentsRes,
          latestProjectsRes,
          latestTicketsRes,
          ticketsByPriorityRes,
          ticketsByTypeRes,
          ticketTimeLoggedRes,
          userTimeLoggedRes,
        ] = await Promise.all([
          api.get('/dashboard/stats'),
          api.get('/dashboard/favorite-projects'),
          api.get('/dashboard/latest-activities'),
          api.get('/dashboard/latest-comments'),
          api.get('/dashboard/latest-projects'),
          api.get('/dashboard/latest-tickets'),
          api.get('/dashboard/tickets-by-priority'),
          api.get('/dashboard/tickets-by-type'),
          api.get('/dashboard/ticket-time-logged'),
          api.get('/dashboard/user-time-logged'),
        ]);

        this.stats = statsRes.data;
        this.favoriteProjects = favProjectsRes.data;
        this.latestActivities = latestActivitiesRes.data;
        this.latestComments = latestCommentsRes.data;
        this.latestProjects = latestProjectsRes.data;
        this.latestTickets = latestTicketsRes.data;
        this.ticketsByPriority = ticketsByPriorityRes.data;
        this.ticketsByType = ticketsByTypeRes.data;
        this.ticketTimeLogged = ticketTimeLoggedRes.data;
        this.userTimeLogged = userTimeLoggedRes.data;

      } catch (err) {
        this.error = 'Failed to fetch dashboard data.';
        console.error(err);
      } finally {
        this.loading = false;
      }
    },
  },
});
