<?php
/******************************************************************
# Wallet--- Wallet                                                *
# ----------------------------------------------------------------*
# author    Webkul                                                *
# copyright Copyright (C) 2010 webkul.com. All Rights Reserved.   *
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL     *
# Websites: http://webkul.com                                     *
*******************************************************************
*/

use Tygh\Embedded;
use Tygh\Http;
use Tygh\Mailer;
use Tygh\Pdf;
use Tygh\Registry;
use Tygh\Storage;
use Tygh\Session;
use Tygh\Settings;
use Tygh\Shippings\Shippings;
use Tygh\Navigation\LastView;
use Tygh\Models\VendorPlan;
use Tygh\Models\Company;

if (!defined('AREA')) {
    die('Access denied');
}


function fn_pdrmgh_cscart_wallet_install()
{
    $addon_name = fn_get_lang_var('pdrmgh_cscart_wallet');

    fn_set_notification(
        'S', __('well_done'), __(
            'wk_webkul_user_guide_content', array(
                '[support_link]' => 'https://webkul.uvdesk.com/en/customer/create-ticket/',
                '[user_guide]' => 'https://webkul.com/blog/cs-cart-wallet-system/',
                '[addon_name]' => $addon_name,
            )
        )
    );
}

function fn_create_transfer_for_user($email, $amount)
{
    if (!empty($_SESSION['auth']['user_id'])) {
        $sender_wallet_id= fn_get_user_wallet_id($_SESSION['auth']['user_id']);
        $reciever_user_id=db_get_field("SELECT user_id FROM ?:users WHERE email = ?s", $email);
        $reciever_wallet_id=fn_get_user_wallet_id($reciever_user_id);
        $reciever_wallet_total_cash = fn_get_wallet_amount($reciever_wallet_id, null);
        $sender_wallet_total_cash = fn_get_wallet_amount($sender_wallet_id, null);
        $sender_email_id= db_get_field("SELECT email FROM ?:users WHERE user_id = ?i", $_SESSION['auth']['user_id']);
        $extra_info= array(
        'sender_email' => $sender_email_id,
        'reciever_email' => $email,
        'transfer_amount' => $amount,
        'timestamp' => TIME,
        'sender_remain_amount' => $sender_wallet_total_cash-$amount,
        'reciever_remain_amount' => $reciever_wallet_total_cash+$amount,
         );
        // cash_credt_to_user

        $updated_cash = array(
                        'total_cash' => $amount+$reciever_wallet_total_cash
                    );

        db_query('UPDATE ?:wallet_cash SET ?u WHERE user_id =?i', $updated_cash, $reciever_user_id);
        if(fn_allowed_for('ULTIMATE'))
        {
            $company_id = Registry::get('runtime.company_id');
        }
        else
        {
            $company_id = Registry::get('runtime.company_id');
        }

        $_data = array(

                'source'         => "transfer",
                'source_id'      => '0',
                'wallet_id'      => $reciever_wallet_id,
                'credit_amount'  => $amount,
                'total_amount'   => $reciever_wallet_total_cash+$amount,
                'timestamp'      => TIME,
                'company_id'     => $company_id,
                'extra_info'     => serialize($extra_info),
                );

        $wallet_credit_log_id = db_query('INSERT INTO ?:wallet_credit_log ?e', $_data);
        $tran_data=array(
                        'credit_id' => $wallet_credit_log_id,
                        'wallet_id' => $reciever_wallet_id,
                        'timestamp' => TIME,
                    );
        db_query('INSERT INTO ?:wallet_transaction ?e', $tran_data);
        fn_credit_wallet_notification($wallet_credit_log_id);
                        
        //cash debit from user wallet

        

        $updated_cash = array(
                        'total_cash' => $sender_wallet_total_cash-$amount
                    );
                        
        db_query('UPDATE ?:wallet_cash SET ?u WHERE user_id =?i', $updated_cash, $_SESSION['auth']['user_id']);
        $data = array(
                     'wallet_id' => $sender_wallet_id,
                     'debit_amount' => $amount,
                     'remain_amount' => $sender_wallet_total_cash-$amount,
                     'order_id' => '0',
                     'timestamp' => TIME,
                     'area' => AREA,
                     'company_id' => $company_id,
                     'extra_info'     => serialize($extra_info),
                    );
            
        $wallet_debit_id=db_query('INSERT INTO ?:wallet_debit_log ?e', $data);

        $tran_data=array(
                        'debit_id' => $wallet_debit_id,
                        'wallet_id' => $sender_wallet_id,
                        'timestamp' => TIME,
                    );
        db_query('INSERT INTO ?:wallet_transaction ?e', $tran_data);

        fn_debit_wallet_notification($wallet_debit_id);
    }
}


function fn_pdrmgh_cscart_wallet_pre_update_order(&$cart, $order_id)
{
    // if(isset($cart['wallet']['used_cash']) && !empty($cart['wallet']['used_cash']))
     // {
     //  $cart['total']+=$cart['wallet']['used_cash'];
     // }
}

function fn_pdrmgh_cscart_wallet_get_external_discounts($product, &$discounts)
{
    $current_controller=Registry::get('runtime.controller');
         
    if (isset($_SESSION['cart']['wallet']['used_cash']) && $current_controller == 'checkout') {
        $discounts += fn_format_price($_SESSION['cart']['wallet']['used_cash'], CART_LANGUAGE);
    }
}

function fn_pdrmgh_cscart_wallet_calculate_cart(&$cart, &$cart_products, &$auth)
{
    if (isset($cart['pdrmgh_cscart_wallet'])) {
        foreach ($cart['pdrmgh_cscart_wallet'] as $cart_id => $wallet_data) {
            $cart['amount'] = 1;
            $cart['total'] = $wallet_data['recharge_amount'];
            $cart['subtotal'] = $wallet_data['recharge_amount'];
            $cart['display_subtotal'] = $wallet_data['recharge_amount'];

            $cart['pure_subtotal'] = $wallet_data['recharge_amount'];
        }
        $cart['shipping_failed'] = false;
        $cart['company_shipping_failed'] = false;
    }

    if (!empty($cart['wallet']['used_cash'])) {
        $cart['total']-=$cart['wallet']['used_cash'];
    }
}
function fn_pdrmgh_cscart_wallet_place_suborders(&$cart, &$sub_order_cart)
{
    if (!empty($cart['wallet']['used_cash'])) {
        $sub_order_cart['total']+=$cart['wallet']['used_cash'];
        $suborder_total=$sub_order_cart['total']+$sub_order_cart['payment_surcharge'];
        $get_order_perchentage=(($suborder_total*100)/($cart['wallet']['used_cash']+$cart['total']));
        $sub_order_cart['wallet']['used_cash']=($get_order_perchentage*$cart['wallet']['used_cash'])/100;
        $sub_order_cart['total']-=$sub_order_cart['wallet']['used_cash'];
    }
}

