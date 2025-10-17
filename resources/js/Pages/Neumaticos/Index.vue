<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps ({
    neumaticos: Object,
});

</script>

<template>
    <AppLayout title="Neumaticos">
        <!-- Page Heading -->
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Neumaticos
            </h2>
        </template>

        <!-- Page Content -->
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <!-- Aqui va basicamente todo mi contenido, puedo crear un componente, y hacer todo lo que hago aquí en un componente aislado/separado, luego importar ese componente y llamarlo justo aquí, es a gusto mío y depende de cuan separadas quiero tener las cosas -->
                    <div>
                        <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                            <!-- {{ neumaticos }} -->
                            <ul class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 items-stretch">
                                <li v-for="neumatico in neumaticos" :key="neumatico.id" class="h-full">
                                    <div class="w-full bg-gradient-to-l from-slate-300 to-slate-100 text-slate-600 border border-slate-300 flex flex-col p-4 gap-4 rounded-lg shadow-md h-full">
                                        <!-- Título -->
                                        <div class="text-lg font-bold capitalize rounded-md">
                                            <p>{{ neumatico.titulo }} - {{ neumatico.codigo }}</p>
                                        </div>
                                        
                                        <!-- Información de precios y medidas -->
                                        <div class="rounded-md flex-grow">
                                            $ {{ neumatico.precio_normal }} ||
                                            $ {{ neumatico.precio_mayorista }} ||
                                            $ {{ neumatico.precio_efectivo }}
                                            <br>
                                            Rodad: {{ neumatico.rodado }} |
                                            Ancho: {{ neumatico.ancho }} |
                                            Alto: {{ neumatico.alto }} |
                                            Carga: {{ neumatico.capacidad_carga }} 
                                        </div>

                                        <!-- Botones de acción en una fila -->
                                        <div class="mt-auto flex justify-end items-center">
                                            <a :href="neumatico.link_producto" target="_blank">
                                            <button class="rounded-md bg-slate-300 hover:bg-slate-600 hover:text-slate-200 duration-300 p-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                                    <polyline points="15 3 21 3 21 9"></polyline>
                                                    <line x1="10" y1="14" x2="21" y2="3"></line>
                                                </svg>
                                            </button>
                                            </a>

                                            <!-- Enlace interno -->
                                            <!-- Indico que vaya a la ruta con nombre 'neumaticos.show' y que el parametro dinamico que recibe esa ruta es 'neumatico.id' -->
                                            <Link :href="route('neumaticos.show', neumatico.id)">
                                            <button class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="50" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M4 12H20M20 12L16 8M20 12L16 16"></path> 
                                                </svg>
                                            </button>
                                            </Link>
                                        </div>
                                    </div>
                                </li>
                            </ul>


                            <!-- <ul>
                                <li v-for="neumatico in neumaticos" :key="neumatico.id">
                                    <div class="py-4 space-y-2">
                                        <p>{{ neumatico.titulo }} - {{ neumatico.codigo }}</p>
                                        <p>
                                            $ {{ neumatico.precio_normal }} | 
                                            $ {{ neumatico.precio_mayorista }} | 
                                            $ {{ neumatico.precio_efectivo }}
                                        </p>
                                        <p>
                                            Rodad: {{ neumatico.rodado }} |
                                            Ancho: {{ neumatico.ancho }} |
                                            Alto: {{ neumatico.alto }} |
                                            Carga: {{ neumatico.capacidad_carga }} 
                                        </p>
                                        <div class="bg-gray-400 text-center w-80 rounded-sm py-1">
                                            <a :href="neumatico.link_producto" target="_blank">Ver producto</a>
                                        </div>
                                        <div class="bg-blue-700 text-white text-center w-80 rounded-sm py-1">
                                            <Link :href="route('neumaticos.show', neumatico.id)">Ver neumático</Link>
                                        </div>
                                    </div>
                                    <hr>
                                </li>
                            </ul> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>