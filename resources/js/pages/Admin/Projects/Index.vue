<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { route } from 'ziggy-js';

const props = defineProps({
    projects: Object,
});

const projectsList = ref([...(props.projects?.data ?? [])]);
const togglingIds = ref(new Set());

const confirmDelete = (project) => {
    if (confirm(`Czy na pewno chcesz usunąć projekt "${project.title}"? Tej operacji nie można cofnąć.`)) {
        router.delete(route('admin.projects.destroy', project.id));
    }
};

const isToggling = (projectId) => togglingIds.value.has(projectId);

const setToggling = (projectId, active) => {
    const next = new Set(togglingIds.value);
    if (active) {
        next.add(projectId);
    } else {
        next.delete(projectId);
    }
    togglingIds.value = next;
};

const toggleFeatured = (project) => {
    if (isToggling(project.id)) return;

    const previous = project.is_featured;
    project.is_featured = !project.is_featured;
    setToggling(project.id, true);

    router.patch(route('admin.projects.toggle-featured', project.id), {}, {
        preserveScroll: true,
        preserveState: true,
        onError: () => {
            project.is_featured = previous;
        },
        onFinish: () => {
            setToggling(project.id, false);
        },
    });
};
</script>

<template>
    <Head title="Realizacje" />

    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Zarządzanie Realizacjami
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-white">Lista Projektów</h1>
                    <Link :href="route('admin.projects.create')" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition">
                        Dodaj Projekt
                    </Link>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="project in projectsList" :key="project.id" class="bg-gray-800 rounded-lg overflow-hidden shadow-lg border border-gray-700 hover:border-gray-500 transition-colors">
                        <div class="relative h-48 w-full bg-gray-900">
                             <img v-if="project.cover_image_url || project.cover_image_path" :src="project.cover_image_url || ('/' + project.cover_image_path)" alt="Cover" class="w-full h-full object-cover" />
                             <div v-else class="w-full h-full flex flex-col items-center justify-center text-gray-500">
                                 <span>No Cover</span>
                                 <span class="text-xs text-red-500">{{ project.cover_image_path || 'NULL' }}</span>
                             </div>
                             <button
                                 type="button"
                                 class="absolute top-2 left-2 z-10 rounded-full p-1.5 border transition-colors"
                                 :class="project.is_featured ? 'bg-yellow-500 border-yellow-400 text-gray-900' : 'bg-gray-800/80 border-gray-600 text-gray-200 hover:text-yellow-400 hover:border-yellow-400'"
                                 :disabled="isToggling(project.id)"
                                 @click="toggleFeatured(project)"
                                 title="Przełącz wyróżnienie"
                             >
                                 <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                     <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l2.041 6.286a1 1 0 00.95.69h6.608c.969 0 1.371 1.24.588 1.81l-5.346 3.885a1 1 0 00-.364 1.118l2.042 6.287c.3.922-.755 1.688-1.539 1.118l-5.346-3.884a1 1 0 00-1.176 0l-5.346 3.884c-.783.57-1.838-.196-1.539-1.118l2.042-6.287a1 1 0 00-.364-1.118L.862 11.713c-.783-.57-.38-1.81.588-1.81h6.608a1 1 0 00.95-.69l2.041-6.286z"/>
                                 </svg>
                             </button>
                             <div v-if="project.is_catalog" class="absolute top-2 right-2 bg-purple-600 text-white text-xs px-2 py-1 rounded uppercase font-bold tracking-wider">
                                 Katalog
                             </div>
                        </div>
                        
                        <div class="p-4">
                            <h3 class="text-lg font-bold text-white mb-2 truncate" :title="project.title">{{ project.title }}</h3>
                            <p class="text-sm text-gray-400 mb-4 line-clamp-2 h-10">{{ project.description }}</p>
                            
                            <div class="flex justify-between items-center mt-4">
                                <Link :href="route('admin.projects.edit', project.id)" class="text-blue-400 hover:text-blue-300 font-medium text-sm">
                                    Edytuj
                                </Link>
                                <button @click="confirmDelete(project)" class="text-red-400 hover:text-red-300 font-medium text-sm focus:outline-none">
                                    Usuń
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div v-if="projectsList.length === 0" class="text-center py-12 text-gray-400">
                    Brak projektów. Dodaj pierwszy!
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
