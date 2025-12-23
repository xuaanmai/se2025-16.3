<template>
  <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
      <div>
        <h3 class="font-bold text-xl text-gray-900">Task Timeline</h3>
        <p class="text-sm text-gray-500">Timeline of all tasks with start and due dates</p>
      </div>
      
      <div class="flex items-center gap-3">
        <!-- View Mode Selector -->
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

        <!-- Legend Toggle -->
        <button 
          @click="showLegend = !showLegend"
          class="px-3 py-1.5 text-xs font-semibold text-gray-600 hover:text-gray-900 border border-gray-200 rounded-lg hover:bg-gray-50 transition-all"
        >
          {{ showLegend ? 'Hide' : 'Show' }} Legend
        </button>
      </div>
    </div>

    <!-- Legend Panel -->
    <div v-if="showLegend" class="mb-4 p-4 bg-gradient-to-br from-slate-50 to-blue-50 rounded-lg border border-slate-200">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Status Legend (Main Bar Color) -->
        <div>
          <h4 class="text-xs font-bold text-gray-700 mb-2 uppercase tracking-wide flex items-center gap-2">
            <span>Status</span>
            <span class="text-[10px] font-normal text-gray-500">(Bar Color)</span>
          </h4>
          <div class="space-y-1.5">
            <div class="flex items-center gap-2">
              <div class="w-12 h-3 rounded" style="background-color: #10b981;"></div>
              <span class="text-xs text-gray-600">Done</span>
            </div>
            <div class="flex items-center gap-2">
              <div class="w-12 h-3 rounded" style="background-color: #f59e0b;"></div>
              <span class="text-xs text-gray-600">In Progress</span>
            </div>
            <div class="flex items-center gap-2">
              <div class="w-12 h-3 rounded" style="background-color: #6b7280;"></div>
              <span class="text-xs text-gray-600">Todo</span>
            </div>
            <div class="flex items-center gap-2">
              <div class="w-12 h-3 rounded" style="background-color: #ef4444;"></div>
              <span class="text-xs text-gray-600">Archived</span>
            </div>
          </div>
        </div>

        <!-- Priority Legend (Left Border) -->
        <div>
          <h4 class="text-xs font-bold text-gray-700 mb-2 uppercase tracking-wide flex items-center gap-2">
            <span>Priority</span>
            <span class="text-[10px] font-normal text-gray-500">(Left Border)</span>
          </h4>
          <div class="space-y-1.5">
            <div class="flex items-center gap-2">
              <div class="w-12 h-3 bg-gray-200 rounded border-l-4 border-red-600"></div>
              <span class="text-xs text-gray-600">High Priority</span>
            </div>
            <div class="flex items-center gap-2">
              <div class="w-12 h-3 bg-gray-200 rounded"></div>
              <span class="text-xs text-gray-600">Normal Priority</span>
            </div>
            <div class="flex items-center gap-2">
              <div class="w-12 h-3 bg-gray-200 rounded border-l-2 border-dashed border-blue-400"></div>
              <span class="text-xs text-gray-600">Low Priority</span>
            </div>
          </div>
        </div>

        <!-- Type Legend (Icon) -->
        <div>
          <h4 class="text-xs font-bold text-gray-700 mb-2 uppercase tracking-wide flex items-center gap-2">
            <span>Type</span>
            <span class="text-[10px] font-normal text-gray-500">(Icon in Popup)</span>
          </h4>
          <div class="space-y-1.5">
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 2a8 8 0 100 16 8 8 0 000-16zm1 11H9v-2h2v2zm0-4H9V5h2v4z"/>
              </svg>
              <span class="text-xs text-gray-600">Bug</span>
            </div>
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
              <span class="text-xs text-gray-600">Task</span>
            </div>
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/>
              </svg>
              <span class="text-xs text-gray-600">Evolution</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="relative min-h-[400px] border border-gray-100 rounded-xl overflow-hidden bg-gray-50/30">
      <div v-if="loading" class="loading-overlay">
        <div class="flex flex-col items-center gap-2">
          <div class="w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
          <span class="text-sm font-medium text-gray-600">Loading Timeline...</span>
        </div>
      </div>
      
      <div v-if="!loading && error" class="p-10 text-center text-red-500 flex flex-col items-center">
        <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        {{ error }}
      </div>

      <!-- Gantt Container with proper overflow handling -->
      <div class="gantt-wrapper" v-show="!loading && !error">
        <div ref="ganttContainer" class="gantt-chart"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';
