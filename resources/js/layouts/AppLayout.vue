<script setup>
import { onMounted } from 'vue';
// Import Lenis only if installed, otherwise skip for now to prevent errors
// import Lenis from '@studio-freight/lenis' 

onMounted(() => {
    // Smooth scroll init placeholder
});
</script>

<template>
    <div class="min-h-screen bg-black text-white font-sans">
        <nav class="fixed top-0 left-0 right-0 z-50 flex justify-between items-center px-6 py-4 bg-black/10 backdrop-blur-md border-b border-white/5">
            <div class="text-xl font-bold tracking-wider">HORIZONSHOT</div>
            <div class="hidden md:flex gap-6 text-sm font-medium uppercase tracking-widest">
                <a href="#portfolio" class="hover:text-gray-300 transition">Portfolio</a>
                <a href="#contact" class="hover:text-gray-300 transition">Contact</a>
            </div>
        </nav>

        <slot />

        <footer class="bg-black py-8 text-center text-gray-500 text-xs uppercase tracking-widest border-t border-white/10">
            <p>&copy; {{ new Date().getFullYear() }} HORIZONSHOT. All rights reserved.</p>
            <p class="mt-2">Realization: <a href="https://cerasusdigital.pl" target="_blank" class="hover:text-white transition">Cerasus Digital</a></p>
        </footer>
    </div>
</template>
