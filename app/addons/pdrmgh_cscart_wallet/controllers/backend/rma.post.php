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

use \Tygh\Enum\Addons\Rma\ReturnOperationStatuses;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST')
 {

    //
    // Creation wallet
    //
    if ($mode == 'add_wallet')
    {
        $change_return_status = $_REQUEST['change_return_status'];
        $return_info = fn_get_return_info($change_return_status['return_id']);
        $order_info= fn_get_order_info($return_info['order_id']);
        if (!empty($_REQUEST['accepted']))
        {
            $total = 0;
            
            foreach ((array) $_REQUEST['accepted'] as $item_id => $v)
            {
                if (isset($v['chosen']) && $v['chosen'] == 'Y') {
                    $total += $v['amount'] * $return_info['items'][RETURN_PRODUCT_ACCEPTED][$item_id]['price'];
                }
            }

            $get_order_refunded_amount=db_get_field('SELECT wallet_refunded_amount FROM ?:orders WHERE order_id = ?i',$return_info['order_id']);
            $remain_amount_to_be_refunded=$order_info['subtotal']-$get_order_refunded_amount;
            $remain_amount_to_be_refunded = round($remain_amount_to_be_refunded,2);

            if($total > $remain_amount_to_be_refunded)
            {
                fn_set_notification('w',__("warning"),__("can_not_refunded"));
                fn_set_notification('N',__("remain_amount"),__("remain_unrefunded_amount_regarding_this_order_is").$order_info['secondary_currency']." ".$remain_amount_to_be_refunded,true);
                 return array(CONTROLLER_STATUS_REDIRECT, "rma.details?return_id=$change_return_status[return_id]");
            }
          
            if (!empty($total))
            {
              $wallet_credit_id = fn_create_return_wallet($return_info['order_id'], fn_format_price($total), $change_return_status['return_id'], $return_info['user_id']);
                    
              if(!empty($wallet_credit_id))
              {
                $return_info['extra'] = unserialize($return_info['extra']);
                if (!isset($return_info['extra']['wallet']))
                {
                  $return_info['extra']['wallet'] = array();
                }

                $return_info['extra']['wallet'] = fn_array_merge($return_info['extra']['wallet'], array($wallet_credit_id => array('amount' => fn_format_price($total))));

                $_data = array('extra' => serialize($return_info['extra']));

                 db_query("UPDATE ?:orders SET wallet_refunded_amount =?i WHERE order_id = ?i",$get_order_refunded_amount+$total,$order_info['order_id']);
                 db_query("UPDATE ?:rma_returns SET ?u WHERE return_id = ?i", $_data, $change_return_status['return_id']);
              }
            }
          }

       return array(CONTROLLER_STATUS_REDIRECT, "rma.details?return_id=$change_return_status[return_id]");
    }
 }