import axios from 'axios';
import Gantt from 'frappe-gantt';

const props = defineProps({ projectId: { type: String, required: true } });
const ganttContainer = ref(null);
const loading = ref(true);
const error = ref(null);
const viewMode = ref('Week');
const showLegend = ref(true);
let ganttInstance = null;

const changeViewMode = (mode) => {
  viewMode.value = mode;
  if (ganttInstance) {
    ganttInstance.change_view_mode(mode);
    // Re-apply styles after view change
    setTimeout(() => {
      const tasks = ganttInstance.get_all_tasks(); // Get tasks from instance
      if (tasks && tasks.length > 0) {
        applyCustomClasses(tasks);
      }
    }, 200);
  }
};

// const fetchAndRenderGantt = async () => {
//   loading.value = true;
//   error.value = null;

//   console.log('=== GANTT CHART DEBUG START ===');
//   console.log('Project ID:', props.projectId);
//   console.log('Gantt Library Loaded:', !!Gantt);

//   if (!Gantt) {
//     error.value = 'Gantt library failed to load. Please refresh.';
//     loading.value = false;
//     console.error('Gantt library is not available');
//     return;
//   }

//   if (!props.projectId) {
//     error.value = 'Project ID not found.';
//     loading.value = false;
//     console.error('No project ID provided');
//     return;
//   }

//   try {
//     console.log('Fetching roadmap data from API...');
//     const response = await axios.get(`/api/projects/${props.projectId}/roadmap`);
//     const tasks = response.data;

//     console.log('=== API RESPONSE ===');
//     console.log('Response Status:', response.status);
//     console.log('Tasks Received:', tasks);
//     console.log('Number of Tasks:', tasks?.length || 0);

//     if (tasks && tasks.length > 0) {
//       console.log('=== FIRST TASK SAMPLE ===');
//       console.log('Task Data:', tasks[0]);
//       console.log('Custom Classes:', tasks[0].custom_class);
//       console.log('Start Date:', tasks[0].start);
//       console.log('End Date:', tasks[0].end);
//       console.log('Progress:', tasks[0].progress);
//     }

//     if (!tasks || tasks.length === 0) {
//       error.value = 'No tasks with start and due dates found for this project.';
//       console.warn('Empty task list returned from API');
      
//       if (ganttInstance) {
//         ganttInstance.refresh([]);
//       }
//       loading.value = false;
//       return;
//     }

//     await nextTick();

//     const options = {
//       view_mode: viewMode.value,
//       language: 'en',
//       bar_height: 32,
//       bar_corner_radius: 4,
//       padding: 18,
//       header_height: 60,
//       step: 24,
//       view_modes: ['Day', 'Week', 'Month'],
//       popup_trigger: 'click',
//       on_click: (task) => {
//         console.log('Task Clicked:', task);
//       },
//       on_date_change: (task, start, end) => {
//         console.log('Date Changed:', { task, start, end });
//       },
//       custom_popup_html: (task) => {
//         return `
//           <div class="popup-wrapper shadow-xl border-0 rounded-lg overflow-hidden bg-white">
//             ${task.custom_popup_html}
//           </div>
//         `;
//       }
//     };

//     console.log('=== INITIALIZING GANTT ===');
//     console.log('Options:', options);
//     console.log('Container:', ganttContainer.value);

//     if (ganttInstance) {
//       console.log('Refreshing existing Gantt instance');
//       ganttInstance.refresh(tasks);
//     } else {
//       console.log('Creating new Gantt instance');
//       ganttInstance = new Gantt(ganttContainer.value, tasks, options);
//       console.log('Gantt instance created successfully');
//     }

//     console.log('=== GANTT CHART DEBUG END ===');

//   } catch (err) {
//     console.error('=== ERROR OCCURRED ===');
//     console.error('Error Type:', err.name);
//     console.error('Error Message:', err.message);
//     console.error('Full Error:', err);
    
