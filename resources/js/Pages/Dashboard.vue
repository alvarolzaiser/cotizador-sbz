<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import EyeIcon from '@/Components/EyeIcon.vue';
import axios from 'axios'; // Antes de importar AXIOS, asegurate de tenerlo instalado ``npm install axios``
import { ref, watch } from 'vue';
import { useCartStore } from '@/stores/cart';
import { usePage, Link } from '@inertiajs/vue3';
import { debounce } from 'lodash'; // ``Lodash`` tiene una función 'debounce' muy útil. Puedes instalarla (npm install lodash) y luego importarla

const cartStore = useCartStore();
const productos = ref(usePage().props.productos);
const quantity = ref({}); // Objeto vacío
const selectedPriceType = ref();
// Constante 'message' que va a almacenar el mensaje que me va a enviar servidor como resultado de la llamada al endpoint o ruta con name => 'productos.actualizarProductos'
const message = ref();
const loading = ref(false); // Usado para el loader de la funcion 'actualizarProductos'

// Constante que va a almacenar lo que se escriba en el buscador
const searchQuery = ref('');

// Rellenamos los objetos de "quantity" con "1". Cada producto tiene su ID, por cada producto dentro del objeto o array "productos", voy a crear dentro del objeto "quantity" un "1" con el indice del id del producto
(productos.value).forEach(product => {
    quantity.value[product.id] = 1;
});
// Hacemos que por defecto, se seleccione "precio_normal"
selectedPriceType.value = 'precio_normal';

watch(quantity, (newVal) => {
    Object.keys(newVal).forEach(id => {
        if (isNaN(newVal[id]) || newVal[id] < 0) {
            quantity.value[id] = 0;
        }
    });
});

function addToCart(product) {
    const qty = quantity.value[product.id] || 0;
    const priceType = selectedPriceType.value;

    if (qty > 0) {
        cartStore.addItem(product, qty, priceType);
        quantity.value[product.id] = 0; // Reiniciar input
    }
}

// Funcion asíncronca que llama al endpoint de '/actualizar-productos'
async function actualizarProductos() {
    loading.value = true;
    try {
        const response = await axios.post(route('productos.actualizarProductos')); // la ruta con name => 'productos.actualizarProductos' es mi endpoint. Es la URL o ruta donde hago la petición de tipo POST por medio de axios
        // En caso que la response sea 'success' entonces 'message' va a ser igual al "message" que viene de la respuesta de la peticion al enpoint o ruta con nombre 'productos.actualizarProductos'
        message.value = response.data.message;

        // Hacemos una nueva petición para obtener los productos actualizados
        const productosActualizados = await axios.get(route('productos.productosActualizados')); // la ruta con name => 'productos.productosActualizados' es mi endpoint. Es la URL o ruta donde hago la petición de tipo GET por medio de axios
        // Actualizo el objeto reactivo "productos" y le digo que ahora contenga los productos actualizados que obtuve por la petición de tipo get al endpoint o ruta con nombre 'productos.productosActualizados' (de esta manera se ven reflejadas las actualizaciones en el bucle de productos, sin necesidad de recargar la pagina para verlo)
        productos.value = productosActualizados.data.productos_actualizados;

        // Guardo en la constante "carrito" los productos que tengo en mi state "items" (propiedad de mi "store" que almacena los productos de mi carrito)
        const carrito = cartStore.items;

        // Actualizamos los datos de los productos en el carrito (en caso de haber sufrido una modificacion producto de la actualización)
        // Recorro cada producto en el carrito, y chequeo si el precio que tiene coincide con el precio actualizado del producto dentro del objeto reactivo "productos". Si no coincide, actualizo el precio del carrito
        carrito.forEach( item => {
            const id = item.id;
            const precio = item.precio;
            const priceType = item.priceType;

            // Busco el producto que está en el carrito dentro del array de productos
            const producto = (productos.value).find(producto => producto.id === id);
            // Chequeo que el precio del producto del carrito esté desactualizado, y si se cumple esa condición, entonces actualizo el precio
            if(producto && precio !== producto[priceType]) {
                item.precio = producto[priceType];
            }
        })

        // Sincronizamos los nuevos valores del state "items" en localStorage, para que todo quede guardado, sin importar la recarga de página
        cartStore.syncStorage();
    } catch(err) {
        // En caso que no se complete el 'try' entonces 'message' va a ser igual a 'Ocurrió un error al intentar actualizar los productos'
        message.value = '¡¡¡Ocurrió un error al intentar actualizar los productos!!!';
        console.error(err);
    } finally {
        loading.value = false;
    }
}


// Funcion de buscador
async function search() {
    try {
        if(searchQuery.value) {
            const response = await axios.get(route('productos.search'), {
                // 'params: {}' me permite enviar parametros por la URL, en este caso envio el parametro con la key "search" y va a tener como valor el 'value' del objeto reactivo 'searchQuery'
                params: {
                    search: searchQuery.value
                }
            });
            productos.value = response.data.results;
            // console.log(response.data);
        } else {
            // Si 'searchQuery' esta vacío, productos.value va a ser igual a TODOS los nuematicos
            productos.value = usePage().props.productos;
        }
    } catch(err) {
        console.log(err);
    }
}


// Funcion de buscador con 'debounce'. 
// Detecta que el usuario deja de escribir (porque el valor de searchQuery se mantiene igual durante 500ms), y en tal caso, que 'searchQuery' no cambie por 500ms, entonces allí ejecuta la funcion de 'search()'. Evitando peticiones innecesarias o solapadas
const debouncedSearch = debounce(async () => {
    await search();
}, 200); // 200 ms sin escribir

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-AR', {
        style: 'currency',
        currency: 'ARS'
    }).format(value);
};
</script>

