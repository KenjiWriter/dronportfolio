<script setup>
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import ProjectCard from '@/components/ProjectCard.vue';
import MediaModal from '@/components/MediaModal.vue';
import ApplicationLogo from '@/components/AppLogo.vue'; 
import HeroLens from '@/components/HeroLens.vue';
import FloatingDrone from '@/components/FloatingDrone.vue';

const props = defineProps({
    projects: Object, // Expecting a Resource collection or paginator
});

const videoLoaded = ref(false);
const showModal = ref(false);
const selectedProject = ref(null);

const handleVideoLoaded = () => {
    videoLoaded.value = true;
};

const openProject = (project) => {
    if (project.is_catalog || (project.media && project.media.length > 0)) {
        selectedProject.value = project;
        showModal.value = true;
    } else {
        // Fallback for single item viewing if we wanted to redirect
        // window.location.href = route('project.show', project.slug);
        // For now, modal is preferred for all as per instruction "Single Item: Opens media directly in lightbox" (Modal is a lightbox)
        selectedProject.value = project;
        showModal.value = true;
    }
};

const closeModal = () => {
    showModal.value = false;
    setTimeout(() => {
        selectedProject.value = null;
    }, 300); // Clear after transition
};

const scrollToSection = (sectionId) => {
    const element = document.getElementById(sectionId);
    if (element) {
        element.scrollIntoView({ 
            behavior: 'smooth',
            block: 'start' 
        });
    }
};
</script>

