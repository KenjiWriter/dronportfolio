<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    leads: Object, // Paginated resource
});

const confirmingDelete = ref(null);

const toggleStatus = (lead) => {
    router.patch(route('admin.leads.update', lead.id), {}, {
        preserveScroll: true,
        preserveState: true,
    });
};

const confirmDelete = (leadId) => {
    confirmingDelete.value = leadId;
};

const cancelDelete = () => {
    confirmingDelete.value = null;
};

const deleteLead = (leadId) => {
    router.delete(route('admin.leads.destroy', leadId), {
        preserveScroll: true,
        onSuccess: () => {
            confirmingDelete.value = null;
        },
    });
};
</script>

<template>
    <Head title="Leads" />

    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Zgłoszenia (Leads)
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-white">Zgłoszenia Kontaktowe</h1>
                </div>

                <div class="bg-gray-800 shadow-xl rounded-lg overflow-hidden border border-gray-700">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-700">
                            <thead class="bg-gray-900">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Data</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Imię</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Kontakt</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider w-1/3">Wiadomość</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Akcje</th>
                                </tr>
                            </thead>
                            <tbody class="bg-gray-800 divide-y divide-gray-700">
                                <tr v-for="lead in leads.data" :key="lead.id" class="hover:bg-gray-750 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                        {{ new Date(lead.created_at).toLocaleDateString('pl-PL') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-white">{{ lead.name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-300">{{ lead.email }}</div>
                                        <div v-if="lead.phone" class="text-sm text-gray-400">{{ lead.phone }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-300 whitespace-pre-wrap max-w-sm">
                                        {{ lead.message }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <button 
                                            @click="toggleStatus(lead)"
                                            :class="[
                                                'px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full cursor-pointer transition-colors',
                                                lead.status === 'new' ? 'bg-green-900 text-green-200 hover:bg-green-800' : 'bg-gray-600 text-gray-300 hover:bg-gray-500'
                                            ]">
                                            {{ lead.status === 'new' ? 'Nowe' : 'Obsłużone' }}
                                        </button>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        <template v-if="confirmingDelete === lead.id">
                                            <span class="text-red-400 text-xs mr-2">Na pewno?</span>
                                            <button @click="deleteLead(lead.id)" class="text-red-400 hover:text-red-300 font-medium mr-2 transition-colors">Tak</button>
                                            <button @click="cancelDelete" class="text-gray-400 hover:text-gray-200 font-medium transition-colors">Nie</button>
                                        </template>
                                        <button v-else @click="confirmDelete(lead.id)" class="text-red-500 hover:text-red-400 transition-colors" title="Usuń">
                                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="!leads.data || leads.data.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        Brak zgłoszeń.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="leads.links && leads.last_page > 1" class="px-6 py-4 bg-gray-900 border-t border-gray-700 flex items-center justify-between">
                        <div class="text-sm text-gray-400">
                            Pokazywanie {{ leads.from }}–{{ leads.to }} z {{ leads.total }}
                        </div>
                        <div class="flex gap-1">
                            <template v-for="link in leads.links" :key="link.label">
                                <button
                                    v-if="link.url"
                                    @click="router.get(link.url, {}, { preserveState: true, preserveScroll: true })"
                                    :class="[
                                        'px-3 py-1 text-sm rounded transition-colors',
                                        link.active ? 'bg-blue-600 text-white' : 'bg-gray-800 text-gray-400 hover:bg-gray-700'
                                    ]"
                                    v-html="link.label"
                                />
                                <span v-else class="px-3 py-1 text-sm text-gray-600" v-html="link.label" />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
