<template>
  <div>
    <div class="flex justify-end mb-6">
      <button @click="openCreateModal" class="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700">
        Create Project
      </button>
    </div>

    <!-- Loading and Error States -->
    <div v-if="projectsStore.loading" class="text-center py-10">Loading projects...</div>
    <div v-else-if="projectsStore.error" class="text-center py-10 text-red-500">{{ projectsStore.error }}</div>

    <!-- Projects Table -->
    <div v-else class="bg-white shadow-md rounded-lg overflow-hidden">
      <table class="min-w-full leading-normal">
        <thead>
          <tr>
            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
              Name
            </th>
            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
              Status
            </th>
            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
              Owner
            </th>
            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
              Created At
            </th>
            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="project in projectsStore.projects" :key="project.id" class="hover:bg-gray-50">
            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
              <div class="flex items-center">
                <button @click="projectsStore.toggleFavorite(project.id)" class="mr-2 text-gray-400 hover:text-yellow-500">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" 
                       :class="['h-5 w-5', project.is_favorite ? 'text-yellow-400' : '']">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                  </svg>
                </button>
                <router-link :to="{ name: 'ProjectDetail', params: { id: project.id } }" class="text-blue-600 hover:text-blue-900 font-semibold">
                  {{ project.name }}
                </router-link>
              </div>
            </td>
            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
              <span class="relative inline-block px-3 py-1 font-semibold text-green-900 leading-tight">
                <span aria-hidden class="absolute inset-0 bg-green-200 opacity-50 rounded-full"></span>
                <span class="relative">{{ project.status?.name || 'N/A' }}</span>
              </span>
            </td>
            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
              {{ project.owner?.name || 'N/A' }}
            </td>
            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
              {{ new Date(project.created_at).toLocaleDateString() }}
            </td>
            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-right">
              <button @click="openEditModal(project)" class="text-indigo-600 hover:text-indigo-900 mr-4">Edit</button>
              <button @click="openDeleteModal(project)" class="text-red-600 hover:text-red-900">Delete</button>
            </td>
          </tr>
           <tr v-if="projectsStore.projects.length === 0">
            <td colspan="5" class="text-center py-10 text-gray-500">No projects found.</td>
          </tr>
        </tbody>
      </table>
      
      <!-- Pagination -->
      <div class="px-5 py-5 bg-white border-t flex flex-col xs:flex-row items-center xs:justify-between">
        <span class="text-xs xs:text-sm text-gray-900">
          Showing {{ projectsStore.projects.length }} of {{ projectsStore.pagination.total }} projects
        </span>
        <div class="inline-flex mt-2 xs:mt-0">
          <button 
            @click="changePage(projectsStore.pagination.currentPage - 1)" 
            :disabled="projectsStore.pagination.currentPage <= 1"
            class="text-sm bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded-l disabled:opacity-50">
              Prev
          </button>
          <button 
            @click="changePage(projectsStore.pagination.currentPage + 1)"
            :disabled="projectsStore.pagination.currentPage >= projectsStore.pagination.totalPages"
            class="text-sm bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded-r disabled:opacity-50">
              Next
          </button>
        </div>
      </div>
    </div>

    <!-- Project Form Modal -->
    <ProjectFormModal 
      v-if="isFormModalOpen" 
      :project="selectedProject"
      @close="closeFormModal" 
      @save="handleSave" 
    />

    <!-- Confirmation Modal -->
    <ConfirmationModal
      :show="isDeleteModalOpen"
      title="Delete Project"
      :message="`Are you sure you want to delete the project '${projectToDelete?.name}'? This action cannot be undone.`"
      confirm-button-text="Delete"
      @close="closeDeleteModal"
      @confirm="handleDelete"
    />

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useProjectsStore } from '@/stores/projects';
import ProjectFormModal from '../components/Projects/ProjectFormModal.vue';
import ConfirmationModal from '../components/ConfirmationModal.vue';

const projectsStore = useProjectsStore();

const isFormModalOpen = ref(false);
const selectedProject = ref(null);

const isDeleteModalOpen = ref(false);
const projectToDelete = ref(null);

onMounted(() => {
  projectsStore.fetchProjects();
});

const changePage = (page) => {
  if (page > 0 && page <= projectsStore.pagination.totalPages) {
    projectsStore.fetchProjects(page);
  }
};

// Form Modal (Create/Edit)
const openCreateModal = () => {
  selectedProject.value = null;
  isFormModalOpen.value = true;
};

const openEditModal = (project) => {
  selectedProject.value = { ...project };
  isFormModalOpen.value = true;
};

const closeFormModal = () => {
  isFormModalOpen.value = false;
  selectedProject.value = null;
};

const handleSave = async (payload) => {
  if (payload.id) {
    await projectsStore.updateProject(payload.id, payload.data);
  } else {
    await projectsStore.createProject(payload.data);
  }
  if (!projectsStore.error) {
    closeFormModal();
  }
};

// Delete Modal
const openDeleteModal = (project) => {
  projectToDelete.value = project;
  isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
  isDeleteModalOpen.value = false;
  projectToDelete.value = null;
};

const handleDelete = () => {
  if (projectToDelete.value) {
    projectsStore.deleteProject(projectToDelete.value.id);
  }
  closeDeleteModal();
};
</script>