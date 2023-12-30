<?php

use Tygh\Registry;


if($mode == 'edit')
{
    $order_info = fn_get_order_info($_REQUEST['order_id']);
    Registry::get('view')->assign('order_info',$order_info);
  
}


if($mode == 'update')
{

	if(isset($_SESSION['cart']['order_id']))
	{
		$order_info = fn_get_order_info($_SESSION['cart']['order_id']);
		//fn_print_r($order_info);
		Registry::get('view')->assign('order_info',$order_info);
		if(isset($order_info['wallet']['used_cash']))
		{
			$remaining_wallet_amount=$order_info['wallet']['used_cash']-$order_info['wallet_refunded_amount'];
			if($_SESSION['cart']['total']<=$remaining_wallet_amount)
			{
				$_SESSION['cart']['payment_id']=0;
				$payment_data = fn_get_payment_method_data(0);
				Registry::get('view')->assign('payment_method', $payment_data);
				Registry::get('view')->assign('hidden_payment', 1);
			}
			if($_SESSION['cart']['total']>$remaining_wallet_amount)
			{ 
				if(empty($_SESSION['cart']['payment_id']))
				{
					if(isset($customer_auth['usergroup_ids']))
					{
						 $payment_methods = fn_get_payments(array('usergroup_ids' => $customer_auth['usergroup_ids']));
		        	 	  Registry::get('view')->assign('payment_method', $payment_data);
					}
					
		        	 Registry::get('view')->assign('hidden_payment', 0);
				}
			}
		}

	}
}
if($mode == 'update_totals')
{
	if(isset($_SESSION['cart']['order_id']))
	{
		$order_info = fn_get_order_info($_SESSION['cart']['order_id']);
		if(isset($order_info['wallet']['used_cash']))
		{
			$remaining_wallet_amount=$order_info['wallet']['used_cash']-$order_info['wallet_refunded_amount'];
			if($_SESSION['cart']['total']<=$remaining_wallet_amount)
			{
				$_SESSION['cart']['payment_id']=0;
				$order_info = fn_get_order_info($_SESSION['cart']['order_id']);
				$payment_data = fn_get_payment_method_data(0);
				Registry::get('view')->assign('payment_method', $payment_data);
				Registry::get('view')->assign('hidden_payment', 1);
				
			}
			if($_SESSION['cart']['total']>$remaining_wallet_amount)
			{
				if(empty($_SESSION['cart']['payment_id']))
				{
					if(isset($customer_auth['usergroup_ids']))
					{
						  $payment_methods = fn_get_payments(array('usergroup_ids' => $customer_auth['usergroup_ids']));
		        	 	  Registry::get('view')->assign('payment_method', $payment_data);
					}
		        	 Registry::get('view')->assign('hidden_payment', 0);
				}
			}
		}
	}
}






