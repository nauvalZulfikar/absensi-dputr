<template>
<div class="flex items-center justify-center h-screen bg-gray-900">
    <div class="text-center text-white">
    <h1 class="text-6xl font-bold mb-4">404</h1>
    <p class="text-xl mb-8">Oops! Halaman tidak ditemukan.</p>
    <!-- <router-link to="/" class="text-blue-500 hover:underline">Kembali ke Beranda</router-link> -->
    </div>
</div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
</script>

<style scoped>
/* Gaya khusus untuk halaman 404 */
</style>