function fn_pdrmgh_cscart_wallet_mve_place_order(&$order_info, $company_data, $action, $__order_status, $cart, &$_data)
{
    if (isset($cart['wallet']['used_cash']) && !empty($cart['wallet']['used_cash'])) {
        $_data['order_amount']+=fn_format_price($cart['wallet']['used_cash']);
        $order_info['total']+=$cart['wallet']['used_cash'];
        $company_data = fn_get_company_data($order_info['company_id']);
        if (isset($company_data['plan_id']) && !empty($company_data['plan_id'])) {
            $addons = Registry::get('addons');
            if (isset($addons['vendor_plans']['status']) && $addons['vendor_plans']['status'] == 'A') {
                include_once 'vendor_plan.func.php';
                $company_data = fn_pdrmgh_cscart_wallet_get_vendor_commission_data($order_info, $company_data['plan_id']);
                $commission_amount = 0;
                if ($company_data['commission_type'] == 'P') {
                    //Calculate commission amount and check if we need to include shipping cost
                    $commission_amount = (($_data['order_amount'] - (Registry::get('settings.Vendors.include_shipping') == 'N' ?  $order_info['shipping_cost'] : 0)) * $company_data['commission'])/100;
                } else {
                    $commission_amount = $company_data['commission'];
                }
                $_data['commission_amount']=$commission_amount;
                $_data['commission'] = $company_data['commission'];
                $_data['commission_type'] = $company_data['commission_type'];
            }
        }
    }
}

function fn_pdrmgh_cscart_wallet_get_order_info(&$order, &$additional_data)
{
    if (isset($order['order_id'])) {
        $amount=db_get_field('SELECT credit_amount FROM ?:wallet_credit_log WHERE source=?s AND source_id=?i', 'recharge', $order['order_id']);

        if (empty($amount)) {
            $check=db_get_field('SELECT order_id FROM ?:wallet_offline_payment WHERE order_id=?i', $order['order_id']);

            if (!empty($check)) {
                if (isset($order['payment_surcharge']) && !empty($order['payment_surcharge'])) {
                    $amount = $order['total'] - $order['payment_surcharge'];
                } else {
                    $amount = $order['total'];
                }
            }
        }
        if (!empty($amount)) {
            $order['display_subtotal'] = $amount;
            $order['subtotal'] = $amount;
            $order['pdrmgh_cscart_wallet']['recharge_amount'] = $amount;
        }

        $get_wallet_order_data=db_get_field('SELECT data FROM ?:order_data WHERE order_id = ?i AND type = ?s', $order['order_id'], 'N');
        $get_wallet_order_data = unserialize($get_wallet_order_data);
            
        if (isset($get_wallet_order_data['used_cash'])) {
            $order['wallet']=$get_wallet_order_data;
            //$order['subtotal_discount']=$get_wallet_order_data['used_cash'];
            $current_controller=Registry::get('runtime.controller');
         
            $current_mode=Registry::get('runtime.mode');
                
            if ($current_controller == 'orders') {
                if ($current_mode == 'details' || $current_mode == 'print_invoice' || $current_mode == 'manage') {
                    $order['total']+=$get_wallet_order_data['used_cash'];
                }
            }
     
            if ($current_mode == 'add_wallet' && $current_controller='rma') {
                $order['total']+=$get_wallet_order_data['used_cash'];
            }

            if ($current_mode == 'refund' && $current_controller='pdrmgh_cscart_wallet') {
                $order['total']+=$get_wallet_order_data['used_cash'];
            }
            if ($current_mode == 'refund_in_wallet' && $current_controller='pdrmgh_cscart_wallet_wallet') {
                $order['total']+=$get_wallet_order_data['used_cash'];
            }
        }

        if (!isset($order['wallet']['used_cash'])) {
            $get_wallet_used_cash=db_get_field('SELECT pay_by_wallet_amount FROM ?:orders WHERE order_id = ?i ', $order['order_id']);
            if (!empty($get_wallet_used_cash)&& $get_wallet_used_cash>0.0) {
                $used_cash_data= array(
                'wallet'=>array(
                        'used_cash'=>$get_wallet_used_cash
                    )
                );
                $order=array_merge($order, $used_cash_data);
            }
        }
    }
    
    // if(isset($_REQUEST['dispatch']) &&  ($_REQUEST['dispatch'] == 'orders.update_status' || $_REQUEST['dispatch'] == 'checkout.place_order')){
    //     $email_template = Registry::get('settings.Appearance.email_templates');
    //     if($email_template == 'new'){
    //         if (isset($order['wallet']['used_cash'])) {
    //             $order['total'] = $order['wallet']['used_cash'] + $order['total'];
    //         }
    //     }
    // }
    if ((Registry::get('runtime.controller') == 'payment_notification' && Registry::get('runtime.mode') == 'return') || $_REQUEST['dispatch'] == 'orders.update_status') {
        $email_template = Registry::get('settings.Appearance.email_templates');
        if ($email_template == 'new') {
            if (isset($order['wallet']['used_cash'])) {
                $order['total'] = $order['wallet']['used_cash'] + $order['total'];
            }
        }
    }
}

function fn_pdrmgh_cscart_wallet_get_orders_post($params, &$orders)
{
    foreach ($orders as $key => $order) {
        $get_wallet_order_data=db_get_field('SELECT data FROM ?:order_data WHERE order_id = ?i AND type = ?s', $order['order_id'], 'N');

        $get_wallet_order_data = unserialize($get_wallet_order_data);
    
        if (isset($get_wallet_order_data['used_cash'])) {
            $current_controller=Registry::get('runtime.controller');
         
            $current_mode=Registry::get('runtime.mode');
                
            if ($current_controller == 'orders' || $current_controller == 'index') {
                if ($current_mode == 'manage' || $current_mode == 'search' || $current_mode == 'index') {
                    $order['total']+=fn_format_price($get_wallet_order_data['used_cash']);
                }
            }
        }
        $orders[$key]=$order;
    }
}

function fn_pdrmgh_cscart_wallet_send_order_notification(&$order_info, $edp_data, $force_notification, $notified, $send_order_notification)
{
    $get_wallet_order_data=db_get_field('SELECT data FROM ?:order_data WHERE order_id = ?i AND type = ?s', $order_info['order_id'], 'N');

    $get_wallet_order_data = unserialize($get_wallet_order_data);
    
    if (isset($get_wallet_order_data['used_cash'])) {
        $order_info['total']+=fn_format_price($get_wallet_order_data['used_cash']);
    }
}

