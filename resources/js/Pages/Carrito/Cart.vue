<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios'; // Antes de importar AXIOS, asegurate de tenerlo instalado ``npm install axios``
import { ref, computed, onMounted } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import { useCartStore } from '@/stores/cart';
import { debounce } from 'lodash'; // ``Lodash`` tiene una función 'debounce' muy útil. Puedes instalarla (npm install lodash) y luego importarla

// Rescato los clientes de lo que me llegan por la URL
const clientes = ref(usePage().props.clientes);

const cartStore = useCartStore();
const enviarCliente = ref(true); // Defino si se envía el cliente o no
const selectedCliente = ref(null); // Guardo el ID del cliente seleccionado en el radio
// Cuando el usuario selecciona un cliente, el valor de 'selectedCliente' será el 'id' de ese cliente, y al seleccionar uno nuevo se deseleccionará el anterior
const clienteSeleccionado = computed(() => {
    return clientes.value.find(cl => cl.id === selectedCliente.value); // Retorno los datos del cliente cuyo ID fue seleccionado (agarro el id que guarda selectedCliente y busco en el array de "clientes" el cliente que tenga ese ID y retorno sus datos)
})

const crearCliente = ref(false); // En caso que se quiera enviar el cliente, pero no exista, se puede crear, esta constante guarda la referencia de lo que selecciona el cliente (true: crear | false: NO crear)
// Formulario de creación de cliente
const nombre = ref(null);
const telefono = ref(null);
const email = ref(null);
const direccion = ref(null);

// Almaceno el error en esta variable, en caso que el cliente no haya seleccionado un cliente
const errorCliente = ref('');
const resultado = ref(null);
const cotizacion = ref(null);
const telefonoCliente = ref('');

// Almaceno el mensaje de whatsapp aqui
const msgWP = ref('');

// Guardo o almaceno en la constante 'searchQuery' lo que se desea buscar
const searchQuery = ref('');
// Funcion de buscador
async function search() {
    try {
        if(searchQuery.value) {
            const response = await axios.get(route('clientes.search'), {
                // 'params: {}' me permite enviar parametros por la URL, en este caso envio el parametro con la key "search" y va a tener como valor el 'value' del objeto reactivo 'searchQuery'
                params: {
                    search: searchQuery.value
                }
            });
            clientes.value = response.data.results;
            // console.log(response.data);
        } else {
            // Si 'searchQuery' esta vacío, productos.value va a ser igual a TODOS los nuematicos
            clientes.value = usePage().props.clientes;
        }
    } catch(err) {
        console.log(err);
    }
}
// Funcion de buscador con 'debounce'. 
// Detecta que el usuario deja de escribir (porque el valor de searchQuery se mantiene igual durante 500ms), y en tal caso, que 'searchQuery' no cambie por 500ms, entonces allí ejecuta la funcion de 'search()'. Evitando peticiones innecesarias o solapadas
const debouncedSearch = debounce(async () => {
    search();
}, 500); // 500 ms sin escribir

// Funcion que finaliza la cotización
const finalizarCotizacion = async () => {
    try {
        errorCliente.value = '';

        const response = await axios.post(route('cotizaciones.store'), {
   
            carrito: cartStore.items,
            enviar_cliente: enviarCliente.value,
            cliente_seleccionado: selectedCliente.value,

            crear_cliente: crearCliente.value,

            nombre: nombre.value ? nombre.value : 'Ejemplo',
            telefono: telefono.value ? telefono.value : '3518019558',
            email: email.value ? email.value : 'ejemplo@ejemplo.com',
            direccion: direccion.value ? direccion.value : 'Ejemplo',
            user_id: usePage().props.auth.user.id,

            total: total.value,

        });

        resultado.value = response.data.resultado;
        cotizacion.value = response.data.cotizacion;

        // Reiniciamos el carrito
        cartStore.items = [];
        // Sincronizamos el carrio para pasarle a 'localStorage' que el carrito está vacío
        cartStore.syncStorage()

        // Guardo en el objeto reactivo 'telefonoCliente', el numero al cual se va enviar la cotización
        telefonoCliente.value = cotizacion.value.cliente.telefono;

        // Ahora 'msgWP' va a tener como valor el retorno de la funcion 'msgWPF' de mi store 'useCartStore'. La función 'msgWPF' se encarga de componer, crear y fromatear el mensaje de wpp tomando como base la "cotizacion" que se envía como argumento
        msgWP.value = await cartStore.msgWPF(cotizacion);
    } catch (err) {
        errorCliente.value = err.response.data.error;
        console.log(err.response.data.error);
    }
}

