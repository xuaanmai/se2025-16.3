<template>
  <div class="bg-white p-6 rounded-lg shadow-md">
    <h3 class="font-semibold text-lg text-gray-800 mb-4">Project Timeline</h3>
    <div v-if="isLoading" class="h-96 flex items-center justify-center">
      <p class="text-gray-500">Loading Gantt Chart...</p>
    </div>
    <div v-else-if="tasks.length === 0" class="h-96 flex items-center justify-center">
      <p class="text-gray-500">No tasks found for this project to draw the chart.</p>
    </div>
    <div ref="ganttContainer" class="gantt-chart-container"></div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';
import Gantt from 'frappe-gantt';

const props = defineProps({
  projectId: {
    type: [String, Number],
    required: true,
  },
});

const ganttContainer = ref(null);
const tasks = ref([]);
const gantt = ref(null);
const isLoading = ref(true);

const fetchGanttData = async (id) => {
  if (!id) return;
  isLoading.value = true;
  try {
    const response = await fetch(`/api/projects/${id}/gantt`);
    if (!response.ok) {
      throw new Error('Failed to fetch Gantt data');
    }
    const data = await response.json();
    tasks.value = data;
  } catch (error) {
    console.error('Error fetching Gantt data:', error);
    tasks.value = [];
  } finally {
    isLoading.value = false;
  }
};

const renderGanttChart = () => {
  if (gantt.value) {
    gantt.value.refresh(tasks.value);
    return;
  }

  if (ganttContainer.value && tasks.value.length > 0) {
    gantt.value = new Gantt(ganttContainer.value, tasks.value, {
      on_click: (task) => {
        alert(`Task: ${task.name}`);
        // Later, we can open a modal here.
      },
      on_date_change: (task, start, end) => {
        console.log(`Date changed for ${task.name} to ${start} - ${end}`);
        // Here we can call an API to save the changes.
      },
      bar_height: 20,
      bar_corner_radius: 3,
      padding: 18,
      view_modes: ['Quarter Day', 'Half Day', 'Day', 'Week', 'Month'],
      view_mode: 'Week',
      custom_popup_html: null, // We handle clicks ourselves
    });
  }
};

onMounted(() => {
  fetchGanttData(props.projectId);
});

watch(() => props.projectId, (newId) => {
  fetchGanttData(newId);
});

watch(tasks, async (newTasks) => {
  if (newTasks.length > 0) {
    await nextTick(); // Wait for the DOM to update
    renderGanttChart();
  }
}, { deep: true });

</script>

<style>
/* Frappe Gantt Customizations */
.gantt .bar-progress {
  fill: #3b82f6; /* blue-500 */
}

.gantt .bar-label {
  font-size: 12px;
  font-weight: 500;
  fill: #1f2937; /* gray-800 */
}

/* Custom classes for task statuses from API */
.gantt-bar-to-do .bar, .gantt-bar-open .bar {
  fill: #d1d5db; /* gray-300 */
}
.gantt-bar-in-progress .bar {
  fill: #60a5fa; /* blue-400 */
}
.gantt-bar-done .bar, .gantt-bar-resolved .bar, .gantt-bar-closed .bar {
  fill: #4ade80; /* green-400 */
}
.gantt-bar-reopened .bar {
  fill: #facc15; /* yellow-400 */
}
.gantt-bar-default .bar {
  fill: #e5e7eb; /* gray-200 */
}

/* Make the container have a dynamic height based on content */
.gantt-chart-container .gantt-container {
    height: auto !important;
}
</style>