<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { computed, ref, onUnmounted } from 'vue';
import { route } from 'ziggy-js';

const props = defineProps({
    project: Object, // Optional, for Edit mode
    types: Object,
});

const deleteMedia = (media) => {
    if (confirm('Czy na pewno chcesz usunąć ten plik?')) {
        router.delete(route('admin.project-media.destroy', media.id));
    }
};

const isEdit = !!props.project;

const form = useForm({
    _method: isEdit ? 'PUT' : 'POST',
    title: props.project?.data.title ?? '',
    description: props.project?.data.description ?? '',
    is_catalog: props.project?.data.is_catalog ?? false,
    project_type_id: props.project?.data.project_type_id ?? '',
    cover_image: null,
    gallery_files: [],
    video_qualities: [],  // parallel array – quality per gallery_files entry
});

const typeOptions = computed(() => props.types?.data ?? []);

// ─── Incremental file staging ────────────────────────────────────────────────

/**
 * Each entry: { file: File, previewUrl: string|null, isVideo: boolean, id: number }
 * previewUrl is an object URL for images, null for videos.
 */
const selectedMedia = ref([]);
let _mediaIdCounter = 0;

const coverInput = ref(null);
const galleryInput = ref(null);

/**
 * Called when the file input changes.
 * Appends new files to the staging array instead of overwriting.
 */
const onGalleryChange = (event) => {
    const files = Array.from(event.target.files);

    files.forEach((file) => {
        const isVideo = file.type.startsWith('video/');
        selectedMedia.value.push({
            id: ++_mediaIdCounter,
            file,
            isVideo,
            previewUrl: isVideo ? null : URL.createObjectURL(file),
            quality: '720p',  // default compression quality for videos
        });
    });

    // Reset the input so the same file(s) can be re-selected if needed
    // and so subsequent selections append rather than trigger a "no change" skip.
    event.target.value = '';
};

/**
 * Remove a single staged file and free its object URL.
 */
const removeStagedFile = (entry) => {
    if (entry.previewUrl) {
        URL.revokeObjectURL(entry.previewUrl);
    }
    selectedMedia.value = selectedMedia.value.filter((m) => m.id !== entry.id);
};

/** Remove all staged files and revoke their object URLs. */
const clearStagedFiles = () => {
    selectedMedia.value.forEach((m) => {
        if (m.previewUrl) URL.revokeObjectURL(m.previewUrl);
    });
    selectedMedia.value = [];
};

/** Revoke all object URLs when the component is destroyed. */
onUnmounted(() => {
    selectedMedia.value.forEach((m) => {
        if (m.previewUrl) URL.revokeObjectURL(m.previewUrl);
    });
});

/** Format bytes to a human-readable string. */
const formatBytes = (bytes) => {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
};

// ─── Form submission ─────────────────────────────────────────────────────────

