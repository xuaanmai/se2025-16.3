<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center" @click.self="$emit('close')">
    <div class="relative mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
      <div class="mt-3">
        <h3 class="text-lg leading-6 font-medium text-gray-900 text-center">{{ formTitle }}</h3>
        <form @submit.prevent="submitForm" class="mt-4 px-4 py-3 text-left space-y-4">
          
          <div v-if="ticketsStore.error" class="mb-4 rounded-md bg-red-50 p-4">
            <p class="text-sm font-medium text-red-800">{{ ticketsStore.error }}</p>
          </div>

          <div>
            <label for="ticket-project" class="block text-sm font-medium text-gray-700">Project</label>
            <select id="ticket-project" v-model="form.project_id" required class="mt-1 form-select">
              <option disabled value="">Please select a project</option>
              <option v-for="project in referentialStore.projects" :key="project.id" :value="project.id">{{ project.name }}</option>
            </select>
          </div>

          <div>
            <label for="ticket-name" class="block text-sm font-medium text-gray-700">Ticket Title</label>
            <input type="text" id="ticket-name" v-model="form.name" required class="mt-1 form-input" />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="ticket-type" class="block text-sm font-medium text-gray-700">Type</label>
              <select id="ticket-type" v-model="form.type_id" required class="mt-1 form-select">
                 <option v-for="type in referentialStore.ticketTypes" :key="type.id" :value="type.id">
                  {{ type.name }}
                 </option>
              </select>
            </div>
            <div>
              <label for="ticket-priority" class="block text-sm font-medium text-gray-700">Priority</label>
              <select id="ticket-priority" v-model="form.priority_id" required class="mt-1 form-select">
                 <option v-for="priority in referentialStore.ticketPriorities" :key="priority.id" :value="priority.id">
                  {{ priority.name }}
                 </option>
              </select>
            </div>
          </div>

          <div>
            <label for="ticket-assignee" class="block text-sm font-medium text-gray-700">Assignee</label>
            <select id="ticket-assignee" v-model="form.responsible_id" class="mt-1 form-select" :disabled="!form.project_id || loadingMembers">
              <option value="">Unassigned</option>
              <option v-if="loadingMembers" value="">Loading members...</option>
              <option v-for="user in projectMembers" :key="user.id" :value="user.id">{{ user.name }}</option>
            </select>
          </div>

          <div>
            <label for="ticket-description" class="block text-sm font-medium text-gray-700">Description</label>
            <textarea id="ticket-description" v-model="form.description" rows="4" class="mt-1 form-input"></textarea>
          </div>

          <div class="items-center pt-4">
            <button type="submit" :disabled="ticketsStore.loading"
                    class="w-full px-4 py-2 bg-blue-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50">
              {{ ticketsStore.loading ? 'Saving...' : 'Save Ticket' }}
            </button>
            <button type="button" @click="$emit('close')"
                    class="w-full px-4 py-2 mt-2 bg-gray-200 text-gray-800 text-base font-medium rounded-md shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
.form-input, .form-select {
  @apply block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm;
}
</style>

<script setup>
import { ref, watch, computed } from 'vue';
import { useTicketsStore } from '@/stores/tickets';
import { useProjectsStore } from '@/stores/projects';
import { useReferentialStore } from '@/stores/referentials';

const props = defineProps({
  ticket: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(['close', 'save']);

const ticketsStore = useTicketsStore();
const projectsStore = useProjectsStore();
const referentialStore = useReferentialStore();

const form = ref({});
const projectMembers = ref([]);
const loadingMembers = ref(false);

const isEditMode = computed(() => !!props.ticket);
const formTitle = computed(() => (isEditMode.value ? 'Edit Ticket' : 'Create New Ticket'));

const initializeForm = (ticket) => {
  if (ticket) {
    form.value = {
      id: ticket.id,
      name: ticket.name,
      description: ticket.description || '',
      project_id: ticket.project_id,
      responsible_id: ticket.responsible_id || '',
      status_id: ticket.status_id,
      priority_id: ticket.priority_id,
      type_id: ticket.type_id,
    };
  } else {
    form.value = {
      id: null,
      name: '',
      description: '',
      project_id: referentialStore.projects[0]?.id || '',
      responsible_id: '',
      status_id: referentialStore.ticketStatuses.find(s => s.is_default)?.id || '',
      priority_id: referentialStore.ticketPriorities.find(p => p.is_default)?.id || '',
      type_id: referentialStore.ticketTypes.find(t => t.is_default)?.id || '',
    };
  }
  ticketsStore.error = null;
};

watch(() => props.ticket, (newTicket) => {
  initializeForm(newTicket);
}, { immediate: true });

watch(() => form.value.project_id, async (newProjectId, oldProjectId) => {
  if (newProjectId) {
    loadingMembers.value = true;
    projectMembers.value = await projectsStore.fetchProjectMembers(newProjectId);
    loadingMembers.value = false;
    
    // If project changed, check if the current assignee is still in the new project's member list
    const isAssigneeInNewProject = projectMembers.value.some(member => member.id === form.value.responsible_id);
    if (!isAssigneeInNewProject) {
      form.value.responsible_id = ''; // Reset if assignee is not in the new project
    }
  } else {
    projectMembers.value = [];
  }
}, { immediate: true });


const submitForm = () => {
  emit('save', { ...form.value });
};
</script>