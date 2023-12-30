<?php
/***************************************************************************
*                                                                          *
*   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    
    array('calculate_cart', 200),
    'is_cart_empty',
    'pre_add_to_cart',
    'order_placement_routines',
    'place_order',
    'change_order_status',
    'get_order_info',
    'allow_place_order',
    'pre_update_order',
    'place_suborders',
    'mve_place_order',
    'get_orders_post',
    'send_order_notification',
    'get_external_discounts',
    'update_profile',
    'checkout_place_order_before_check_amount_in_stock',
    'pre_place_order',
    'get_users_pre'
);