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

use Tygh\Models\VendorPlan;
use Tygh\Registry;

if (!defined('AREA')) {
		die('Access denied');
}

function fn_pdrmgh_cscart_wallet_get_vendor_commission_data($order_info, $plan_id)
{
	if ($plan_id
				&& $plan = VendorPlan::model()->find($plan_id)
		) {
		$commission = $order_info['total'] > 0 ? $plan->commission : 0;
		$commission_amount = 0;

		//Calculate commission amount and check if we need to include shipping cost
		$shipping_cost = Registry::get('addons.vendor_plans.include_shipping') == 'N' ? $order_info['shipping_cost'] : 0;
		$commission_amount = ($order_info['total'] - $shipping_cost) * $commission / 100;

		//Check if we need to take payment surcharge from vendor
		if (Registry::get('addons.vendor_plans.include_payment_surcharge') == 'Y') {
			$commission_amount += $order_info['payment_surcharge'];
		}

		if ($commission_amount > $order_info['total']) {
			$commission_amount = $order_info['total'];
		}

		$data['commission'] = $commission;
		$data['commission_amount'] = $commission_amount;
		$data['commission_type'] = 'P'; // Backward compatibility
	}
		return $data;
}