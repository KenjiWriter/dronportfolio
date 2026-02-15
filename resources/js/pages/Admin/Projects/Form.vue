<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { route } from 'ziggy-js';

const props = defineProps({
    project: Object, // Optional, for Edit mode
});

const deleteMedia = (media) => {
    if (confirm('Czy na pewno chcesz usunąć ten plik?')) {
        router.delete(route('project-media.destroy', media.id));
    }
};

const isEdit = !!props.project;

const form = useForm({
    _method: isEdit ? 'PUT' : 'POST', // Spoofing for file uploads in Laravel if needed, though Inertia handles post as put fine usually, but for files sometimes POST is better + _method: PUT.
    title: props.project?.data.title ?? '',
    description: props.project?.data.description ?? '',
    is_catalog: props.project?.data.is_catalog ?? false,
    cover_image: null,
    gallery_files: [],
});

// For file input clearing/preview logic if needed
const coverInput = ref(null);
const galleryInput = ref(null);

const submit = () => {
    if (isEdit) {
        // Inertia file upload with PUT requires special handling usually (using POST with _method field)
        form.post(route('admin.projects.update', props.project.data.id), {
            forceFormData: true,
        });
    } else {
        form.post(route('admin.projects.store'));
    }
};
</script>

<template>
    <Head :title="isEdit ? 'Edytuj Projekt' : 'Nowy Projekt'" />

    <AdminLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-white">{{ isEdit ? 'Edycja Projektu' : 'Nowy Projekt' }}</h1>
                </div>

                <div class="bg-gray-800 rounded-lg shadow border border-gray-700 p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <!-- Title -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-300">Tytuł Projektu</label>
                            <input id="title" v-model="form.title" type="text" class="mt-1 block w-full bg-gray-900 border-gray-700 text-white rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required autofocus />
                            <div v-if="form.errors.title" class="text-red-400 text-sm mt-1">{{ form.errors.title }}</div>
                        </div>

                         <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-300">Opis (opcjonalnie)</label>
                            <textarea id="description" v-model="form.description" rows="3" class="mt-1 block w-full bg-gray-900 border-gray-700 text-white rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"></textarea>
                            <div v-if="form.errors.description" class="text-red-400 text-sm mt-1">{{ form.errors.description }}</div>
                        </div>

                        <!-- Is Catalog Checkbox -->
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="is_catalog" v-model="form.is_catalog" type="checkbox" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-700 bg-gray-900 rounded" />
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="is_catalog" class="font-medium text-gray-300">Projekt Katalogowy</label>
                                <p class="text-gray-500">Zaznacz, jeśli projekt zawiera wiele zdjęć/filmów i powinien otwierać się jako galeria.</p>
                            </div>
                        </div>

                        <!-- Cover Image -->
                        <div>
                             <label class="block text-sm font-medium text-gray-300">Okładka (Główna miniaturka)</label>
                             <div class="mt-1 flex items-center gap-4">
                                 <div v-if="props.project?.data.cover_image_path" class="w-32 h-20 bg-gray-700 rounded overflow-hidden">
                                     <img :src="'/' + props.project.data.cover_image_path" class="w-full h-full object-cover opacity-75" />
                                 </div>
                                 <input type="file" @change="form.cover_image = $event.target.files[0]" ref="coverInput" class="block w-full text-sm text-gray-400
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-full file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-blue-600 file:text-white
                                    file:cursor-pointer hover:file:bg-blue-700
                                  " accept="image/*" />
                             </div>
                             <div v-if="form.errors.cover_image" class="text-red-400 text-sm mt-1">{{ form.errors.cover_image }}</div>
                        </div>

                        <!-- Gallery Files -->
                        <div>
                             <label class="block text-sm font-medium text-gray-300">Galeria (Zdjęcia/Wideo)</label>
                             <p class="text-xs text-gray-500 mb-2">Możesz wybrać wiele plików naraz.</p>
                             <input type="file" @change="form.gallery_files = $event.target.files" multiple ref="galleryInput" class="block w-full text-sm text-gray-400
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-full file:border-0
                                file:text-sm file:font-semibold
                                file:bg-gray-600 file:text-white
                                file:cursor-pointer hover:file:bg-gray-700
                              " accept="image/*,video/*" />
                              <div v-if="form.errors['gallery_files']" class="text-red-400 text-sm mt-1">{{ form.errors['gallery_files'] }}</div>
                              <!-- Individual file errors if any -->
                              <div v-for="(error, key) in form.errors" :key="key">
                                  <div v-if="key.startsWith('gallery_files.')" class="text-red-400 text-sm mt-1">{{ error }}</div>
                              </div>
                        </div>

                        <!-- Current Gallery Preview (Edit Mode) -->
                        <!-- Current Gallery Preview (Edit Mode) -->
                        <div v-if="isEdit && props.project.data.media && props.project.data.media.length > 0" class="mt-4">
                             <h3 class="text-sm font-medium text-gray-300 mb-2">Obecna Galeria ({{ props.project.data.media.length }} plików)</h3>
                             <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                 <div v-for="media in props.project.data.media" :key="media.id" class="relative group aspect-square bg-black rounded overflow-hidden border border-gray-700">
                                     <img v-if="media.type === 'image'" :src="media.url" class="w-full h-full object-cover" />
                                     <video v-else :src="media.url" class="w-full h-full object-cover"></video>
                                     
                                     <!-- Delete Overlay -->
                                     <button @click.prevent="deleteMedia(media)" class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity hover:bg-opacity-75 text-white">
                                         <span class="bg-red-600 p-2 rounded-full">
                                             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                         </span>
                                     </button>
                                 </div>
                             </div>
                        </div>

                         <!-- Actions -->
                        <div class="flex items-center justify-end gap-4 border-t border-gray-700 pt-6">
                            <Link :href="route('admin.projects.index')" class="text-gray-400 hover:text-white text-sm font-medium">Anuluj</Link>
                            
                            <button type="submit" :disabled="form.processing" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded focus:outline-none focus:shadow-outline transition disabled:opacity-50 disabled:cursor-not-allowed">
                                {{ form.processing ? 'Zapisywanie...' : (isEdit ? 'Zapisz Zmiany' : 'Utwórz Projekt') }}
                            </button>
                        </div>
                         
                         <!-- Progress Bar -->
                        <div v-if="form.progress" class="w-full bg-gray-700 rounded-full h-2.5 mt-4">
                            <div class="bg-blue-600 h-2.5 rounded-full" :style="{ width: form.progress.percentage + '%' }"></div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
