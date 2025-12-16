
<template>
  <div class="bg-white shadow-md rounded-lg overflow-hidden">
    <div v-if="ticketsStore.loading" class="text-center py-10">Loading tickets...</div>
    <div v-else-if="ticketsStore.error" class="text-center py-10 text-red-500">{{ ticketsStore.error }}</div>
    
    <table v-else class="min-w-full leading-normal">
      <thead>
        <tr>
          <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Ticket</th>
          <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
          <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Priority</th>
          <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Assignee</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="ticket in ticketsStore.tickets" :key="ticket.id" class="hover:bg-gray-50">
          <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
            <p class="text-gray-900 whitespace-no-wrap font-bold">{{ ticket.code }}</p>
            <p class="text-gray-600 whitespace-no-wrap">{{ ticket.name }}</p>
          </td>
          <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
            <span class="relative inline-block px-3 py-1 font-semibold leading-tight" :style="{ color: ticket.status?.color }">
              <span aria-hidden class="absolute inset-0 opacity-20 rounded-full" :style="{ backgroundColor: ticket.status?.color }"></span>
              <span class="relative">{{ ticket.status?.name || 'N/A' }}</span>
            </span>
          </td>
          <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">{{ ticket.priority?.name }}</td>
          <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">{{ ticket.responsible?.name || 'Unassigned' }}</td>
        </tr>
        <tr v-if="ticketsStore.tickets.length === 0">
          <td colspan="4" class="text-center py-10 text-gray-500">No tickets found for this project.</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useTicketsStore } from '@/stores/tickets';

const props = defineProps({
  projectId: {
    type: [String, Number],
    required: true,
  },
});

const ticketsStore = useTicketsStore();

onMounted(() => {
  ticketsStore.fetchTickets(1, { project_id: props.projectId });
});
</script>
