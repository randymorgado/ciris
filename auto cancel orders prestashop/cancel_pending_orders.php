<?php
require_once(dirname(__FILE__).'/config/config.inc.php');
require_once(dirname(__FILE__).'/init.php');
require_once dirname(__FILE__).'/classes/order/Order.php';

if (!defined('_PS_VERSION_')) {
	echo "Version no definida"; 
    exit;
}



$orders = Order::getOrderIdsByStatus(35);
$order = reset($orders);


$id_order = $order;
// Reemplaza "123" con el ID del pedido que deseas consultar
//echo "Id de orden: ".$id_order;

// Crear una instancia de la clase Order utilizando el ID del pedido
$order = new Order($id_order);

// Obtener el ID del cliente a partir del objeto Order
$id_customer = $order->id_customer;
//echo "el id de cliente es: ". $id_customer;

// Obtener el primer pedido del array

$orders = Order::getCustomerOrders($id_customer);
//$id_order = $order['id_order'];

//echo "Estado del pedido: ". $order['current_state'];

// Verificar si el pedido está pendiente de pago
$date_add = $order->date_add;
//echo "Fecha del pedido: ".$date_add;
$date_now = new DateTime();
//echo "Fecha actual: ".$date_now->format('Y-m-d H:i:s');

$fecha1 = new DateTime($date_add);
$fecha2 = new DateTime('2022-01-01 12:34:56');
$intervalo = $fecha1->diff($fecha2);
//echo $intervalo->format('%H:%I:%S');
$diff = $fecha1->diff($date_now);
//echo "Diferencia de tiempo ". $diff->format('%H:%I:%S');;
 
    // Verificar si el pedido ha estado pendiente de pago durante más de 10 minutos
    if ($diff->i >= 6) {
      //  echo "entré a cancelar el pedido";
        // Cancelar el pedido
        //$order->setCurrentState(Configuration::get('PS_OS_CANCELED'));
        $order = new Order($id_order);
        //echo "El pedido que cancelaré será el: " .$id_order;
        $order->current_state = Configuration::get('PS_OS_CANCELED');
        $order->update();
        
        // Liberar el stock del producto
        $cart = new Cart($order->id_cart);
        $products = $cart->getProducts();

        foreach ($products as $product) {
          StockAvailable::updateQuantity(
          $product['id_product'], 
          $product['id_product_attribute'], 
          $product['cart_quantity'], 
          null, 
          1);
        }
}
