<?php
/******************************************************************
# Wallet - Wallet                                                 *
# ----------------------------------------------------------------*
# author    Webkul                                                *
# copyright Copyright (C) 2010 webkul.com. All Rights Reserved.   *
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL     *
# Websites: http://webkul.com                                     *
*******************************************************************
*/

use Tygh\Registry;

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}


if ($mode == 'refund_in_wallet') {
    if (isset($_REQUEST['order_id'])) {
        $order_info = fn_get_order_info($_REQUEST['order_id']);
        // fn_print_r($order_info);
        Registry::get('view')->assign('order_id', $order_info['order_id']);
        
        if (isset($_REQUEST['refund_amount'])) {
            Registry::get('view')->assign('amount', $_REQUEST['refund_amount']);
        } else {
            if (isset($order_info['wallet_refunded_amount']) && !empty($order_info['wallet_refunded_amount'])) {
                Registry::get('view')->assign('amount', $order_info['subtotal'] - $order_info['wallet_refunded_amount']);
                Registry::get('view')->assign('shipping_cost', $order_info['shipping_cost']);

            } else {
                Registry::get('view')->assign('amount', $order_info['subtotal']);
                Registry::get('view')->assign('shipping_cost', $order_info['shipping_cost']);

            }
        }
        if (isset($_REQUEST['refund_reason'])) {
            Registry::get('view')->assign('reason', $_REQUEST['refund_reason']);
        }
    }
}

if ($mode == "refund") {
    $currencies = fn_get_currencies_list();
    foreach ($currencies as $key => $value) {
        if ($value['is_primary'] == 'Y') {
            $wk_currency = $value['symbol'];
        }
    }
    $suffix = 'pdrmgh_cscart_wallet.refund_in_wallet?order_id='. $_REQUEST['wallet_refund']['order_id'].'&refund_amount='. $_REQUEST['wallet_refund']['refund_amount'].'&refund_reason='. $_REQUEST['wallet_refund']['refund_reason'];
     
    if (!empty($_REQUEST['wallet_refund']['order_id']) && !empty($_REQUEST['wallet_refund']['refund_reason']) && !empty($_REQUEST['wallet_refund']['refund_amount'])) {
        $order_info=fn_get_order_info($_REQUEST['wallet_refund']['order_id']);
        if(fn_allowed_for('ULTIMATE'))
        {
            $company_id = $order_info['company_id'];
        }
        else
        {
            $company_id = $order_info['company_id'];
        }
        $user_wallet_current_cash=fn_get_wallet_amount($wallet_id=null, $order_info['user_id']);
        $wallet_id=fn_get_user_wallet_id($order_info['user_id']);
        $min = !empty(Registry::get('addons.pdrmgh_cscart_wallet.min_refund_amount')) ? Registry::get('addons.pdrmgh_cscart_wallet.min_refund_amount') : 0;
        $max = !empty(Registry::get('addons.pdrmgh_cscart_wallet.max_refund_amount')) ? Registry::get('addons.pdrmgh_cscart_wallet.max_refund_amount') : 0;
        // echo "min = " . $min;
        // echo "<br>";
        // echo "max = " . $max;
        // echo "<br>";
        // echo __("wallet_limit_is").$min.__("_to_").$max;
        // exit;
        if ($_REQUEST['wallet_refund']['refund_amount'] < $min || $_REQUEST['wallet_refund']['refund_amount'] > $max) {
            fn_set_notification('W', __('wallet_error'), __('can_not_add_money_in_wallet_please_check_refund_limit_in_addon_setting'));
            fn_set_notification("N", __("wallet_limit"), __("wallet_limit_is").$min.__("_to_").$max);

            return array(CONTROLLER_STATUS_REDIRECT, $suffix);
        }

        $get_order_refunded_amount=fn_format_price(db_get_field('SELECT wallet_refunded_amount FROM ?:orders WHERE order_id = ?i', $order_info['order_id']));
        $remain_amount_to_be_refunded=$order_info['subtotal']-fn_format_price($get_order_refunded_amount);

        if ($_REQUEST['wallet_refund']['refund_amount'] > fn_format_price($remain_amount_to_be_refunded)) {
            fn_set_notification('w', __("warning"), __("can_not_refunded"));
            fn_set_notification('N', __("remain_amount"), __("remain_unrefunded_amount_regarding_this_order_is").fn_wallet_formatPrice($remain_amount_to_be_refunded));
            return array(CONTROLLER_STATUS_REDIRECT, $suffix);
        }

        $user_wallet_updated_cash=$user_wallet_current_cash+$_REQUEST['wallet_refund']['refund_amount'];

        $updated_cash = array(
                                'total_cash' => $user_wallet_updated_cash
                            );
        db_query("UPDATE ?:wallet_cash SET ?u WHERE wallet_id = ?i", $updated_cash, $wallet_id);
        $user_updated_order_refund = $get_order_refunded_amount+$_REQUEST['wallet_refund']['refund_amount'];
        $order_wallet_data = array(
                    'wallet_refunded_amount' => $user_updated_order_refund
                );
        db_query("UPDATE ?:orders SET ?u WHERE order_id = ?i", $order_wallet_data, $order_info['order_id']);

        $_data = array(
                        'source'         => "refund_order",
                        'source_id'      => $order_info['order_id'],
                        'wallet_id'      => $wallet_id,
                        'credit_amount'  => $_REQUEST['wallet_refund']['refund_amount'],
                        'total_amount'   => $user_wallet_updated_cash,
                        'timestamp'      => TIME,
                        'refund_reason'  => $_REQUEST['wallet_refund']['refund_reason'],
                        'company_id'     => $company_id,
                        );
        $wallet_credit_log_id = db_query('INSERT INTO ?:wallet_credit_log ?e', $_data);
        $tran_data=array(
                                'credit_id' => $wallet_credit_log_id,
                                'wallet_id' => $wallet_id,
                                'timestamp' => TIME,
                            );
        db_query('INSERT INTO ?:wallet_transaction ?e', $tran_data);

        fn_credit_wallet_notification($wallet_credit_log_id);
                 
        fn_set_notification('N', __('wallet_refund'), __('amount_has_been_refunded_in_user_wallet'));
                 
        return array(CONTROLLER_STATUS_REDIRECT, 'pdrmgh_cscart_wallet.wallet_transaction');
    } else {
        fn_set_notification('W', __('warning'), __('please_fill_all_fields'));
        return array(CONTROLLER_STATUS_REDIRECT, $suffix);
    }
}