function fn_pdrmgh_cscart_wallet_wallet_change_order_status($status_to, $status_from, $order_info, $force_notification, $order_statuses, $place_order)
{
    if (!empty($order_info['payment_method'])) {
        if (empty($order_info['payment_method']['processor'])) {
            if ($status_to == 'C') {
                $user_id= $order_info['user_id'];
                $order_id=$order_info['order_id'];
                $wallet_id=fn_get_user_wallet_id($user_id);
                $user_wallet_amount=fn_get_wallet_amount($wallet_id, null);
                $check_offline_order=db_get_array("SELECT * FROM ?:wallet_offline_payment WHERE order_id = ?i AND status = ?s", $order_id, "no");
                if (isset($order_info['payment_surcharge']) && !empty($order_info['payment_surcharge'])) {
                    $credit_amount = $order_info['total'] - $order_info['payment_surcharge'];
                } else {
                    $credit_amount = $order_info['total'];
                }

                if(fn_allowed_for('ULTIMATE'))
                {
                    $company_id = $order_info['company_id'];
                }
                else
                {
                    $company_id = $order_info['company_id'];
                }
                
                        
                if (!empty($check_offline_order)) {
                    $_data = array(

                    'source'         => "recharge",
                    'source_id'      => $order_info['order_id'],
                    'wallet_id'      => $wallet_id,
                    'credit_amount'  => $credit_amount,
                    'company_id'     => $company_id,
                    'total_amount'   => $credit_amount+$user_wallet_amount,
                    'timestamp'      => TIME,
                                         
                    );
                    $wallet_credit_log_id = db_query('INSERT INTO ?:wallet_credit_log ?e', $_data);
                    $tran_data=array(
                    'credit_id' => $wallet_credit_log_id,
                    'wallet_id' => $wallet_id,
                    'timestamp' => TIME,
                    );
                    db_query('INSERT INTO ?:wallet_transaction ?e', $tran_data);

                    fn_credit_wallet_notification($wallet_credit_log_id);
                    $data = array(
                                    'total_cash' => $credit_amount + $user_wallet_amount
                                    );

                    db_query('UPDATE ?:wallet_cash SET ?u WHERE user_id = ?i', $data, $user_id);
                    db_query('UPDATE ?:wallet_offline_payment SET status = ?s WHERE order_id = ?i', "yes", $order_id);
                        
                    fn_set_notification("N", __("wallet_recharge"), __("money_added_in_user_wallet"));
                }
            }
        }
        if ($status_to == 'C') {
            $wk_promotions=db_get_field("SELECT promotions FROM ?:orders WHERE order_id=?i", $order_info['order_id']);
            $order_total=$order_info['total'];
            if (!empty($wk_promotions)) {
                $user_id= $order_info['user_id'];
                $wallet_id=fn_get_user_wallet_id($user_id);
                if (!empty($wallet_id)) {
                    $cash_back_amount=fn_wk_wallet_apply_promotion_bonous($wk_promotions, $order_total);
                    $user_wallet_amount=fn_get_wallet_amount($wallet_id, null);
                    if ($cash_back_amount>0) {
                        if(fn_allowed_for('ULTIMATE'))
                        {
                            $company_id = $order_info['company_id'];
                        }
                        else
                        {
                            $company_id = $order_info['company_id'];
                        }

                        $_data = array(

                        'source'         => "cash_back",
                        'source_id'      => $order_info['order_id'],
                        'wallet_id'      => $wallet_id,
                        'credit_amount'  => $cash_back_amount,
                        'total_amount'   => $cash_back_amount+$user_wallet_amount,
                        'timestamp'      => TIME,
                        'company_id'     => $company_id,
                        'refund_reason'  => 'cash back on order total'
                                                 
                        );
                        $wallet_credit_log_id = db_query('INSERT INTO ?:wallet_credit_log ?e', $_data);
                        $tran_data=array(
                         'credit_id' => $wallet_credit_log_id,
                         'wallet_id' => $wallet_id,
                         'timestamp' => TIME,
                        );
                        db_query('INSERT INTO ?:wallet_transaction ?e', $tran_data);

                        fn_credit_wallet_notification($wallet_credit_log_id);
                        $data = array(
                                        'total_cash' => $cash_back_amount + $user_wallet_amount
                                        );

                        db_query('UPDATE ?:wallet_cash SET ?u WHERE user_id = ?i', $data, $user_id);
                        fn_set_notification("N", __("wallet_recharge"), __("money_added_in_user_wallet"));
                    }
                }
            }
        }
    }
}

function fn_pdrmgh_cscart_wallet_place_order(&$order_id, &$action, &$order_status, &$cart)
{
    if (isset($cart['pdrmgh_cscart_wallet']) && !empty($cart['pdrmgh_cscart_wallet_wallet_wallet'])) {
        $order_info = fn_get_order_info($order_id);
        if (empty($order_info['payment_data']['processor'])) {
            $data=array(
            'wallet_id' => fn_get_user_wallet_id($order_info['user_id']),
                'order_id' => $order_id
                );
            db_query('REPLACE INTO ?:wallet_offline_payment ?e', $data);
        }
    }

    if (isset($cart['wallet']['used_cash']) && !empty($cart['wallet']['used_cash'])) {
        // $cart['total']=$cart['total']-$cart['wallet']['used_cash'];
        $data=array(
                'data' => serialize($cart['wallet']),
                'order_id' => $order_id,
                'type' => 'N'
                    );
        db_query("REPLACE INTO ?:order_data ?e", $data);
    } else {
        $data=db_get_field('SELECT data FROM ?:order_data WHERE order_id = ?i AND type = ?s', $order_id, 'N');
        if (!empty($data)) {
            db_query("DELETE FROM ?:order_data WHERE order_id = ?i AND type = ?s", $order_id, 'N');
        }
    }
}

