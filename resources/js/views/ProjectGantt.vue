<template>
  <div class="p-4">
    <h2 class="text-2xl font-bold mb-4">Gantt Chart</h2>
    <div v-if="isLoading" class="text-center">Loading chart...</div>
    <div v-if="!isLoading && tasks.length === 0" class="text-center text-gray-500">No tasks to display in the chart.</div>
    <div ref="ganttContainer"></div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import Gantt from 'frappe-gantt';
import api from '@/services/api';

const route = useRoute();
const projectId = route.params.id;

const ganttContainer = ref(null);
const tasks = ref([]);
const isLoading = ref(true);
let gantt = null;

const fetchGanttData = async () => {
  try {
    isLoading.value = true;
    const response = await api.get(`/projects/${projectId}/gantt`);
    tasks.value = response.data;
    renderGantt();
  } catch (error) {
    console.error('Error fetching Gantt data:', error);
  } finally {
    isLoading.value = false;
  }
};

const renderGantt = () => {
  if (ganttContainer.value && tasks.value.length > 0) {
    // Clear previous instance if any
    ganttContainer.value.innerHTML = '';
    gantt = new Gantt(ganttContainer.value, tasks.value, {
      on_click: (task) => {
        // Example: Navigate to ticket detail page
        // router.push({ name: 'tickets.detail', params: { id: task.id.replace('task_', '') } });
        console.log('Clicked task:', task);
      },
      on_date_change: (task, start, end) => {
        console.log('Date changed:', { task, start, end });
        // Here you would call an API to save the changes
      },
      bar_height: 20,
      bar_corner_radius: 3,
      padding: 18,
      view_mode: 'Week', // Can be 'Quarter Day', 'Half Day', 'Day', 'Week', 'Month'
      language: 'en' // Change to 'vi' if you have translations
    });
  }
};

onMounted(() => {
  fetchGanttData();
});
</script>