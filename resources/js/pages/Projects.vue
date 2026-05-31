<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import ProjectCard from '@/components/ProjectCard.vue';
import MediaModal from '@/components/MediaModal.vue';
import { Head } from '@inertiajs/vue3';
import { useIntersectionObserver } from '@vueuse/core';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    projects: Object,
    types: Object,
    current_type: {
        type: String,
        default: null,
    },
});

const selectedType = ref(props.current_type ?? null);
const projectsList = ref([...(props.projects?.data ?? [])]);
const currentPage = ref(props.projects?.meta?.current_page ?? 1);
const lastPage = ref(props.projects?.meta?.last_page ?? 1);
const loadingMore = ref(false);
const showModal = ref(false);
const selectedProject = ref(null);
const selectedProjectMedia = ref([]);
const loadingProjectId = ref(null);
const sentinel = ref(null);

const typeOptions = computed(() => props.types?.data ?? []);
const hasMore = computed(() => currentPage.value < lastPage.value);

const buildApiUrl = (page = 1) => {
    const params = new URLSearchParams();
    params.set('page', String(page));

    if (selectedType.value) {
        params.set('type', selectedType.value);
    }

    return `/api/projects?${params.toString()}`;
};

const fetchProjects = async (page = 1, replace = false) => {
    if (loadingMore.value) return;

    loadingMore.value = true;

    try {
        const response = await fetch(buildApiUrl(page), {
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) throw new Error('Failed to load projects');

        const json = await response.json();
        const data = json.data ?? [];

        if (replace) {
            projectsList.value = data;
        } else {
            projectsList.value = [...projectsList.value, ...data];
        }

        currentPage.value = json.meta?.current_page ?? page;
        lastPage.value = json.meta?.last_page ?? page;
    } finally {
        loadingMore.value = false;
    }
};

const loadMore = () => {
    if (!hasMore.value || loadingMore.value) return;
    fetchProjects(currentPage.value + 1);
};

const setTypeFilter = (slug) => {
    if (selectedType.value === slug) return;
    selectedType.value = slug;
};

watch(selectedType, () => {
    fetchProjects(1, true);
});

useIntersectionObserver(
    sentinel,
    ([entry]) => {
        if (entry?.isIntersecting) {
            loadMore();
        }
    },
    {
        threshold: 0.2,
    }
);

const openProject = async (project) => {
    loadingProjectId.value = project.id;

    try {
        const res = await fetch(`/api/projects/${project.slug}/media`);
        if (!res.ok) throw new Error('fetch failed');
        const json = await res.json();
        selectedProject.value = project;
        selectedProjectMedia.value = json.data ?? json;
        showModal.value = true;
    } catch {
        selectedProject.value = project;
        selectedProjectMedia.value = [];
        showModal.value = true;
    } finally {
        loadingProjectId.value = null;
    }
};

const closeModal = () => {
    showModal.value = false;
    setTimeout(() => {
        selectedProject.value = null;
        selectedProjectMedia.value = [];
    }, 300);
};
</script>

<template>
    <Head title="Wszystkie realizacje" />

    <AppLayout>
        <section class="pt-28 pb-10 px-4 sm:px-6 lg:px-8 bg-gradient-to-b from-gray-950 via-gray-900 to-gray-900">
            <div class="max-w-7xl mx-auto text-center">
                <p class="text-sm uppercase tracking-[0.3em] text-blue-400/80 mb-3">Portfolio</p>
                <h1 class="text-4xl md:text-5xl font-black tracking-tight text-white">Wszystkie realizacje</h1>
                <p class="text-gray-400 mt-4 max-w-2xl mx-auto">Przegladaj pelne portfolio od najnowszych realizacji z mozliwoscia filtrowania po kategorii.</p>
            </div>
        </section>

        <section id="portfolio" class="pb-20 px-4 sm:px-6 lg:px-8 bg-gray-900">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-wrap gap-3 mb-10">
                    <button
                        type="button"
                        class="px-4 py-2 rounded-full text-sm font-semibold border transition-colors"
                        :class="selectedType === null ? 'bg-blue-600 border-blue-500 text-white' : 'border-gray-600 text-gray-300 hover:border-blue-400 hover:text-white'"
                        @click="setTypeFilter(null)"
                    >
                        Wszystkie
                    </button>

                    <button
                        v-for="type in typeOptions"
                        :key="type.id"
                        type="button"
                        class="px-4 py-2 rounded-full text-sm font-semibold border transition-colors"
                        :class="selectedType === type.slug ? 'bg-blue-600 border-blue-500 text-white' : 'border-gray-600 text-gray-300 hover:border-blue-400 hover:text-white'"
                        @click="setTypeFilter(type.slug)"
                    >
                        {{ type.name }}
                    </button>
                </div>

                <ul v-if="projectsList.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12 lg:gap-16 list-none p-0">
                    <li v-for="project in projectsList" :key="project.id">
                        <ProjectCard
                            :project="project"
                            :loading="loadingProjectId === project.id"
                            @click="openProject"
                        />
                    </li>
                </ul>

                <div v-else class="text-center py-16 text-gray-400">
                    Brak realizacji dla wybranego filtra.
                </div>

                <div ref="sentinel" class="h-10"></div>

                <div v-if="loadingMore" class="flex justify-center py-6">
                    <svg class="w-8 h-8 text-white animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                    </svg>
                </div>

                <p v-else-if="!hasMore && projectsList.length > 0" class="text-center text-gray-500 pt-2">
                    To wszystkie realizacje.
                </p>
            </div>
        </section>

        <MediaModal
            :show="showModal"
            :project="selectedProject"
            :media="selectedProjectMedia"
            @close="closeModal"
        />
    </AppLayout>
</template>