<template>
    <AppLayout title="Productos">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Productos
            </h2>
        </template>


        <!-- Barra de búsqueda sticky fuera del contenedor principal -->
        <div class="sticky top-0 bg-white z-50 shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="py-4">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-lg lg:text-xl">Buscá tus productos ({{ productos.length }} {{ searchQuery ? 'encontrado/s' : 'en total' }})</h2>
                        <!-- Carrito sticky -->
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
                    </div>
                    <!-- Buscador -->
                    <div class="flex flex-row gap-2 rounded-lg">
                        <!-- Cuando se presiona enter => llamo a 'search()'. Al escribirse y dejar de hacerlo por 500ms => llamo a 'debounceSearch()' -->
                        <input v-on:keyup="debouncedSearch" v-on:keyup.enter="search" type="text" v-model="searchQuery" class="w-full rounded-md" placeholder="Busca tu producto...">
                        <button class="w-fit px-6 bg-blue-500 rounded-md text-white hidden lg:block" @click="search">Buscar</button>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                        
                        
                        

                        <!-- Actualizar datos / Listas de precios -->
                        <div class="mb-4">
                            <div class="flex flex-row gap-4 mb-2 items-center justify-between lg:justify-normal">
                                <select class="border py-1 rounded-md" v-model="selectedPriceType">
                                    <option value="precio_normal">Precio normal</option>
                                    <option value="precio_mayorista">Precio mayorista</option>
                                </select>
                                <!-- Boton de actualizacion -->
                                <button
                                    v-if="!loading"
                                    @click="actualizarProductos()"
                                    class="bg-emerald-500 text-white py-1 px-3 rounded-md"
                                >
                                    Actualizar datos
                                </button>
                                <p v-if="loading" class="text-emerald-500">
                                    Actualizando datos, espere por favor...
                                </p>
                            </div>
                            <!-- Mostrar el mensaje que resulta de la peticion para actualizar productos -->
                            <p v-if="message && !loading" class="text-yellow-600">
                                {{ message }}
                            </p>
                        </div>
                        <!-- Archive de productos -->
                        <div v-if="productos.length > 0" v-for="product in productos" :key="product.id" class="mb-4 p-4 bg-gray-100 rounded shadow">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                                <div class="flex flex-row gap-4">
                                    <!-- Imagen -->
                                    <div v-if="product.image_url" class="bg-white rounded-md border border-gray-300 p-2">
                                        <img :src="product.image_url" class="w-[100px] h-[100%] max-h-[100px] object-contain rounded-md">
                                    </div>
                                    <!-- Otros datos -->
                                    <div>
                                        <p class="font-bold flex items-start gap-2">
                                            {{ product.titulo }} 
                                            <a v-if="product.link_producto" :href="product.link_producto" target="_blank" class="text-blue-600 rounded-md">
                                                <button>
                                                    <EyeIcon class="w-6 h-6"/>
                                                </button>
                                            </a>
                                        </p>
                                        <!-- Atributos | Stock | Codigo -->
                                        <div class="flex flex-row items-start gap-2 my-2">
                                            <!-- Atributos (en caso de NO '!' ser un array, muestro los atributos) -->
                                            <div v-if="!Array.isArray(product.atributos)" class="flex flex-row gap-2">
                                                <p v-for="atributo in product.atributos" :key="atributo.slug" class="bg-slate-600 text-white px-1 rounded-sm">
                                                    {{ atributo.value }}
                                                </p>
                                            </div>
                                            <span :class="{
                                                'bg-green-700 text-white px-1 rounded-sm': product.stock > 0,
                                                'bg-red-700 text-white px-1 rounded-sm': product.stock <= 0
                                            }">
                                                {{ product.stock }}
                                            </span>
                                            <p>SKU: {{ product.codigo }}</p>
                                        </div>
                                        <div>
                                            <!-- Tipo de precio -->
                                            <p class="my-2">Precio: {{ formatCurrency(product[selectedPriceType]) }} <span :class="selectedPriceType == 'precio_normal' ? 'bg-blue-200 px-1 rounded-sm lg:ml-1' : selectedPriceType == 'precio_mayorista' ? 'bg-green-200 px-1 rounded-sm lg:ml-1' : 'bg-yellow-200 px-1 rounded-sm lg:ml-1'">{{ selectedPriceType == 'precio_normal' ? 'NORMAL' : selectedPriceType == 'precio_mayorista' ? 'MAYORISTA' : 'DESCONOCIDO' }}</span></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <input 
                                        type="number" 
                                        v-model.number="quantity[product.id]" 
                                        min="0" 
                                        :max="product.stock"
                                        class="w-16 border p-2 mr-2 text-center rounded-md"
                                        :disabled="quantity[product.id] <= 0 || product.stock <= 0"
                                        :class="{ 'opacity-50 cursor-not-allowed': quantity[product.id] <= 0 || product.stock <= 0 }"
                                    >
                                    <button 
                                        @click="addToCart(product)" 
                                        :disabled="quantity[product.id] <= 0 || product.stock <= 0 || quantity[product.id] > product.stock"
                                        class="bg-blue-500 text-white px-4 py-2 rounded"
                                        :class="{ 'opacity-50 cursor-not-allowed': quantity[product.id] <= 0 || product.stock <= 0 || quantity[product.id] > product.stock }"
                                    >
                                        Agregar
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- Mensaje si no hay productos -->
                        <div v-else>
                            <h2 class="text-red-500">No se encontraron productos que coincidan con tu busqueda</h2>
                        </div>



                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>