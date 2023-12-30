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
use Tygh\Navigation\LastView;
use Tygh\Tygh;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if($mode == 'create_transfer')
{
     $suffix="pdrmgh_cscart_wallet.wallet_transfer_user_to_user&email=".$_REQUEST['wallet_transfer_system']['transfer_email']."&amount=".$_REQUEST['wallet_transfer_system']['transfer_amount'];
     if(Registry::get('addons.pdrmgh_cscart_wallet.status_wallet_transfer') == 'N')
     {
       return array(CONTROLLER_STATUS_DENIED);
     }

     $_REQUEST['wallet_transfer_system']['transfer_amount'] = fn_format_price_by_currency($_REQUEST['wallet_transfer_system']['transfer_amount'],CART_SECONDARY_CURRENCY,CART_PRIMARY_CURRENCY);

     if($_REQUEST['wallet_transfer_system']['transfer_email'] == db_get_field("SELECT email FROM ?:users WHERE user_id =?i",$_SESSION['auth']['user_id']))
     {
       fn_set_notification('E',__("error"),__("can_not_transfer_to_own_email"));
      return array(CONTROLLER_STATUS_REDIRECT,$suffix);
     }
    $get_transfer_min=!empty(Registry::get('addons.pdrmgh_cscart_wallet.min_transfer_amount')) ? Registry::get('addons.pdrmgh_cscart_wallet.min_transfer_amount') : 0;

    $get_transfer_max=!empty(Registry::get('addons.pdrmgh_cscart_wallet.max_transfer_amount')) ? Registry::get('addons.pdrmgh_cscart_wallet.max_transfer_amount') : 0;

    $get_user_wallet_amount=fn_get_wallet_amount(null,$_SESSION['auth']['user_id']);
    
    $transfer_email_id=trim($_REQUEST['wallet_transfer_system']['transfer_email']);


    

    if (fn_allowed_for('ULTIMATE')) 
    {
        $company_id = Registry::get('runtime.company_id');
        if($company_id != 0)
        {
            $transfer_user_id=db_get_field("SELECT user_id FROM ?:users WHERE email = ?s AND company_id = ?i ",$transfer_email_id, $company_id);
        }
    }

    else
    {
      $transfer_user_id=db_get_field("SELECT user_id FROM ?:users WHERE email = ?s",$transfer_email_id);
    }


    $check_amount=is_numeric($_REQUEST['wallet_transfer_system']['transfer_amount']);
    if(empty($check_amount))
    {
      fn_set_notification('W',__("amount_error"),__("please_insert_only_numeric_value"));
      return array(CONTROLLER_STATUS_REDIRECT,$suffix);
    }
    if($_REQUEST['wallet_transfer_system']['transfer_amount'] > $get_user_wallet_amount)
    {
      fn_set_notification('W',__("amount_error"),__("transfer_amount_is_more_than_available_cash"));
      return array(CONTROLLER_STATUS_REDIRECT, $suffix);
    }

    
    
    if($get_transfer_max < $_REQUEST['wallet_transfer_system']['transfer_amount']||$_REQUEST['wallet_transfer_system']['transfer_amount'] < $get_transfer_min)
    {

      $error_msg=__("transfer_limit_is").fn_format_price_by_currency($get_transfer_min,CART_PRIMARY_CURRENCY,CART_SECONDARY_CURRENCY).__("_to_").fn_format_price_by_currency($get_transfer_max,CART_PRIMARY_CURRENCY,CART_SECONDARY_CURRENCY);
      fn_set_notification('W',__("amount_error"),$error_msg);
      return array(CONTROLLER_STATUS_REDIRECT, $suffix);
    }

    if(empty($transfer_user_id))
    {
      fn_set_notification('E',__("user_not_exist"),__("email_user_not_found_at_store"));
      return array(CONTROLLER_STATUS_REDIRECT, $suffix);
    }
    $wallet_transfer_data = $_REQUEST['wallet_transfer_system'];
    // $wallet_transfer_data['transfer_amount'] = fn_format_price_by_currency($wallet_transfer_data['transfer_amount'],CART_SECONDARY_CURRENCY,CART_PRIMARY_CURRENCY);
    fn_create_transfer_for_user($wallet_transfer_data['transfer_email'],$wallet_transfer_data['transfer_amount']);
    fn_set_notification('N',__("success"),__("transfer_completed_successfully"));

    return array(CONTROLLER_STATUS_REDIRECT, "pdrmgh_cscart_wallet.wallet_transactions");
}

