<template>
  <div class="bg-white p-6 rounded-lg shadow-md text-center">
    <!-- Avatar -->
    <img class="h-32 w-32 rounded-full object-cover mx-auto mb-4 ring-4 ring-blue-100" :src="user.avatar" :alt="user.name">

    <!-- Info -->
    <h2 class="text-2xl font-bold text-gray-800">{{ user.name }}</h2>
    <p class="text-gray-500">{{ user.email }}</p>

    <!-- Role Attributes -->
    <div class="mt-4 flex justify-center space-x-2">
      <span v-for="role in user.roles" :key="role" class="px-3 py-1 text-sm font-medium rounded-full" :class="roleClass(role)">
        {{ role }}
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  user: {
    type: Object,
    required: true,
  }
});

const roleClass = (role) => {
  const classes = {
    Manager: 'bg-blue-100 text-blue-800',
    Member: 'bg-green-100 text-green-800',
    Viewer: 'bg-gray-100 text-gray-800',
  };
  return classes[role] || 'bg-gray-100 text-gray-800';
};
</script>
