<template>
  <div class="space-y-4">
    <h2 class="text-xl font-semibold">Sprints</h2>
    <div v-if="loading" class="text-center py-10">Loading sprints...</div>
    <div v-else-if="sprints.length === 0" class="text-center py-10 text-gray-500">
      No sprints found for this project.
    </div>
    <div v-else class="bg-white shadow-md rounded-lg overflow-hidden">
      <ul class="divide-y divide-gray-200">
        <li v-for="sprint in sprints" :key="sprint.id" class="px-6 py-4">
          <div class="flex justify-between items-center">
            <div>
              <p class="text-lg font-medium text-blue-600">{{ sprint.name }}</p>
              <p class="text-sm text-gray-500">
                {{ formatDate(sprint.starts_at) }} - {{ formatDate(sprint.ends_at) }}
              </p>
            </div>
            <div class="text-sm text-gray-700">
              <span class="font-semibold">{{ sprint.tickets_count || 0 }}</span> tickets
            </div>
          </div>
          <p v-if="sprint.description" class="mt-2 text-sm text-gray-600">{{ sprint.description }}</p>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useProjectsStore } from '@/stores/projects';

const props = defineProps({
  project: {
    type: Object,
    required: true,
  },
});

const projectsStore = useProjectsStore();
const sprints = ref([]);
const loading = ref(false);

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  const options = { year: 'numeric', month: 'long', day: 'numeric' };
  return new Date(dateString).toLocaleDateString(undefined, options);
};

const loadSprints = async (projectId) => {
  if (!projectId) return;
  loading.value = true;
  sprints.value = await projectsStore.fetchSprints(projectId);
  loading.value = false;
};

watch(() => props.project.id, (newId) => {
  loadSprints(newId);
}, { immediate: true });
</script>
