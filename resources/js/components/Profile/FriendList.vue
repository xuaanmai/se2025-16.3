<template>
  <div class="bg-white p-6 rounded-lg shadow-md">
    <h3 class="font-semibold text-lg text-gray-800 mb-4">Connections</h3>
    <ul class="space-y-4">
      <li v-for="friend in friends" :key="friend.id" class="flex items-center">
        <img class="h-10 w-10 rounded-full object-cover" :src="friend.avatar" :alt="friend.name">
        <div class="ml-4">
          <p class="text-sm font-medium text-gray-900">{{ friend.name }}</p>
          <p class="text-xs text-gray-500">{{ friend.role }}</p>
        </div>
      </li>
    </ul>
  </div>
</template>

<script setup>
defineProps({
  friends: {
    type: Array,
    required: true,
  }
});
</script>
