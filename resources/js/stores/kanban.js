import { defineStore } from 'pinia';
import api from '../services/api';

export const useKanbanStore = defineStore('kanban', {
  state: () => ({
    board: {
      columns: [],
    },
    loading: false,
    error: null,
  }),

  getters: {
    // Example getter
    getColumns: (state) => state.board.columns,
  },

  actions: {
    async fetchBoard(projectId) {
      this.loading = true;
      this.error = null;
      try {
        // Based on the documentation, we need to get statuses (columns) and tickets
        const [statusesRes, ticketsRes] = await Promise.all([
          api.get(`/projects/${projectId}/kanban/statuses`),
          api.get(`/projects/${projectId}/kanban/tickets`),
        ]);

        const statuses = statusesRes.data;
        const tickets = ticketsRes.data;

        // Map statuses to columns and distribute tickets into them
        this.board.columns = statuses.map(status => ({
          id: status.id,
          title: status.title,
          color: status.color,
          tasks: tickets.filter(ticket => ticket.status === status.id)
        }));

      } catch (err) {
        this.error = 'Failed to fetch Kanban board data.';
        console.error(err);
      } finally {
        this.loading = false;
      }
    },

    async moveTask(taskId, newColumnId) {
      try {
        await api.put(`/tickets/${taskId}/move`, {
          status_id: newColumnId,
        });
      } catch (err) {
        this.error = 'Failed to move task.';
        console.error(err);
        // Re-throw the error so the component can handle it (e.g., revert UI)
        throw err;
      }
    },

    async createTask(taskData) {
      // taskData should include { name, project_id, status_id }
      try {
        await api.post('/tickets', taskData);
        // After creating, refresh the entire board to ensure consistency
        await this.fetchBoard(taskData.project_id);
      } catch (err) {
        this.error = 'Failed to create task.';
        console.error(err);
        throw err;
      }
    }
  },
});
