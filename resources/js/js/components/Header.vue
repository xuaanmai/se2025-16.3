<template>
  <header class="flex items-center justify-between h-16 px-6 bg-white border-b border-gray-200">
    <!-- Page Title -->
    <h1 class="text-xl font-semibold text-gray-800">{{ pageTitle }}</h1>

    <!-- Global Search (optional, can be expanded) -->
    <div class="relative flex-1 max-w-xs mx-8">
      <span class="absolute inset-y-0 left-0 flex items-center pl-3">
        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
        </svg>
      </span>
      <input 
        type="text" 
        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md text-sm placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
        placeholder="Search..."
      >
    </div>

    <!-- Right side: Notifications and User Profile -->
    <div class="flex items-center space-x-4">
      <!-- Notification Bell -->
      <button class="p-2 rounded-full text-gray-500 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
        <span class="sr-only">View notifications</span>
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
      </button>

      <!-- User Profile -->
      <div class="relative">
        <button @click="dropdownOpen = !dropdownOpen" class="flex items-center space-x-2 focus:outline-none">
          <span class="text-sm font-medium text-gray-700" v-if="authStore.user">{{ authStore.user.name }}</span>
          <img class="h-9 w-9 rounded-full object-cover" :src="authStore.user?.avatar || 'https://via.placeholder.com/150'" :alt="authStore.user?.name">
        </button>
        
        <!-- Dropdown Menu -->
        <div v-show="dropdownOpen" @click="dropdownOpen = false" class="fixed inset-0 h-full w-full z-30"></div>
        <transition
          enter-active-class="transition ease-out duration-100"
          enter-from-class="transform opacity-0 scale-95"
          enter-to-class="transform opacity-100 scale-100"
          leave-active-class="transition ease-in duration-75"
          leave-from-class="transform opacity-100 scale-100"
          leave-to-class="transform opacity-0 scale-95"
        >
          <div v-show="dropdownOpen" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-40">
            <router-link :to="{ name: 'UserProfile' }" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Your Profile</router-link>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</a>
            <div class="border-t border-gray-100"></div>
            <a href="#" @click.prevent="handleLogout" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
          </div>
        </transition>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const dropdownOpen = ref(false);

const pageTitle = computed(() => route.meta.title || 'Dashboard');

const handleLogout = async () => {
  await authStore.logout();
  router.push({ name: 'Login' });
};
</script>