//     if (err.response) {
//       console.error('API Response Error:', {
//         status: err.response.status,
//         data: err.response.data,
//         headers: err.response.headers
//       });
//     }
    
//     error.value = `Cannot fetch timeline data. ${err.message}`;
//   } finally {
//     loading.value = false;
//   }
// };

const fetchAndRenderGantt = async () => {
  loading.value = true;
  error.value = null;

  console.log('=== GANTT CHART DEBUG START ===');
  console.log('Project ID:', props.projectId);
  console.log('Gantt Library Loaded:', !!Gantt);

  if (!Gantt) {
    error.value = 'Gantt library failed to load. Please refresh.';
    loading.value = false;
    console.error('Gantt library is not available');
    return;
  }

  if (!props.projectId) {
    error.value = 'Project ID not found.';
    loading.value = false;
    console.error('No project ID provided');
    return;
  }


  try {
    console.log('Fetching roadmap data from API...');
    const response = await axios.get(`/api/projects/${props.projectId}/roadmap`);
    const tasks = response.data;

    console.log('=== API RESPONSE ===');
    console.log('Response Status:', response.status);
    console.log('Tasks Received:', tasks);
    console.log('Number of Tasks:', tasks?.length || 0);

    if (tasks && tasks.length > 0) {
      console.log('=== FIRST TASK SAMPLE ===');
      console.log('Task Data:', tasks[0]);
      console.log('Status Class:', tasks[0].status_class);
      console.log('Priority Class:', tasks[0].priority_class);
      console.log('Start Date:', tasks[0].start);
      console.log('End Date:', tasks[0].end);
      console.log('Progress:', tasks[0].progress);
    }

    if (!tasks || tasks.length === 0) {
      error.value = 'No tasks with start and due dates found for this project.';
      console.warn('Empty task list returned from API');
      
      if (ganttInstance) {
        ganttInstance.refresh([]);
      }
      loading.value = false;
      return;
    }

    await nextTick();

    const options = {
      view_mode: viewMode.value,
      language: 'en',
      bar_height: 32,
      bar_corner_radius: 4,
      padding: 18,
      header_height: 60,
      step: 24,
      view_modes: ['Day', 'Week', 'Month'],
      popup_trigger: 'click',
      on_click: (task) => {
        console.log('Task Clicked:', task);
      },
      on_date_change: (task, start, end) => {
        console.log('Date Changed:', { task, start, end });
      },
      on_view_change: (mode) => {
        console.log('View mode changed to:', mode);
        // Re-apply custom classes when view changes
        setTimeout(() => applyCustomClasses(tasks), 100);
      },
      custom_popup_html: (task) => {
        return `
          <div class="popup-wrapper shadow-xl border-0 rounded-lg overflow-hidden bg-white">
            ${task.custom_popup_html}
          </div>
        `;
      }
    };

    console.log('=== INITIALIZING GANTT ===');
    console.log('Options:', options);
    console.log('Container:', ganttContainer.value);

    if (ganttInstance) {
      console.log('Refreshing existing Gantt instance');
      ganttInstance.refresh(tasks);
    } else {
      console.log('Creating new Gantt instance');
      ganttInstance = new Gantt(ganttContainer.value, tasks, options);
      console.log('Gantt instance created successfully');
    }

    // ✅ Apply custom classes after Gantt renders
    await nextTick();
    setTimeout(() => {
      applyCustomClasses(tasks);
      console.log('Custom classes applied successfully');
    }, 100);

    console.log('=== GANTT CHART DEBUG END ===');

  } catch (err) {
    console.error('=== ERROR OCCURRED ===');
    console.error('Error Type:', err.name);
    console.error('Error Message:', err.message);
    console.error('Full Error:', err);
    
    if (err.response) {
      console.error('API Response Error:', {
        status: err.response.status,
        data: err.response.data,
        headers: err.response.headers
      });
    }
    
    error.value = `Cannot fetch timeline data. ${err.message}`;
  } finally {
    loading.value = false;
  }
};

// ✅ Helper function to apply custom classes to SVG elements
// const applyCustomClasses = (tasks) => {
//   if (!ganttContainer.value) return;
  