function fn_pdrmgh_cscart_wallet_order_placement_routines(&$order_id, &$force_notification, &$order_info, &$_error)
{
    $varify_order=db_get_field('SELECT source_id FROM ?:wallet_credit_log WHERE source = ?s AND source_id =?i', "recharge", $order_id);

    if (!empty($varify_order)) {
        if (in_array($order_info['status'], array('N', 'F', 'D'))) {
            $user_current_amount=fn_get_wallet_amount(null, $order_info['user_id']);
            $user_updated_amount=$user_current_amount-$order_info['total'];

            $user_updated_amount_data = array(
                                        'total_cash' => $user_updated_amount
                                        );

            db_query('UPDATE ?:wallet_cash SET ?u WHERE user_id =?i', $user_updated_amount_data, $order_info['user_id']);
            db_query("DELETE FROM ?:wallet_credit_log WHERE source_id = ?i AND source = ?s", $order_id, "recharge");
        }
    }
        
    if (isset($order_info['gift_certificates'])) {
    } else {
        if (!empty($order_info['payment_method']['processor'])) {
            if (empty($order_info['products'])) {
                if (!in_array($order_info['status'], array('N', 'F', 'D'))) {
                    $wallet_id=fn_get_user_wallet_id($order_info['user_id']);
                    $user_total_cash = fn_get_wallet_amount($wallet_id, null);
                    if (isset($order_info['payment_surcharge']) && !empty($order_info['payment_surcharge'])) {
                        $credit_amount = $order_info['total'] - $order_info['payment_surcharge'];
                    } else {
                        $credit_amount = $order_info['total'];
                    }

                    $user_updated_amount_daa = array(
                                        'total_cash' => $credit_amount+$user_total_cash
                                        );

                    db_query('UPDATE ?:wallet_cash SET ?u WHERE user_id =?i', $user_updated_amount_daa, $order_info['user_id']);

                    if(fn_allowed_for('ULTIMATE'))
                    {
                        $company_id = Registry::get('runtime.company_id');
                    }
                    else
                    {
                        $company_id = Registry::get('runtime.company_id');
                    }

                    $_data = array(

                            'source'         => "recharge",
                            'source_id'      => $order_id,
                            'wallet_id'      => $wallet_id,
                            'credit_amount'  => $credit_amount,
                            'company_id'     => $company_id,
                            'total_amount'   => $user_total_cash+$order_info['total'],
                            'timestamp'      => TIME,

                         );

                    $wallet_credit_log_id = db_query('INSERT INTO ?:wallet_credit_log ?e', $_data);
                    $tran_data=array(
                            'credit_id' => $wallet_credit_log_id,
                            'wallet_id' => $wallet_id,
                            'timestamp' => TIME,
                             );
                    db_query('INSERT INTO ?:wallet_transaction ?e', $tran_data);
                    fn_credit_wallet_notification($wallet_credit_log_id);
                }
            }
        }
    }

    $varify_debit_order = db_get_field('SELECT data FROM ?:order_data WHERE type = ?s AND order_id = ?i', 'N', $order_id);
    if (!empty($varify_debit_order)) {
        if (!in_array($order_info['status'], array('N', 'F', 'D'))) {
            if ($order_info['is_parent_order'] == 'N') {
                $debit_check=db_get_field("SELECT order_id FROM ?:wallet_debit_log WHERE order_id = ?i", $order_id);
            } else {
                $sub_order=db_get_array('SELECT order_id FROM ?:orders WHERE parent_order_id = ?i', $order_info['order_id']);
                foreach ($sub_order as $key => $value) {
                    $temp_debit_check=db_get_field("SELECT order_id FROM ?:wallet_debit_log WHERE order_id = ?i", $value['order_id']);
                    if (!empty($temp_debit_check)) {
                        $debit_check=$temp_debit_check;
                    }
                }
            }
            if (empty($debit_check)) {
                $wallet_info=unserialize($varify_debit_order);
                $wallet_info_current_cash = array(
                                        'total_cash' => $wallet_info['current_cash']
                                        );
                db_query('UPDATE ?:wallet_cash SET ?u WHERE user_id = ?i', $wallet_info_current_cash, $order_info['user_id']);
                if ($order_info['is_parent_order'] == 'N') {
                    db_query("UPDATE ?:orders SET pay_by_wallet_amount=?i WHERE order_id=?i", $wallet_info['used_cash'], $order_info['order_id']);
                    fn_create_wallet_debit_log($order_info, $wallet_info);
                } else {
                    $sub_orders=db_get_array('SELECT order_id,total FROM ?:orders WHERE parent_order_id = ?i', $order_info['order_id']);
                    $sub_order_current_cash=$wallet_info['current_cash']+$wallet_info['used_cash'];
                    foreach ($sub_orders as $key => $sub_order) {
                        $sub_order_info=fn_get_order_info($sub_order['order_id']);
                        if (isset($sub_order_info['wallet']['used_cash']) && !empty($sub_order_info['wallet']['used_cash'])) {
                            $sub_wallet_info['used_cash'] = $sub_order_info['wallet']['used_cash'];
                            $sub_wallet_info['used_cash'] = $sub_order_info['wallet']['used_cash'];
                            if (!empty($sub_wallet_info['used_cash'])) {
                                $sub_order_current_cash-=$sub_wallet_info['used_cash'];
                                $sub_wallet_info['current_cash']=$sub_order_current_cash;
                                fn_create_wallet_debit_log($sub_order_info, $sub_wallet_info);
                                db_query("UPDATE ?:orders SET pay_by_wallet_amount=?i WHERE order_id=?i", $sub_wallet_info['used_cash'], $sub_order['order_id']);
                            }
                        }
                    }
                }
            }
        }
    }
    if (isset($order_info['wallet']['used_cash']) && $order_info['wallet_refunded_amount']>0.0 ) {
        $remaining_wallet_amount=$order_info['wallet']['used_cash']-$order_info['wallet_refunded_amount'];
        if ($remaining_wallet_amount>0) {
            if ($order_info['total']<=$remaining_wallet_amount) {
                $credit_wallet_amount=$remaining_wallet_amount-$order_info['total'];
                $order_info['payment_id']=0;
                $order_info['payment_method']=array();
                db_query("UPDATE ?:orders SET pay_by_wallet_amount=?i WHERE order_id=?i", $order_info['total'], $order_info['order_id']);
                if ($credit_wallet_amount>0) {
                    $order_info['wallet']['used_cash']=$order_info['total'];
                    $wallet_id=db_get_field("SELECT wallet_id FROM ?:wallet_cash WHERE user_id =?i", $order_info['user_id']);
                    $user_wallet_amount=db_get_field("SELECT total_cash FROM ?:wallet_cash WHERE wallet_id =?i", $wallet_id);
                    if(fn_allowed_for('ULTIMATE'))
                    {
                        $company_id = Registry::get('runtime.company_id');
                    }
                    else
                    {
                        $company_id = Registry::get('runtime.company_id');
                    }
                    $_data = array(

                    'source'         => "order_edit",
                    'source_id'      => $order_info['order_id'],
                    'wallet_id'      => $wallet_id,
                    'credit_amount'  => $credit_wallet_amount,
                    'total_amount'   => $credit_wallet_amount+$user_wallet_amount,
                    'timestamp'      => TIME,
                    'company_id'     => $company_id,
                    'refund_reason'  =>'by_edit_order',
                                                 
                         );
                    $wallet_credit_log_id = db_query('INSERT INTO ?:wallet_credit_log ?e', $_data);
                    $tran_data=array(
                        'credit_id' => $wallet_credit_log_id,
                        'wallet_id' => $wallet_id,
                        'timestamp' => TIME,
                         );
                    db_query('INSERT INTO ?:wallet_transaction ?e', $tran_data);

                    fn_credit_wallet_notification($wallet_credit_log_id);
                    $data = array(
                                        'total_cash' => $credit_wallet_amount + $user_wallet_amount
                                        );

                    db_query('UPDATE ?:wallet_cash SET ?u WHERE user_id = ?i', $data, $order_info['user_id']);
                }
            } else {
                db_query("UPDATE ?:orders SET wallet_refunded_amount=?i WHERE order_id=?i", 0.0, $order_info['order_id']);
                db_query("UPDATE ?:orders SET pay_by_wallet_amount=?i WHERE order_id=?i", $remaining_wallet_amount, $order_info['order_id']);
            }
        } else {
            db_query("UPDATE ?:orders SET wallet_refunded_amount=?i WHERE order_id=?i", 0.0, $order_info['order_id']);
            $order_info['wallet']['used_cash']=0;
            db_query("UPDATE ?:orders SET pay_by_wallet_amount=?i WHERE order_id=?i", 0.0, $order_info['order_id']);
        }
    }

    if(isset($order_info['payment_info']['order_status']) && $order_info['payment_info']['order_status']=='N' )
    {  
        $sub_orders= db_get_array('SELECT order_id FROM ?:orders WHERE parent_order_id=?i', $order_info['order_id']);
      
        foreach($sub_orders as $key=> $sub_order )
        {
            $wallet_id=db_get_field("SELECT wallet_id FROM ?:wallet_debit_log WHERE order_id =?i", $sub_order['order_id']);
            $debit_id=db_get_field("SELECT debit_id FROM ?:wallet_debit_log WHERE order_id =?i", $sub_order['order_id']);
            if(!empty($wallet_id))
            {
                $debit_amount= db_get_field("SELECT debit_amount FROM ?:wallet_debit_log WHERE order_id =?i", $sub_order['order_id']);
                $current_wallet_amount = fn_get_wallet_amount($wallet_id, null);
                $updated_cash = array(
                    'total_cash' => $current_wallet_amount+$debit_amount
                );
                $add_amount = db_query('UPDATE ?:wallet_cash SET ?u WHERE wallet_id =?i', $updated_cash, $wallet_id);
                if($add_amount)
                {
                    db_query("DELETE FROM ?:wallet_transaction WHERE debit_id = ?i", $debit_id);
                    db_query("DELETE FROM ?:wallet_debit_log WHERE order_id = ?i", $sub_order['order_id']);
                }
            }
        }    
    }
}

