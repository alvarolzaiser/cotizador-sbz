<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

const clientes = ref(usePage().props.clientes) // Por la url envío la prop 'clientes' y aquí guardo su contenido en la constante 'clientes'

// Crear un array reactivo para manejar los clientes localmente
// 'localPosts' es un array reactivo que copia los posts recibidos como props y agrega una propiedad 'isEditing' para controlar el estado de edición.
const localClientes = ref(
    (clientes.value).map(cliente => ({ 
        ...cliente, // datos originales del cliente
        isEditing: false, // se agrega la propiedad 'isEditing'. 
        // Inicialmente está en 'false', y si se hace click en editar, se llama a la funcion 'startEditing' que cambia el valor a 'true' y muestra el cliente en una vista de edicion
    }))
);
// cambia el valor a 'true' de la nueva propiedad 'isEditing', para así mostrar el cliente como editable
function startEditing (cliente) {
    cliente.isEditing = true;
}
// vuelve la propiedad de 'isEditing' a 'false' y restaura los datos como estaban antes de editarse
function cancelEditing (cliente) {
    cliente.isEditing = false;
    const clienteOriginal = (clientes.value).find(cl => cl.id === cliente.id);
    cliente.nombre = clienteOriginal.nombre;
    cliente.email = clienteOriginal.email;
    cliente.telefono = clienteOriginal.telefono;
    cliente.direccion = clienteOriginal.direccion;
}

const saveCliente = async(cliente) => {
    try {
        const response = await axios.put(`clientes/${cliente.id}`, {
            nombre: cliente.nombre ? cliente.nombre : 'Sin nombre',
            email: cliente.email ? cliente.email : 'Sin email',
            telefono: cliente.telefono ? cliente.telefono : 'Sin telefono',
            direccion: cliente.direccion ? cliente.direccion : 'Sin direccion',
        });
        cliente.isEditing = false; // Cambia el estado a no editable
        console.log(response.data.message);
        
        // El servidor devuelve el cliente actualizado en 'response.data.cliente'
        const clienteActualizado = response.data.cliente;
        // Encontrar el índice del cliente actualizado en el array 'localClientes'
        const indexCliente = localClientes.value.findIndex(cl => cl.id === cliente.id);
                                                // El método 'findIndex' busca en el array 'localClientes' el índice del elemento que cumple con una condición dada (por ejemplo, que el id de un cliente coincida con el id del cliente buscado en el array 'localClientes' => cl.id === cliente.id)
                                                // Si 'findIndex' encuentra que el id del cliente que enviamos como argumento de esta funcion coincide con el id de un cliente en el array 'localClientes', entonces devuelve el índice que tiene ese elemento en el array 'localClientes' (un número entero como 0, 1, 2, etc.)
                                                // Si no encuentra ningún elemento que cumpla la condición, devuelve -1

        if (indexCliente != -1) {
            localClientes.value[indexCliente] = {
                ...clienteActualizado,
                isEditing: false,
            };
        };
    } catch (error) {
        if (error.response && error.response.status === 422) {
            console.error('Errores de validación:', error.response.data.errors);
            // Aquí podrías mostrar los errores en la interfaz
        } else {
            console.error('Error al actualizar el cliente:', error);
        }
    }
}
</script>


<template>
    <AppLayout title="Clientes">
        <!-- Page Heading -->
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Clientes
            </h2>
        </template>

        
        <!-- Page Content -->
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <!-- Aqui va basicamente todo mi contenido, puedo crear un componente, y hacer todo lo que hago aquí en un componente aislado/separado, luego importar ese componente y llamarlo justo aquí, es a gusto mío y depende de cuan separadas quiero tener las cosas -->
                    <div>
                        <div class="bg-white border-b border-gray-200">
                            <!-- {{ clientes }} -->
                            <div class="text-gray-900 bg-gray-200 rounded-lg">
                                <div class="flex justify-center">
                                    <!-- Contenedor para desplazamiento horizontal -->
                                    <div class="w-full overflow-x-auto">
                                        <table class="w-full text-md bg-white shadow-md rounded">
                                            <tbody>
                                                <tr class="border-b">
                                                    <th class="text-left p-3 px-5">Nombre</th>
                                                    <th class="text-left p-3 px-5">Email</th>
                                                    <th class="text-left p-3 px-5">Telefono</th>
                                                    <th class="text-left p-3 px-5">Dirección</th>
                                                    <th></th>
                                                </tr>
                                                <tr v-if="localClientes.length > 0" v-for="cliente in localClientes" :key="cliente.id" class="border-b hover:bg-orange-100 bg-gray-100">
                                                    <td class="p-3 px-5">
                                                        <input v-if="cliente.isEditing" type="text" v-model="cliente.nombre" class="bg-transparent sm:rounded-lg">
                                                        <p v-else class="bg-transparent sm:rounded-lg">{{ cliente.nombre }}</p>
                                                    </td>
                                                    <td class="p-3 px-5">
                                                        <input v-if="cliente.isEditing" type="text" v-model="cliente.email" class="bg-transparent sm:rounded-lg">
                                                        <p v-else class="bg-transparent sm:rounded-lg">{{ cliente.email }}</p>
                                                    </td>
                                                    <td class="p-3 px-5">
                                                        <input v-if="cliente.isEditing" type="text" v-model="cliente.telefono" class="bg-transparent sm:rounded-lg">
                                                        <p v-else class="bg-transparent sm:rounded-lg">{{ cliente.telefono }}</p>
                                                    </td>
                                                    <td class="p-3 px-5">
                                                        <input v-if="cliente.isEditing" type="text" v-model="cliente.direccion" class="bg-transparent sm:rounded-lg">
                                                        <p v-else class="bg-transparent sm:rounded-lg">{{ cliente.direccion }}</p>
                                                    </td>
                                                    <td class="p-3 px-5 flex justify-end">
                                                        <button v-if="cliente.isEditing" @click="saveCliente(cliente)" type="button" class="mr-3 text-sm bg-blue-500 hover:bg-blue-700 text-white py-1 px-2 rounded focus:outline-none focus:shadow-outline">Guardar</button>
                                                        <button v-if="cliente.isEditing" @click="cancelEditing(cliente)" type="button" class="text-sm bg-red-500 hover:bg-red-700 text-white py-1 px-2 rounded focus:outline-none focus:shadow-outline">Cancelar</button>
                                                        <button v-else @click="startEditing(cliente)" type="button" class="mr-3 text-sm bg-blue-500 hover:bg-blue-700 text-white py-1 px-2 rounded focus:outline-none focus:shadow-outline">Editar</button>
                                                    </td>
                                                </tr>
                                                <tr v-else class="text-gray-500 p-10 flex flex-col justify-center">
                                                    <!-- Texto en caso que no haya elementos -->
                                                    <p class="text-center text-lg">Aun no hay clientes. Realiza una cotizacion <Link :href="route('dashboard')" class="text-blue-500 font-bold">aquí</Link> para crearlos</p>
                                                </tr>
                                            </tbody>
                                        </table>
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