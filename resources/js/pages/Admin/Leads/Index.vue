<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';

defineProps({
    leads: Array,
});

const toggleStatus = (lead) => {
    router.patch(route('admin.leads.update', lead.id), {}, {
        preserveScroll: true,
        preserveState: true,
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
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Imię / Firma</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Kontakt</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider w-1/3">Wiadomość</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-gray-800 divide-y divide-gray-700">
                                <tr v-for="lead in leads" :key="lead.id" class="hover:bg-gray-750 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                        {{ new Date(lead.created_at).toLocaleDateString() }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-white">{{ lead.name }}</div>
                                        <div class="text-sm text-gray-400">{{ lead.company_name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-300">{{ lead.email }}</div>
                                        <div class="text-sm text-gray-400">{{ lead.phone }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-300 whitespace-pre-wrap">
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
                                </tr>
                                <tr v-if="leads.length === 0">
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        Brak zgłoszeń.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
