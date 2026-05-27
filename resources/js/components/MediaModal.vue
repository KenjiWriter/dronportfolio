<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { useSwipe } from '@vueuse/core';

const props = defineProps({
    show: Boolean,
    project: Object,
    media: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['close']);

const activeIndex = ref(0);
const videoRef = ref(null);
const carouselEl = ref(null);
const thumbsEl = ref(null);

const total = computed(() => props.media.length);
const current = computed(() => props.media[activeIndex.value] ?? null);
const hasPrev = computed(() => activeIndex.value > 0);
const hasNext = computed(() => activeIndex.value < total.value - 1);

// ── Swipe support ──────────────────────────────────────────────────────────
useSwipe(carouselEl, {
    onSwipeEnd(_, direction) {
        if (direction === 'left') goNext();
        if (direction === 'right') goPrev();
    },
});

// ── Navigation ─────────────────────────────────────────────────────────────
function pauseCurrentVideo() {
    if (videoRef.value) {
        videoRef.value.pause();
    }
}

function goTo(index) {
    if (index < 0 || index >= total.value) return;
    pauseCurrentVideo();
    activeIndex.value = index;
    scrollThumbIntoView(index);
}
function goPrev() { goTo(activeIndex.value - 1); }
function goNext() { goTo(activeIndex.value + 1); }

// ── Thumbnail strip auto-scroll ────────────────────────────────────────────
function scrollThumbIntoView(index) {
    nextTick(() => {
        const strip = thumbsEl.value;
        if (!strip) return;
        const thumb = strip.children[index];
        if (thumb) thumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
    });
}

// ── Keyboard ───────────────────────────────────────────────────────────────
function onKeydown(e) {
    if (!props.show) return;
    if (e.key === 'ArrowLeft')  { e.preventDefault(); goPrev(); }
    if (e.key === 'ArrowRight') { e.preventDefault(); goNext(); }
    if (e.key === 'Escape')     { close(); }
}

// ── Body scroll lock ───────────────────────────────────────────────────────
watch(() => props.show, (val) => {
    if (val) {
        activeIndex.value = 0;
        document.body.style.overflow = 'hidden';
    } else {
        pauseCurrentVideo();
        document.body.style.overflow = '';
    }
});

onMounted(() => window.addEventListener('keydown', onKeydown));
onUnmounted(() => {
    window.removeEventListener('keydown', onKeydown);
    document.body.style.overflow = '';
});

function close() {
    pauseCurrentVideo();
    emit('close');
}
</script>

<template>
    <Transition name="modal-fade">
        <div
            v-if="show"
            class="fixed inset-0 z-50 flex flex-col bg-black/95 backdrop-blur-md"
            @click.self="close"
        >
            <!-- ── Header ──────────────────────────────────────────────── -->
            <div class="flex-none w-full bg-black/80 backdrop-blur-md border-b border-gray-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-4">
                    <h2 class="text-lg md:text-xl font-bold text-white font-manrope tracking-tight truncate">
                        {{ project?.title }}
                    </h2>
                    <div class="flex items-center gap-4 shrink-0">
                        <!-- Counter -->
                        <span v-if="total > 0" class="text-gray-400 text-sm tabular-nums">
                            {{ activeIndex + 1 }} / {{ total }}
                        </span>
                        <button
                            class="text-gray-400 hover:text-white p-2 rounded-full hover:bg-white/10 transition-colors"
                            aria-label="Zamknij"
                            @click="close"
                        >
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ── Main viewer ────────────────────────────────────────── -->
            <div
                ref="carouselEl"
                class="flex-1 min-h-0 flex items-center justify-center relative select-none overflow-hidden"
            >
                <!-- Empty state -->
                <p v-if="total === 0" class="text-gray-500 italic text-center px-4">
                    Brak mediów w tej galerii.
                </p>

                <template v-else>
                    <!-- Slide -->
                    <Transition name="slide-fade" mode="out-in">
                        <div :key="activeIndex" class="flex items-center justify-center w-full h-full px-14 md:px-20">
                            <!-- Image -->
                            <img
                                v-if="current?.type === 'image'"
                                :src="current.url"
                                :alt="project?.title ? `${project.title} – zdjęcie ${activeIndex + 1}` : `Zdjęcie ${activeIndex + 1}`"
                                class="max-w-full max-h-[75vh] w-auto h-auto object-contain rounded-lg shadow-2xl"
                                loading="eager"
                                decoding="async"
                            />
                            <!-- Video -->
                            <video
                                v-else-if="current?.type === 'video'"
                                ref="videoRef"
                                controls
                                preload="metadata"
                                class="w-full max-h-[75vh] rounded-lg shadow-2xl"
                            >
                                <source :src="current.url" type="video/mp4" />
                            </video>
                        </div>
                    </Transition>

                    <!-- Prev button -->
                    <button
                        v-if="hasPrev"
                        class="absolute left-2 md:left-4 p-3 rounded-full bg-black/60 text-white hover:bg-white/20 transition-colors focus:outline-none"
                        aria-label="Poprzednie"
                        @click="goPrev"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>

                    <!-- Next button -->
                    <button
                        v-if="hasNext"
                        class="absolute right-2 md:right-4 p-3 rounded-full bg-black/60 text-white hover:bg-white/20 transition-colors focus:outline-none"
                        aria-label="Następne"
                        @click="goNext"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </template>
            </div>

            <!-- ── Thumbnail strip ────────────────────────────────────── -->
            <div v-if="total > 1" class="flex-none border-t border-gray-800 bg-black/80 py-2 px-2">
                <div
                    ref="thumbsEl"
                    class="flex gap-2 overflow-x-auto scrollbar-hide justify-start items-center pb-1"
                    style="scroll-snap-type: x mandatory;"
                >
                    <button
                        v-for="(item, i) in media"
                        :key="item.id"
                        class="shrink-0 w-14 h-14 md:w-16 md:h-16 rounded overflow-hidden border-2 transition-all duration-200 focus:outline-none"
                        :class="i === activeIndex ? 'border-white opacity-100' : 'border-transparent opacity-40 hover:opacity-70'"
                        style="scroll-snap-align: center;"
                        :aria-label="`Przejdź do ${i + 1}`"
                        @click="goTo(i)"
                    >
                        <img
                            v-if="item.type === 'image'"
                            :src="item.url"
                            :alt="`Miniatura ${i + 1}`"
                            class="w-full h-full object-cover"
                            loading="lazy"
                            decoding="async"
                        />
                        <div v-else class="w-full h-full bg-gray-800 flex items-center justify-center">
                            <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8 5v14l11-7z"/>
                            </svg>
                        </div>
                    </button>
                </div>
            </div>

            <!-- ── Description (collapsible below strip) ──────────────── -->
            <div v-if="project?.description" class="flex-none text-center px-4 py-3 border-t border-gray-800/50">
                <p class="text-sm text-gray-500 font-light leading-relaxed max-w-3xl mx-auto truncate">{{ project.description }}</p>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.font-manrope { font-family: 'Manrope', 'Inter', sans-serif; }

.modal-fade-enter-active, .modal-fade-leave-active {
    transition: opacity 0.3s ease;
}
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }

.slide-fade-enter-active, .slide-fade-leave-active {
    transition: all 0.2s ease;
}
.slide-fade-enter-from { opacity: 0; transform: scale(0.97); }
.slide-fade-leave-to   { opacity: 0; transform: scale(1.02); }

.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>