// Cuando se monte la pag., chequeo que los precios de los productos en el carrito coincidan con los precios de los productos
async function updateProductos() {
    const productosActualizados = await axios.get(route('productos.productosActualizados'));

    // Guardo en la constante "carrito" los productos que tengo en mi state "items" (propiedad de mi "store" que almacena los productos de mi carrito)
    const carrito = cartStore.items;
    carrito.forEach(item => {
        const id = item.id;
        const priceType = item.priceType;
        const precio = item.precio;

        // Busco el producto que está en el carrito dentro del array de productos y lo guardo en una constante "producto"
        const producto = productosActualizados.data.productos_actualizados.find(producto => producto.id === id);
        if(precio !== producto[priceType]) {
            item.precio = producto[priceType];
        }
    })

    // Sincronizamos los nuevos valores del state "items" en localStorage, para que todo quede guardado, sin importar la recarga de página
    cartStore.syncStorage();
}
onMounted( async() => {
    try {
        await updateProductos();
    } catch(err) {
        console.error(err);
    }
});

const total = computed(() => {
    return cartStore.items.reduce((sum, item) => sum + (item.precio * item.quantity), 0);
    // ".reduce()" es un método de los arreglos en JavaScript que permite acumular (o reducir) los elementos de un arreglo a un solo valor, en este caso, el total del carrito.
    // sum: Este es el valor acumulado que se va actualizando en cada iteración del arreglo. Comienza con el valor inicial de 0 (el segundo parámetro de reduce).
    // item: Este es el elemento actual del arreglo que se está procesando. En este caso, cada item es un producto en el carrito
    // Para cada item en el arreglo "cartStore.items", la función hace lo siguiente: 1) item.precio * item.quantity: Multiplica el precio del producto por la cantidad del producto en el carrito, lo que da el subtotal para ese producto (es decir, el total por ese producto específico). 2) sum + (item.precio * item.quantity): Suma ese subtotal al acumulador sum. Esto acumula el total de todos los productos a medida que se recorre el arreglo. Al final de la iteración de todos los elementos, sum contendrá el total acumulado de todos los productos en el carrito.
});
</script>

