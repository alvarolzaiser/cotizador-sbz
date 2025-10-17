<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import WhatsappGreenIcon from '@/Components/WhatsappGreenIcon.vue';
import EnviarSistema from '@/Components/EnviarSistema.vue';
import axios from 'axios';
import { ref, computed, onMounted, watch } from 'vue';
import { usePage, useForm, Head, Link } from '@inertiajs/vue3';
import { useCartStore } from '@/stores/cart'; // importo mi store 'useCartStore' => es mi carritos
import { debounce } from 'lodash'; // Importo la función 'debounce' de lodash para evitar múltiples llamadas a la API al escribir en el input de busqueda

// Manejo de fechas con el paquete 'dayjs' + 'dayjs-plugin-utc' + 'dayjs-timezone-iana-plugin'
import dayjs from "dayjs"; // Necesitamos instalar 'dayjs' previamente => ``npm install dayjs``
import utc from "dayjs-plugin-utc"; // Necesitamos instalar 'dayjs-plugin-utc' previamente => ``npm install dayjs-plugin-utc``
import timezone from "dayjs-timezone-iana-plugin"; // Necesitamos instalar 'dayjs-timezone-iana-plugin' previamente => ``npm install dayjs-timezone-iana-plugin``
dayjs.extend(utc); // Activamos el plugin 'dayjs-plugin-utc'
dayjs.extend(timezone); // Activamos el plugin 'dayjs-timezone-iana-plugin'
import "dayjs/locale/es"; // Importa el idioma español
dayjs.locale("es"); // Configura dayjs en español

// Guardo la instancia de mi carrito en la const 'cartStore' => desde allí llamaré a mis actions, state's, etc
const cartStore = useCartStore();
// Guardo en 'cotizacion' la prop "cotizacion" que me llega en esta URL o PAGE
const cotizacion = ref(usePage().props.cotizacion);
// Guardo el numero que tiene el cliente asociado en el objeto reactivo "telefonoCliente"
const telefonoCliente = ref(cotizacion.value.cliente.telefono);

// Posibles estados de una cotizacion
const estados = ref([
    'pendiente',
    'sin respuesta',
    'revision',
    'aprobada',
    'rechazada',
]);
const resultadoEstado = ref(''); // Guardo el resultado proveniente de la respuesta de la request o peticion de actualización
const isLoading = ref(false); // Detecto cuando se está actualizando y desactivo el SELECT para evitar superposicion
// Observo los cambios en el "estado" de la cotizacion
watch(
    () => cotizacion.value.estado, // Observo el cambio en el "estado" de la cotizaciòn 
    async (newEstado, oldEstado) => { // Guardo el estado NUEVO y el VIEJO
        // Mientras se ejecuta la llamada o el request al endpoint, desactivo el SELECT
        isLoading.value = true;

        // Al detectar un cambio en el "estado" de la cotizacion, llamo al endpoint que se encarga de hacer la actualizacion
        try {
            const response = await axios.put(route('cotizaciones.actualizarEstado', cotizacion.value.id), {
                newEstado: newEstado,
                oldEstado: oldEstado,
            })

            resultadoEstado.value = response.data.resultadoEstado;
            console.log(response.resultadoEstado);
        } catch(err) {
            // Si la solicitud o request falla, el estado en la UI permanecerá con el valor incorrecto. Aquí lo revertimos para que vuelva al valor SIN actualizar
            console.error(err); // Muestra en consola el error
            cotizacion.value.estado = oldEstado; // ✅ Revierte al estado anterior
            alert('Error al actualizar el estado');
        } finally {
            isLoading.value = false; // Volvemos a activar el select cuando se finaliza la peticion y se obtiene una respuesta
        }

    }
)

// Funcion para actualizar número
const resultado = ref(null);
const actNumero = async () => {
    try {
        const response = await axios.put(route('clientes.actualizarNumero', cotizacion.value.cliente.id), {
            telefono: telefonoCliente.value,
        })

        resultado.value = response.data.resultado
    } catch (err) {
        console.log(err);
    }
}

