
<template>
  <div class="space-y-6">
    <!-- Add Member Form -->
    <div class="bg-white p-4 rounded-lg shadow">
      <h3 class="font-semibold text-lg mb-2">Add New Member</h3>
      <form @submit.prevent="handleAddUser" class="flex items-end space-x-4">
        <div class="flex-grow">
          <label for="user-select" class="block text-sm font-medium text-gray-700">User</label>
          <select id="user-select" v-model="newUser.id" required class="mt-1 form-select">
            <option value="">Select a user</option>
            <option v-for="user in availableUsers" :key="user.id" :value="user.id">{{ user.name }} ({{ user.email }})</option>
          </select>
        </div>
        <div>
          <label for="role-select" class="block text-sm font-medium text-gray-700">Role</label>
          <select id="role-select" v-model="newUser.role" required class="mt-1 form-select">
            <option>member</option>
            <option>manager</option>
            <option>viewer</option>
          </select>
        </div>
        <button type="submit" class="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700 h-10">Add Member</button>
      </form>
       <p v-if="projectsStore.error" class="text-sm text-red-600 mt-2">{{ projectsStore.error }}</p>
    </div>

    <!-- Members List -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
      <table class="min-w-full leading-normal">
        <thead>
          <tr>
            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Role</th>
            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="member in members" :key="member.id" class="hover:bg-gray-50">
            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
              <p class="font-semibold">{{ member.name }}</p>
              <p class="text-gray-600">{{ member.email }}</p>
            </td>
            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
              <select v-model="member.pivot.role" @change="updateRole(member.id, $event.target.value)" class="form-select py-1">
                <option>member</option>
                <option>manager</option>
                <option>viewer</option>
              </select>
            </td>
            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-right">
              <button @click="removeUser(member.id)" class="text-red-600 hover:text-red-900">Remove</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<style scoped>
.form-select {
  @apply block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm;
}
</style>

<script setup>
import { ref, computed } from 'vue';
import { useProjectsStore } from '@/stores/projects';
import { useReferentialStore } from '@/stores/referentials';

const props = defineProps({
  projectId: {
    type: [String, Number],
    required: true,
  },
  members: {
    type: Array,
    required: true,
  },
});

const projectsStore = useProjectsStore();
const referentialStore = useReferentialStore();

const newUser = ref({
  id: '',
  role: 'member',
});

// Filter out users who are already members of the project
const availableUsers = computed(() => {
  const memberIds = new Set(props.members.map(m => m.id));
  return referentialStore.users.filter(u => !memberIds.has(u.id));
});

const handleAddUser = async () => {
  if (!newUser.value.id) return;
  await projectsStore.attachUser(props.projectId, {
    user_id: newUser.value.id,
    role: newUser.value.role,
  });
  if (!projectsStore.error) {
    newUser.value.id = '';
    newUser.value.role = 'member';
  }
};

const updateRole = async (userId, role) => {
  await projectsStore.updateUserRole(props.projectId, userId, role);
};

const removeUser = async (userId) => {
  if (window.confirm('Are you sure you want to remove this member from the project?')) {
    await projectsStore.detachUser(props.projectId, userId);
  }
};
</script>
