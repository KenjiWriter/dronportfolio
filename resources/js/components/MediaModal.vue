<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    show: Boolean,
    project: Object,
});

const emit = defineEmits(['close']);

// Prevent scrolling on body when modal is open
watch(() => props.show, (val) => {
    if (val) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
});

const zoomedImage = ref(null);

const openZoom = (url) => {
    zoomedImage.value = url;
};

const closeZoom = () => {
    zoomedImage.value = null;
};

const close = () => {
    emit('close');
    document.body.style.overflow = ''; 
    zoomedImage.value = null;
};
</script>

<template>
    <transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-95 backdrop-blur-md" @click.self="close">
            
            <!-- Sticky Header -->
            <div class="sticky top-0 z-50 w-full bg-black bg-opacity-80 backdrop-blur-md border-b border-gray-800 transition-all duration-300">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
                     <h2 class="text-xl md:text-2xl font-bold text-white font-manrope tracking-tight truncate pr-4">{{ project?.title }}</h2>
                     
                     <button class="text-gray-400 hover:text-white p-2 rounded-full hover:bg-white hover:bg-opacity-10 transition-colors focus:outline-none" @click="close">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </div>

            <!-- Content Container -->
            <div class="px-4 py-8 flex flex-col items-center justify-center">
                 
                 <div class="w-full max-w-6xl mx-auto space-y-12">
                    
                    <!-- Description -->
                    <div v-if="project?.description" class="text-center max-w-3xl mx-auto">
                        <p class="text-lg text-gray-400 font-light leading-relaxed">{{ project.description }}</p>
                    </div>

                    <!-- Gallery Grid/Stack -->
                    <div v-if="project?.media && project.media.length > 0" class="space-y-24">
                         <div v-for="media in project.media" :key="media.id" class="flex justify-center w-full">
                            <div class="relative w-full rounded-lg overflow-hidden shadow-2xl bg-gray-900 border border-gray-800 transition-transform duration-500 hover:scale-[1.01]">
                                 <img 
                                    v-if="media.type === 'image'" 
                                    :src="media.url" 
                                    class="w-full h-auto object-contain max-h-[85vh] cursor-zoom-in" 
                                    @click.stop="openZoom(media.url)"
                                />
                                 <video v-else controls class="w-full h-auto max-h-[85vh]">
                                     <source :src="media.url" type="video/mp4">
                                     Your browser does not support the video tag.
                                 </video>
                             </div>
                         </div>
                    </div>
                    
                    <div v-else class="text-center text-gray-500 italic py-12">
                        Brak mediów w tej galerii.
                    </div>

                    <!-- Footer Action -->
                    <div class="text-center py-12 mt-12 border-t border-gray-800">
                        <button @click="close" class="text-gray-400 hover:text-white text-sm uppercase tracking-[0.2em] font-bold py-4 px-8 border border-gray-700 hover:border-white rounded-full transition-all duration-300">
                            Zamknij Projekt
                        </button>
                    </div>

                 </div>
            </div>

            <!-- Zoom Overlay -->
            <div v-if="zoomedImage" class="fixed inset-0 z-[999] bg-black/95 flex items-center justify-center p-4 cursor-zoom-out" @click="closeZoom">
                <img :src="zoomedImage" class="max-w-full max-h-full object-contain shadow-2xl rounded-sm" />
                <button class="fixed top-6 right-6 text-white bg-black bg-opacity-50 rounded-full p-2 hover:bg-opacity-80 z-[1000]" @click="closeZoom">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

        </div>
    </transition>
</template>

<style scoped>
.font-manrope {
    font-family: 'Manrope', 'Inter', sans-serif;
}
</style>
