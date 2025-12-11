<template>
  <div v-if="projectsStore.loading" class="text-center py-10">Loading project details...</div>
  <div v-else-if="projectsStore.error" class="text-center py-10 text-red-500">{{ projectsStore.error }}</div>
  <div v-else-if="project" class="space-y-6">
    
    <!-- Tab Navigation -->
    <div class="border-b border-gray-200">
      <nav class="-mb-px flex space-x-8" aria-label="Tabs">
        <button 
          v-for="tab in tabs" 
          :key="tab.name" 
          @click="handleTabClick(tab)"
          :class="[
            currentTab === tab.name 
              ? 'border-blue-500 text-blue-600' 
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
          ]"
        >
          {{ tab.name }}
        </button>
      </nav>
    </div>

    <!-- Tab Header -->
    <div class="flex justify-end">
        <button v-if="currentTab === 'Tickets' && canManageProject" @click="openCreateTicketModal" class="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700">
            Create Ticket
        </button>
    </div>

    <!-- Tab Content -->
    <div>
      <router-view v-if="currentTab === 'Gantt'"></router-view>
      <div v-if="currentTab === 'Tickets'">
        <ProjectTickets :project-id="projectId" />
      </div>
      <div v-if="currentTab === 'Members'">
        <ProjectMembers :project-id="projectId" :members="project.users" />
      </div>
      <div v-if="currentTab === 'Sprints'">
        <h2 class="text-xl font-semibold mb-4">Sprints</h2>
        <p>Sprint list for this project will be displayed here.</p>
        <!-- Sprint list component will go here -->
      </div>
       <div v-if="currentTab === 'Settings'">
        <h2 class="text-xl font-semibold mb-4">Settings</h2>
        <p>Project settings will be displayed here.</p>
        <!-- Settings component will go here -->
      </div>
    </div>

    <!-- Ticket Form Modal -->
    <TicketFormModal 
      v-if="isTicketModalOpen" 
      :ticket="selectedTicket"
      @close="closeTicketModal" 
      @save="handleSaveTicket" 
    />

  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useProjectsStore } from '@/stores/projects';
import { useAuthStore } from '@/stores';
import { useTicketsStore } from '@/stores/tickets';
import ProjectMembers from '../components/Projects/ProjectMembers.vue';
import ProjectTickets from '../components/Projects/ProjectTickets.vue';
import TicketFormModal from '../components/Tickets/TicketFormModal.vue';

const route = useRoute();
const router = useRouter();
const projectsStore = useProjectsStore();
const authStore = useAuthStore();
const ticketsStore = useTicketsStore();

const project = computed(() => projectsStore.project);
const projectId = route.params.id;

const tabs = [
  { name: 'Tickets', routeName: 'ProjectDetail' },
  { name: 'Gantt', routeName: 'projects.gantt' },
  { name: 'Members', routeName: 'ProjectDetail' },
  { name: 'Sprints', routeName: 'ProjectDetail' },
  { name: 'Settings', routeName: 'ProjectDetail' },
];
const currentTab = ref('Tickets');

// Sync tab with route
watch(route, (to) => {
  if (to.name === 'projects.gantt') {
    currentTab.value = 'Gantt';
  } else if (to.name === 'ProjectDetail' && currentTab.value === 'Gantt') {
    // If navigating back to detail from gantt, reset to a default tab
    currentTab.value = 'Tickets';
  }
}, { immediate: true });


const handleTabClick = (tab) => {
  currentTab.value = tab.name;
  if (tab.name === 'Gantt') {
    router.push({ name: 'projects.gantt', params: { id: projectId } });
  } else {
    // If the current route is the gantt chart, navigate back to the parent project detail
    if (route.name === 'projects.gantt') {
      router.push({ name: 'ProjectDetail', params: { id: projectId } });
    }
  }
}

// Permissions
const canManageProject = computed(() => {
  if (!authStore.user || !project.value?.users) {
    return false;
  }
  const userInProject = project.value.users.find(u => u.id === authStore.user.id);
  return userInProject && ['owner', 'manager'].includes(userInProject.pivot.role);
});

// Ticket Modal State
const isTicketModalOpen = ref(false);
const selectedTicket = ref(null);

const openCreateTicketModal = () => {
  selectedTicket.value = null; // Ensure it's a create operation
  isTicketModalOpen.value = true;
};

const closeTicketModal = () => {
  isTicketModalOpen.value = false;
  selectedTicket.value = null;
};

const handleSaveTicket = async (ticketData) => {
  // Ensure project_id is set for new tickets
  if (!ticketData.id) {
    ticketData.project_id = projectId;
  }

  if (ticketData.id) {
    await ticketsStore.updateTicket(ticketData.id, ticketData);
  } else {
    await ticketsStore.createTicket(ticketData);
  }

  if (!ticketsStore.error) {
    closeTicketModal();
    // Refresh the ticket list in the child component
    // This is a simple way; a better way might be using an event bus or a shared store state
    if (currentTab.value === 'Tickets') {
        ticketsStore.fetchTickets(1, { project_id: projectId });
    }
  }
};


onMounted(() => {
  projectsStore.fetchProject(projectId);
});
</script>