function fn_pdrmgh_cscart_wallet_pre_add_to_cart(&$product_data, &$cart, &$auth, &$update)
{
    if (!empty($cart['pdrmgh_cscart_wallet'])) {
        fn_set_notification('W', 'Warning', __('wallet_recharge_with_products_not_accepted'));

        $product_data = array();
    }
}

function fn_pdrmgh_cscart_wallet_is_cart_empty(&$cart, &$result)
{
    if (!empty($cart['pdrmgh_cscart_wallet'])) {
        $result = false;
    }
}

function fn_pdrmgh_cscart_wallet_allow_place_order(&$total, &$cart)
{
    if (isset($cart['pdrmgh_cscart_wallet'])) {
        // Need to skip shipping
        $cart['shipping_failed'] = false;
        $cart['company_shipping_failed'] = false;
    }
}

function fn_create_return_wallet($order_id, $amount, $return_id, $user_id)
{
    $min = !empty(Registry::get('addons.pdrmgh_cscart_wallet.min_refund_amount')) ? Registry::get('addons.pdrmgh_cscart_wallet.min_refund_amount') : 0;
    $max = !empty(Registry::get('addons.pdrmgh_cscart_wallet.max_refund_amount')) ? Registry::get('addons.pdrmgh_cscart_wallet.max_refund_amount') : 0;

    $order_info = fn_get_order_info($order_id);

    if(fn_allowed_for('ULTIMATE'))
    {
        $company_id = $order_info['company_id'];
    }
    else
    {
        $company_id = $order_info['company_id'];
    }
                 
    if ($amount < $min || $amount > $max) {
        fn_set_notification('W', __('wallet_error'), __('can_not_add_money_in_wallet_please_check_refund_limit_in_addon_setting'));
        fn_set_notification("N", __("wallet_limit"), __("wallet_limit_is").' '.$min.__("_to_").' '.$max);

        $result = array();
    } else {
        $user_wallet_amount = fn_get_wallet_amount($wallet_id = null, $user_id);
                                
        if (empty($user_wallet_amount)) {
            $data = array(
                             'user_id'    => $user_id,
                             'total_cash'     => $amount,
                             'company_id'  => $company_id
                            );

            $wallet_id = db_query('INSERT INTO ?:wallet_cash ?e', $data);
        } else {
            $total_cash = $user_wallet_amount+$amount;

            $data = array(
                                            'total_cash' => $total_cash
                                            );

            db_query('UPDATE ?:wallet_cash SET ?u WHERE user_id = ?i', $data, $user_id);
        }
        $_data = array(

        'source'         => "refund_rma",
        'source_id'      => $return_id,
        'wallet_id'      => fn_get_user_wallet_id($user_id),
        'credit_amount'  => $amount,
        'total_amount'   => $amount+$user_wallet_amount,
        'timestamp'      => TIME,
        'company_id'     => $company_id,
        'refund_reason'      => "RMA generated By Customer",
                             
        );
        $wallet_credit_log_id = db_query('INSERT INTO ?:wallet_credit_log ?e', $_data);
        $tran_data=array(
                        'credit_id'  => $wallet_credit_log_id,
                        'wallet_id'  => fn_get_user_wallet_id($user_id),
                        'company_id' => $company_id,
                        'timestamp'  => TIME,
                    );
        db_query('INSERT INTO ?:wallet_transaction ?e', $tran_data);

        fn_credit_wallet_notification($wallet_credit_log_id);
        $result = $wallet_credit_log_id;
        fn_set_notification("N", __("wallet_refund"), __("money_added_in_user_wallet"));
    }

    return $result;
}



function fn_get_wallet_user_id($wallet_id)
{
    $user_id=db_get_field('SELECT user_id FROM ?:wallet_cash WHERE wallet_id =?i', $wallet_id);

    return $user_id;
}

function fn_get_user_wallet_id($user_id)
{
    if(fn_allowed_for('ULTIMATE'))
    {
        $company_id = Registry::get('runtime.company_id');
    }
    else
    {
        $company_id = Registry::get('runtime.company_id');
    }
    $wallet_id=db_get_field('SELECT wallet_id FROM ?:wallet_cash WHERE user_id =?i', $user_id);
    if (empty($wallet_id)) {
        $data = array(
            'user_id' => $user_id,
            'total_cash' => 0.00,
            'company_id' => $company_id
        );
        $wallet_id=db_query('INSERT INTO ?:wallet_cash ?e', $data);
    }
    return $wallet_id;
}

function fn_get_wallet_amount($wallet_id = null, $user_id = null)
{
    if (!empty($user_id)) {
        $total_cash = db_get_field('SELECT total_cash FROM ?:wallet_cash WHERE user_id =?i', $user_id);
    } elseif (!empty($wallet_id)) {
        $total_cash = db_get_field('SELECT total_cash FROM ?:wallet_cash WHERE wallet_id =?i', $wallet_id);
    } else {
        $total_cash =0.00;
    }

    return $total_cash;
}

    // function fn_get_order_wallet_refund_status($order_id)
    // {
    //    $refund_status=db_get_field('SELECT refunded FROM ?:wallet_debit_log WHERE order_id =?i',$order_id);

    //    return $refund_status;
    // }