<template>
    <Head title="Aerial Cinematography" />

    <div class="bg-gray-900 text-white font-sans antialiased selection:bg-blue-500 selection:text-white">
        
        <!-- Hero Section -->
        <div class="relative h-screen w-full overflow-hidden bg-black">
            <video 
                class="absolute top-0 left-0 w-full h-full object-cover z-0 transition-opacity duration-1000 ease-in-out"
                :class="{ 'opacity-60': videoLoaded, 'opacity-0': !videoLoaded }"
                autoplay 
                loop 
                muted 
                playsinline
                @loadeddata="handleVideoLoaded"
            >
                <source src="/images/panoramic.mp4" type="video/mp4">
            </video>

            <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-transparent to-gray-900 z-10"></div>

            <div class="relative z-20 flex flex-col items-center justify-center h-full text-center px-4">
                
                <div class="animate-float-slow relative z-10 flex flex-col items-center text-center font-black tracking-tighter select-none">
    
                    <h1 class="text-[5rem] md:text-[10rem] leading-none" 
                        style="color: rgba(255, 255, 255, 0.05); -webkit-text-stroke: 3px rgba(255, 255, 255, 0.9); text-shadow: 0 0 25px rgba(255, 255, 255, 0.2);">
                        HORIZON
                    </h1>
                    
                    <div class="flex items-center justify-center text-[5rem] md:text-[10rem] leading-none md:-mt-6">
                        <span style="color: rgba(255, 255, 255, 0.05); -webkit-text-stroke: 3px rgba(255, 255, 255, 0.9); text-shadow: 0 0 25px rgba(255, 255, 255, 0.2);">
                            SH
                        </span>
                        
                        <span id="hero-lens-origin" class="inline-block mx-1 md:mx-3">
                            <HeroLens class="w-[0.8em] h-[0.8em]" />
                        </span>
                        
                        <span style="color: rgba(255, 255, 255, 0.05); -webkit-text-stroke: 3px rgba(255, 255, 255, 0.9); text-shadow: 0 0 25px rgba(255, 255, 255, 0.2);">
                            T
                        </span>
                    </div>

                </div>

                <p class="mt-12 text-lg md:text-xl text-gray-300 font-light max-w-2xl mx-auto mb-10 opacity-0 animate-fade-in-up animation-delay-300 leading-relaxed drop-shadow-md">
                    Profesjonalne usługi dronowe, fotografia produktowa i montaż wideo.
                    <br class="hidden md:block" />
                    Tworzymy perspektywę, której potrzebujesz.
                </p>
                
                <div class="flex flex-col sm:flex-row gap-6 opacity-0 animate-fade-in-up animation-delay-600">
                    <a href="#portfolio" @click.prevent="scrollToSection('portfolio')" class="group relative px-8 py-4 bg-white text-black font-bold rounded-full overflow-hidden transition-all duration-300 hover:scale-105 shadow-[0_0_20px_rgba(255,255,255,0.3)]">
                        <span class="relative z-10 uppercase tracking-widest text-sm">Zobacz Portfolio</span>
                        <div class="absolute inset-0 bg-gray-200 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left duration-300 ease-out"></div>
                    </a>
                    <a href="#contact" @click.prevent="scrollToSection('contact')" class="px-8 py-4 border border-white/30 backdrop-blur-sm text-white font-bold rounded-full hover:bg-white/10 hover:border-white transition-all duration-300 uppercase tracking-widest text-sm shadow-lg">
                        Skontaktuj się
                    </a>
                </div>
            </div>

            <!-- Scroll Indicator -->
            <div class="absolute bottom-10 left-1/2 transform -translate-x-1/2 z-20 animate-bounce opacity-70">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
            </div>
        </div>

        <!-- Portfolio Section -->
        <div id="portfolio" class="relative py-24 px-4 sm:px-6 lg:px-8 bg-gray-900">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-20">
                    <h2 class="text-3xl md:text-5xl font-bold text-white mb-4 tracking-tight font-manrope">Wybrane Realizacje</h2>
                    <div class="w-24 h-1 bg-blue-600 mx-auto rounded-full"></div>
                </div>

                <div v-if="projects && projects.data && projects.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12 lg:gap-16">
                    <ProjectCard 
                        v-for="project in projects.data" 
                        :key="project.id" 
                        :project="project" 
                        @click="openProject"
                    />
                </div>
                
                <div v-else class="text-center py-20">
                    <p class="text-gray-500 text-xl font-light">Realizacje pojawią się wkrótce.</p>
                </div>
            </div>
        </div>

        <!-- Contact Section -->
        <div id="contact" class="relative py-24 bg-gray-800 border-t border-gray-700">
             <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-16">
                    <!-- Info -->
                    <div>
                        <h2 class="text-3xl md:text-4xl font-bold text-white mb-8 font-manrope">Rozpocznijmy Współpracę</h2>
                        <p class="text-gray-400 mb-8 text-lg leading-relaxed">
                            Masz pomysł na ujęcie z powietrza? Potrzebujesz profesjonalnego wideo promocyjnego?
                            Skontaktuj się ze mną. Konsultacje są zawsze darmowe.
                        </p>
                        
                        <div class="space-y-6">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-white">Lokalizacja</h3>
                                    <p class="mt-1 text-gray-400">Warszawa, Polska</p>
                                    <p class="mt-1 text-sm text-blue-400">Koszty dojazdu w cenie na terenie woj. Mazowieckiego.</p>
                                </div>
                            </div>
                            
                            <!-- Phone/Email placeholders or real data if available -->
                        </div>
                    </div>

                    <!-- Form Trigger (Placeholder / To Implement logic later if separate component desired) -->
                    <div class="bg-gray-900 p-8 rounded-2xl shadow-xl border border-gray-700">
                        <form @submit.prevent="console.log('Form submission to be implemented via LeadController')" class="space-y-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-400">Imię i Nazwisko</label>
                                <input type="text" id="name" class="mt-1 block w-full bg-gray-800 border-gray-600 rounded-md text-white focus:ring-blue-500 focus:border-blue-500" placeholder="Jan Kowalski" />
                            </div>
                            
                             <div>
                                <label for="contact" class="block text-sm font-medium text-gray-400">Telefon / Email</label>
                                <input type="text" id="contact" class="mt-1 block w-full bg-gray-800 border-gray-600 rounded-md text-white focus:ring-blue-500 focus:border-blue-500" placeholder="+48 123 456 789" />
                            </div>

                            <div>
                                <label for="message" class="block text-sm font-medium text-gray-400">Wiadomość</label>
                                <textarea id="message" rows="4" class="mt-1 block w-full bg-gray-800 border-gray-600 rounded-md text-white focus:ring-blue-500 focus:border-blue-500" placeholder="Opisz swój projekt..."></textarea>
                            </div>

                            <button type="button" class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-md hover:bg-blue-700 transition duration-300">
                                Wyślij Wiadomość
                            </button>
                        </form>
                    </div>
                </div>
             </div>
        </div>

        <!-- Footer -->
        <footer class="bg-black py-12 border-t border-gray-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="text-gray-400 text-sm">
                    &copy; {{ new Date().getFullYear() }} HorizonShot. Wszelkie prawa zastrzeżone.
                </div>
                <div class="text-gray-500 text-sm">
                    <!-- Mandatory Credit -->
                    Realizacja: <a href="https://cerasusdigital.pl" target="_blank" class="text-gray-400 hover:text-white transition-colors">Cerasus Digital</a>
                </div>
            </div>
        </footer>

        <!-- Modals -->
        <MediaModal 
            :show="showModal" 
            :project="selectedProject" 
            @close="closeModal" 
        />
        
        <!-- Easter Egg -->
        <FloatingDrone />
    </div>
</template>

<style>
/* Custom Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translate3d(0, 40px, 0);
    }
    to {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.8s ease-out forwards;
}

.animation-delay-300 {
    animation-delay: 300ms;
}

.animation-delay-600 {
    animation-delay: 600ms;
}

.font-manrope {
    font-family: 'Manrope', 'Inter', sans-serif;
}
</style>
