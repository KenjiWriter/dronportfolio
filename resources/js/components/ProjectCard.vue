<script setup>
import { computed } from 'vue';

const props = defineProps({
    project: Object,
});

const emit = defineEmits(['click']);

// Get the first 2 gallery items for the stack effect
const galleryStack = computed(() => {
    return props.project.media ? props.project.media.slice(0, 2) : [];
});

const handleClick = () => {
    emit('click', props.project);
};
</script>

<template>
    <div 
        class="group relative cursor-pointer perspective-1000"
        @click="handleClick"
    >
        <!-- Stack Layers (Ghost Images) -->
        <!-- Stack Layers (Ghost Images) -->
        <div v-if="galleryStack.length > 0" 
             class="absolute inset-0 bg-gray-800 rounded-xl transform translate-x-2 translate-y-1 rotate-3 opacity-60 transition-all duration-500 group-hover:translate-x-4 group-hover:translate-y-2 group-hover:rotate-6 z-0 border border-gray-700 shadow-xl overflow-hidden pointer-events-none">
               <!-- First ghost -->
             <img v-if="galleryStack[0].type === 'image'" :src="galleryStack[0].url" class="w-full h-full object-cover blur-[2px] brightness-75" />
              <div v-else class="w-full h-full bg-gray-800 flex items-center justify-center"><svg class="w-8 h-8 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg></div>
        </div>
        
        <div v-if="galleryStack.length > 1" 
             class="absolute inset-0 bg-gray-800 rounded-xl transform -translate-x-1 -rotate-2 opacity-40 transition-all duration-500 group-hover:-translate-x-3 group-hover:-rotate-4 -z-10 border border-gray-600 shadow-xl overflow-hidden pointer-events-none">
             <!-- Second ghost -->
             <img v-if="galleryStack[1].type === 'image'" :src="galleryStack[1].url" class="w-full h-full object-cover blur-[3px] brightness-50" />
              <div v-else class="w-full h-full bg-gray-800 flex items-center justify-center"><svg class="w-8 h-8 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg></div>
        </div>

        <!-- Main Card -->
        <div class="relative bg-gray-900 rounded-xl overflow-hidden shadow-2xl border border-gray-800 transform transition-all duration-300 group-hover:-translate-y-1 group-hover:shadow-[0_20px_50px_rgba(0,0,0,0.5)] z-10 w-full aspect-[4/3] md:aspect-[3/2]">
            
            <img v-if="project.cover_image_path" :src="'/' + project.cover_image_path" class="w-full h-full object-cover brightness-90 group-hover:brightness-105 transition-all duration-500 transform group-hover:scale-105" alt="Cover" />
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
            
            <!-- Play Icon hint if video? Or Gallery icon? -->
            <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                <div class="bg-white bg-opacity-20 backdrop-blur-md p-2 rounded-full border border-white border-opacity-30">
                    <svg v-if="project.is_catalog" class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <svg v-else class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.perspective-1000 {
    perspective: 1000px;
}
.font-manrope {
    font-family: 'Manrope', 'Inter', sans-serif;
}
</style>
