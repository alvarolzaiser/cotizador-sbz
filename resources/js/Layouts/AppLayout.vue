<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

// Importamos el el store donde esta carrito
import { useCartStore } from '@/stores/cart';
// Instanciamos el carrito y guardamos la instancia en la const 'cartStore', para poder acceder a sus acciones y propiedades a traves de la const 'cartStore'
const cartStore = useCartStore();

defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);

const switchToTeam = (team) => {
    router.put(route('current-team.update'), {
        team_id: team.id,
    }, {
        preserveState: false,
    });
};

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div>
        <Head :title="title" />

        <Banner />

        <div class="min-h-screen bg-gray-100">
            <nav class="bg-white border-b border-gray-100">
                <!-- Primary Navigation Menu -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationMark class="block h-9 w-auto" />
                                </Link>

                                <!-- #### Carrito MOBILE #### -->
                                <Link :href="route('carrito')" class="ml-4 lg:hidden">
                                    <div class="flex flex-row">
                                        <svg width="30" height="30" viewBox="-1 -1 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M8 21.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0ZM19 21.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0ZM2 4.5H1m3 0 1.5 12H18L21 8.5H6" 
                                                stroke="#6875F5" 
                                                stroke-width="1.5" 
                                                stroke-linecap="round"
                                                stroke-linejoin="round"/>
                                        </svg>
                                        <div class="bg-[#6875F5] text-white flex items-center justify-center w-4 h-4 rounded-full text-xs -ml-3">
                                            {{ cartStore.items.length }}
                                        </div>
                                    </div>
                                </Link>

                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                    Escritorio
                                </NavLink>
                                <NavLink :href="route('productos.index')" :active="route().current('productos.*')"> <!-- Cualquier ruta que lleve 'productos.*' va a marcar como 'active' este link de mi nav-->
                                    Productos
                                </NavLink>
                                <NavLink :href="route('clientes.index')" :active="route().current('clientes.*')"> <!-- Cualquier ruta que lleve 'clientes.*' va a marcar como 'active' este link de mi nav-->
                                    Clientes
                                </NavLink>
                                <NavLink :href="route('cotizaciones.index')" :active="route().current('cotizaciones.*')"> <!-- Cualquier ruta que lleve 'cotizaciones.*' va a marcar como 'active' este link de mi nav-->
                                    Cotizaciones
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ms-6">

                            <!-- #### Carrito DESKTOP #### -->
                            <Link :href="route('carrito')">
                                <div class="flex flex-row">
                                    <svg width="30" height="30" viewBox="-1 -1 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M8 21.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0ZM19 21.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0ZM2 4.5H1m3 0 1.5 12H18L21 8.5H6" 
                                            stroke="#6875F5" 
                                            stroke-width="1.5" 
                                            stroke-linecap="round"
                                            stroke-linejoin="round"/>
                                    </svg>
                                    <div class="bg-[#6875F5] text-white flex items-center justify-center w-4 h-4 rounded-full text-xs -ml-3">
                                        {{ cartStore.items.length }}
                                    </div>
                                </div>
                            </Link>

                            <!-- Settings Dropdown -->
                            <div class="ms-3 relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button v-if="$page.props.jetstream.managesProfilePhotos" class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                            <img class="size-8 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                                        </button>

                                        <span v-else class="inline-flex rounded-md">
                                            <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                                                {{ $page.props.auth.user.name }}

                                                <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <!-- Account Management -->
                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                            Gestionar cuenta
                                        </div>

                                        <DropdownLink :href="route('profile.show')">
                                            Perfil
                                        </DropdownLink>

                                        <div class="border-t border-gray-200" />

                                        <!-- Authentication -->
                                        <form @submit.prevent="logout">
                                            <DropdownLink as="button">
                                                Cerrar sesión
                                            </DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out" @click="showingNavigationDropdown = ! showingNavigationDropdown">
                                <svg
                                    class="size-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{'hidden': showingNavigationDropdown, 'inline-flex': ! showingNavigationDropdown }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{'hidden': ! showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div :class="{'block': showingNavigationDropdown, 'hidden': ! showingNavigationDropdown}" class="sm:hidden">
                    <div class="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            Escritorio
                        </ResponsiveNavLink>

                        <ResponsiveNavLink :href="route('productos.index')" :active="route().current('productos.*')"> <!-- Cualquier ruta que lleve 'productos.*' va a marcar como 'active' este link de mi nav-->
                            Productos
                        </ResponsiveNavLink>

                        <ResponsiveNavLink :href="route('clientes.index')" :active="route().current('clientes.*')"> <!-- Cualquier ruta que lleve 'clientes.*' va a marcar como 'active' este link de mi nav-->
                            Clientes
                        </ResponsiveNavLink>

                        <ResponsiveNavLink :href="route('cotizaciones.index')" :active="route().current('cotizaciones.*')"> <!-- Cualquier ruta que lleve 'cotizaciones.*' va a marcar como 'active' este link de mi nav-->
                            Cotizaciones
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div class="flex items-center px-4">
                            <div v-if="$page.props.jetstream.managesProfilePhotos" class="shrink-0 me-3">
                                <img class="size-10 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                            </div>

                            <div>
                                <div class="font-medium text-base text-gray-800">
                                    {{ $page.props.auth.user.name }}
                                </div>
                                <div class="font-medium text-sm text-gray-500">
                                    {{ $page.props.auth.user.email }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.show')" :active="route().current('profile.show')">
                                Perfil
                            </ResponsiveNavLink>

                            <!-- Authentication -->
                            <form method="POST" @submit.prevent="logout">
                                <ResponsiveNavLink as="button">
                                    Cerrar sesion
                                </ResponsiveNavLink>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header v-if="$slots.header" class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
