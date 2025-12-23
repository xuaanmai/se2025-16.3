
import { defineStore } from 'pinia';
import api from '../services/api';

export const useTicketsStore = defineStore('tickets', {
  state: () => ({
    tickets: [],
    ticket: null,
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
    async fetchTickets(page = 1, filters = {}) {
      this.loading = true;
      this.error = null;
      try {
        // Remove null or empty filter values
        const cleanedFilters = Object.fromEntries(
          Object.entries(filters).filter(([_, v]) => v != null && v !== '')
        );
        
        const params = { page, ...cleanedFilters };
        const response = await api.get('/tickets', { params });
        
        // Defensive check for response structure
        const responseData = response.data || {};
        const meta = responseData.meta || {};
        const data = responseData.data || [];

        this.tickets = data;
        this.pagination = {
          currentPage: meta.current_page || 1,
          totalPages: meta.last_page || 1,
          total: meta.total || 0,
          perPage: meta.per_page || 15,
        };
      } catch (err) {
        this.error = 'Failed to fetch tickets.';
        console.error(err);
      } finally {
        this.loading = false;
      }
    },

    async fetchTicket(id) {
      this.loading = true;
      this.error = null;
      this.ticket = null;
      try {
        const response = await api.get(`/tickets/${id}`);
        this.ticket = response.data;
      } catch (err) {
        this.error = 'Failed to fetch ticket details.';
        console.error(err);
      } finally {
        this.loading = false;
      }
    },

    async createTicket(ticketData) {
        this.loading = true;
        this.error = null;
        try {
            const response = await api.post('/tickets', ticketData);
            await this.fetchTickets(); 
            return response.data.data;
        } catch (err) {
            this.error = err.response?.data?.message || 'Failed to create ticket.';
            console.error(err);
            throw err;
        } finally {
            this.loading = false;
        }
    },

async updateTicket(id, ticketData) {
  this.loading = true
  this.error = null

  try {
    await api.put(`/tickets/${id}`, ticketData)

    // refresh list
    await this.fetchTickets(this.pagination.currentPage)

    // 🔥 QUAN TRỌNG: refresh ticket đang xem
    if (this.ticket && this.ticket.id === id) {
      await this.fetchTicket(id)
    }

  } catch (err) {
    this.error = err.response?.data?.message || 'Failed to update ticket.'
    console.error(err)
    throw err
  } finally {
    this.loading = false
  }
},

     async deleteTicket(id) {
        this.loading = true;
        this.error = null;
        try {
            await api.delete(`/tickets/${id}`);
            await this.fetchTickets(this.pagination.currentPage);
        } catch (err) {
            this.error = 'Failed to delete ticket.';
            console.error(err);
            throw err;
        } finally {
            this.loading = false;
        }
    },

    async fetchComments(ticketId) {
        try {
            const response = await api.get(`/tickets/${ticketId}/comments`);
            if (this.ticket && this.ticket.id == ticketId) {
                this.ticket.comments = response.data.data;
            }
        } catch (err) {
            console.error('Failed to fetch comments:', err);
        }
    },

    async postComment(ticketId, commentData) {
        try {
            await api.post(`/tickets/${ticketId}/comments`, commentData);
            await this.fetchComments(ticketId); // Refresh comments after posting
        } catch (err) {
            console.error('Failed to post comment:', err);
            throw err;
        }
    },

    async fetchHours(ticketId) {
        try {
            const response = await api.get(`/tickets/${ticketId}/hours`);
            if (this.ticket && this.ticket.id == ticketId) {
                this.ticket.hours = response.data.data;
                // Also update total logged hours
                this.ticket.total_logged_hours = response.data.total;
            }
        } catch (err) {
            console.error('Failed to fetch hours:', err);
        }
    },

    async logHours(ticketId, hourData) {
        try {
            await api.post(`/tickets/${ticketId}/hours`, hourData);
            await this.fetchHours(ticketId); // Refresh hours after logging
        } catch (err) {
            console.error('Failed to log hours:', err);
            throw err;
        }
    },
    async fetchOpenTickets() {
      this.loading = true
      try {
        const res = await api.get('/tickets/open')
        this.tickets = res.data.data
      } catch (e) {
        console.error(e)
        this.error = 'Failed to fetch open tickets'
      } finally {
        this.loading = false
      }
    },

    async fetchTicketsInProgress() {
      this.loading = true
      try {
        const res = await api.get('/tickets/in-progress')
        this.tickets = res.data.data
      } catch (e) {
        console.error(e)
        this.error = 'Failed to fetch tickets in progress'
      } finally {
        this.loading = false
      }
    },
  },
});