function fn_create_wallet_debit_log($order_info, $wallet_info)
{
    if(fn_allowed_for('ULTIMATE'))
    {
        $company_id = Registry::get('runtime.company_id');
    }
    else
    {
        $company_id = Registry::get('runtime.company_id');
    }
    $data = array(
                 'wallet_id' => fn_get_user_wallet_id($order_info['user_id']),
                 'debit_amount' => $wallet_info['used_cash'],
                 'remain_amount' => $wallet_info['current_cash'],
                 'order_id' => $order_info['order_id'],
                 'timestamp' => TIME,
                 'company_id' => $company_id,
                 'area' => AREA,
        );
    $wallet_debit_id=db_query('INSERT INTO ?:wallet_debit_log ?e', $data);
    $tran_data=array(
                        'debit_id' => $wallet_debit_id,
                        'wallet_id' => fn_get_user_wallet_id($order_info['user_id']),
                        'timestamp' => TIME,
                        );
    db_query('INSERT INTO ?:wallet_transaction ?e', $tran_data);
    fn_debit_wallet_notification($wallet_debit_id);
}

function fn_credit_wallet_notification($wallet_credit_log_id)
{
    $data= db_get_array('SELECT source,source_id,wallet_id,credit_amount,total_amount FROM ?:wallet_credit_log WHERE credit_id=?i', $wallet_credit_log_id);
    $user_id = fn_get_wallet_user_id($data[0]['wallet_id']);
    $wallet_data['email']=db_get_field("SELECT email FROM ?:users WHERE user_id=?i", $user_id);
    $wallet_data['user_name']=fn_get_user_name($user_id);
    $wallet_data['amount']= $data[0]['credit_amount'];
    $wallet_data['total_cash']= $data[0]['total_amount'];
    $wallet_data['source']= $data[0]['source'];
    $wallet_data['source_id']= $data[0]['source_id'];

    if (Registry::get('settings.Appearance.email_templates') == 'old') {
        Mailer::sendMail(array(
            'to' => $wallet_data['email'],
            'from' => 'company_orders_department',
            'data' => array(
                    'wallet_data' => $wallet_data
                                
            ),
            'tpl' => 'addons/pdrmgh_cscart_wallet/credit.tpl',
                                
        ), 'C');
    } else {
        $currencies = Registry::get('currencies');
        $currency = $currencies[CART_PRIMARY_CURRENCY];
        if ($currency['after'] == 'Y') {
            $wallet_data['amount'] .= ' ' . $currency['symbol'];
            $wallet_data['total_cash'] .= ' ' . $currency['symbol'];
        } else {
            $wallet_data['amount'] = $currency['symbol'] . $wallet_data['amount'];
            $wallet_data['total_cash'] = $currency['symbol'] . $wallet_data['total_cash'];
        }
                                
        Mailer::sendMail(array(
            'to' => $wallet_data['email'],
            'from' => 'company_orders_department',
            'data' => array(
                    'wallet_data' => $wallet_data
                                
            ),
            'template_code' => 'wallet_credit',
            'tpl' => 'addons/pdrmgh_cscart_wallet/credit.tpl',
                                
            ), 'C');
    }
    return true;
}

function fn_debit_wallet_notification($wallet_debit_log_id)
{
    $data= db_get_array('SELECT order_id,wallet_id,debit_amount,remain_amount FROM ?:wallet_debit_log WHERE debit_id=?i', $wallet_debit_log_id);
    $user_id = fn_get_wallet_user_id($data[0]['wallet_id']);
    $wallet_data['email']=db_get_field("SELECT email FROM ?:users WHERE user_id=?i", $user_id);
    $wallet_data['user_name']=fn_get_user_name($user_id);
    $wallet_data['amount']= $data[0]['debit_amount'];
    $wallet_data['total_cash']= $data[0]['remain_amount'];
    $wallet_data['order_id']= $data[0]['order_id'];

    if (Registry::get('settings.Appearance.email_templates') == 'old') {
        Mailer::sendMail(array(
                    'to' => $wallet_data['email'],
                    'from' => 'company_orders_department',
                    'data' => array(
                            'wallet_data' => $wallet_data
                                        
                    ),
                    'tpl' => 'addons/pdrmgh_cscart_wallet/debit.tpl',
            ), 'C');
    } else {
        $currencies = Registry::get('currencies');
        $currency = $currencies[CART_PRIMARY_CURRENCY];
        if ($currency['after'] == 'Y') {
            $wallet_data['amount'] .= ' ' . $currency['symbol'];
            $wallet_data['total_cash'] .= ' ' . $currency['symbol'];
        } else {
            $wallet_data['amount'] = $currency['symbol'] . $wallet_data['amount'];
            $wallet_data['total_cash'] = $currency['symbol'] . $wallet_data['total_cash'];
        }
        Mailer::sendMail(array(
                                    'to' => $wallet_data['email'],
                                    'from' => 'company_orders_department',
                                    'data' => array(
                                            'wallet_data' => $wallet_data
                                                                        
                                    ),
                                    'template_code' => 'wallet_debit',
                                    'tpl' => 'addons/pdrmgh_cscart_wallet/debit.tpl',
                            ), 'C');
    }
    return true;
}


function fn_wallet_generate_sections($section)
{
    Registry::set('navigation.dynamic.sections', array(
            'wallet_users' => array(
                    'title' => __('wallet_users'),
                    'href' => 'pdrmgh_cscart_wallet.wallet_users',
                    ),
            'wallet_transaction' => array(
                    'title' => __('wallet_transaction'),
                    'href' => 'pdrmgh_cscart_wallet.wallet_transaction',
            ),
        ));
    Registry::set('navigation.dynamic.active_section', $section);

    return true;
}