if ($mode == "wallet_transaction") {
    fn_wallet_generate_sections('wallet_transaction');

    list($wallet_transaction, $search) = fn_get_wallet_transactions($_REQUEST, Registry::get('settings.Appearance.admin_elements_per_page'));

    Registry::get('view')->assign('wallet_transaction', $wallet_transaction);
    Registry::get('view')->assign('search', $search);

    if (isset($_REQUEST['email']) && !empty($_REQUEST['email'])) {
        $credit_total=db_get_field('SELECT SUM(credit_amount) FROM ?:wallet_credit_log WHERE wallet_id =?i', db_get_field("SELECT wallet_id FROM ?:wallet_cash WHERE user_id = ?i", fn_get_user_id_of_email(trim($_REQUEST['email']))));
        $debit_total=db_get_field('SELECT SUM(debit_amount) FROM ?:wallet_debit_log WHERE wallet_id =?i', db_get_field("SELECT wallet_id FROM ?:wallet_cash WHERE user_id = ?i", fn_get_user_id_of_email(trim($_REQUEST['email']))));
    } else {
        if(!empty(Registry::get('runtime.company_id')))
        {
            $company_id = Registry::get('runtime.company_id');
            $credit_total=db_get_field('SELECT SUM(credit_amount) FROM ?:wallet_credit_log WHERE company_id = ?i', $company_id);
            $debit_total=db_get_field('SELECT SUM(debit_amount) FROM ?:wallet_debit_log WHERE company_id = ?i', $company_id);
        }
        else 
        {
            $credit_total=db_get_field('SELECT SUM(credit_amount) FROM ?:wallet_credit_log');
            $debit_total=db_get_field('SELECT SUM(debit_amount) FROM ?:wallet_debit_log');
        }
    }
    Registry::get('view')->assign('credit_total', $credit_total);
    Registry::get('view')->assign('debit_total', $debit_total);
}

