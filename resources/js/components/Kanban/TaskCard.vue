<template>
  <div 
    class="bg-white rounded-lg shadow-md p-4 border-l-4 cursor-pointer hover:shadow-lg hover:-translate-y-1 transition-all duration-200" 
    :class="priorityClass"
    @click="$emit('view-task', ticket)" 
  >
    <h4 class="font-semibold text-gray-800 text-sm mb-2">
      {{ ticket?.title || ticket?.name || 'Không có tiêu đề' }}
    </h4>
    
    <div class="flex justify-between items-center mt-3">
      <div class="flex items-center">
        <img 
          :src="ticket?.responsible?.avatar || 'data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\' fill=\'%23ccc\'><circle cx=\'50\' cy=\'50\' r=\'50\'/></svg>'" 
          class="w-6 h-6 rounded-full object-cover" 
          alt="Avatar"
        >
        <span class="text-xs ml-2 text-gray-600">
          {{ ticket?.responsible?.name || 'Chưa phân công' }}
        </span>
      </div>
      
      <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600">
        {{ ticket?.code || 'N/A' }}
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  // ĐỔI TỪ task THÀNH ticket ĐỂ KHỚP VỚI TEMPLATE
  ticket: {
    type: Object,
    required: true
  }
});

defineEmits(['view-task']);

const priorityClass = computed(() => {
  // Lấy tên priority an toàn
  const pName = props.ticket?.priority?.name || '';
  const priorities = {
    'High': 'border-red-500',
    'Normal': 'border-blue-500',
    'Low': 'border-green-500'
  };
  return priorities[pName] || 'border-gray-200';
});
</script>