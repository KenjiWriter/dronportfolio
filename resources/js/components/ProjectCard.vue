<script setup>
import { computed } from 'vue';

const props = defineProps({
    project: Object,
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['click']);

// preview_media contains max 2 items for the stack ghost layers
const galleryStack = computed(() => {
    return props.project.preview_media ?? [];
});

// Use the lightweight thumbnail for the grid; fall back to the full cover image
const coverSrc = computed(() => {
    if (props.project.cover_thumbnail_path) {
        return '/' + props.project.cover_thumbnail_path;
    }
    return '/' + props.project.cover_image_path;
});

const handleClick = () => {
    if (props.loading) return;
    emit('click', props.project);
};
</script>

<template>
    <article
        class="group relative cursor-pointer perspective-1000"
        :aria-label="project.title ? `Projekt: ${project.title}` : 'Projekt'"
        :aria-busy="loading"
        role="button"
        tabindex="0"
        @click="handleClick"
        @keydown.enter="handleClick"
        @keydown.space.prevent="handleClick"
    >
        <!-- Stack Layers (Ghost Images) -->
        <template v-if="galleryStack.length > 0">
            <div v-for="(media, index) in galleryStack" :key="media.id"
                 :class="[
                    'absolute inset-0 bg-gray-800 rounded-xl transition-all duration-500 ease-out border border-gray-700 shadow-xl overflow-hidden pointer-events-none',
                    index === 0 ? 'transform rotate-3 translate-x-3 translate-y-3 opacity-70 group-hover:rotate-6 group-hover:translate-x-12 group-hover:-translate-y-2 group-hover:opacity-100 z-0' : '',
                    index === 1 ? 'transform -rotate-3 -translate-x-3 translate-y-4 opacity-50 group-hover:-rotate-8 group-hover:-translate-x-12 group-hover:-translate-y-1 group-hover:opacity-100 -z-10 border-gray-600' : ''
                 ]">
                 <img
                    v-if="media.type === 'image'"
                    :src="media.url"
                    :alt="project.title ? `${project.title} – podgląd galerii` : 'Podgląd galerii zdjęć dronowych'"
                    class="w-full h-full object-cover blur-[1px] brightness-75"
                    loading="lazy"
                    decoding="async"
                    aria-hidden="true"
                 />
                 <div v-else class="w-full h-full bg-gray-800 flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                 </div>
            </div>
        </template>

        <!-- Main Card -->
        <div class="relative bg-gray-900 rounded-xl overflow-hidden shadow-2xl border border-gray-800 transform transition-all duration-500 ease-out group-hover:-translate-y-1 group-hover:shadow-[0_20px_50px_rgba(0,0,0,0.5)] z-10 w-full aspect-[4/3] md:aspect-[3/2]">
            
            <img
                v-if="coverSrc"
                :src="coverSrc"
                :alt="project.title ? `${project.title} – okładka projektu` : 'Zdjęcie z drona – okładka projektu'"
                class="w-full h-full object-cover brightness-90 group-hover:brightness-105 transition-all duration-500 ease-out transform group-hover:scale-[1.02]"
                loading="lazy"
                decoding="async"
                width="800"
                height="600"
            />
            <div v-else class="w-full h-full flex items-center justify-center bg-gray-800 text-gray-500 font-mono text-xs">NO COVER</div>
            
            <!-- Overlay Content -->
            <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-transparent to-transparent opacity-80 group-hover:opacity-60 transition-opacity"></div>
            
            <div class="absolute bottom-0 left-0 right-0 p-6 transform translate-y-2 group-hover:translate-y-0 transition-transform duration-300">
                <span v-if="project.is_catalog" class="inline-block px-2 py-1 mb-2 text-xs font-bold tracking-widest text-white uppercase bg-blue-600 rounded bg-opacity-80 backdrop-blur-sm">
                    Katalog
                </span>
                <h3 class="text-xl md:text-2xl font-bold text-white font-manrope tracking-tight leading-none drop-shadow-md">
                    {{ project.title }}
                </h3>
            </div>

            <!-- Loading overlay shown while media is being fetched -->
            <Transition name="fade">
                <div v-if="loading" class="absolute inset-0 z-20 flex items-center justify-center bg-black/60 backdrop-blur-sm">
                    <svg class="w-10 h-10 text-white animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                    </svg>
                </div>
            </Transition>
        </div>
    </article>
</template>

<style scoped>
.perspective-1000 {
    perspective: 1000px;
}
.font-manrope {
    font-family: 'Manrope', 'Inter', sans-serif;
}
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
