<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    projects: Object,
});
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
                    <div v-for="project in projects.data" :key="project.id" class="bg-gray-800 rounded-lg overflow-hidden shadow-lg border border-gray-700 hover:border-gray-500 transition-colors">
                        <div class="relative h-48 w-full bg-gray-900">
                             <img v-if="project.cover_image_path" :src="'/' + project.cover_image_path" alt="Cover" class="w-full h-full object-cover" />
                             <div v-else class="w-full h-full flex flex-col items-center justify-center text-gray-500">
                                 <span>No Cover</span>
                                 <span class="text-xs text-red-500">{{ project.cover_image_path || 'NULL' }}</span>
                             </div>
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
                                <!-- Basic delete button if needed, but not specified in detail. Sticking to Edit as primary action. -->
                            </div>
                        </div>
                    </div>
                </div>
                
                <div v-if="projects.total === 0" class="text-center py-12 text-gray-400">
                    Brak projektów. Dodaj pierwszy!
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