//   console.log('=== APPLYING CUSTOM CLASSES ===');
  
//   tasks.forEach(task => {
//     // Find the bar group element for this task
//     const barGroup = ganttContainer.value.querySelector(`g[data-id="${task.id}"]`);
    
//     if (barGroup) {
//       // Find bar elements within the group
//       const barRect = barGroup.querySelector('.bar');
//       const barProgress = barGroup.querySelector('.bar-progress');
      
//       console.log(`Task ${task.id}:`, {
//         found: !!barGroup,
//         statusClass: task.status_class,
//         priorityClass: task.priority_class
//       });
      
//       // Apply status class (for color)
//       if (barProgress && task.status_class) {
//         barProgress.classList.add(task.status_class);
//       }
      
//       // Apply priority class (for border)
//       if (barRect && task.priority_class) {
//         barRect.classList.add(task.priority_class);
//       }
      
//       // Also apply to progress bar for consistency
//       if (barRect && task.status_class) {
//         barRect.classList.add(task.status_class);
//       }
//     } else {
//       console.warn(`Bar group not found for task ${task.id}`);
//     }
//   });
  
//   console.log('=== CUSTOM CLASSES APPLIED ===');
// };

const applyCustomClasses = (tasks) => {
  if (!ganttContainer.value) {
    console.warn('Gantt container not found');
    return;
  }
  
  console.log('=== APPLYING CUSTOM CLASSES WITH INLINE STYLES ===');
  console.log('Total tasks to process:', tasks.length);
  
  // Color mapping
  const statusColors = {
    'status-done': '#10b981',        // Green
    'status-in-progress': '#f59e0b', // Orange
    'status-todo': '#6b7280',        // Gray
    'status-archived': '#ef4444'     // Red
  };
  
  const priorityStyles = {
    'priority-high': { 
      stroke: '#dc2626', 
      strokeWidth: '4',
      strokeDasharray: 'none'
    },
    'priority-normal': { 
      stroke: '#d1d5db', 
      strokeWidth: '1',
      strokeDasharray: 'none'
    },
    'priority-low': { 
      stroke: '#60a5fa', 
      strokeWidth: '2',
      strokeDasharray: '4,2'
    }
  };
  
  let successCount = 0;
  
  tasks.forEach(task => {
    // Find bar group
    let barGroup = ganttContainer.value.querySelector(`g[data-id="${task.id}"]`);
    
    if (!barGroup) {
      barGroup = ganttContainer.value.querySelector(`[data-id="${task.id}"]`);
    }
    
    if (barGroup) {
      const barRect = barGroup.querySelector('.bar');
      const barProgress = barGroup.querySelector('.bar-progress');
      
      console.log(`✓ Task ${task.id} (${task.name}):`, {
        barRect: !!barRect,
        barProgress: !!barProgress,
        statusClass: task.status_class,
        priorityClass: task.priority_class
      });
      
      // ✅ Apply STATUS color to bar-progress (the filled part)
      if (barProgress && task.status_class && statusColors[task.status_class]) {
        barProgress.setAttribute('fill', statusColors[task.status_class]);
        barProgress.classList.add(task.status_class);
        console.log(`  → Applied color ${statusColors[task.status_class]} to bar-progress`);
      }
      
      // ✅ Apply PRIORITY border to bar (the container)
      if (barRect && task.priority_class && priorityStyles[task.priority_class]) {
        const style = priorityStyles[task.priority_class];
        barRect.setAttribute('stroke', style.stroke);
        barRect.setAttribute('stroke-width', style.strokeWidth);
        if (style.strokeDasharray !== 'none') {
          barRect.setAttribute('stroke-dasharray', style.strokeDasharray);
        }
        barRect.classList.add(task.priority_class);
        console.log(`  → Applied priority border ${style.stroke} to bar`);
      }
      
      successCount++;
    } else {
      console.warn(`✗ Bar group NOT found for task ${task.id} (${task.name})`);
    }
  });
  
  console.log(`=== STYLES APPLIED: ${successCount}/${tasks.length} ===`);
};
onMounted(fetchAndRenderGantt);
watch(() => props.projectId, fetchAndRenderGantt);
</script>

