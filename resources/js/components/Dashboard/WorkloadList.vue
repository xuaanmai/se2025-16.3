<template>
  <div class="bg-white p-6 rounded-lg shadow-md">
<<<<<<< HEAD
    <h3 class="font-semibold text-lg text-gray-800 mb-4">Top Users by Hours Logged</h3>
    <ul v-if="users.length" class="space-y-4">
      <li v-for="user in users" :key="user.name" class="flex items-center">
        <img class="h-10 w-10 rounded-full object-cover" :src="user.avatar" :alt="user.name">
        <div class="ml-4 flex-1">
          <div class="flex justify-between items-baseline">
            <p class="text-sm font-medium text-gray-900">{{ user.name }}</p>
            <p class="text-xs text-gray-500">{{ user.hours.toFixed(2) }} hrs</p>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2.5 mt-1">
            <div class="bg-blue-600 h-2.5 rounded-full" :style="{ width: user.percentage + '%' }"></div>
=======
    <h3 class="font-semibold text-lg text-gray-800 mb-4">
      Top Users by Hours Logged
    </h3>

    <ul v-if="users.length" class="space-y-4">
      <li
        v-for="user in users"
        :key="user.name"
        class="flex items-center"
      >
        <img
          class="h-10 w-10 rounded-full object-cover"
          :src="user.avatar"
          :alt="user.name"
        />

        <div class="ml-4 flex-1">
          <div class="flex justify-between items-baseline">
            <p class="text-sm font-medium text-gray-900">
              {{ user.name }}
            </p>
            <p class="text-xs text-gray-500">
              {{ user.hours.toFixed(2) }} hrs
            </p>
          </div>

          <div class="w-full bg-gray-200 rounded-full h-2.5 mt-1">
            <div
              class="bg-blue-600 h-2.5 rounded-full"
              :style="{ width: user.percentage + '%' }"
            ></div>
>>>>>>> origin/dashboard
          </div>
        </div>
      </li>
    </ul>
<<<<<<< HEAD
=======

>>>>>>> origin/dashboard
    <div v-else class="text-center py-4 text-gray-500">
      No time logged data available.
    </div>
  </div>
</template>

<script setup>
<<<<<<< HEAD
import { computed } from 'vue';
=======
import { computed } from 'vue'
>>>>>>> origin/dashboard

const props = defineProps({
  workloadData: {
    type: Object,
<<<<<<< HEAD
    default: null
  }
});

const users = computed(() => {
  if (!props.workloadData || !props.workloadData.labels || !props.workloadData.values) {
    return [];
  }

  const maxHours = Math.max(...props.workloadData.values);
  if (maxHours === 0) return [];

  return props.workloadData.labels.map((label, index) => ({
    name: label,
    hours: props.workloadData.values[index],
    // A simple avatar based on name
    avatar: `https://ui-avatars.com/api/?name=${encodeURIComponent(label)}&background=random`,
    percentage: (props.workloadData.values[index] / maxHours) * 100,
  }));
});
=======
    default: null,
  },
})

const users = computed(() => {
  if (
    !props.workloadData ||
    !props.workloadData.labels ||
    !props.workloadData.datasets?.length
  ) {
    return []
  }

  const values = props.workloadData.datasets[0].data
  const labels = props.workloadData.labels

  const maxHours = Math.max(...values)
  if (!maxHours) return []

  return labels.map((label, index) => ({
    name: label,
    hours: values[index],
    avatar: `https://ui-avatars.com/api/?name=${encodeURIComponent(
      label
    )}&background=random`,
    percentage: (values[index] / maxHours) * 100,
  }))
})
>>>>>>> origin/dashboard
</script>
