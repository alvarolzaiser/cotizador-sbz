<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    neumatico: Object,
})

</script>

<template>
    <AppLayout :title="'Neumatico - ' + neumatico.titulo">
        <!-- Page Heading -->
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Neumatico - {{ neumatico.titulo }}
            </h2>
        </template>

        <!-- Page Content -->
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <!-- Aqui va basicamente todo mi contenido, puedo crear un componente, y hacer todo lo que hago aquí en un componente aislado/separado, luego importar ese componente y llamarlo justo aquí, es a gusto mío y depende de cuan separadas quiero tener las cosas -->
                    <div>
                        <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                            <h1>Vista de neumatico específico</h1>
                            <pre>
                                {{ neumatico }}
                            </pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>