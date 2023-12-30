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
use Tygh\Registry;

if($mode == 'details')
{
	 $order_info=fn_get_order_info($_REQUEST['order_id']);
 if(isset($order_info['gift_certificates']))
    {

    }
    else
    { 	
      if(empty($order_info['products']))
      {
         Registry::get('view')->assign('wallet_recharge',"yes");
      }
    }  
   
}