const submit = () => {
    if (form.project_type_id === '') {
        form.project_type_id = null;
    }

    // Sync the staged File objects + their qualities into the Inertia form before posting.
    form.gallery_files   = selectedMedia.value.map((m) => m.file);
    form.video_qualities = selectedMedia.value.map((m) => m.isVideo ? m.quality : null);

    if (isEdit) {
        form.post(route('admin.projects.update', props.project.data.id), {
            forceFormData: true,
        });
    } else {
        form.post(route('admin.projects.store'), {
            forceFormData: true,
        });
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

                        <div>
                            <label for="project_type_id" class="block text-sm font-medium text-gray-300">Typ realizacji</label>
                            <select id="project_type_id" v-model="form.project_type_id" class="mt-1 block w-full bg-gray-900 border-gray-700 text-white rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="">Bez typu</option>
                                <option v-for="type in typeOptions" :key="type.id" :value="type.id">{{ type.name }}</option>
                            </select>
                            <div v-if="form.errors.project_type_id" class="text-red-400 text-sm mt-1">{{ form.errors.project_type_id }}</div>
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
                                 <div v-if="props.project?.data.cover_image_url || props.project?.data.cover_image_path" class="w-32 h-20 bg-gray-700 rounded overflow-hidden">
                                     <img :src="props.project.data.cover_image_url || ('/' + props.project.data.cover_image_path)" class="w-full h-full object-cover opacity-75" />
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
                            <p class="text-xs text-gray-500 mb-2">
                                Możesz dodawać pliki z wielu folderów — każde kliknięcie <strong>dołącza</strong> pliki do kolejki, nie nadpisuje.
                            </p>

                            <!-- File picker -->
                            <input
                                type="file"
                                @change="onGalleryChange"
                                multiple
                                ref="galleryInput"
                                class="block w-full text-sm text-gray-400
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-full file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-gray-600 file:text-white
                                    file:cursor-pointer hover:file:bg-gray-700"
                                accept="image/*,video/*"
                            />

                            <!-- Validation errors -->
                            <div v-if="form.errors['gallery_files']" class="text-red-400 text-sm mt-1">{{ form.errors['gallery_files'] }}</div>
                            <div v-for="(error, key) in form.errors" :key="key">
                                <div v-if="key.startsWith('gallery_files.')" class="text-red-400 text-sm mt-1">{{ error }}</div>
                            </div>

                            <!-- ── Staging area ─────────────────────────────────────── -->
                            <div v-if="selectedMedia.length > 0" class="mt-4">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-sm font-medium text-gray-300">
                                        Kolejka do przesłania
                                        <span class="ml-2 inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-bold bg-blue-600 text-white">{{ selectedMedia.length }}</span>
                                    </h3>
                                    <button
                                        type="button"
                                        @click="clearStagedFiles"
                                        class="text-xs text-red-400 hover:text-red-300 transition-colors"
                                    >
                                        Usuń wszystkie
                                    </button>
                                </div>

                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                                    <div
                                        v-for="entry in selectedMedia"
                                        :key="entry.id"
                                        class="relative group rounded-lg overflow-hidden border border-gray-600 bg-gray-900"
                                    >
                                        <!-- Image preview -->
                                        <img
                                            v-if="!entry.isVideo"
                                            :src="entry.previewUrl"
                                            :alt="entry.file.name"
                                            class="w-full aspect-square object-cover"
                                        />

                                        <!-- Video placeholder + quality selector -->
                                        <div
                                            v-else
                                            class="w-full aspect-square flex flex-col items-center justify-center gap-2 bg-gray-800 px-2"
                                        >
                                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z" />
                                            </svg>
                                            <span class="text-gray-400 text-[10px] text-center break-all leading-tight line-clamp-2">{{ entry.file.name }}</span>
                                            <span class="text-gray-500 text-[10px]">{{ formatBytes(entry.file.size) }}</span>

                                            <!-- ── Quality selector ── -->
                                            <div class="w-full px-1 mt-1">
                                                <label :for="`quality-${entry.id}`" class="block text-[9px] text-gray-500 mb-0.5 text-center">Jakość wyjściowa</label>
                                                <select
                                                    :id="`quality-${entry.id}`"
                                                    v-model="entry.quality"
                                                    class="w-full text-[10px] bg-gray-900 border border-gray-600 text-white rounded py-0.5 px-1 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 cursor-pointer"
                                                    @click.stop
                                                >
                                                    <option value="1080p">1080p – Wysoka (4 Mbps)</option>
                                                    <option value="720p" selected>720p – Standardowa (2.5 Mbps)</option>
                                                    <option value="480p">480p – Lekka (1 Mbps)</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- File size badge (images) -->
                                        <div v-if="!entry.isVideo" class="absolute bottom-0 left-0 right-0 bg-black/60 px-1.5 py-1 text-[10px] text-gray-300 truncate">
                                            {{ entry.file.name }}
                                        </div>

                                        <!-- Remove button (always visible, top-right) -->
                                        <button
                                            type="button"
                                            @click="removeStagedFile(entry)"
                                            class="absolute top-1 right-1 bg-red-600 hover:bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity shadow-lg"
                                            title="Usuń"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <!-- ─────────────────────────────────────────────────────── -->
                        </div>

                        <!-- Current Gallery Preview (Edit Mode) -->
                        <!-- Current Gallery Preview (Edit Mode) -->
                        <div v-if="isEdit && props.project.data.media && props.project.data.media.length > 0" class="mt-4">
                             <h3 class="text-sm font-medium text-gray-300 mb-2">Obecna Galeria ({{ props.project.data.media.length }} plików)</h3>
                             <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                 <div v-for="media in props.project.data.media" :key="media.id" class="relative group aspect-square bg-black rounded overflow-hidden border border-gray-700">
                                     <img v-if="media.type === 'image'" :src="media.url" class="w-full h-full object-cover" />
                                     <video v-else-if="media.processing_status === 'ready'" :src="media.url" class="w-full h-full object-cover"></video>

                                     <!-- Processing placeholder -->
                                     <div v-else-if="media.processing_status === 'processing'" class="w-full h-full flex flex-col items-center justify-center gap-2 bg-gray-900 text-center px-2">
                                         <svg class="w-8 h-8 text-blue-400 animate-spin" fill="none" viewBox="0 0 24 24">
                                             <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                             <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                         </svg>
                                         <span class="text-[10px] text-blue-300">Kompresja w toku…</span>
                                         <span v-if="media.video_quality" class="text-[9px] text-gray-500">{{ media.video_quality }}</span>
                                     </div>

                                     <!-- Failed placeholder -->
                                     <div v-else class="w-full h-full flex flex-col items-center justify-center gap-2 bg-gray-900 text-center px-2">
                                         <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12A9 9 0 113 12a9 9 0 0118 0z" />
                                         </svg>
                                         <span class="text-[10px] text-red-300">Błąd kompresji</span>
                                     </div>
                                     
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