if ($mode == "wallet_users") {
    fn_wallet_generate_sections('wallet_users');

    list($wallet_users, $search) = fn_get_wallet_users($_REQUEST, Registry::get('settings.Appearance.admin_elements_per_page'));

    Registry::get('view')->assign('wallet_users', $wallet_users);
    Registry::get('view')->assign('search', $search);

    if (isset($_REQUEST['email']) && !empty($_REQUEST['email'])) {
        $credit_total=db_get_field('SELECT SUM(credit_amount) FROM ?:wallet_credit_log WHERE wallet_id =?i', db_get_field("SELECT wallet_id FROM ?:wallet_cash WHERE user_id = ?i", fn_get_user_id_of_email(trim($_REQUEST['email']))));
        $debit_total=db_get_field('SELECT SUM(debit_amount) FROM ?:wallet_debit_log WHERE wallet_id =?i', db_get_field("SELECT wallet_id FROM ?:wallet_cash WHERE user_id = ?i", fn_get_user_id_of_email(trim($_REQUEST['email']))));
    } else {
        if(!empty(Registry::get('runtime.company_id')))
        {
            $company_id = Registry::get('runtime.company_id');
            $credit_total=db_get_field('SELECT SUM(credit_amount) FROM ?:wallet_credit_log WHERE company_id = ?i', $company_id);
            $debit_total=db_get_field('SELECT SUM(debit_amount) FROM ?:wallet_debit_log WHERE company_id = ?i', $company_id);
        }
        else 
        {
            $credit_total=db_get_field('SELECT SUM(credit_amount) FROM ?:wallet_credit_log');
            $debit_total=db_get_field('SELECT SUM(debit_amount) FROM ?:wallet_debit_log');
        }
    }
    Registry::get('view')->assign('credit_total', $credit_total);
    Registry::get('view')->assign('debit_total', $debit_total);
}

if ($mode =='debit_wallet_manually') {
    if (isset($_REQUEST['wallet_id']) && !empty($_REQUEST['wallet_id'])) {
        Registry::get('view')->assign('wallet_id', $_REQUEST['wallet_id']);
    }
}

if ($mode =='credit_wallet_manually') {
    if (isset($_REQUEST['wallet_id']) && !empty($_REQUEST['wallet_id'])) {
        Registry::get('view')->assign('wallet_id', $_REQUEST['wallet_id']);
    }
}

if ($mode =='credit_wallet') {
    $suffix = 'pdrmgh_cscart_wallet.credit_wallet_manually?wallet_id='. $_REQUEST['wallet_credit']['wallet_id'].'&credit_amount='. $_REQUEST['wallet_credit']['credit_amount'].'&credit_reason='. $_REQUEST['wallet_credit']['credit_reason'];
     
    if (!empty($_REQUEST['wallet_credit']['wallet_id']) && !empty($_REQUEST['wallet_credit']['credit_reason']) && !empty($_REQUEST['wallet_credit']['credit_amount'])) {
        $user_wallet_current_cash=db_get_field("SELECT total_cash FROM ?:wallet_cash WHERE wallet_id=?i", $_REQUEST['wallet_credit']['wallet_id']);

        $user_wallet_updated_cash=$user_wallet_current_cash+$_REQUEST['wallet_credit']['credit_amount'];

        $updated_cash = array(
                                'total_cash' => $user_wallet_updated_cash
                            );
        db_query("UPDATE ?:wallet_cash SET ?u WHERE wallet_id = ?i", $updated_cash, $_REQUEST['wallet_credit']['wallet_id']);

        $_data = array(
                        'source'         => "credit_by_admin",
                        'source_id'      => 0,
                        'wallet_id'      => $_REQUEST['wallet_credit']['wallet_id'],
                        'credit_amount'  => $_REQUEST['wallet_credit']['credit_amount'],
                        'total_amount'   => $user_wallet_updated_cash,
                        'timestamp'      => TIME,
                        'refund_reason'  => $_REQUEST['wallet_credit']['credit_reason'],
                                     
                        );
        $wallet_credit_log_id = db_query('INSERT INTO ?:wallet_credit_log ?e', $_data);
        $tran_data=array(
                                'credit_id' => $wallet_credit_log_id,
                                'wallet_id' => $_REQUEST['wallet_credit']['wallet_id'],
                                'timestamp' => TIME,
                            );
        db_query('INSERT INTO ?:wallet_transaction ?e', $tran_data);

        fn_credit_wallet_notification($wallet_credit_log_id);
                 
        fn_set_notification('N', __('wallet_credit'), __('amount_has_been_credited_in_user_wallet'));

        return array(CONTROLLER_STATUS_REDIRECT, 'pdrmgh_cscart_wallet.wallet_transaction');
    } else {
        fn_set_notification('W', __('warning'), __('please_fill_all_fields'));
        return array(CONTROLLER_STATUS_REDIRECT, $suffix);
    }
}

