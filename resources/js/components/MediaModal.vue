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

const close = () => {
    emit('close');
    document.body.style.overflow = ''; 
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
            
            <!-- Close Button -->
            <button class="fixed top-6 right-6 text-gray-400 hover:text-white z-50 p-2 bg-black bg-opacity-50 rounded-full transition-colors" @click="close">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>

            <!-- Content Container -->
            <div class="min-h-screen px-4 py-12 flex flex-col items-center justify-center">
                 
                 <div class="w-full max-w-6xl mx-auto space-y-12">
                    
                    <!-- Header -->
                    <div class="text-center max-w-3xl mx-auto mb-16">
                        <h2 class="text-4xl md:text-6xl font-bold text-white font-manrope mb-6 tracking-tighter">{{ project?.title }}</h2>
                        <p v-if="project?.description" class="text-lg text-gray-400 font-light leading-relaxed">{{ project.description }}</p>
                    </div>

                    <!-- Gallery Grid/Stack -->
                    <div v-if="project?.media && project.media.length > 0" class="space-y-24">
                         <div v-for="media in project.media" :key="media.id" class="flex justify-center w-full">
                             <div class="relative w-full rounded-lg overflow-hidden shadow-2xl bg-gray-900 border border-gray-800 transition-transform duration-500 hover:scale-[1.01]">
                                 <img v-if="media.file_type === 'image'" :src="'/' + media.file_path" class="w-full h-auto object-contain max-h-[85vh]" />
                                 <video v-else controls class="w-full h-auto max-h-[85vh]">
                                     <source :src="'/' + media.file_path" type="video/mp4">
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
</style>
