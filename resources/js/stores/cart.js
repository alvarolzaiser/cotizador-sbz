// resources/js/stores/cart.js
import { defineStore } from 'pinia';

export const useCartStore = defineStore('cart', {
    state: () => ({
        items: JSON.parse(localStorage.getItem('cart')) || [],
    }),
    actions: {
        addItem(product, quantity, priceType = 'precio_normal') {
            // Validar que el precio seleccionado exista
            if (!product[priceType]) {
                alert('Precio no válido');
                return;
            }

            const existingItem = this.items.find(item => item.id === product.id);

            if (existingItem) {
                existingItem.quantity += quantity;
                // Actualizamos el tipo de precio y el precio del producto en base al tipo seleccionado
                existingItem.precio = product[priceType];
                existingItem.priceType = priceType;
            } else {
                this.items.push({
                    id: product.id,
                    codigo: product.codigo,
                    nombre: product.titulo, 
                    precio: product[priceType],
                    priceType: priceType,
                    quantity: quantity,
                });
            }

            this.syncStorage(); // Sincronizar con localStorage
        },
        removeItem(productId) {
            this.items = this.items.filter(item => item.id !== productId);
            this.syncStorage();
        },
        updateQuantity(productId, newQuantity) {
            const item = this.items.find(item => item.id === productId);
            if (item) {
                item.quantity = Math.max(newQuantity, 1); // Evitar cantidades negativas
                this.syncStorage();
            }
        },
        syncStorage() {
            localStorage.setItem('cart', JSON.stringify(this.items));
        },
        async msgWPF (cotizacion, incluirTotal = true) {
            // Si cotizacion.value está en null, no debe hacer NADA
            if (!cotizacion.value) return;
            
            // Formateador 'nf' para precios
            const nf = new Intl.NumberFormat('es-AR', {
                style: 'currency',
                currency: 'ARS',
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            });

            // Genero el enlace para finalizar la compra en la web
            // Ejemplo: https://golosinasplin.com.ar/?add=SKU1,SKU2:3,SKU3 (SKU[:qty])
            // Donde SKU es el código del producto y qty es la cantidad (opcional, si no se pone se asume 1)
            const enlace = this.generarEnlaceWPF(cotizacion);

            // Construyo los detalles de la cotización
            const detalles = cotizacion.value.detalles.map(detalle => { // El método 'map()' => Transforma cada elemento del array 'cotizacion.value.detalles' en un string formateado.
                const { titulo, codigo, link_producto } = detalle.producto;
                
                return (
                    `Producto: ${titulo} - ${codigo}\n` +
                    (link_producto != null && link_producto !== 'null' && link_producto.trim() !== ''
                        ? `- Link WEB: ${link_producto}\n`  
                        : `- Link WEB: No disponible\n`  ) +
                    `- Precio de Lista: ${nf.format(detalle.precio_unitario)}\n` +
                    // `- Precio Efectivo: ${nf.format(detalle.precio_unitario * (1-0.10))}\n` +
                    `- Cantidad: ${detalle.cantidad}\n` +
                    (detalle.cantidad > 1 
                        ? `*Subtotal: ${nf.format(detalle.subtotal)}\n\n` 
                        : '\n')
                );
            }).join(''); // El método 'join()' => Combina todos los strings generados por 'map()' en un único string. El Parámetro `('')`: Indica que no haya separación entre los elementos (ya que cada detalle ya termina con \n\n). Si colocaramos `('-')`, cada elemento del map() estaría separado por un "-"
        
            return ( 
                `Cotización #${cotizacion.value.id} de golosinasplin.com.ar \n\n` +
                detalles +
                (incluirTotal 
                            ? 
                                `╔═════════════════════════╗\n` +
                                `║         Total del Pedido: ${nf.format(cotizacion.value.total)}         \n` +
                                `╚═════════════════════════╝\n\n` 
                            : 
                                '') +
                `🌐 Finalizar compra en web: ${enlace} \n\n` +
                // (incluirTotal 
                //             ? `Total en Efectivo: ${nf.format(cotizacion.value.total * (1-0.1))}\n\n` 
                //             : '') +
                // `CONDICIONES:\n\n`+
                // `- PAGO DE CONTADO (EFECTIVO/TRANSFERENCIA/DEBITO)\n\n`+ 
                // `    <5% DESCUENTO>\n\n`+
                // `- PAGO DE CONTADO SUPERANDO LOS $30.000 (EFECTIVO/TRANSFERENCIA)\n\n`+
                // `    <10% DESCUENTO>\n\n`+
                // `- PAGO CON TARJETA BANCARIA :\n\n`+
                // `    1 PAGO:  PRECIO DE LISTA\n`+
                // `    3 PAGOS: 10% RECARGO\n`+
                // `    6 PAGOS: 20% RECARGO\n\n`+
                // `- PAGO CON TARJETA NARANJA :\n\n`+
                // `    1 PAGO: PRECIO DE LISTA\n`+
                // `    NARANJA Z: 10% RECARGO\n\n` +
                `Cotización a cargo de: ${cotizacion.value.user.name}\n` +
                `Recibe la cotización: ${cotizacion.value.cliente.nombre}`
            );
        },
        // Generar URL para añadir multiples items al carrito "?add=SKU1,SKU2:3,SKU3 (SKU[:qty])". 
        // Para ello el sitio web debe tener una "funcion" que tome el parámetro 'add' enviado por GET y procese los diferentes SKUs y cantidades que se envian dentro del parametro.
        // En este caso, en el code snippets del sitio, se agrego la "funcion" que toma ese parametro, analiza el SKU, la cantidad y los agrega al carrito.
        generarEnlaceWPF (cotizacion) {
            // Si cotizacion.value está en null, no debe hacer NADA
            if (!cotizacion.value) return;

            const products = cotizacion.value.detalles.map(detalle => {
                return detalle.cantidad > 1 
                    ? `${detalle.producto.codigo}:${detalle.cantidad}` 
                    : detalle.producto.codigo;
            }).join(',');

            return `https://golosinasplin.com.ar/?add=${products}`;
        }
    },
});