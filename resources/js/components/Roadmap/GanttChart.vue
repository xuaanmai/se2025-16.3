<template>
  <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
      <div>
        <h3 class="font-bold text-xl text-gray-900">Project Roadmap</h3>
        <p class="text-sm text-gray-500">Quản lý tiến độ Epics và các Ticket liên quan</p>
      </div>
      
      <div class="flex items-center gap-2 bg-gray-50 p-1 rounded-lg border border-gray-200">
        <button 
          v-for="mode in ['Day', 'Week', 'Month']" 
          :key="mode"
          @click="changeViewMode(mode)"
          :class="[
            viewMode === mode ? 'bg-white shadow-sm text-blue-600' : 'text-gray-500 hover:text-gray-700',
            'px-3 py-1.5 rounded-md text-xs font-semibold transition-all'
          ]"
        >
          {{ mode }}
        </button>
      </div>

      <button @click="isModalOpen = true" class="px-5 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm shadow-sm">
        + Create Epic
      </button>
    </div>

    <div class="relative min-h-[400px] border border-gray-100 rounded-xl overflow-hidden bg-gray-50/30">
      <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-white/80 z-10">
        <div class="flex flex-col items-center gap-2">
          <div class="w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
          <span class="text-sm font-medium text-gray-600">Đang tải lộ trình...</span>
        </div>
      </div>
      
      <div v-if="!loading && error" class="p-10 text-center text-red-500 flex flex-col items-center">
        <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        {{ error }}
      </div>

      <div class="gantt-target-wrapper overflow-auto" v-show="!loading && !error">
        <svg ref="ganttContainer" class="gantt-chart"></svg>
      </div>
    </div>

    <EpicFormModal v-if="isModalOpen" :project-id="projectId" @close="isModalOpen = false" @save="handleSaveEpic" />
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';
import axios from 'axios';
import 'frappe-gantt/dist/frappe-gantt.css'; 
import EpicFormModal from '../Epics/EpicFormModal.vue';

const props = defineProps({ projectId: { type: String, required: true } });
const ganttContainer = ref(null);
const loading = ref(true);
const error = ref(null);
const isModalOpen = ref(false);
const viewMode = ref('Week');
let ganttInstance = null;

const changeViewMode = (mode) => {
  viewMode.value = mode;
  if (ganttInstance) ganttInstance.change_view_mode(mode);
};

const fetchAndRenderGantt = async () => {
  const Gantt = window.Gantt;
  if (!Gantt || !props.projectId) return;

  loading.value = true;
  error.value = null;

  try {
    const response = await axios.get(`/api/projects/${props.projectId}/roadmap`);
    const epics = response.data.epics;

    if (!epics || epics.length === 0) {
      error.value = 'Chưa có Epic nào. Hãy tạo mới để xây dựng lộ trình.';
      return;
    }

    const tasks = epics.map(epic => ({
      id: String(epic.id),
      name: epic.name,
      start: epic.starts_at,
      end: epic.ends_at,
      progress: epic.progress || 0,
      custom_class: 'custom-epic-bar',
      custom_data: { tickets: epic.tickets || [] }
    }));

    await nextTick();
    ganttInstance = new Gantt(ganttContainer.value, tasks, {
      view_mode: viewMode.value,
      language: 'en',
      bar_height: 30,
      padding: 18,
      header_height: 60,
      column_width: 40,
      custom_popup_html: function(task) {
        const ticketTags = task.custom_data.tickets.map(t => 
          `<span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded border border-blue-100 text-[10px] font-medium">${t.code}</span>`
        ).join(' ');

        return `
          <div class="popup-wrapper shadow-xl border-0 rounded-lg overflow-hidden bg-white min-w-[200px]">
            <div class="p-3 bg-gray-900 text-white">
              <div class="text-xs opacity-70 mb-1">Epic Name</div>
              <div class="font-bold text-sm">${task.name}</div>
            </div>
            <div class="p-3 bg-white space-y-2">
              <div class="flex justify-between text-[11px] text-gray-500">
                <span>Start: ${task._start.toLocaleDateString()}</span>
                <span>End: ${task._end.toLocaleDateString()}</span>
              </div>
              <div class="border-t pt-2">
                <div class="text-[11px] font-semibold text-gray-400 uppercase mb-2">Associated Tickets</div>
                <div class="flex flex-wrap gap-1">${ticketTags || '<span class="text-gray-400 italic">No tickets</span>'}</div>
              </div>
            </div>
          </div>
        `;
      }
    });
  } catch (err) {
    error.value = 'Không thể tải dữ liệu lộ trình.';
  } finally {
    loading.value = false;
  }
};

onMounted(fetchAndRenderGantt);
watch(() => props.projectId, fetchAndRenderGantt);
</script>

<style>
/* CSS gánh trọng trách làm biểu đồ đẹp hơn */
.gantt .bar {
  fill: #e2e8f0 !important; /* Tailwind Slate-200 */
}
.gantt .bar-progress {
  fill: #3b82f6 !important; /* Tailwind Blue-500 */
}
.gantt .bar-label {
  fill: #1e293b !important; /* Tailwind Slate-800 */
  font-weight: 600;
  font-size: 11px;
}
.gantt .grid-row {
  fill: #ffffff;
}
.gantt .grid-row:nth-child(even) {
  fill: #f8fafc;
}
.gantt .grid-header {
  fill: #f1f5f9;
}
.gantt .upper-text, .gantt .lower-text {
  fill: #64748b;
  font-weight: 500;
  text-transform: uppercase;
  font-size: 10px;
}
/* Xóa bỏ đường viền mặc định của popup frappe */
.popup-wrapper {
  font-family: inherit;
}
.gantt-container .details-container {
  padding: 0;
  background: transparent;
  box-shadow: none;
}
</style>