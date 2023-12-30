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
use Tygh\SESSION;

if (!defined('BOOTSTRAP')) { die('Access denied'); }
  if($mode == 'checkout')
  {

  $cart_total = $_SESSION['cart']['total'];
// die($cart_total);
     $user_wallet_total_amnt=fn_get_wallet_amount($wallet_id=null,$user_id=$auth['user_id']);

     Registry::get('view')->assign('user_wallet_total_amnt', $user_wallet_total_amnt);


     if(!empty($_SESSION['cart']['gift_certificates']) && !empty($_SESSION['cart']['pdrmgh_cscart_wallet']))
     {
        fn_set_notification('E',__("error"),__("remove_gift_certificate_first"));
     	return array(CONTROLLER_STATUS_REDIRECT, "checkout.cart"); 
     }
  }