// Almaceno el mensaje de whatsapp aqui
const msgWP = ref('');
// Al ingresar a una cotización individual, genero el mensaje de WP
onMounted( async() => {
    try {
        // Ahora 'msgWP' va a tener como valor el retorno de la funcion 'msgWPF' de mi store 'useCartStore'. La función 'msgWPF' se encarga de componer, crear y fromatear el mensaje de wpp tomando como base la "cotizacion" que se envía como argumento
        msgWP.value = await cartStore.msgWPF(cotizacion);
    } catch(err) {
        console.error(err);
    }
});


// Respuesta de envio de pedido a 3C
const loadingFetch = ref(false);
const responseFetch = ref('');
const enviarPedidoResponse = async () => {
    loadingFetch.value = true;
    try {
        const response = await axios.post(route('cotizaciones.enviarCotizacion', cotizacion.value.id));
        // responseFetch.value = 'Envío exitoso: ' + response.data.body;
        responseFetch.value = 'Envío exitoso: ' + response.data.estado + ' | ' + response.data.mensaje;
        console.log(response.data);
    } catch(err) {
        responseFetch.value = 'Envío fallido: ' + err.response.data.error;
        console.log(err);
    } finally {
        loadingFetch.value = false;
    }
}


// --- FUNCIONALIDAD DE EDICION ---
    // 1. Estado para controlar el modo edición
    const isEditing = ref(false);
    
    // Función para activar el modo edición
    const activarEdicion = () => {
        // Hacemos una copia profunda de los detalles para el formulario
        form.detalles = JSON.parse(JSON.stringify(cotizacion.value.detalles));
        isEditing.value = true;
    };

    // Función para cancelar la edición
    const cancelarEdicion = () => {
        form.reset(); // Restablece el formulario a su estado inicial
        form.clearErrors();
        isEditing.value = false;
    };

    // 2. Usar 'useForm' de Inertia para manejar los datos de edición
    // Inicializamos el formulario con los datos de la cotización actual
    const form = useForm({
        detalles: [], // Se inicializa vacío y se llena al activar la edición
    });

    // 3. Propiedad computada para el total dinámico
    const totalEditado = computed(() => {
        return form.detalles.reduce((sum, item) => sum + (item.precio_unitario * item.cantidad), 0);
    });

    // 4. Lógica para la búsqueda de nuevos productos
    const searchQuery = ref('');
    const searchResults = ref([]);
    const buscarProductos = async () => {
        if (!searchQuery.value) {
            searchResults.value = [];
            return;
        }
        try {
            const response = await axios.get(route('productos.search', { search: searchQuery.value }));
            searchResults.value = response.data.results;
        } catch (error) {
            console.error('Error buscando productos:', error);
        }
    };
    const debouncedSearch = debounce(buscarProductos, 300);

    // 5. Funciones para manipular los detalles en el formulario (agregar y eliminar productos)
    const agregarProducto = (producto) => {
        // Evitar duplicados
        const existe = form.detalles.find(detalle => detalle.producto_id === producto.id);
        if (existe) {
            existe.cantidad++;
            return;
        }
        // Agregar nuevo producto al formulario
        form.detalles.push({
            // Creamos una estructura similar a 'Detalle_Cotizacion'
            id: null, // No tiene ID de detalle aún
            cotizacion_id: cotizacion.value.id,
            producto_id: producto.id,
            cantidad: 1,
            precio_unitario: producto.precio_normal, // o el precio que corresponda
            subtotal: producto.precio_normal,
            producto: producto // Lo guardamos para tener acceso a sus datos en la UI
        });
        searchQuery.value = '';
        searchResults.value = [];
    };

    const eliminarProducto = (productoId) => {
        form.detalles = form.detalles.filter(detalle => detalle.producto_id !== productoId);
    };

    // Enviar el formulario actualizado al backend
    const submitting = ref(false);

    const submitUpdate = async () => {
        // Evitar reentradas
        if (submitting.value) return;
        submitting.value = true;

        try {
            console.log('Enviando', form.detalles);

            const payload = {
                total: Number(totalEditado.value),
                detalles: form.detalles.map(item => ({
                        producto_id: Number(item.producto_id),
                        cantidad: Number(item.cantidad),
                        precio_unitario: Number(item.precio_unitario),
                }))
            };

            console.log('Payload final ->', payload);

            // --- Usar SOLO Inertia form.put ---
            await form
            .transform(() => payload)
            .put(route('cotizaciones.update', cotizacion.value.id), {
                preserveState: false,
                onStart: () => console.log('Inertia: request iniciada'),
                onSuccess: (page) => {
                    console.log('Inertia success', page);
                    cotizacion.value = JSON.parse(JSON.stringify(page.props.cotizacion)); // Actualiza la cotización con los nuevos datos
                    isEditing.value = false; // Salimos del modo edición
                    form.reset(); // Limpiamos el formulario
                },
                onError: (errors) => {
                    console.error('Errores de validacion de Inertia:', errors);
                },
                onFinish: () => {
                    submitting.value = false; // aseguramos liberar el lock cuando termina
                }
            });

        } catch (err) {
            // Si por alguna razón form.put lanza excepción aquí, lo capturamos
            console.error('submitUpdate error:', err);
            submitting.value = false;
        }
    };
