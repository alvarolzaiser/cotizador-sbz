<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps ({
    productos: Object,
});

</script>

<template>
    <AppLayout title="Productos">
        <!-- Page Heading -->
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Productos
            </h2>
        </template>

        <!-- Page Content -->
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <!-- Aqui va basicamente todo mi contenido, puedo crear un componente, y hacer todo lo que hago aquí en un componente aislado/separado, luego importar ese componente y llamarlo justo aquí, es a gusto mío y depende de cuan separadas quiero tener las cosas -->
                    <div>
                        <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                            <!-- {{ productos }} -->
                            <ul class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 items-stretch">
                                <li v-for="producto in productos" :key="producto.id" class="h-full">
                                    <div class="w-full bg-slate-200 text-slate-600 border border-slate-300 rounded-lg shadow-md h-full flex flex-col justify-between">
                                        
                                        <!-- Imagen
                                        <div>
                                            <img :src="producto.image_url" class="w-[100%] h-[fit-content] object-cover rounded-t-lg">
                                        </div> -->

                                        <!-- Titulo, Atributos, Precios y Stock -->
                                        <div class="p-4 flex flex-col gap-3">                                        
                                            <!-- Título -->
                                            <div class="text-lg font-bold capitalize rounded-md">
                                                <p>{{ producto.titulo }} - {{ producto.codigo }}</p>
                                            </div>
                                            <!-- Atributos -->
                                            <div class="flex flex-row gap-2">
                                                <p v-for="atributo in producto.atributos" class="bg-slate-600 text-white px-1 rounded-sm">
                                                    {{ atributo.value }}
                                                </p>
                                            </div>
                                            <!-- Información de precios y stock -->
                                            <div class="rounded-md flex-grow">
                                                $ {{ producto.precio_normal }} |
                                                <span :class="{
                                                    'bg-green-700 text-white px-1 rounded-sm': producto.stock > 0,
                                                    'bg-red-700 text-white px-1 rounded-sm': producto.stock <= 0
                                                }">
                                                    STOCK {{ producto.stock }}
                                                </span>
                                                {{ producto.precio_mayorista !== null ? `| ${producto.precio_mayorista}` : '' }}
                                            </div>
                                        </div>

                                        <!-- Botones de acción en una fila -->
                                        <div class="pb-4 mt-auto flex justify-end items-center">
                                            <a :href="producto.link_producto" target="_blank">
                                            <button class="rounded-md bg-slate-300 hover:bg-slate-600 hover:text-slate-200 duration-300 p-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                                    <polyline points="15 3 21 3 21 9"></polyline>
                                                    <line x1="10" y1="14" x2="21" y2="3"></line>
                                                </svg>
                                            </button>
                                            </a>

                                            <!-- Enlace interno -->
                                            <!-- Indico que vaya a la ruta con nombre 'productos.show' y que el parametro dinamico que recibe esa ruta es 'producto.id' -->
                                            <Link :href="route('productos.show', producto.id)">
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
                                <li v-for="producto in productos" :key="producto.id">
                                    <div class="py-4 space-y-2">
                                        <p>{{ producto.titulo }} - {{ producto.codigo }}</p>
                                        <p>
                                            $ {{ producto.precio_normal }} | 
                                            $ {{ producto.precio_mayorista }} | 
                                            $ {{ producto.precio_efectivo }}
                                        </p>
                                        <div class="bg-gray-400 text-center w-80 rounded-sm py-1">
                                            <a :href="producto.link_producto" target="_blank">Ver producto</a>
                                        </div>
                                        <div class="bg-blue-700 text-white text-center w-80 rounded-sm py-1">
                                            <Link :href="route('productos.show', producto.id)">Ver neumático</Link>
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