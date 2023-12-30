<?php

if (!fn_allowed_for('ULTIMATE:FREE')) {
    $schema['bonuses']['wallet_cash_back'] = array(
        'function' => array('fn_promotion_apply_cart_rule', '#this', '@cart', '@auth', '@cart_products'),
        'discount_bonuses' => array('by_percentage','by_fixed'),
        'zones' => array('cart'),
        'filter' => 'floatval'
    );
 }  
return $schema;
?>