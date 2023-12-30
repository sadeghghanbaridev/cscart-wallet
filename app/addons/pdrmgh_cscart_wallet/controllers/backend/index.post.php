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

use Tygh\Enum\ProductTracking;
use Tygh\Registry;
use Tygh\Settings;
use Tygh\Tools\DateTimeHelper;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

// Generate dashboard
if ($mode == 'index') {

     // Check for feedback request
    if (
        (!Registry::get('runtime.company_id') || Registry::get('runtime.simple_ultimate'))
        && (Registry::get('settings.General.feedback_type') == 'auto' || fn_allowed_for('ULTIMATE:FREE'))
        && fn_is_expired_storage_data('send_feedback', SECONDS_IN_DAY * 30)
    ) {
        $redirect_url = 'feedback.send?action=auto&redirect_url=' . urlencode(Registry::get('config.current_url'));

        return array(CONTROLLER_STATUS_REDIRECT, $redirect_url);
    }

    $time_periods = array(
        DateTimeHelper::PERIOD_TODAY,
        DateTimeHelper::PERIOD_YESTERDAY,
        DateTimeHelper::PERIOD_THIS_MONTH,
        DateTimeHelper::PERIOD_LAST_MONTH,
        DateTimeHelper::PERIOD_THIS_YEAR,
        DateTimeHelper::PERIOD_LAST_YEAR,
    );

    $time_period = DateTimeHelper::getPeriod(DateTimeHelper::PERIOD_MONTH_AGO_TILL_NOW);

    // Predefined period selected
    if (isset($_REQUEST['time_period']) && in_array($_REQUEST['time_period'], $time_periods)) {
        $time_period = DateTimeHelper::getPeriod($_REQUEST['time_period']);

        fn_set_session_data('dashboard_selected_period', serialize(array(
            'period' => $_REQUEST['time_period']
        )));
    }
    // Custom period selected
    elseif (isset($_REQUEST['time_from'], $_REQUEST['time_to'])) {
        $time_period = DateTimeHelper::createCustomPeriod('@' . $_REQUEST['time_from'], '@' . $_REQUEST['time_to']);

        fn_set_session_data('dashboard_selected_period', serialize(array(
            'from' => $time_period['from']->format(DateTime::ISO8601),
            'to' => $time_period['to']->format(DateTime::ISO8601),
        )));
    }
    // Fallback to previously saved period
    elseif ($timeframe = fn_get_session_data('dashboard_selected_period')) {
        $timeframe = unserialize($timeframe);

        if (isset($timeframe['period']) && in_array($timeframe['period'], $time_periods)) {
            $time_period = DateTimeHelper::getPeriod($timeframe['period']);
        } elseif (isset($timeframe['from'], $timeframe['to'])) {
            $time_period = DateTimeHelper::createCustomPeriod($timeframe['from'], $timeframe['to']);
        }
    }

    $timestamp_from = $time_period['from']->getTimestamp();
    $timestamp_to = $time_period['to']->getTimestamp();

    $time_difference = $timestamp_to - $timestamp_from;
    $is_day = ($timestamp_to - $timestamp_from) <= SECONDS_IN_DAY ? true : false;

    $stats = '';

    if (!defined('HTTPS')) {
        $stats .= base64_decode('PGltZyBzcmM9Imh0dHA6Ly93d3cuY3MtY2FydC5jb20vaW1hZ2VzL2JhY2tncm91bmQuZ2lmIiBoZWlnaHQ9IjEiIHdpZHRoPSIxIiBhbHQ9IiIgLz4=');
    }

    /* Order */
    $orders_stat = array();

    if (fn_check_view_permissions('orders.manage', 'GET') || fn_check_view_permissions('sales_reports.view', 'GET') || fn_check_view_permissions('taxes.manage', 'GET')) {
        $params = array(
            'period' => 'C',
            'time_from' => $timestamp_from,
            'time_to' => $timestamp_to,
        );
        list($orders_stat['orders'], $search_params, $orders_stat['orders_total']) = fn_get_orders($params, 0, true);
        //changes//
        $orders_stat['orders_total'] = fn_display_order_totals($orders_stat['orders']);
        $time_difference = $timestamp_to - $timestamp_from;
        $params = array(
            'period' => 'C',
            'time_from' => $timestamp_from - $time_difference,
            'time_to' => $timestamp_to - $time_difference,
        );
        list($orders_stat['prev_orders'], $search_params, $orders_stat['prev_orders_total']) = fn_get_orders($params, 0, true);
        $orders_stat['prev_orders_total'] = fn_display_order_totals($orders_stat['prev_orders']);

        $orders_stat['diff']['orders_count'] = count($orders_stat['orders']) - count($orders_stat['prev_orders']);

        $orders_stat['diff']['sales'] = fn_calculate_differences($orders_stat['orders_total']['totally_paid'], $orders_stat['prev_orders_total']['totally_paid']);
    }

    /* Abandoned carts */
    $company_condition = '';

    if (fn_allowed_for('ULTIMATE')) {
        $company_condition = fn_get_company_condition('?:user_session_products.company_id');
    }

    if (fn_check_view_permissions('cart.cart_list', 'GET')) {
        $orders_stat['abandoned_cart_total'] = count(db_get_fields('SELECT COUNT(*) FROM ?:user_session_products WHERE `timestamp` BETWEEN ?i AND ?i ?p GROUP BY user_id', $timestamp_from, $timestamp_to, $company_condition));
        $orders_stat['prev_abandoned_cart_total'] = count(db_get_fields('SELECT COUNT(*) FROM ?:user_session_products WHERE `timestamp` BETWEEN ?i AND ?i ?p GROUP BY user_id', $timestamp_from - $time_difference, $timestamp_to - $time_difference, $company_condition));

        $orders_stat['diff']['abandoned_carts'] = fn_calculate_differences($orders_stat['abandoned_cart_total'], $orders_stat['prev_abandoned_cart_total']);
    }

    // Calculate orders taxes.
    if (fn_check_view_permissions('taxes.manage', 'GET')) {
        $orders_stat['taxes']['subtotal'] = fn_get_orders_taxes_subtotal($orders_stat['orders'], $search_params);
        $orders_stat['taxes']['prev_subtotal'] = fn_get_orders_taxes_subtotal($orders_stat['prev_orders'], $search_params);

        $orders_stat['taxes']['diff'] = fn_calculate_differences($orders_stat['taxes']['subtotal'], $orders_stat['taxes']['prev_subtotal']);
    }

    if (!fn_check_view_permissions('orders.manage', 'GET')) {
        $orders_stat['orders'] = array();
        $orders_stat['prev_orders'] = array();
    }

    if (!fn_check_view_permissions('sales_reports.view', 'GET')) {
        $orders_stat['orders_total'] = array();
        $orders_stat['prev_orders_total'] = array();
    }
    /* /Orders */

    /* Order statuses */
    $order_statuses = array();

    if (fn_check_view_permissions('orders.manage', 'GET')) {
        $order_statuses = fn_get_statuses(STATUSES_ORDER, array(), false, true, CART_LANGUAGE);
    }
    /* /Order statuses */

    /* Recent activity block */
    $logs = array();

    if (fn_check_view_permissions('logs.manage', 'GET')) {
        list($logs, $search) = fn_get_logs(array('time_from' => $timestamp_from, 'time_to' => $timestamp_to, 'period' => 'C'), 10); // Get last 10 items
    }
    /* /Recent activity block */

    /* Order by statuses */
    $order_by_statuses = array();

    if (fn_check_view_permissions('orders.manage', 'GET')) {
        $company_condition = fn_get_company_condition('?:orders.company_id');

        $order_by_statuses = db_get_array(
            "SELECT "
                . " ?:status_descriptions.description as status_name,"
                . " ?:orders.status,"
                . " COUNT(*) as count,"
                . " SUM(?:orders.total) as total,"
                . " SUM(?:orders.shipping_cost) as shipping"
            . " FROM ?:orders"
            . " INNER JOIN ?:statuses"
                . " ON ?:statuses.status = ?:orders.status"
            . " INNER JOIN ?:status_descriptions"
                . " ON ?:status_descriptions.status_id = ?:statuses.status_id"
            . " WHERE ?:statuses.type = ?s"
                . " AND ?:orders.timestamp > ?i"
                . " AND ?:orders.timestamp < ?i"
                . " AND ?:status_descriptions.lang_code = ?s"
                . " ?p "
            . " GROUP BY ?:orders.status",
            STATUSES_ORDER,
            $timestamp_from,
            $timestamp_to,
            CART_LANGUAGE,
            $company_condition
        );
    }
    /* /Order by statuses */

    /* Statistics */
    $graphs = fn_dashboard_get_graphs_data($timestamp_from, $timestamp_to, $is_day);
    /* /Statistics */

    if (!empty(Tygh::$app['session']['stats'])) {
        $stats .= implode('', Tygh::$app['session']['stats']);
        unset(Tygh::$app['session']['stats']);
    }
   
     Tygh::$app['view']->assign('orders_stat', $orders_stat);
}

//
// Calculate gross total and totally paid values for the current set of orders
//
function fn_display_order_totals($orders)
{
    $wallet_recharge_orders = array();
    $wallet_recharge_orders = db_get_fields("SELECT order_id FROM ?:wallet_offline_payment");
    $result = array();
    $result['gross_total'] = 0;
    $result['totally_paid'] = 0;
    if (is_array($orders)) {
        foreach ($orders as $k => $v) {
            $result['gross_total'] += $v['total'];
            if ($v['status'] == 'C' || $v['status'] == 'P') {
                if(!in_array($v['order_id'], $wallet_recharge_orders))
                $result['totally_paid'] += $v['total'];
            }
        }
    }
    return $result;
}