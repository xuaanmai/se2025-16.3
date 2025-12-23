<template>
  <div class="bg-white p-6 rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-4">
      <h3 class="font-semibold text-lg text-gray-800">
        My Tasks Today
      </h3>
      <router-link
        to="/tasks"
        class="text-sm text-blue-600 hover:underline"
      >
        View all
      </router-link>
    </div>

    <ul v-if="tasks.length" class="space-y-4">
      <li
        v-for="task in tasks"
        :key="task.id"
        class="flex items-start justify-between"
      >
        <div>
          <p class="text-sm font-medium text-gray-900">
            {{ task.title }}
          </p>
          <p class="text-xs text-gray-500">
            {{ task.project?.name }} • Due {{ formatDate(task.due_date) }}
          </p>
        </div>

        <span
          class="text-xs px-2 py-1 rounded-full"
          :class="statusClass(task)"
        >
          {{ task.status }}
        </span>
      </li>
    </ul>

    <div v-else class="text-center py-6 text-gray-500">
      🎉 You have no tasks for today.
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  tasks: {
    type: Array,
    default: () => [],
  },
});

const formatDate = (date) =>
  new Date(date).toLocaleDateString();

const statusClass = (task) => {
  if (task.is_overdue) {
    return 'bg-red-100 text-red-700';
  }
  if (task.is_due_today) {
    return 'bg-yellow-100 text-yellow-700';
  }
  return 'bg-gray-100 text-gray-600';
};
</script>