// --- FIN FUNCIONALIDAD DE EDICION ---

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-AR', {
        style: 'currency',
        currency: 'ARS'
    }).format(value);
};
</script>


<template>
    <AppLayout :title="'Cotizacion #' + cotizacion.id ">
        <!-- Page Heading -->
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Cotizacion #{{ cotizacion.id }}
            </h2>
        </template>

        <!-- Page Content -->
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <!-- Edicion de la cotizacion -->
                <!-- BOTONES DE ACCIÓN PRINCIPALES -->
                <div class="mb-4 flex justify-center sm:justify-end gap-2">
                    <button v-if="!isEditing" @click="activarEdicion" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition-transform transform hover:scale-105 w-full sm:w-fit mx-4 sm:mx-0">
                        Editar Cotización
                    </button>
                    <template v-if="isEditing">
                        <button @click="cancelarEdicion" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition-transform transform hover:scale-105">
                            Cancelar
                        </button>
                        <button @click="submitUpdate" :disabled="submitting" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition-transform transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed">
                            Guardar Cambios
                        </button>
                    </template>
                </div>

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <!-- Aqui va basicamente todo mi contenido, puedo crear un componente, y hacer todo lo que hago aquí en un componente aislado/separado, luego importar ese componente y llamarlo justo aquí, es a gusto mío y depende de cuan separadas quiero tener las cosas -->
                    <div>
                        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    
                            <!-- MODO EDICIÓN: Se muestra si isEditing es true -->
                            <div v-if="isEditing" class="animate-fade-in">
                                <h3 class="text-2xl font-bold mb-6 text-gray-800 border-b pb-4">Editando Cotización #{{ cotizacion.id }}</h3>

                                <!-- 1. Lista de productos editables -->
                                <div class="space-y-3 mb-8">
                                    <div v-for="detalle in form.detalles" :key="detalle.producto_id" class="flex items-center flex-col sm:flex-row justify-between p-2 bg-gray-50 rounded-lg border border-gray-200">
                                        <div class="flex flex-col items-start w-full">
                                            <p class="font-semibold text-gray-700">{{ detalle.producto.titulo }}</p>
                                            <p class="text-sm text-gray-500">Precio Unitario: {{ formatCurrency(detalle.precio_unitario) }}</p>
                                        </div>
                                        <div class="flex flex-col sm:flex-row items-center gap-4 mt-2 sm:mt-0">
                                            <div class="flex items-center gap-4">
                                                <label class="flex items-center gap-2 text-sm">
                                                    Cantidad:
                                                    <input type="number" v-model="detalle.cantidad" min="1" class="w-20 text-center border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                                </label>
                                                <label class="flex items-center gap-2 text-sm">
                                                    Precio:
                                                    <input type="number" v-model="detalle.precio_unitario" min="1" class="w-28 text-center border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                                </label>
                                            </div>
                                            <div class="w-full sm:w-auto flex place-content-between gap-4">
                                                <p class="text-right font-medium text-gray-700">Subtotal: ${{ (detalle.precio_unitario * detalle.cantidad).toFixed(2) }}</p>
                                                <button @click="eliminarProducto(detalle.producto_id)" class="text-red-500 hover:text-red-700 transition-transform transform hover:scale-110">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="form.detalles.length === 0" class="text-center py-4 text-gray-500">
                                        La cotización no tiene productos. ¡Agrega uno!
                                    </div>
                                </div>

                                <!-- 2. Buscador para agregar nuevos productos -->
                                <div class="mt-8 border-t pt-6">
                                    <h4 class="text-lg font-semibold mb-2 text-gray-700">Agregar Producto</h4>
                                    <input type="text" v-model="searchQuery" @keyup="debouncedSearch" placeholder="Buscar por código, título..." class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <ul v-if="searchResults.length" class="border rounded-md mt-2 max-h-60 overflow-y-auto bg-white shadow-lg">
                                        <li v-for="producto in searchResults" :key="producto.id" @click="agregarProducto(producto)" class="p-3 hover:bg-indigo-50 cursor-pointer border-b last:border-b-0" :class="{'opacity-50 cursor-not-allowed': producto.stock <= 0}">
                                            {{ producto.titulo }} - 
                                            <span class="text-gray-500">({{ producto.codigo }}) - {{ formatCurrency(producto.precio_normal) }}</span>
                                            <span class="text-white px-1 rounded-sm ml-2" :class="producto.stock > 0 ? 'bg-green-700' : 'bg-red-700'">{{ producto.stock }} und.</span>
                                        </li>
                                    </ul>
                                </div>

                                <!-- 3. Total -->
                                <div class="mt-6 text-center sm:text-right border-t pt-4">
                                    <p class="text-2xl font-bold text-gray-800">Total: ${{ totalEditado.toFixed(2) }}</p>
                                    <!-- Acciones principales abajo -->
                                    <div class="flex justify-center sm:justify-end gap-2 mt-4">
                                        <template v-if="isEditing">
                                            <button @click="cancelarEdicion" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition-transform transform hover:scale-105">
                                                Cancelar
                                            </button>
                                            <button @click="submitUpdate" :disabled="submitting" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition-transform transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed">
                                                Guardar Cambios
                                            </button>
                                        </template>
                                    </div>

                                </div>
                            </div>

                            <!-- MODO VISUALIZACIÓN: Se muestra si isEditing es false -->
                            <div v-else>
                                <div class="flex flex-col xl:flex-row jusitfy-center items-stretch w-full xl:space-x-8 space-y-4 md:space-y-6 xl:space-y-0">
                                    <div class="flex flex-col justify-start items-start w-full space-y-4 md:space-y-6 xl:space-y-8">
                                        <div class="flex flex-col justify-start items-start w-full">
                                                <div class="flex flex-col md:flex-row justify-start items-start md:items-center md:space-x-6 xl:space-x-8 w-full">
                                                    <div class="md:flex-col flex-col flex justify-between items-start w-full space-y-4 md:space-y-6">
                                                        <div class="w-full flex flex-col justify-start items-start space-y-8">
                                                            <!-- Contiene el titulo de la coizacion y el botón para enviarla -->
                                                            <div class="flex flex-col gap-2 w-full">
                                                                <!-- Fecha con el paquete 'dayjs' -->
                                                                <h3 class="text-xl xl:text-2xl font-semibold text-center lg:text-left leading-6 text-gray-800">Nro #{{ cotizacion.id }} | {{ dayjs(cotizacion.created_at).tz("America/Argentina/Buenos_Aires").format("D [de] MMMM [de] YYYY, HH:mm:ss") }}</h3>
                                                                <!-- Boton de enviar cotizacion -->
                                                                <a :href="`https://api.whatsapp.com/send?phone=549${telefonoCliente}&text=${encodeURIComponent(msgWP)}`" target="_blank">
                                                                    <button class="bg-emerald-500 text-white py-2 px-4 rounded-md w-full lg:w-60">
                                                                        <div class="flex flex-row items-center justify-center gap-2">
                                                                            Enviar cotizacion
                                                                            <WhatsappGreenIcon class="w-8 h-8"/>
                                                                        </div>    
                                                                    </button>
                                                                </a>
                                                                <!-- Boton de enviar al sistema -->
                                                                <button 
                                                                    v-on:click="enviarPedidoResponse" 
                                                                    :disabled="loadingFetch" 
                                                                    class="py-2 px-4 rounded-md w-full lg:w-60"
                                                                    :class="{
                                                                        'bg-blue-600 text-white': loadingFetch,
                                                                        'bg-gray-900 text-white': !loadingFetch,
                                                                    }"
                                                                >
                                                                    <div class="flex flex-row items-center justify-center gap-2">
                                                                        <span v-if="!loadingFetch">Enviar a 3C</span>
                                                                        <span v-else>Enviando...</span>
                                                                        <EnviarSistema class="w-8 h-8"/>
                                                                    </div>    
                                                                </button>
                                                                <!-- Mensaje de envio al sistema -->
                                                                <p v-if="responseFetch">
                                                                    {{ responseFetch }}
                                                                </p>
                                                            </div>
                                                            <div class="flex justify-start items-start flex-col space-y-2">
                                                                <p class="text-sm leading-none text-gray-800"><span class="dark:text-gray-400 text-gray-300">Responsable: </span> {{ cotizacion.user.name }}</p>
                                                                <p class="text-sm leading-none text-gray-800"><span class="dark:text-gray-400 text-gray-300">Cliente: </span> {{ cotizacion.cliente.nombre }}</p>
                                                                <select
                                                                    v-model="cotizacion.estado"
                                                                    class="capitalize rounded-md"
                                                                    :disabled="isLoading"
                                                                    :class="{
                                                                        'bg-green-200': cotizacion.estado === 'aprobada',
                                                                        'bg-red-200': cotizacion.estado === 'rechazada',
                                                                        'bg-gray-300': cotizacion.estado === 'sin respuesta',
                                                                        'bg-blue-200': cotizacion.estado === 'pendiente',
                                                                        'bg-yellow-200': cotizacion.estado === 'revision'
                                                                    }"
                                                                >
                                                                    <option
                                                                        v-for="estado in estados"
                                                                        :key="estado"
                                                                        :value="estado"
                                                                        class="capitalize"
                                                                    >
                                                                    {{ estado }}
                                                                    </option>
                                                                </select>
                                                                <p v-if="resultadoEstado" class="text-md text-emerald-500">{{ resultadoEstado }}</p>
                                                            </div>
                                                        </div>
                                                        <div class="flex justify-between items-end lg:items-end space-x-8 w-full">
                                                            <p class="text-base xl:text-lg leading-6">{{ formatCurrency(cotizacion.total) }}</p>
                                                            <p class="text-base xl:text-lg leading-6 text-gray-800 border border-gray-800 rounded-full py-1 px-3">{{ cotizacion.detalles.length }} items</p>
                                                        </div>
                                                        <!-- Detalles de la cotizacion -->
                                                        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 items-stretch">
                                                            <div v-for="detalles in cotizacion.detalles" :key="detalles.id" class="flex flex-col bg-gray-100 shadow-md border border-gray-200 p-4 rounded-lg justify-between items-start lg:items-start space-y-1 w-full">
                                                                <a :href="detalles.producto.link_producto" target="_blank" class="w-full">
                                                                    <p class="flex justify-between items-start space-x-8 w-full gap-4 text-sm leading-none text-gray-800 pb-4">
                                                                        {{ detalles.producto.titulo }}
                                                                        <span>
                                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                                                                <polyline points="15 3 21 3 21 9"></polyline>
                                                                                <line x1="10" y1="14" x2="21" y2="3"></line>
                                                                            </svg>
                                                                        </span>
                                                                    </p>
                                                                </a>
                                                                <p class="text-sm leading-none text-gray-800"><span class="dark:text-gray-400 text-gray-300">Codigo: </span> {{ detalles.producto.codigo }}</p>
                                                                <p class="text-sm leading-none text-gray-800"><span class="dark:text-gray-400 text-gray-300">Detalles: </span>{{ formatCurrency(detalles.precio_unitario) }} x {{ detalles.cantidad }} und</p>
                                                                <p class="text-sm leading-none text-gray-800"><span class="dark:text-gray-400 text-gray-300">Subtotal: </span>{{ formatCurrency(detalles.subtotal) }}</p>
                                                            </div>
                                                        </div>

                                                        <!-- Mensaje y teléfono -->
                                                        <div class="space-y-2 w-full">
                                                            <!-- Telefono del cliente -->
                                                            <h3 class="text-md text-gray-500 text-center lg:text-left">Telefono de {{ cotizacion.cliente.nombre }}. Ej: 3518019558</h3>
                                                            <div class="mt-1 mb-6 flex flex-row gap-2 rounded-lg">

                                                                <input v-model="telefonoCliente" type="text" class="w-full rounded-md border-gray-200">
                                                                <button class="w-fit px-6 bg-blue-500 rounded-md text-white" @click="actNumero">Actualizar</button>
                                                            </div>
                                                            <p v-if="resultado" class="text-emerald-500">{{ resultado }}</p>
                                                            <!-- Vista previa del mensaje -->
                                                            <h3 class="text-md text-gray-500 text-center lg:text-left">Vista previa del mensaje:</h3>
                                                            <!-- <pre class="bg-emerald-500 text-white p-4 rounded-lg responsive-pre">{{ msgWP }}</pre> -->
                                                            <textarea v-model="msgWP" class="bg-emerald-500 text-white p-4 rounded-lg w-full h-[300px]"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
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

<style scoped>
.responsive-pre {
    white-space: pre-wrap; /* Envuelve el texto en dispositivos móviles */
    word-break: break-word; /* Divide palabras largas si es necesario */
}

@media (min-width: 768px) {
    .responsive-pre {
        white-space: pre; /* Restaura el comportamiento predeterminado de <pre> */
        overflow-x: auto; /* Añade scroll horizontal en pantallas más grandes */
    }
}

/* Estilos del textarea */
textarea {
  /* Deshabilitar redimensionamiento */
  resize: none;

  /* Permitir scroll solo cuando sea necesario (sin mostrar barras) */
  overflow: auto;

  /* Ocultar scrollbars en Firefox */
  scrollbar-width: none;

  /* Ocultar scrollbars en IE/Edge */
  -ms-overflow-style: none;
}

/* Ocultar scrollbars en Chrome, Safari y otros navegadores basados en WebKit */
textarea::-webkit-scrollbar {
  width: 0;  /* Ancho del scrollbar horizontal */
  height: 0; /* Alto del scrollbar vertical */
}

/* Animación sutil para la aparición del formulario */
.animate-fade-in {
  animation: fadeIn 0.5s ease-in-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>