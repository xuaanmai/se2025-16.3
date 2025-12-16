<template>
  <div 
    class="bg-white rounded-lg shadow-md p-4 border-l-4 cursor-pointer hover:shadow-lg hover:-translate-y-1 transition-all duration-200" 
    :class="priorityClass"
    @click="$emit('view-task', task)"
  >
    <h4 class="font-semibold text-gray-800 text-sm mb-2">{{ task.title }}</h4>
    
    <div class="flex justify-between items-center mt-3">
      <div class="flex items-center">
        <img v-if="task.assignee" class="h-6 w-6 rounded-full object-cover" :src="task.assignee.avatar" :alt="task.assignee.name">
        <span v-else class="h-6 w-6 rounded-full bg-gray-200 flex items-center justify-center text-xs font-semibold text-gray-500">?</span>
      </div>
      <span 
        class="px-2 py-1 text-xs font-medium rounded-full"
        :class="dueDateClass"
      >
        {{ task.dueDate }}
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  task: {
    type: Object,
    required: true
  }
});

defineEmits(['view-task']);

const priorityClass = computed(() => {
  const priorities = {
    High: 'border-red-500',
    Medium: 'border-yellow-500',
    Low: 'border-green-500'
  };
  return priorities[props.task.priority] || 'border-gray-300';
});

const dueDateClass = computed(() => {
  // This is a simplified logic. A real implementation would parse dates.
  if (props.task.isOverdue) {
    return 'bg-red-100 text-red-800';
  }
  return 'bg-gray-200 text-gray-800';
});
</script>
