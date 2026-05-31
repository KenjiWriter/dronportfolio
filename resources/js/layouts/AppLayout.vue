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
        <header role="banner">
            <nav
                aria-label="Nawigacja główna"
                class="fixed top-0 left-0 right-0 z-50 flex justify-between items-center px-6 py-4 bg-black/10 backdrop-blur-md border-b border-white/5"
            >
                <a href="/" class="text-xl font-bold tracking-wider" aria-label="HorizonShot – strona główna">HORIZONSHOT</a>
                <ul class="hidden md:flex gap-6 text-sm font-medium uppercase tracking-widest list-none p-0 m-0">
                    <li><a href="/" class="hover:text-gray-300 transition">Start</a></li>
                    <li><a href="/#contact" class="hover:text-gray-300 transition">Kontakt</a></li>
                </ul>
            </nav>
        </header>

        <main>
            <slot />
        </main>

        <footer class="bg-black py-8 text-center text-gray-500 text-xs uppercase tracking-widest border-t border-white/10">
            <p>&copy; {{ new Date().getFullYear() }} HORIZONSHOT. All rights reserved.</p>
            <!-- Realization: Cerasus Digital | https://cerasusdigital.pl -->
            <p class="mt-2">Realization: <a href="https://cerasusdigital.pl" target="_blank" rel="noopener noreferrer" class="hover:text-white transition">Cerasus Digital</a></p>
        </footer>
    </div>
</template>
