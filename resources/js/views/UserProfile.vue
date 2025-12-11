<template>
  <div v-if="!authStore.user">
    <p>Loading profile...</p>
  </div>
  <div v-else>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left Column -->
      <div class="lg:col-span-1 space-y-6">
        <div class="bg-white p-6 rounded-lg shadow-md text-center">
          <!-- Avatar -->
          <img class="h-32 w-32 rounded-full object-cover mx-auto mb-4 ring-4 ring-blue-100" :src="authStore.user.avatar || `https://ui-avatars.com/api/?name=${authStore.user.name}`" :alt="authStore.user.name">

          <!-- Info -->
          <h2 class="text-2xl font-bold text-gray-800">{{ authStore.user.name }}</h2>
          <p class="text-gray-500">{{ authStore.user.email }}</p>

          <!-- Role Attributes -->
          <div class="mt-4 flex justify-center space-x-2">
            <span v-for="role in authStore.user.roles" :key="role.id" class="px-3 py-1 text-sm font-medium rounded-full bg-blue-100 text-blue-800">
              {{ role.name }}
            </span>
          </div>

          <!-- Edit Button -->
          <div class="mt-6">
            <button @click="isModalOpen = true" class="w-full px-4 py-2 bg-blue-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-blue-700">
              Edit Profile
            </button>
          </div>
        </div>
      </div>

      <!-- Right Column -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Your Projects -->
        <div class="bg-white p-6 rounded-lg shadow-md">
          <h3 class="font-semibold text-lg text-gray-800 mb-4">My Projects</h3>
          <div v-if="authStore.loading">Loading projects...</div>
          <ul v-else-if="myProjects.length" class="space-y-3">
            <li v-for="project in myProjects" :key="project.id" class="flex items-center justify-between p-2 rounded-md hover:bg-gray-50">
              <router-link :to="{ name: 'ProjectDetail', params: { id: project.id } }" class="font-semibold text-blue-600 hover:underline">
                {{ project.name }}
              </router-link>
              <span class="text-sm text-gray-500">{{ project.pivot.role }}</span>
            </li>
          </ul>
          <p v-else class="text-gray-500">You are not a member of any projects yet.</p>
        </div>
      </div>
    </div>

    <!-- Profile Edit Modal -->
    <ProfileFormModal 
      v-if="isModalOpen"
      :user="authStore.user"
      @close="isModalOpen = false"
      @save="handleProfileUpdate"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useAuthStore } from '@/stores';
import { useUsersStore } from '@/stores/users';
import ProfileFormModal from '../components/Profile/ProfileFormModal.vue';

const authStore = useAuthStore();
const usersStore = useUsersStore();

const isModalOpen = ref(false);

onMounted(() => {
  // The user object from the auth store should contain the projects.
  // If not, re-fetch the user data to ensure we have the latest associations.
  if (!authStore.user?.projects) {
    authStore.fetchUser();
  }
});

const myProjects = computed(() => {
  return authStore.user?.projects || [];
});

const handleProfileUpdate = async (payload) => {
  if (!authStore.user) return;
  
  await usersStore.updateUser(authStore.user.id, payload);

  if (!usersStore.error) {
    // Refresh user data from backend to get the latest info
    await authStore.fetchUser();
    isModalOpen.value = false;
  }
};
</script>
