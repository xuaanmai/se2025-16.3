<template>
  <div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left Column -->
      <div class="lg:col-span-1 space-y-6">
        <ProfileCard :user="user" />
      </div>

      <!-- Right Column -->
      <div class="lg:col-span-2 space-y-6">
        <StatsGauge :hours="user.totalLoggedHours" :max-hours="100" />
        <FriendList :friends="user.connections" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import ProfileCard from '../components/Profile/ProfileCard.vue';
import StatsGauge from '../components/Profile/StatsGauge.vue';
import FriendList from '../components/Profile/FriendList.vue';

// Dummy data for the user profile
const user = ref({
  name: 'John Doe',
  email: 'john.doe@planora.app',
  avatar: 'https://via.placeholder.com/150/92c950',
  roles: ['Manager', 'Member'],
  totalLoggedHours: 72,
  connections: [
    { id: 1, name: 'Jane Smith', role: 'Frontend Developer', avatar: 'https://via.placeholder.com/150/771796' },
    { id: 2, name: 'Peter Jones', role: 'Backend Developer', avatar: 'https://via.placeholder.com/150/24f355' },
    { id: 3, name: 'Sara Wilson', role: 'UI/UX Designer', avatar: 'https://via.placeholder.com/150/f66b97' },
  ]
});
</script>