if ($mode =='debit_wallet') {
    $suffix = 'pdrmgh_cscart_wallet.debit_wallet_manually?wallet_id='. $_REQUEST['wallet_debit']['wallet_id'].'&debit_amount='. $_REQUEST['wallet_debit']['debit_amount'].'&debit_reason='. $_REQUEST['wallet_debit']['debit_reason'];

    if (!empty($_REQUEST['wallet_debit']['wallet_id']) && !empty($_REQUEST['wallet_debit']['debit_reason']) && !empty($_REQUEST['wallet_debit']['debit_amount'])) {
        $user_wallet_current_cash=db_get_field("SELECT total_cash FROM ?:wallet_cash WHERE wallet_id=?i", $_REQUEST['wallet_debit']['wallet_id']);
        if ($_REQUEST['wallet_debit']['debit_amount'] > $user_wallet_current_cash) {
            fn_set_notification('W', __('warning'), __('amount_cannot_be_greater'));
            return array(CONTROLLER_STATUS_REDIRECT, $suffix);
        }
        if ($user_wallet_current_cash>0.0) {
            $user_wallet_updated_cash=$user_wallet_current_cash-$_REQUEST['wallet_debit']['debit_amount'];
            $require_dabit_amount=$_REQUEST['wallet_debit']['debit_amount'];
            if ($user_wallet_updated_cash<0.0) {
                $user_wallet_updated_cash=0.0;
                $require_dabit_amount=$user_wallet_current_cash;
            }

            $updated_cash = array(
                                'total_cash' => $user_wallet_updated_cash
                            );

            db_query("UPDATE ?:wallet_cash SET ?u WHERE wallet_id = ?i", $updated_cash, $_REQUEST['wallet_debit']['wallet_id']);

            $_data = array(
                                            'wallet_id'      => $_REQUEST['wallet_debit']['wallet_id'],
                                            'debit_amount'  => $require_dabit_amount,
                                            'remain_amount'   => $user_wallet_updated_cash,
                                            'order_id' => '0',
                                            'timestamp'      => TIME,
                                            'area' => AREA,
                                            'debit_reason'=> $_REQUEST['wallet_debit']['debit_reason'],
                                            'source' => "debit_by_admin",
                                            );
            $wallet_debit_id=db_query('INSERT INTO ?:wallet_debit_log ?e', $_data);

            $tran_data=array(
                                        'debit_id' => $wallet_debit_id,
                                        'wallet_id' => $_REQUEST['wallet_debit']['wallet_id'],
                                        'timestamp' => TIME,
                                    );
            db_query('INSERT INTO ?:wallet_transaction ?e', $tran_data);

            fn_debit_wallet_notification($wallet_debit_id);

            fn_set_notification('N', __('wallet_debit'), __('amount_has_been_debited_from_user_wallet'));
            return array(CONTROLLER_STATUS_REDIRECT, 'pdrmgh_cscart_wallet.wallet_transaction');
        } else {
            fn_set_notification('W', __('warning'), __('amount_is_zero'));
            return array(CONTROLLER_STATUS_REDIRECT, $suffix);
        }
    } else {
        fn_set_notification('W', __('warning'), __('please_fill_all_fields'));
        return array(CONTROLLER_STATUS_REDIRECT, $suffix);
    }
}


