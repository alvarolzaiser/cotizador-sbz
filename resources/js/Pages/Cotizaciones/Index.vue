<script setup>
import { usePage, Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import WhatsappGreenIcon from '@/Components/WhatsappGreenIcon.vue';
import { useCartStore } from '@/stores/cart';
import { ref, onMounted } from 'vue';

// Manejo de fechas con el paquete 'dayjs' + 'dayjs-plugin-utc' + 'dayjs-timezone-iana-plugin'
import dayjs from "dayjs"; // Necesitamos instalar 'dayjs' previamente => ``npm install dayjs``
import utc from "dayjs-plugin-utc"; // Necesitamos instalar 'dayjs-plugin-utc' previamente => ``npm install dayjs-plugin-utc``
import timezone from "dayjs-timezone-iana-plugin"; // Necesitamos instalar 'dayjs-timezone-iana-plugin' previamente => ``npm install dayjs-timezone-iana-plugin``
dayjs.extend(utc); // Activamos el plugin 'dayjs-plugin-utc'
dayjs.extend(timezone); // Activamos el plugin 'dayjs-timezone-iana-plugin'
import "dayjs/locale/es"; // Importa el idioma español
dayjs.locale("es"); // Configura dayjs en español


// Guardo en 'cotizaciones' la prop "cotizaciones" que me llega en esta URL o PAGE
const cotizaciones = ref(usePage().props.cotizaciones);
// // Guardo la instancia de mi carrito en la const 'cartStore' => desde allí llamaré a mis actions, state's, etc
// const cartStore = useCartStore();
// // En esta pagina msgWP va a ser un objeto vacío, y luego en el forEach de abajo, voy agregando elementos al objeto en base a la cantidad de cotizaciones que tengo, por cada cotización, le agrego un elemento al objeto 'msgWP' y el indice de ese elemento que agrego al objeto 'msgWP' es el ID de la cotizacion, de esa manera, puedo luego cuando recorro las cotizaciones, rescatar el msgWP que le corresponde a esa cotización por su ID
// const msgWP = ref({});
// onMounted(async() => {
//     try {
//         for (const cotizacion of cotizaciones.value) {
//             console.log(cotizacion);
//             msgWP.value[cotizacion.id] = await cartStore.msgWPF(cotizacion);
//         };
//     } catch (err) {
//         console.error(err);
//     }
// });

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-AR', {
        style: 'currency',
        currency: 'ARS'
    }).format(value);
};
</script>

<template>
    <AppLayout title="Cotizaciones">
        <!-- Page Heading -->
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Cotizaciones
            </h2>
        </template>

        <!-- Page Content -->
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <!-- Aqui va basicamente todo mi contenido, puedo crear un componente, y hacer todo lo que hago aquí en un componente aislado/separado, luego importar ese componente y llamarlo justo aquí, es a gusto mío y depende de cuan separadas quiero tener las cosas -->
                    <div>
                        <div class="bg-white border-b border-gray-200">
                            
                            
                            
                            
                            <!-- cotizaciones -->
                            <div class="flex flex-col xl:flex-row jusitfy-center items-stretch w-full xl:space-x-8 space-y-4 md:space-y-6 xl:space-y-0">
                                <div class="flex flex-col justify-start items-start w-full space-y-4 md:space-y-6 xl:space-y-8">
                                    <div class="flex flex-col justify-start items-start px-4 py-4 md:py-6 md:p-6 xl:p-8 w-full">
                                            <div v-if="cotizaciones.length > 0"     v-for="cotizacion in cotizaciones" :key="cotizacion.id"     class="mb-6 flex flex-col md:flex-row justify-start items-start md:items-center md:space-x-6 xl:space-x-8 w-full">
                                                <div class="border-b border-gray-200 md:flex-row flex-col flex justify-between items-start w-full pb-8 space-y-4 md:space-y-0">
                                                    <div class="w-full flex flex-col justify-start items-start space-y-8">
                                                        <!-- Fecha con el paquete 'dayjs' -->
                                                        <h3 class="text-xl xl:text-2xl font-semibold leading-6 text-gray-800">Nro #{{ cotizacion.id }} | {{ dayjs(cotizacion.created_at).tz("America/Argentina/Buenos_Aires").format("D [de] MMMM [de] YYYY, HH:mm:ss") }}</h3>
                                                        <div class="flex justify-start items-start flex-col space-y-2">
                                                            <p class="text-sm leading-none text-gray-800"><span class="dark:text-gray-400 text-gray-300">Responsable: </span> {{ cotizacion.user.name }}</p>
                                                            <p class="text-sm leading-none text-gray-800"><span class="dark:text-gray-400 text-gray-300">Cliente: </span> {{ cotizacion.cliente.nombre }}</p>
                                                            <p class="capitalize pt-2 text-sm leading-none text-gray-800"><span :class="cotizacion.estado == 'aprobada' ? 'bg-green-200 py-1 px-2' : cotizacion.estado == 'rechazada' ? 'bg-red-200 py-1 px-2' : cotizacion.estado == 'sin respuesta' ? 'bg-gray-300 py-1 px-2' : cotizacion.estado == 'pendiente' ? 'bg-blue-200 py-1 px-2' : cotizacion.estado == 'revision' ? 'bg-yellow-200 py-1 px-2' : ''">{{ cotizacion.estado }}</span></p>
                                                        </div>
                                                    </div>
                                                    <div class="flex justify-between items-end lg:items-start space-x-8 w-full">
                                                        <p class="text-base xl:text-lg leading-6">{{ formatCurrency(cotizacion.total) }}</p>
                                                        <p class="text-base xl:text-lg leading-6 text-gray-800 border border-gray-800 rounded-full py-1 px-3">{{ cotizacion.detalles.length }} items</p>
                                                        <!-- Boton de wpp | ingresar a los detalles -->
                                                        <div>
                                                            <!-- <a :href="`https://api.whatsapp.com/send?phone=549${cotizacion.cliente.telefono}&text=${encodeURIComponent(msgWP[cotizacion.id])}`" target="_blank">
                                                                <WhatsappGreenIcon/>
                                                            </a> -->
                                                            <!-- Enlace interno -->
                                                            <!-- Indico que vaya a la ruta con nombre 'productos.show' y que el parametro dinamico que recibe esa ruta es 'producto.id' -->
                                                            <Link :href="route('cotizaciones.show', cotizacion.id)">
                                                                <button class="flex items-center">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="50" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                        <path d="M4 12H20M20 12L16 8M20 12L16 16"></path> 
                                                                    </svg>
                                                                </button>
                                                            </Link>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div v-else class="text-gray-500 p-10 flex flex-col justify-center">
                                                <!-- Texto en caso que no haya elementos -->
                                                <p class="text-center text-lg">Aun no hay cotizaciones. Realiza una <Link :href="route('dashboard')" class="text-blue-500 font-bold">aquí</Link></p>
                                            </div>
                                    </div>
                                </div>
                            </div>


                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>