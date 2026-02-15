<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { route } from 'ziggy-js';
import ProjectCard from '@/components/ProjectCard.vue';
import MediaModal from '@/components/MediaModal.vue';
import ApplicationLogo from '@/components/AppLogo.vue'; 
import HeroLens from '@/components/HeroLens.vue';
import FloatingDrone from '@/components/FloatingDrone.vue';

const props = defineProps({
    projects: Object,
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success);

const videoLoaded = ref(false);
const showModal = ref(false);
const selectedProject = ref(null);

// Contact form
const form = useForm({
    name: '',
    email: '',
    phone: '',
    message: '',
});

const submitContact = () => {
    form.post(route('contact.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

const handleVideoLoaded = () => {
    videoLoaded.value = true;
};

const openProject = (project) => {
    if (project.is_catalog || (project.media && project.media.length > 0)) {
        selectedProject.value = project;
        showModal.value = true;
    } else {
        selectedProject.value = project;
        showModal.value = true;
    }
};

const closeModal = () => {
    showModal.value = false;
    setTimeout(() => {
        selectedProject.value = null;
    }, 300);
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
        <section id="contact" class="relative py-24 px-6 z-20 bg-gray-900">
            <div class="max-w-3xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-5xl font-bold text-white mb-4 tracking-tight font-manrope">Napisz do nas</h2>
                    <div class="w-24 h-1 bg-blue-600 mx-auto rounded-full mb-6"></div>
                    <p class="text-gray-400 text-lg max-w-xl mx-auto">
                        Darmowe konsultacje. Koszty dojazdu w cenie na terenie woj. mazowieckiego.
                    </p>
                </div>

                <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-8 md:p-12 shadow-2xl">
                    
                    <!-- Flash Success -->
                    <Transition
                        enter-active-class="transition duration-300 ease-out"
                        enter-from-class="opacity-0 -translate-y-2"
                        enter-to-class="opacity-100 translate-y-0"
                        leave-active-class="transition duration-200 ease-in"
                        leave-from-class="opacity-100 translate-y-0"
                        leave-to-class="opacity-0 -translate-y-2"
                    >
                        <div v-if="flashSuccess" class="mb-8 p-4 bg-green-500/20 border border-green-500/50 rounded-xl text-green-200 text-center text-sm font-medium">
                            {{ flashSuccess }}
                        </div>
                    </Transition>

                    <form @submit.prevent="submitContact" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-white/70 text-sm font-bold mb-2 tracking-wide">Imię i Nazwisko</label>
                                <input 
                                    v-model="form.name" 
                                    type="text" 
                                    class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3.5 text-white placeholder-white/30 focus:outline-none focus:border-blue-500/60 focus:ring-1 focus:ring-blue-500/30 transition-all duration-300"
                                    placeholder="Jan Kowalski"
                                >
                                <div v-if="form.errors.name" class="text-red-400 text-xs mt-1.5 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                                    {{ form.errors.name }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-white/70 text-sm font-bold mb-2 tracking-wide">E-mail</label>
                                <input 
                                    v-model="form.email" 
                                    type="email" 
                                    class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3.5 text-white placeholder-white/30 focus:outline-none focus:border-blue-500/60 focus:ring-1 focus:ring-blue-500/30 transition-all duration-300"
                                    placeholder="jan@firma.pl"
                                >
                                <div v-if="form.errors.email" class="text-red-400 text-xs mt-1.5 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                                    {{ form.errors.email }}
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-white/70 text-sm font-bold mb-2 tracking-wide">Telefon <span class="text-white/30 font-normal">(opcjonalnie)</span></label>
                            <input 
                                v-model="form.phone" 
                                type="text" 
                                class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3.5 text-white placeholder-white/30 focus:outline-none focus:border-blue-500/60 focus:ring-1 focus:ring-blue-500/30 transition-all duration-300"
                                placeholder="+48 123 456 789"
                            >
                        </div>
                        <div>
                            <label class="block text-white/70 text-sm font-bold mb-2 tracking-wide">Wiadomość</label>
                            <textarea 
                                v-model="form.message" 
                                rows="5" 
                                class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3.5 text-white placeholder-white/30 focus:outline-none focus:border-blue-500/60 focus:ring-1 focus:ring-blue-500/30 transition-all duration-300 resize-none"
                                placeholder="Opisz swój projekt..."
                            ></textarea>
                            <div v-if="form.errors.message" class="text-red-400 text-xs mt-1.5 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                                {{ form.errors.message }}
                            </div>
                        </div>
                        <button 
                            type="submit" 
                            :disabled="form.processing" 
                            class="group relative w-full px-8 py-4 rounded-xl bg-white text-black font-bold uppercase tracking-widest text-sm overflow-hidden transition-all duration-300 hover:shadow-[0_0_30px_rgba(255,255,255,0.2)] disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span class="relative z-10">{{ form.processing ? 'Wysyłanie...' : 'Wyślij Wiadomość' }}</span>
                            <div class="absolute inset-0 bg-gray-200 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left duration-300 ease-out"></div>
                        </button>
                    </form>
                </div>
            </div>
        </section>

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