if ($mode == "wallet_dabit_credit") {
}

if ($mode =='group_debit_wallet') {
    $suffix = 'pdrmgh_cscart_wallet.wallet_users?amount='. $_REQUEST['wallet_credit_debit']['amount'].'&reason='. $_REQUEST['wallet_credit_debit']['reason'];

    //$company_id = Registry::get('runtime.company_id');
    if (!empty($_REQUEST['wallet_credit_debit']['reason']) && !empty($_REQUEST['wallet_credit_debit']['amount']) && !empty($_REQUEST['wallet_credit_debit']['user'])) {
        $list_of_users=explode(",", $_REQUEST['wallet_credit_debit']['user']);
        $amount=$_REQUEST['wallet_credit_debit']['amount'];
        $reason=$_REQUEST['wallet_credit_debit']['reason'];
        foreach ($list_of_users as $key => $user_id) {
            $company_id = db_get_field('SELECT `company_id` FROM ?:users WHERE user_id=?i',$user_id);
            $user_wallet_current_cash=db_get_field("SELECT total_cash FROM ?:wallet_cash WHERE user_id=?i", $user_id);
            $wallet_id=db_get_field("SELECT wallet_id FROM ?:wallet_cash WHERE user_id=?i", $user_id);
            if ($user_wallet_current_cash>0.0 && $user_wallet_current_cash>=$amount) {
                $user_wallet_updated_cash=$user_wallet_current_cash-$amount;
                $require_dabit_amount=$amount;
                // if ($user_wallet_updated_cash<0.0) {
                //     $user_wallet_updated_cash=0.0;
                //     $require_dabit_amount=$user_wallet_current_cash;
                // }
                $updated_cash = array(
                                'total_cash' => $user_wallet_updated_cash
                            );

                db_query("UPDATE ?:wallet_cash SET ?u WHERE user_id = ?i", $updated_cash, $user_id);

                $_data = array(
                                'wallet_id'      => $wallet_id,
                                'debit_amount'  => $require_dabit_amount,
                                'remain_amount'   => $user_wallet_updated_cash,
                                'order_id' => '0',
                                'timestamp'      => TIME,
                                'area' => AREA,
                                'debit_reason'=>$reason,
                                'source' => "debit_by_admin",
                                'company_id' => $company_id,
                                );
                $wallet_debit_id=db_query('INSERT INTO ?:wallet_debit_log ?e', $_data);
                $tran_data=array(
                                            'debit_id' => $wallet_debit_id,
                                            'wallet_id' => $wallet_id,
                                            'timestamp' => TIME,
                                            'company_id' => $company_id,
                                );
                db_query('INSERT INTO ?:wallet_transaction ?e', $tran_data);
                fn_set_notification('N', __('wallet_debit'), __('amount_has_been_debited_from_user_wallet'));
                fn_debit_wallet_notification($wallet_debit_id);
            } else {
                fn_set_notification('W', __('warning'), __('amount_is_zero'));
            }
        }
        return array(CONTROLLER_STATUS_REDIRECT, 'pdrmgh_cscart_wallet.wallet_transaction');
    } else {
        fn_set_notification('W', __('warning'), __('please_fill_all_fields'));
        if (isset($_REQUEST['return_url'])) {
            return array(CONTROLLER_STATUS_REDIRECT, $_REQUEST['return_url']);
        }
        return array(CONTROLLER_STATUS_REDIRECT, $suffix);
    }
}

