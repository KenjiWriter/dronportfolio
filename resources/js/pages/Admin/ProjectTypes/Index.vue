<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { ref } from 'vue';

const props = defineProps({
    types: Object,
});

const form = useForm({
    name: '',
});

const editTypeId = ref(null);
const editName = ref('');

const submit = () => {
    form.post(route('admin.project-types.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

const removeType = (type) => {
    if ((type.projects_count ?? 0) > 0) return;
    if (!confirm(`Usunac typ "${type.name}"?`)) return;

    router.delete(route('admin.project-types.destroy', type.id), {
        preserveScroll: true,
    });
};

const startEdit = (type) => {
    editTypeId.value = type.id;
    editName.value = type.name;
};

const cancelEdit = () => {
    editTypeId.value = null;
    editName.value = '';
};

const saveEdit = (type) => {
    router.patch(route('admin.project-types.update', type.id), {
        name: editName.value,
    }, {
        preserveScroll: true,
        onSuccess: () => cancelEdit(),
    });
};
</script>

<template>
    <Head title="Typy realizacji" />

    <AdminLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="bg-gray-800 rounded-lg shadow border border-gray-700 p-6">
                    <h1 class="text-2xl font-bold text-white mb-4">Typy realizacji</h1>
                    <p class="text-sm text-gray-400 mb-6">Dodawaj kategorie, po ktorych filtrujesz realizacje na stronie projects.</p>

                    <form @submit.prevent="submit" class="flex flex-col sm:flex-row gap-3">
                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="Np. Reklama, Nieruchomosci, Event"
                            class="flex-1 bg-gray-900 border border-gray-700 text-white rounded-md px-4 py-2 focus:border-blue-500 focus:ring-blue-500"
                        />
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2 rounded-md disabled:opacity-50"
                        >
                            Dodaj typ
                        </button>
                    </form>

                    <div v-if="form.errors.name" class="text-red-400 text-sm mt-2">{{ form.errors.name }}</div>
                </div>

                <div class="bg-gray-800 rounded-lg shadow border border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-white mb-4">Istniejace typy</h2>

                    <div v-if="(types?.data?.length ?? 0) === 0" class="text-gray-400">Brak typow.</div>

                    <ul v-else class="space-y-3">
                        <li
                            v-for="type in types.data"
                            :key="type.id"
                            class="flex items-center justify-between bg-gray-900 border border-gray-700 rounded-md px-4 py-3"
                        >
                            <div>
                                <div v-if="editTypeId !== type.id" class="text-white font-medium">{{ type.name }}</div>
                                <form v-else @submit.prevent="saveEdit(type)" class="flex items-center gap-2">
                                    <input
                                        v-model="editName"
                                        type="text"
                                        class="bg-gray-800 border border-gray-600 text-white rounded-md px-3 py-1 text-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                    <button
                                        type="submit"
                                        class="text-green-400 hover:text-green-300 text-sm font-semibold"
                                    >
                                        Zapisz
                                    </button>
                                    <button
                                        type="button"
                                        class="text-gray-400 hover:text-gray-300 text-sm"
                                        @click="cancelEdit"
                                    >
                                        Anuluj
                                    </button>
                                </form>
                                <div class="text-xs text-gray-400">/{{ type.slug }}</div>
                                <div class="text-xs text-gray-500 mt-1">Przypisane realizacje: {{ type.projects_count ?? 0 }}</div>
                            </div>

                            <div class="flex items-center gap-3">
                                <button
                                    v-if="editTypeId !== type.id"
                                    type="button"
                                    class="text-blue-400 hover:text-blue-300 text-sm font-semibold"
                                    @click="startEdit(type)"
                                >
                                    Edytuj
                                </button>

                                <button
                                    type="button"
                                    :disabled="(type.projects_count ?? 0) > 0"
                                    class="text-red-400 hover:text-red-300 text-sm font-semibold disabled:opacity-40 disabled:cursor-not-allowed"
                                    @click="removeType(type)"
                                >
                                    Usun
                                </button>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
