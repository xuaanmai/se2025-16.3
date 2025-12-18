<template>
  <div class="bg-white p-6 rounded-lg shadow-md">
<<<<<<< HEAD
    <h3 class="font-semibold text-lg text-gray-800 mb-4">Activity Timeline</h3>
    <div v-if="groupedActivities.length" class="space-y-6">
      <div v-for="group in groupedActivities" :key="group.date">
        <p class="text-sm font-semibold text-gray-600 mb-2">{{ group.date }}</p>
        <ul class="space-y-4">
          <li v-for="activity in group.activities" :key="activity.id" class="flex items-start">
            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
              <svg class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div class="ml-4">
              <p class="text-sm text-gray-800" v-html="activity.description"></p>
              <p class="text-xs text-gray-500 mt-1">{{ formatTimeAgo(activity.created_at) }}</p>
=======
    <h3 class="font-semibold text-lg text-gray-800 mb-4">
      Activity Timeline
    </h3>

    <div v-if="groupedActivities.length" class="space-y-6">
      <div v-for="group in groupedActivities" :key="group.date">
        <!-- Date -->
        <p class="text-sm font-semibold text-gray-600 mb-2">
          {{ group.date }}
        </p>

        <ul class="space-y-4">
          <li
            v-for="activity in group.activities"
            :key="activity.id"
            class="flex items-start"
          >
            <!-- Icon -->
            <div
              class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center"
            >
              <svg
                class="h-5 w-5 text-blue-500"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M5 13l4 4L19 7"
                />
              </svg>
            </div>

            <!-- Content -->
            <div class="ml-4 space-y-1">
              <!-- Description -->
              <p
                class="text-sm text-gray-800"
                v-html="activity.description"
              ></p>

              <!-- Project -->
              <p
                v-if="activity.ticket?.project"
                class="text-xs text-gray-500"
              >
                <span class="font-medium">Project:</span>
                <span class="text-blue-600">
                  {{ activity.ticket.project.name }}
                </span>
              </p>

              <!-- Status change -->
              <p
                v-if="activity.old_status && activity.new_status"
                class="text-xs text-gray-500"
              >
                <span
                  class="px-2 py-0.5 rounded text-white text-xs"
                  :style="{ backgroundColor: activity.old_status.color }"
                >
                  {{ activity.old_status.name }}
                </span>

                →
                <span
                  class="px-2 py-0.5 rounded text-white text-xs"
                  :style="{ backgroundColor: activity.new_status.color }"
                >
                  {{ activity.new_status.name }}
                </span>
              </p>

              <!-- Time -->
              <p class="text-xs text-gray-400">
                {{ formatTime(activity.created_at) }}
              </p>
>>>>>>> origin/dashboard
            </div>
          </li>
        </ul>
      </div>
    </div>
<<<<<<< HEAD
=======

>>>>>>> origin/dashboard
    <div v-else class="text-center py-4 text-gray-500">
      No recent activity.
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
  activities: {
    type: Array,
<<<<<<< HEAD
    default: () => []
  }
});

// Helper function to format time
const formatTimeAgo = (dateString) => {
  const date = new Date(dateString);
  const now = new Date();
  const seconds = Math.round((now - date) / 1000);
  const minutes = Math.round(seconds / 60);
  const hours = Math.round(minutes / 60);
  const days = Math.round(hours / 24);

  if (seconds < 60) return `${seconds} seconds ago`;
  if (minutes < 60) return `${minutes} minutes ago`;
  if (hours < 24) return `${hours} hours ago`;
  return `${days} days ago`;
};

// Group activities by date (Today, Yesterday, etc.)
const groupedActivities = computed(() => {
  const groups = {};
  const today = new Date();
  const yesterday = new Date(today);
  yesterday.setDate(yesterday.getDate() - 1);

  props.activities.forEach(activity => {
    const activityDate = new Date(activity.created_at);
    let dateKey;

    if (activityDate.toDateString() === today.toDateString()) {
      dateKey = 'Today';
    } else if (activityDate.toDateString() === yesterday.toDateString()) {
      dateKey = 'Yesterday';
    } else {
      dateKey = activityDate.toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });
    }

    if (!groups[dateKey]) {
      groups[dateKey] = [];
    }
    groups[dateKey].push(activity);
  });

  return Object.keys(groups).map(date => ({
    date,
    activities: groups[date]
  }));
});
=======
    default: () => [],
  },
})

/**
 * Format time to HH:mm
 */
const formatTime = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleTimeString([], {
    hour: '2-digit',
    minute: '2-digit',
  })
}

/**
 * Group activities by date (Today / Yesterday / Date)
 * and keep correct order
 */
const groupedActivities = computed(() => {
  const today = new Date()
  const yesterday = new Date()
  yesterday.setDate(today.getDate() - 1)

  const groups = {}

  props.activities.forEach((activity) => {
    const activityDate = new Date(activity.created_at)
    let key
    let order

    if (activityDate.toDateString() === today.toDateString()) {
      key = 'Today'
      order = 0
    } else if (
      activityDate.toDateString() === yesterday.toDateString()
    ) {
      key = 'Yesterday'
      order = 1
    } else {
      key = activityDate.toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
      })
      order = 2
    }

    if (!groups[key]) {
      groups[key] = {
        date: key,
        order,
        activities: [],
      }
    }

    groups[key].activities.push(activity)
  })

  return Object.values(groups)
    .sort((a, b) => a.order - b.order)
    .map((group) => ({
      ...group,
      activities: group.activities.sort(
        (a, b) =>
          new Date(b.created_at) - new Date(a.created_at)
      ),
    }))
})
>>>>>>> origin/dashboard
</script>
