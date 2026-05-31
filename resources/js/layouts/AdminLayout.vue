<script setup>
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const page = usePage();
const showUserMenu = ref(false);

const toggleUserMenu = () => {
    showUserMenu.value = !showUserMenu.value;
};
</script>

<template>
    <div class="min-h-screen bg-gray-900 text-gray-100 flex font-sans">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-800 border-r border-gray-700 flex-shrink-0 hidden md:flex flex-col">
            <div class="h-16 flex items-center justify-center border-b border-gray-700 shadow-sm">
                <Link :href="route('admin.dashboard')" class="flex items-center gap-2 hover:opacity-80 transition-opacity">
                    <!-- Simple Logo Icon -->
                    <svg class="h-8 w-8 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span class="font-bold text-xl tracking-tight text-white">HORIZONSHOT</span>
                </Link>
            </div>

            <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
                <Link :href="route('admin.dashboard')"
                    :class="[
                        'flex items-center px-4 py-3 text-sm font-medium rounded-md transition-all duration-200 group',
                        route().current('admin.dashboard')
                            ? 'bg-blue-600 text-white shadow-md'
                            : 'text-gray-300 hover:bg-gray-700 hover:text-white'
                    ]">
                    <svg class="mr-3 h-5 w-5 flex-shrink-0" :class="route().current('admin.dashboard') ? 'text-white' : 'text-gray-400 group-hover:text-white'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Dashboard
                </Link>

                <div class="pt-4 pb-2 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Zarządzanie
                </div>

                <Link :href="route('admin.projects.index')"
                    :class="[
                        'flex items-center px-4 py-3 text-sm font-medium rounded-md transition-all duration-200 group',
                        route().current('admin.projects.*')
                            ? 'bg-blue-600 text-white shadow-md'
                            : 'text-gray-300 hover:bg-gray-700 hover:text-white'
                    ]">
                    <svg class="mr-3 h-5 w-5 flex-shrink-0" :class="route().current('admin.projects.*') ? 'text-white' : 'text-gray-400 group-hover:text-white'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Realizacje
                </Link>

                <Link :href="route('admin.project-types.index')"
                    :class="[
                        'flex items-center px-4 py-3 text-sm font-medium rounded-md transition-all duration-200 group',
                        route().current('admin.project-types.*')
                            ? 'bg-blue-600 text-white shadow-md'
                            : 'text-gray-300 hover:bg-gray-700 hover:text-white'
                    ]">
                    <svg class="mr-3 h-5 w-5 flex-shrink-0" :class="route().current('admin.project-types.*') ? 'text-white' : 'text-gray-400 group-hover:text-white'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 12h10M7 17h6M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Typy realizacji
                </Link>

                <Link :href="route('admin.leads.index')"
                    :class="[
                        'flex items-center px-4 py-3 text-sm font-medium rounded-md transition-all duration-200 group',
                        route().current('admin.leads.*')
                            ? 'bg-blue-600 text-white shadow-md'
                            : 'text-gray-300 hover:bg-gray-700 hover:text-white'
                    ]">
                    <svg class="mr-3 h-5 w-5 flex-shrink-0" :class="route().current('admin.leads.*') ? 'text-white' : 'text-gray-400 group-hover:text-white'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Leads (Form)
                </Link>
            </nav>

            <div class="p-4 border-t border-gray-700 bg-gray-800">
                 <div class="flex items-center gap-3">
                    <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold text-xs">
                        {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">
                            {{ $page.props.auth.user.name }}
                        </p>
                        <p class="text-xs text-gray-400 truncate">
                            {{ $page.props.auth.user.email }}
                        </p>
                    </div>
                    <Link :href="route('logout')" method="post" as="button" class="text-gray-400 hover:text-white" title="Wyloguj">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </Link>
                </div>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-gray-900">
             <!-- Top Header (Mobile) -->
            <header class="md:hidden bg-gray-800 border-b border-gray-700 h-16 flex items-center justify-between px-4 sm:px-6">
                 <div class="flex items-center">
                    <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="text-gray-400 hover:text-white focus:outline-none">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <span class="ml-3 font-bold text-lg text-white">HORIZONSHOT</span>
                </div>
                
                 <div class="flex items-center">
                     <Link :href="route('logout')" method="post" as="button" class="text-gray-400 hover:text-white">
                        <span class="sr-only">Log out</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </Link>
                </div>
            </header>

            <!-- Mobile Menu -->
             <div :class="{'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown}" class="md:hidden bg-gray-800 border-b border-gray-700 animate-fade-in-down origin-top">
                <div class="pt-2 pb-3 space-y-1">
                    <Link :href="route('admin.dashboard')" 
                        class="block px-3 py-2 rounded-md text-base font-medium transition-colors"
                        :class="route().current('admin.dashboard') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'">
                        Dashboard
                    </Link>
                     <Link :href="route('admin.projects.index')" 
                        class="block px-3 py-2 rounded-md text-base font-medium transition-colors"
                        :class="route().current('admin.projects.*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'">
                        Realizacje
                    </Link>
                    <Link :href="route('admin.project-types.index')" 
                        class="block px-3 py-2 rounded-md text-base font-medium transition-colors"
                        :class="route().current('admin.project-types.*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'">
                        Typy realizacji
                    </Link>
                     <Link :href="route('admin.leads.index')" 
                        class="block px-3 py-2 rounded-md text-base font-medium transition-colors"
                        :class="route().current('admin.leads.*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'">
                        Leads
                    </Link>
                </div>
                <div class="pt-4 pb-4 border-t border-gray-700">
                    <div class="px-5 flex items-center">
                        <div class="flex-shrink-0">
                             <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold text-sm">
                                {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                            </div>
                        </div>
                        <div class="ml-3">
                            <div class="text-base font-medium leading-none text-white">{{ $page.props.auth.user.name }}</div>
                            <div class="text-sm font-medium leading-none text-gray-400 mt-1">{{ $page.props.auth.user.email }}</div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-gray-900 p-6">
                <!-- Flash Messages -->
                <transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="transform -translate-y-2 opacity-0"
                    enter-to-class="transform translate-y-0 opacity-100"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="transform translate-y-0 opacity-100"
                    leave-to-class="transform -translate-y-2 opacity-0"
                >
                    <div v-if="$page.props.flash.success" class="mb-6 p-4 bg-green-900/50 border border-green-700 text-green-200 rounded-lg shadow-lg flex items-center">
                        <svg class="h-5 w-5 mr-3 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ $page.props.flash.success }}
                    </div>
                </transition>
                
                <transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="transform -translate-y-2 opacity-0"
                    enter-to-class="transform translate-y-0 opacity-100"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="transform translate-y-0 opacity-100"
                    leave-to-class="transform -translate-y-2 opacity-0"
                >
                    <div v-if="$page.props.flash.error" class="mb-6 p-4 bg-red-900/50 border border-red-700 text-red-200 rounded-lg shadow-lg flex items-center">
                         <svg class="h-5 w-5 mr-3 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ $page.props.flash.error }}
                    </div>
                </transition>

                <slot />
            </main>
        </div>
    </div>
</template>
