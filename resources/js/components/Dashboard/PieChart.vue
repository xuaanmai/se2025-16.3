
<template>
  <Doughnut v-if="chartData" :data="data" :options="options" />
</template>

<script setup>
import { computed } from 'vue';
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js';
import { Doughnut } from 'vue-chartjs';

ChartJS.register(ArcElement, Tooltip, Legend);

const props = defineProps({
  chartData: {
    type: Object,
    required: true,
  },
});

const data = computed(() => ({
  labels: props.chartData.labels,
  datasets: [
    {
      backgroundColor: props.chartData.colors,
      data: props.chartData.values,
    },
  ],
}));

const options = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom',
    },
  },
};
</script>