if ($mode == 'my_wallet' || $mode == 'wallet_transfer_user_to_user')
{
    if($mode == 'my_wallet'){
    fn_add_breadcrumb(__('my_wallet'));}

    if($mode == 'wallet_transfer_user_to_user')
    {
          fn_add_breadcrumb(__('transfer_wallet_cash'));
        
        if(Registry::get('addons.pdrmgh_cscart_wallet.status_wallet_transfer') == 'N')
        {
          return array(CONTROLLER_STATUS_DENIED);
        }
        if(isset($_REQUEST['email']))
        {
          Registry::get('view')->assign('transfer_email',$_REQUEST['email']);
        }
          if(isset($_REQUEST['amount']))
        {
          Registry::get('view')->assign('transfer_amount',$_REQUEST['amount']);
        }
    }
    
    if ($_SESSION['auth']['user_id'] == 0)
    {
        return array(CONTROLLER_STATUS_REDIRECT, "auth.login_form");
    }
    
    Registry::get('view')->assign('total_cash',fn_get_wallet_amount($wallet_id=null,$user_id=$auth['user_id']));
    Registry::get('view')->assign('primary_currency',CART_PRIMARY_CURRENCY);
  
    if(Registry::get('addons.pdrmgh_cscart_wallet.status_wallet_transfer') == 'Y')
    {
      Registry::get('view')->assign('enable_transfer',"yes");
    }
    
}