function fn_get_wallet_transactions($params, $items_per_page = 0, $user_id = null)
{
    $auth = $_SESSION['auth'];
    $params = LastView::instance()->update('wallet_transaction', $params);
    // Set default values to input params
    $default_params = array(
            'page' => 1,
            'items_per_page' => $items_per_page
        );
    $params = array_merge($default_params, $params);
    // Define fields that should be retrieved
    $fields = array(
            '?:wallet_transaction.credit_id',
            '?:wallet_transaction.debit_id',
            '?:wallet_transaction.timestamp',
            '?:wallet_transaction.wallet_id',
        );
    // Define sort fields
    $sortings = array(
            'credit_id' => "?:wallet_transaction.credit_id",
            'debit_id' => "?:wallet_transaction.debit_id",
            'wallet_id' => "?:wallet_transaction.wallet_id",
            'timestamp' => "?:wallet_transaction.timestamp",
        );
    $sorting = db_sort($params, $sortings, 'timestamp', 'desc');
    $condition = $join = '';
    if (isset($params['email']) && fn_string_not_empty($params['email'])) {
        $s_user_id=fn_get_user_id_of_email(trim($params['email']));
        if (empty($s_user_id)) {
            $s_wallet_id = 0;
        } else {
            $s_wallet_id=db_get_field("SELECT wallet_id FROM ?:wallet_cash WHERE user_id = ?i", $s_user_id);
            if (empty($s_wallet_id)) {
                $s_wallet_id=0;
            }
        }
        $condition .= db_quote(" AND ?:wallet_transaction.wallet_id = ?i", $s_wallet_id);
    }
    if (isset($params['user_id']) && fn_string_not_empty($params['user_id'])) {
        $condition .= db_quote(" AND ?:wallet_transaction.wallet_id = ?i", fn_get_user_wallet_id(trim($params['user_id'])));
    }
    if (isset($params['credit_type']) && fn_string_not_empty($params['credit_type']) && $params['credit_type'] == 'credit') {
        $condition .= db_quote(" AND ?:wallet_transaction.debit_id = ?i", 0);
    }
    if (isset($params['credit_type']) && fn_string_not_empty($params['credit_type']) && $params['credit_type'] == 'debit') {
        $condition .= db_quote(" AND ?:wallet_transaction.credit_id = ?i", 0);
    }
    if (!empty($params['period']) && $params['period'] != 'A') {
        list($params['time_from'], $params['time_to']) = fn_create_periods($params);

        $condition .= db_quote(" AND (?:wallet_transaction.timestamp >= ?i AND ?:wallet_transaction.timestamp <= ?i)", $params['time_from'], $params['time_to']);
    }
    if (!empty($user_id)) {
        $condition .= db_quote(" AND ?:wallet_transaction.wallet_id IN (?n)", fn_get_user_wallet_id($user_id));
    }
    $limit = '';
    if (!empty($params['items_per_page'])) {
        $company_id = Registry::get('runtime.company_id');
        if(!empty($company_id) && AREA == 'A')
        { 
            $debit = db_get_field("SELECT COUNT(*) FROM ?:wallet_debit_log WHERE company_id = ?i",$company_id);
            $credit = db_get_field("SELECT COUNT(*) FROM ?:wallet_credit_log WHERE company_id = ?i",$company_id);
            $params['total_items'] = $debit + $credit;
            $limit = db_paginate($params['page'], $params['items_per_page'], $params['total_items']);
        }
        elseif(!empty($company_id) && AREA == 'C')
        { 
            $wallet_id = fn_get_user_wallet_id($auth['user_id']);
            $debit = db_get_field("SELECT COUNT(*) FROM ?:wallet_debit_log WHERE wallet_id = ?i",$wallet_id);
            $credit = db_get_field("SELECT COUNT(*) FROM ?:wallet_credit_log WHERE wallet_id = ?i",$wallet_id);
            $params['total_items'] = $debit + $credit;
            $limit = db_paginate($params['page'], $params['items_per_page'], $params['total_items']);
        }
        else 
        {
            $params['total_items'] = db_get_field("SELECT COUNT(*) FROM ?:wallet_transaction WHERE 1 ?p", $condition);
            $limit = db_paginate($params['page'], $params['items_per_page'], $params['total_items']);
        }
    }
    $wallet_transaction = db_get_array(
            "SELECT ?p  FROM ?:wallet_transaction WHERE 1 ?p ?p ?p",
            implode(',', $fields),
            $condition,
            $sorting,
            $limit
        );

    if(fn_allowed_for('ULTIMATE'))
    {
        $company_id = Registry::get('runtime.company_id');
        
        if(!empty($company_id) && $auth['user_type'] == 'A' && AREA == 'C'){
            $wallet_id = fn_get_user_wallet_id($auth['user_id']);
            foreach ($wallet_transaction as $key => $transaction) {
                if (empty($transaction['credit_id'])) {
                    $wallet_transaction[$key]=db_get_row('SELECT * FROM ?:wallet_debit_log WHERE debit_id =?i AND wallet_id = ?i', $transaction['debit_id'],$wallet_id);
                } elseif (empty($transaction['debit_id'])) {
                    $wallet_transaction[$key]=db_get_row('SELECT * FROM ?:wallet_credit_log WHERE credit_id =?i AND wallet_id = ?i', $transaction['credit_id'], $wallet_id);
                }
            }
        }
        elseif(!empty($company_id))
        { 
            foreach ($wallet_transaction as $key => $transaction) {
                if (empty($transaction['credit_id'])) {
                    $wallet_transaction[$key]=db_get_row('SELECT * FROM ?:wallet_debit_log WHERE debit_id =?i AND company_id = ?i', $transaction['debit_id'],$company_id);
                } elseif (empty($transaction['debit_id'])) {
                    $wallet_transaction[$key]=db_get_row('SELECT * FROM ?:wallet_credit_log WHERE credit_id =?i AND company_id = ?i', $transaction['credit_id'], $company_id);
                }
            }
        }
        else
        { 
            foreach ($wallet_transaction as $key => $transaction) {
                if (empty($transaction['credit_id'])) {
                    $wallet_transaction[$key]=db_get_row('SELECT * FROM ?:wallet_debit_log WHERE debit_id =?i', $transaction['debit_id']);
                } elseif (empty($transaction['debit_id'])) {
                    $wallet_transaction[$key]=db_get_row('SELECT * FROM ?:wallet_credit_log WHERE credit_id =?i', $transaction['credit_id']);
                }
            }
        }
    }
    else 
    {
        foreach ($wallet_transaction as $key => $transaction) {
            if (empty($transaction['credit_id'])) {
                $wallet_transaction[$key]=db_get_row('SELECT * FROM ?:wallet_debit_log WHERE debit_id =?i', $transaction['debit_id']);
            } elseif (empty($transaction['debit_id'])) {
                $wallet_transaction[$key]=db_get_row('SELECT * FROM ?:wallet_credit_log WHERE credit_id =?i', $transaction['credit_id']);
            }
        }
    }
    
    LastView::instance()->processResults('wallet_transaction', $wallet_transaction, $params);
    return array($wallet_transaction, $params);
}

function fn_get_user_id_of_email($email)
{
    if (!empty($email)) {
        $user_id= db_get_field('SELECT user_id FROM ?:users WHERE email LIKE ?l', "%" . $email ."%");
         
        return $user_id;
    } else {
        return null;
    }
}


// function fn_get_user_email($user_id)
// {
//     $email= db_get_field('SELECT email FROM ?:users WHERE user_id = ?i', $user_id);
         
//     return $email;
// }