<style>
.loading-overlay {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: rgba(255, 255, 255, 0.95);
  z-index: 10;
  backdrop-filter: blur(2px);
}

/* === FIX FOR WHITESPACE BUG === */
.gantt-wrapper {
  width: 100%;
  overflow-x: auto;
  overflow-y: auto;
  position: relative;
}

.gantt-chart {
  min-width: 100%;
  width: fit-content;
}

.gantt-wrapper svg {
  display: block;
  max-width: none !important;
  height: auto;
}

/* === DESIGN SYSTEM IMPLEMENTATION === */

/* Base Bar Style (Background) */
.gantt .bar {
  fill: #e5e7eb; /* gray-200 - empty background */
  stroke-width: 1;
  stroke: #d1d5db; /* gray-300 */
}

/* Base Progress Bar Style */
.gantt .bar-progress {
  fill: #9ca3af; /* gray-400 - default */
}

/* === STATUS = MAIN BAR COLOR (Progress Fill) === */
/* Use higher specificity to override Frappe Gantt defaults */

.gantt .bar-progress.status-done,
.gantt g .bar-progress.status-done {
  fill: #10b981 !important; /* emerald-500 - Green for completed */
}

.gantt .bar-progress.status-in-progress,
.gantt g .bar-progress.status-in-progress {
  fill: #f59e0b !important; /* amber-500 - Orange for active */
}

.gantt .bar-progress.status-todo,
.gantt g .bar-progress.status-todo {
  fill: #6b7280 !important; /* gray-500 - Gray for pending */
}

.gantt .bar-progress.status-archived,
.gantt g .bar-progress.status-archived {
  fill: #ef4444 !important; /* red-500 - Red for archived */
}

/* === PRIORITY = LEFT BORDER === */

/* High Priority: Thick Red Border */
.gantt .bar.priority-high,
.gantt g .bar.priority-high {
  stroke: #dc2626 !important; /* red-600 */
  stroke-width: 4 !important;
}

/* Normal Priority: Default border */
.gantt .bar.priority-normal,
.gantt g .bar.priority-normal {
  stroke: #d1d5db !important;
  stroke-width: 1 !important;
}

/* Low Priority: Dashed Blue Border */
.gantt .bar.priority-low,
.gantt g .bar.priority-low {
  stroke: #60a5fa !important; /* blue-400 */
  stroke-width: 2 !important;
  stroke-dasharray: 4, 2 !important;
}

/* === BAR LABELS === */
.gantt .bar-label {
  fill: #1e293b !important;
  font-weight: 600;
  font-size: 11px;
  text-shadow: 0 1px 2px rgba(255, 255, 255, 0.8);
}

/* === GRID STYLING === */
.gantt .grid-row:nth-child(even) {
  fill: #f8fafc;
}

.gantt .grid-row:nth-child(odd) {
  fill: #ffffff;
}

.gantt .grid-header {
  fill: #f1f5f9;
}

.gantt .upper-text,
.gantt .lower-text {
  fill: #475569;
  font-weight: 500;
  font-size: 12px;
}

.gantt .grid-line {
  stroke: #e2e8f0;
}

.gantt .today-highlight {
  fill: rgba(59, 130, 246, 0.1);
  stroke: #3b82f6;
  stroke-width: 1.5;
}

/* === POPUP STYLING === */
.gantt-container .popup-wrapper {
  font-family: inherit;
  background: white !important;
  border: 1px solid #e5e7eb !important;
  border-radius: 12px !important;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
  padding: 0 !important;
  overflow: hidden;
}

/* === HANDLES FOR DRAGGING === */
.gantt .handle {
  fill: rgba(0, 0, 0, 0.1);
  cursor: ew-resize;
}

.gantt .handle:hover {
  fill: rgba(0, 0, 0, 0.2);
}

/* === DEPENDENCY ARROWS === */
.gantt .arrow {
  stroke: #94a3b8;
  stroke-width: 1.5;
  fill: none;
}

/* === SCROLLBAR STYLING === */
.gantt-wrapper::-webkit-scrollbar {
  width: 10px;
  height: 10px;
}

.gantt-wrapper::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 5px;
}

.gantt-wrapper::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 5px;
}

.gantt-wrapper::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>