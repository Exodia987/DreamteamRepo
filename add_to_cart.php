<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    if ($product_id > 0) {
        $cart = isset($_COOKIE['cart']) ? json_decode($_COOKIE['cart'], true) : array();
        
        if (isset($cart[$product_id])) {
            $cart[$product_id] += $quantity;
        } else {
            $cart[$product_id] = $quantity;
        }

        setcookie('cart', json_encode($cart), time() + (86400 * 30), "/"); // 30 days expiration

        $cartCount = array_sum($cart);

        echo json_encode(array('success' => true, 'cartCount' => $cartCount));
    } else {
        echo json_encode(array('success' => false, 'message' => 'Invalid product ID'));
    }
} else {
    echo json_encode(array('success' => false, 'message' => 'Invalid request method'));
}