function fn_get_wallet_users($params, $items_per_page = 0)
{
    $params = LastView::instance()->update('wallet_transaction', $params);

    // Set default values to input params
    $default_params = array(
            'page' => 1,
            'items_per_page' => $items_per_page
        );

    $params = array_merge($default_params, $params);

    // Define fields that should be retrieved
    $fields = array(
            '?:wallet_cash.wallet_id',
            '?:wallet_cash.user_id',
            '?:wallet_cash.total_cash',
        );

    // Define sort fields
    $sortings = array(
            'total_cash' => "?:wallet_cash.total_cash",
            'user_id' => "?:wallet_cash.user_id",
            'wallet_id' => "?:wallet_cash.wallet_id",
        );
    $condition = " ";
    $sorting = db_sort($params, $sortings, 'user_id', 'desc');

        

    if (isset($params['email']) && fn_string_not_empty($params['email'])) {
        $s_user_id=fn_get_user_id_of_email(trim($params['email']));
                    
        if (empty($s_user_id)) {
            $s_wallet_id = 0;
        } else {
            $s_wallet_id=db_get_field("SELECT wallet_id FROM ?:wallet_cash WHERE user_id = ?i", $s_user_id);
            
            if (empty($s_wallet_id)) {
                $s_wallet_id=0;
            }
        }
                 
        $condition .= db_quote(" AND ?:wallet_cash.wallet_id = ?i", $s_wallet_id);
    }
            
    $limit = '';
    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(*) FROM ?:wallet_cash WHERE 1 ?p", $condition);
        $limit = db_paginate($params['page'], $params['items_per_page'], $params['total_items']);
    }

    $wallet_cash = db_get_array(
            "SELECT ?p  FROM ?:wallet_cash WHERE 1 ?p ?p ?p",
            implode(',', $fields),
            $condition,
            $sorting,
            $limit
        );

    LastView::instance()->processResults('wallet_cash', $wallet_cash, $params);

    return array($wallet_cash, $params);
}

function fn_get_total_credit_wallet($wallet_id)
{
    $total_user_credit = db_get_field('SELECT SUM(credit_amount) FROM ?:wallet_credit_log WHERE wallet_id = ?i', $wallet_id);
    return $total_user_credit;
}

function fn_get_total_debit_wallet($wallet_id)
{
    $total_user_debit = db_get_field('SELECT SUM(debit_amount) FROM ?:wallet_debit_log WHERE wallet_id = ?i', $wallet_id);
    return $total_user_debit;
}

function fn_pdrmgh_cscart_wallet_update_profile(&$action, $user_data, $current_user_data)
{
    if (!empty($user_data['user_id']) && $action=='add') {
        $user_id=$user_data['user_id'];
        $check_wallet=db_get_field('SELECT wallet_id FROM ?:wallet_cash WHERE user_id=?i', $user_id);
        if (empty($check_wallet)) {
            $credit_amount=!empty(Registry::get('addons.pdrmgh_cscart_wallet.new_registration_amount')) ? Registry::get('addons.pdrmgh_cscart_wallet.new_registration_amount') : 0;
            $allow_credit_amount=Registry::get('addons.pdrmgh_cscart_wallet.new_registration_cash_back');
            if (!empty($credit_amount) && $credit_amount>0 && $allow_credit_amount=='Y') {
                if(fn_allowed_for('ULTIMATE'))
                {
                    // $company_id = Registry::get('runtime.company_id');
                    $company_id = db_get_field('SELECT `company_id` FROM ?:users WHERE user_id=?i',$user_id);
                }
                else
                {
                    $company_id = Registry::get('runtime.company_id');
                }
                $new_register_wallet_recharge=array(
                'user_id'=> $user_id,
                'total_cash'=>$credit_amount,
                'company_id'=>$company_id
                 );
                $wallet_id=db_query('INSERT INTO ?:wallet_cash ?e', $new_register_wallet_recharge);

                $_data = array(

                    'source'         => "new_registration",
                    'wallet_id'      => $wallet_id,
                    'credit_amount'  => $credit_amount,
                    'total_amount'   => $credit_amount,
                    'company_id'     => $company_id,
                    'timestamp'      => TIME,
                    );
                $wallet_credit_log_id = db_query('INSERT INTO ?:wallet_credit_log ?e', $_data);
                $tran_data=array(
                        'credit_id' => $wallet_credit_log_id,
                        'wallet_id' => $wallet_id,
                        'timestamp' => TIME,
                    );
                db_query('INSERT INTO ?:wallet_transaction ?e', $tran_data);

                fn_credit_wallet_notification($wallet_credit_log_id);
                fn_set_notification("N", __("wallet_recharge"), __("money_added_in_user_wallet"));
            }
        }
    }
}

function fn_wk_wallet_apply_promotion_bonous($wk_promotions, $order_total)
{
    if (!empty($wk_promotions)) {
        $wk_promotions_list=unserialize($wk_promotions);
        foreach ($wk_promotions_list as $key => $bonuses) {
            foreach ($bonuses as $key1 => $bonus) {
                foreach ($bonus as $key2 => $value) {
                    if (isset($value['bonus'])&& $value['bonus']=='wallet_cash_back') {
                        $discount_condition=$value['discount_bonus'];
                        $discount_value=$value['discount_value'];
                        if ($discount_condition=='by_fixed') {
                            return $discount_value;
                        } elseif ($discount_condition=='by_percentage') {
                            $cash_back_amount=$discount_value*$order_total/100;
                            return $cash_back_amount;
                        }
                    }
                }
            }
        }
    }
}

function fn_pdrmgh_cscart_wallet_checkout_place_order_before_check_amount_in_stock($cart,$auth)
{ 
    if(isset($cart['wallet']['used_cash']))
    {  
        $current_wallet_amount=fn_get_wallet_amount('',$auth['user_id']);
        if($cart['wallet']['used_cash']>$current_wallet_amount)
        {  
            $cart['wallet']['used_cash'] = $current_wallet_amount;
            fn_set_notification('W',__('warning'),__("your_current_cash_less_than_order_total"));
            unset($_SESSION['cart']['wallet']);
            $_SESSION['cart']['wallet']['current_cash']=fn_get_wallet_amount(null, $_SESSION['auth']['user_id']);
            fn_redirect('checkout.checkout');
        }
    }
}

function fn_pdrmgh_cscart_wallet_pre_place_order(&$cart, &$allow)
{
    if (isset($cart['order_id'])) {
        $order_info=fn_get_order_info($cart['order_id']);
        if (isset($order_info['wallet']['used_cash'])) {
            if ($cart['total']<=$order_info['wallet']['used_cash']) {
                $cart['payment_id']=0;
                unset($cart['payment_info']);
            }
        }
    }
}
function fn_pdrmgh_cscart_wallet_get_wallet_user_email_id($wallet_id)
{
    if (!empty($wallet_id)) {
        $wallet_user_id=db_get_field("SELECT user_id FROM ?:wallet_cash WHERE wallet_id=?i", $wallet_id);
        $wallet_user_email=db_get_field("SELECT email FROM ?:users WHERE user_id=?i", $wallet_user_id);
        return $wallet_user_email;
    }
}
function fn_pdrmgh_cscart_wallet_get_users_pre(&$params, $auth, $items_per_page, $custom_view)
{
    $params['exclude_user_types'] = array();
}
function fn_wallet_formatPrice($price)
{
    $currency = Registry::get('currencies.' . CART_PRIMARY_CURRENCY);

    $price = fn_format_rate_value(
        $price,
        'F',
        $currency['decimals'],
        $currency['decimals_separator'],
        $currency['thousands_separator'],
        $currency['coefficient']
        );

    return $currency['after'] == 'Y' ? $price . $currency['symbol'] : $currency['symbol'] . $price;
}