if ($mode == 'cash_add_wallet')
 {      
    
    if(empty($_SESSION['auth']['user_id']))
    {
        fn_set_notification("E","wallet_recharge",__("please_login_first"));

         return array(CONTROLLER_STATUS_REDIRECT, "auth.login");
    }
   
    $min = !empty(Registry::get('addons.pdrmgh_cscart_wallet.min_recharge_amount')) ? Registry::get('addons.pdrmgh_cscart_wallet.min_recharge_amount') : 0;
    $max = !empty(Registry::get('addons.pdrmgh_cscart_wallet.max_recharge_amount')) ? Registry::get('addons.pdrmgh_cscart_wallet.max_recharge_amount') : 0;

    $min = fn_format_price_by_currency($min,CART_PRIMARY_CURRENCY,CART_SECONDARY_CURRENCY);

    $max = fn_format_price_by_currency($max,CART_PRIMARY_CURRENCY,CART_SECONDARY_CURRENCY);


    
    if ($_REQUEST['pdrmgh_cscart_wallet']['recharge_amount'] < $min || $_REQUEST['pdrmgh_cscart_wallet']['recharge_amount'] > $max)
     {

        fn_set_notification('W', __('wallet_error'), __('can_not_proceed_please_check_limit'));
        fn_set_notification("N",__("wallet_limit"),__("wallet_limit_is").$min.__("_to_").$max);
        return array(CONTROLLER_STATUS_REDIRECT, "pdrmgh_cscart_wallet.my_wallet");
      }  

    
    if(!empty($_SESSION['cart']['products']))
    {
        fn_set_notification("E",__("wallet_recharge"),__("remove_product_from_cart"));

        return array(CONTROLLER_STATUS_REDIRECT, "pdrmgh_cscart_wallet.my_wallet"); 
    }

    if(!empty($_SESSION['cart']['gift_certificates']))
    {
        fn_set_notification("E","wallet_recharge",__("remove_product_from_cart"));

        return array(CONTROLLER_STATUS_REDIRECT, "pdrmgh_cscart_wallet.my_wallet"); 
    }
   
    $_SESSION['cart']['pdrmgh_cscart_wallet'] = array();

    $_wr = array();
    $_wr[] = TIME;

    $pdrmgh_cscart_wallet=$_REQUEST['pdrmgh_cscart_wallet'];
    $pdrmgh_cscart_wallet['recharge_amount'] = fn_format_price_by_currency($pdrmgh_cscart_wallet['recharge_amount'],CART_SECONDARY_CURRENCY,CART_PRIMARY_CURRENCY);
    
    if (!empty($pdrmgh_cscart_wallet)) {

        foreach ($pdrmgh_cscart_wallet as $k => $v) {
         
                $_wr[] = $v;
            }
        }

    $wallet_cart_id=fn_crc32(implode('_', $_wr));

      if (!empty($wallet_cart_id)) {
                 
                $pdrmgh_cscart_wallet['wallet_cart_id'] = $wallet_cart_id;

                $pdrmgh_cscart_wallet['display_subtotal'] = $pdrmgh_cscart_wallet['recharge_amount'];
         
                $_SESSION['cart']['pdrmgh_cscart_wallet'][$wallet_cart_id] = $pdrmgh_cscart_wallet;

                fn_calculate_cart_content($_SESSION['cart'], $auth, 'S', true, 'F', true);
                
                $pdrmgh_cscart_wallet['display_subtotal'] = $_SESSION['cart']['pdrmgh_cscart_wallet'][$wallet_cart_id]['display_subtotal'];
                                  
                Registry::get('view')->assign('pdrmgh_cscart_wallet', $pdrmgh_cscart_wallet);
                $msg = Registry::get('view')->fetch('views/checkout/components/product_notification.tpl');
                fn_set_notification('I', __('money_added_in_cart_please_make_a_paymnet'), $msg, 'I');
            }

            fn_save_cart_content($_SESSION['cart'], $auth['user_id']);

            if (defined('AJAX_REQUEST')) {
                fn_calculate_cart_content($_SESSION['cart'], $auth, false, false, 'F', false);
            }
    
        return array(CONTROLLER_STATUS_REDIRECT, "pdrmgh_cscart_wallet.my_wallet");
    
 }

 if ($mode == 'wallet_transactions')
  {
    fn_add_breadcrumb(__('wallet_transactions'));
    if ($_SESSION['auth']['user_id'] == 0)
    {
      return array(CONTROLLER_STATUS_REDIRECT, "auth.login_form");
    }
        
    list($wallet_transactions, $search) = fn_get_wallet_transactions($_REQUEST, Registry::get('settings.Appearance.elements_per_page'),$_SESSION['auth']['user_id']);
         
    Registry::get('view')->assign('wallet_transactions', $wallet_transactions);
    Registry::get('view')->assign('search', $search);
  }

    if($mode == 'clear_cart')
    {
       $cart = & $_SESSION['cart'];
       fn_clear_cart($cart);
       fn_set_notification('N','notice',__("clear_cart_successfully"));
        return array(CONTROLLER_STATUS_REDIRECT);
    }

if($mode == 'apply_wallet_cash')
{
  /** @var array $cart */
$cart = &Tygh::$app['session']['cart'];

  fn_calculate_cart_content($cart, $auth, 'E', true, 'F', true);
  $current_wallet_cash=fn_get_wallet_amount(null,$_SESSION['auth']['user_id']);
  $cart_total = Tygh::$app['session']['cart']['total'];

  
  if($cart_total >= $current_wallet_cash)
  {
    $_SESSION['cart']['wallet']['current_cash']= 0.0;
    $_SESSION['cart']['wallet']['used_cash']= $current_wallet_cash;
  }  
  else
  {
    $_SESSION['cart']['wallet']['current_cash']= $current_wallet_cash - $cart_total;
    $_SESSION['cart']['wallet']['used_cash']= $cart_total;
  }  
  return array(CONTROLLER_STATUS_REDIRECT, "checkout.checkout?wallet_cash_applied=yes");
}

if($mode == 'remove_wallet_cash')
{
  if(version_compare(PRODUCT_VERSION, '4.9.3') == 1 )
  {
    $_SESSION['cart']['payment_id']=$_SESSION['cart']['payment']['payment_id'];
  }
  $_SESSION['cart']['payment_id']=$_SESSION['cart']['payment']['payment_id'];
  unset($_SESSION['cart']['wallet']);
  return array(CONTROLLER_STATUS_REDIRECT, "checkout.checkout");
}