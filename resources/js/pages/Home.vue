<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

// Placeholder video for development
const videoUrl = "https://assets.mixkit.co/videos/preview/mixkit-flying-over-a-forest-covered-in-snow-1262-large.mp4"; 
</script>

<template>
    <Head title="Aerial Cinematography" />

    <AppLayout>
        <div class="relative h-screen w-full overflow-hidden">
            <video 
                class="absolute top-0 left-0 w-full h-full object-cover z-0"
                autoplay 
                loop 
                muted 
                playsinline
            >
                <source :src="videoUrl" type="video/mp4">
            </video>

            <div class="absolute inset-0 bg-black/40 z-10"></div>

            <div class="relative z-20 flex flex-col items-center justify-center h-full text-center text-white px-4">
                <h1 class="text-5xl md:text-7xl font-bold tracking-tight mb-4 animate-fade-in-up">
                    Łukasz Hil
                </h1>
                <p class="text-xl md:text-2xl font-light text-gray-200 mb-8 max-w-2xl animate-fade-in-up delay-100">
                    Aerial Cinematography & Visuals
                </p>
                
                <div class="flex gap-4 animate-fade-in-up delay-200">
                    <a href="#portfolio" class="px-8 py-3 bg-white text-black font-medium rounded-full hover:bg-gray-200 transition duration-300">
                        Realizacje
                    </a>
                    <a href="#contact" class="px-8 py-3 border border-white text-white font-medium rounded-full hover:bg-white/10 transition duration-300">
                        Kontakt
                    </a>
                </div>
            </div>
        </div>

        <div id="portfolio" class="min-h-screen bg-neutral-900 text-white p-20 text-center">
            <h2 class="text-3xl font-light mb-10">Portfolio Loading...</h2>
        </div>
    </AppLayout>
</template>
