<template>
  <!-- Sidebar -->
  <aside class="fixed left-0 top-0 h-full w-64 flex-shrink-0 bg-gray-800 text-white flex flex-col z-20">
    <!-- Logo -->
    <div class="h-16 flex items-center justify-center text-2xl font-bold border-b border-gray-700 flex-shrink-0">
      <span class="text-blue-400">PLANORA</span>
    </div>
    
    <!-- Menu -->
    <nav class="flex-1 px-2 py-4 space-y-1">
      <router-link :to="{ name: 'Dashboard' }" class="flex items-center px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700" active-class="bg-gray-900">
        <svg class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
        <span>Dashboard</span>
      </router-link>
      <router-link :to="{ name: 'Projects' }" class="flex items-center px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700" active-class="bg-gray-900">
        <svg class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0l2-2m-2 2l-2-2" /></svg>
        <span>Projects</span>
      </router-link>
      <router-link :to="{ name: 'MyTasks' }" class="flex items-center px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700" active-class="bg-gray-900">
        <svg class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
        <span>My Tasks</span>
      </router-link>
    </nav>

    <!-- Bottom Settings -->
    <div class="mt-auto p-2 border-t border-gray-700">
       <a href="#" class="flex items-center px-3 py-2 rounded-md text-sm font-medium text-gray-400 hover:bg-gray-700 hover:text-white">
        <svg class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.096 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
        <span>Settings</span>
      </a>
    </div>
  </aside>
</template>

<script setup>
// In the future, we can add logic here if needed.
</script>