<template>
    <AppLayout title="Carrito">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Carrito
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    
                    
                    
                    <div v-if="cartStore.items.length > 0" class="pt-2 px-6 pb-6">
                        <div v-for="item in cartStore.items" :key="item.id" class="flex justify-between py-4 border-b">
                            <div>
                                <p class="font-bold">{{ item.nombre }}</p>
                                <p>Código: {{ item.codigo }}</p>
                                <p>Cantidad: 
                                    <button @click="cartStore.updateQuantity(item.id, item.quantity - 1)" class="mr-2">-</button>
                                    {{ item.quantity }}
                                    <button @click="cartStore.updateQuantity(item.id, item.quantity + 1)" class="ml-2">+</button>
                                </p>
                            </div>
                            <div class="w-56">
                                <p>Precio unitario: ${{ item.precio }}</p>
                                <p>Subtotal: ${{ item.precio * item.quantity }}</p>
                                <p :class="item.priceType == 'precio_normal' ? 'bg-blue-200 px-1 rounded-sm w-fit' : item.priceType == 'precio_mayorista' ? 'bg-green-200 px-1 rounded-sm w-fit' : 'bg-yellow-200 px-1 rounded-sm w-fit'">{{ item.priceType == 'precio_normal' ? 'NORMAL' : item.priceType == 'precio_mayorista' ? 'MAYORISTA' : 'EFECTIVO' }}</p>
                                <button @click="cartStore.removeItem(item.id)" class="text-red-500 mt-2">Eliminar</button>
                            </div>
                        </div>
                        <!-- Seccion cliente -->
                        <div class="mt-6">
                            <label for="enviar_cliente" class="flex flex-row items-center gap-2">
                                <input type="checkbox" class="rounded-full w-5 h-5" v-model="enviarCliente" id="enviar_cliente">
                                <span class="text-xl">Enviar cliente</span>
                            </label>
                            <div v-if="enviarCliente" class="mt-2 space-y-2">
                                <h3 v-if="!crearCliente">Seleccionar cliente: <span v-if="selectedCliente" class="bg-blue-500 text-white rounded-sm px-1">{{ clienteSeleccionado.nombre }}</span></h3>
                                <!-- Buscador -->
                                <div v-if="!crearCliente" class="flex flex-row gap-2 rounded-lg">
                                    <input v-on:keyup="debouncedSearch" v-on:keyup.enter="search" type="text" v-model="searchQuery" class="w-full rounded-md border-gray-200" placeholder="Buscar cliente...">
                                    <button @click="search" class="w-fit px-6 bg-blue-500 rounded-md text-white hidden lg:block">Buscar</button>
                                </div>
                                <!-- Mensaje de error si no se selecciona cliente -->
                                <div v-if="errorCliente" class="text-red-500">
                                    {{ errorCliente }}
                                </div>
                                <!-- Lista de clientes -->
                                <div v-if="!crearCliente" class="h-60 overflow-y-scroll rounded-md border border-gray-200">
                                    <div v-if="clientes.length > 0" v-for="cliente in clientes" :key="cliente.id">
                                        <label :for="`radio_${cliente.id}`" class="w-fit p-2 flex flex-row gap-2 items-center border-b-red-500">
                                            <input type="radio" :value="cliente.id" v-model="selectedCliente" class="rounded-full" :id="`radio_${cliente.id}`">
                                            <!-- Los radios están diseñados para que solo se pueda seleccionar uno a la vez, ya que comparten el mismo v-model. -->
                                            <h3 class="border-r-2 border-blue-500 pr-2">{{ cliente.nombre }}</h3>
                                            <h3 class="border-r-none lg:border-r-2 lg:border-blue-500 pr-2">{{ cliente.telefono }}</h3>
                                            <h3 class="hidden lg:block">{{ cliente.email }}</h3>
                                        </label>
                                    </div>
                                    <!-- Mensaje en caso que no haya ningun cliente -->
                                    <div v-else>
                                        <h3 class="text-red-500 text-center p-4">No hay clientes para mostrar</h3>
                                    </div>
                                </div>
                                <!-- Crear cliente -->
                                <div>
                                    <label for="crear_cliente" class="flex flex-row items-center gap-2">
                                        <input type="checkbox" class="rounded-full w-5 h-5" v-model="crearCliente" id="crear_cliente">
                                        <span class="text-xl">Crear cliente</span>
                                    </label>
                                    <div v-if="crearCliente" class="mt-2 space-y-2">
                                        <div class="flex flex-row gap-4">
                                            <!-- Nombre -->
                                            <label for="name" class="w-full">
                                                Nombre
                                                <input class="w-full rounded-md border-gray-200" type="text" id="name" v-model="nombre">
                                            </label>
                                            <!-- Telefono -->
                                            <label for="phone" class="w-full">
                                                Telefono
                                                <input class="w-full rounded-md border-gray-200" type="text" id="phone" v-model="telefono">
                                            </label>
                                        </div>
                                        <div class="flex flex-row gap-4">
                                            <label for="email" class="w-full">
                                                Email
                                                <input class="w-full rounded-md border-gray-200" type="text" id="email" v-model="email">
                                            </label>
                                            <label for="address" class="w-full">
                                                Direccion
                                                <input class="w-full rounded-md border-gray-200" type="text" id="address" v-model="direccion">
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Boton de Finalizar Compra y Total -->
                        <div class="mt-4">
                            <p class="text-xl font-bold">Total: ${{ total }}</p>
                            <button @click="finalizarCotizacion" class="bg-green-500 text-white px-4 py-2 rounded mt-4">
                                Finalizar Cotización
                            </button>
                        </div>
                    </div>
                    <div v-else class="text-gray-500 p-10 flex flex-col justify-center">
                        <!-- Mostramos 'carrito vacío' si NO existe el obj reactivo 'cotizacion' -->
                        <p v-if="!cotizacion" class="text-center">El carrito está vacío.</p>
                        <!-- Mensaje de cotizacion exitosa -->
                        <div v-if="cotizacion" class="flex flex-col gap-2 justify-center">
                            <h3 class="text-emerald-500 text-xl">{{ resultado }}</h3>
                            <p class="text-black">Total: ${{ cotizacion.total }}</p>
                            <Link :href="route('cotizaciones.show', cotizacion.id)">
                                <button class="bg-blue-500 text-white py-2 px-4 rounded-md w-full lg:w-60">Ver cotizacion</button>
                            </Link>
                            <!-- Boton de enviar cotizacion -->
                            <a :href="`https://api.whatsapp.com/send?phone=549${telefonoCliente}&text=${encodeURIComponent(msgWP)}`" target="_blank">
                                <button class="bg-emerald-500 text-white py-2 px-4 rounded-md w-full lg:w-60">
                                        Enviar cotizacion
                                </button>
                            </a>
                            <!-- Mensaje y teléfono -->
                            <div class="space-y-2 w-full mt-4">
                                <!-- Telefono del cliente -->
                                <h3 class="text-md text-gray-500 text-center lg:text-left">Telefono de {{ cotizacion.cliente.nombre }}. Ej: 3518019558</h3>
                                <input v-model="telefonoCliente" type="text" class="w-full rounded-md border-gray-200">
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
</style>