import { defineStore } from 'pinia';
import axios from 'axios';

export const useEpicsStore = defineStore('epics', {
  state: () => ({
    epics: [],
    loading: false,
    error: null,
  }),
  actions: {
    async fetchEpics(projectId) {
      if (!projectId) {
        this.epics = [];
        return;
      }
      this.loading = true;
      this.error = null;
      try {
        // We use per_page=-1 to get all epics for the project, not just a paginated list.
        const response = await axios.get(`/api/epics?project_id=${projectId}&per_page=-1`);
        this.epics = response.data;
      } catch (err) {
        this.error = 'Failed to load epics.';
        console.error('Failed to load epics:', err);
      } finally {
        this.loading = false;
      }
    },
  },
});