if ($mode =='group_credit_wallet') {
    $suffix = 'pdrmgh_cscart_wallet.wallet_users?amount='. $_REQUEST['wallet_credit_debit']['amount'].'&reason='. $_REQUEST['wallet_credit_debit']['reason'];
   // $company_id = Registry::get('runtime.company_id');

    if (!empty($_REQUEST['wallet_credit_debit']['reason']) && !empty($_REQUEST['wallet_credit_debit']['amount']) && !empty($_REQUEST['wallet_credit_debit']['user'])) {
        $list_of_users=explode(",", $_REQUEST['wallet_credit_debit']['user']);
        $amount=$_REQUEST['wallet_credit_debit']['amount'];
        $reason=$_REQUEST['wallet_credit_debit']['reason'];
        foreach ($list_of_users as $key => $user_id) {
            $company_id = db_get_field('SELECT `company_id` FROM ?:users WHERE user_id=?i',$user_id);
            $check_wallet=db_get_field('SELECT wallet_id FROM ?:wallet_cash WHERE user_id=?i', $user_id);
            if (empty($check_wallet)) {
                $new_credit_wallet=array(
                                'user_id'=> $user_id,
                                'total_cash'=>$amount,
                                'company_id'=>$company_id
                                );
                $wallet_id=db_query('INSERT INTO ?:wallet_cash ?e', $new_credit_wallet);

                $_data = array(

                                        'source'         => "credit_by_admin",
                                        'source_id'      =>0,
                                        'wallet_id'      => $wallet_id,
                                        'credit_amount'  => $amount,
                                        'total_amount'   => $amount,
                                        'timestamp'      => TIME,
                                        'refund_reason'  => $reason,
                                        'company_id'     => $company_id,
                                        );
                $wallet_credit_log_id = db_query('INSERT INTO ?:wallet_credit_log ?e', $_data);
                $tran_data=array(
                                            'credit_id' => $wallet_credit_log_id,
                                            'wallet_id' => $wallet_id,
                                            'timestamp' => TIME,
                                            'company_id'     => $company_id,
                                        );
                db_query('INSERT INTO ?:wallet_transaction ?e', $tran_data);

                fn_credit_wallet_notification($wallet_credit_log_id);
            } else {
                $user_wallet_current_cash=db_get_field("SELECT total_cash FROM ?:wallet_cash WHERE user_id=?i", $user_id);
                $wallet_id=db_get_field("SELECT wallet_id FROM ?:wallet_cash WHERE user_id=?i", $user_id);
                $user_wallet_updated_cash=$user_wallet_current_cash+$amount;
                $updated_cash = array(
                                'total_cash' => $user_wallet_updated_cash
                            );
                db_query("UPDATE ?:wallet_cash SET ?u WHERE user_id = ?i", $updated_cash, $user_id);

                $_data = array(

                                    'source'         => "credit_by_admin",
                                    'source_id'      => 0,
                                    'wallet_id'      => $wallet_id,
                                    'credit_amount'  => $amount,
                                    'total_amount'   => $user_wallet_updated_cash,
                                    'timestamp'      => TIME,
                                    'refund_reason'  => $reason,
                                    'company_id'     => $company_id,
                                    );
                $wallet_credit_log_id = db_query('INSERT INTO ?:wallet_credit_log ?e', $_data);
                $tran_data=array(
                                            'credit_id' => $wallet_credit_log_id,
                                            'wallet_id' => $wallet_id,
                                            'timestamp' => TIME,
                                            'company_id'     => $company_id,
                                        );
                db_query('INSERT INTO ?:wallet_transaction ?e', $tran_data);

                fn_credit_wallet_notification($wallet_credit_log_id);
            }
        }
        fn_set_notification("N", __("wallet_recharge"), __("money_added_in_user_wallet"));
        return array(CONTROLLER_STATUS_REDIRECT, 'pdrmgh_cscart_wallet.wallet_transaction');
    } else {
        fn_set_notification('W', __('warning'), __('please_fill_all_fields'));
        if (isset($_REQUEST['return_url'])) {
            return array(CONTROLLER_STATUS_REDIRECT, $_REQUEST['return_url']);
        }
        return array(CONTROLLER_STATUS_REDIRECT, $suffix);
    }
}
