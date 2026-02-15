<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    show: Boolean,
    project: Object,
});

const emit = defineEmits(['close']);
const isLoaded = ref(false);

// Prevent scrolling on body when modal is open
watch(() => props.show, (val) => {
    if (val) {
        document.body.style.overflow = 'hidden';
        setTimeout(() => isLoaded.value = true, 50);
    } else {
        document.body.style.overflow = '';
        isLoaded.value = false;
    }
});

const close = () => {
    emit('close');
    document.body.style.overflow = ''; 
    isLoaded.value = false;
};
</script>

<template>
    <transition name="modal-fade">
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
            <div class="px-4 py-8 flex flex-col items-center justify-center pointer-events-none">
                 
                 <div class="w-full max-w-6xl mx-auto space-y-12 pointer-events-auto">
                    
                    <!-- Description -->
                    <div v-if="project?.description" class="text-center max-w-3xl mx-auto">
                        <p class="text-lg text-gray-400 font-light leading-relaxed">{{ project.description }}</p>
                    </div>

                    <!-- Gallery Grid/Stack -->
                    <div v-if="project?.media && project.media.length > 0" class="space-y-24">
                         <div v-for="(media, index) in project.media" :key="media.id" class="flex justify-center w-full">
                            <div class="relative w-full rounded-lg overflow-hidden shadow-2xl bg-gray-900 border border-gray-800 transition-transform duration-500 hover:scale-[1.01]">
                                 <img 
                                    v-if="media.type === 'image'" 
                                    :src="media.url" 
                                    :class="['transition-all duration-700 ease-out transform w-full h-auto object-contain max-h-[85vh]', isLoaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-12']"
                                    :style="{ transitionDelay: `${index * 100}ms` }"
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

        </div>
    </transition>
</template>

<style scoped>
.font-manrope {
    font-family: 'Manrope', 'Inter', sans-serif;
}

.modal-fade-enter-active, .modal-fade-leave-active {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.modal-fade-enter-from, .modal-fade-leave-to {
    opacity: 0;
    backdrop-filter: blur(0px);
}
</style>
