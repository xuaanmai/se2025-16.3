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
          @click="currentTab = tab.name"
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

    <!-- Tab Content -->
    <div>
      <div v-show="currentTab === 'Tickets'" v-if="project && project.tickets">
        <ProjectTickets :tickets="project.tickets" :project="project" @ticket-created="refreshProjectData" />
      </div>
      <div v-show="currentTab === 'Members'">
        <ProjectMembers :project="project" />
      </div>
      <div v-show="currentTab === 'Sprints'">
        <h2 class="text-xl font-semibold mb-4">Sprints</h2>
        <p>Sprint list for this project will be displayed here.</p>
        <!-- Sprint list component will go here -->
      </div>
       <div v-show="currentTab === 'Settings'">
        <h2 class="text-xl font-semibold mb-4">Settings</h2>
        <p>Project settings will be displayed here.</p>
        <!-- Settings component will go here -->
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import { useProjectsStore } from '@/stores/projects';
import ProjectMembers from '../components/Projects/ProjectMembers.vue';
import ProjectTickets from '../components/Projects/ProjectTickets.vue';

const route = useRoute();
const projectsStore = useProjectsStore();

const project = computed(() => projectsStore.project);
const projectId = route.params.id;

const tabs = [
  { name: 'Tickets' },
  { name: 'Members' },
  { name: 'Sprints' },
  { name: 'Settings' },
];
const currentTab = ref('Tickets'); // Default tab

const refreshProjectData = () => {
  projectsStore.fetchProject(projectId);
};

onMounted(() => {
  refreshProjectData();
});
</script>
