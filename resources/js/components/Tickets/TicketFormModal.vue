<script setup>
import { ref, computed, onMounted } from 'vue';
import { useReferentialStore } from '@/stores/referentials';
import { useUsersStore } from '@/stores/users';
import { useProjectsStore } from '@/stores/projects';
import Modal from '@/components/Modal.vue';
import FormInput from '@/components/FormInput.vue';
import FormSelect from '@/components/FormSelect.vue';
import FormTextarea from '@/components/FormTextarea.vue';

const props = defineProps({
  show: Boolean,
  ticket: Object,
});

const emit = defineEmits(['close', 'save']);

const referentialStore = useReferentialStore();
const usersStore = useUsersStore();
const projectsStore = useProjectsStore();

const form = ref({
  name: '',
  project_id: null,
  content: '',
  type_id: null,
  status_id: null,
  priority_id: null,
  responsible_id: null,
  start_date: null,
  due_date: null,
});
const loading = ref(false);

const title = computed(() => (props.ticket ? 'Edit Ticket' : 'Create Ticket'));

const formatDateForInput = (dateString) => {
  if (!dateString) return null;
  return dateString.split(' ')[0];
};

const fetchData = async () => {
  loading.value = true;
  try {
    await Promise.all([
      referentialStore.fetchReferentials(),
      usersStore.fetchUsers(),
      projectsStore.fetchProjects(),
    ]);
  } catch (error) {
    console.error("Failed to fetch initial data for ticket form", error);
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  await fetchData();
  if (props.ticket) {
    form.value = {
      ...props.ticket,
      start_date: formatDateForInput(props.ticket.start_date),
      due_date: formatDateForInput(props.ticket.due_date),
    };
  } else {
    // Set default values for new tickets
    form.value.type_id = referentialStore.types.find(t => t.is_default)?.id || null;
    form.value.priority_id = referentialStore.priorities.find(p => p.is_default)?.id || null;
    form.value.status_id = referentialStore.statuses.find(s => s.is_default)?.id || null;
  }
});

const save = () => {
  emit('save', form.value);
};
</script>

<template>
  <Modal :show="show" @close="$emit('close')" :title="title">
    <div v-if="loading" class="text-center p-8">
      <p>Loading form data...</p>
    </div>
    <div v-else>
      <form @submit.prevent="save">
        <div class="p-6 space-y-4">
          <FormInput v-model="form.name" label="Name" required />
          <FormSelect
              v-model="form.project_id"
              label="Project"
              :options="projectsStore.projectsForSelection"
              required
          />
          <FormTextarea v-model="form.content" label="Content" />
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <FormSelect
                v-model="form.type_id"
                label="Type"
                :options="referentialStore.typesForSelection"
                required
            />
            <FormSelect
                v-model="form.priority_id"
                label="Priority"
                :options="referentialStore.prioritiesForSelection"
                required
            />
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <FormInput v-model="form.start_date" type="date" label="Start Date" />
            <FormInput v-model="form.due_date" type="date" label="Due Date" />
          </div>
          <FormSelect
              v-model="form.responsible_id"
              label="Assignee"
              :options="usersStore.usersForSelection"
          />
        </div>
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
          <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
            Save
          </button>
          <button @click="$emit('close')" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
            Cancel
          </button>
        </div>
      </form>
    </div>
  </Modal>
</template>

<style scoped>
.form-input, .form-select {
  @apply block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm;
}
</style>