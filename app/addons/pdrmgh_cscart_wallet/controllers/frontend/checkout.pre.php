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

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}


if ($mode == 'checkout') {
    if (!empty($_SESSION['auth']['user_id'])) {
        if (isset($_REQUEST['wallet_cash_applied'])) {
        } else {
                
                $_SESSION['cart']['wallet']['current_cash']=fn_get_wallet_amount(null, $_SESSION['auth']['user_id']);

                if(isset($_SESSION['cart']['wallet']['used_cash']))
                {
                    
                    $current_cash = fn_get_wallet_amount(null, $_SESSION['auth']['user_id']);
                    if (empty($_SESSION['cart']['wallet']['used_cash'])) {
                        $_SESSION['cart']['wallet']['used_cash'] = 0;
                    }
                    if ($_SESSION['cart']['wallet']['used_cash'] > $current_cash) {
                        $_SESSION['cart']['wallet']['used_cash'] = $current_cash;
                    }
                    $_SESSION['cart']['wallet']['current_cash']=$current_cash -$_SESSION['cart']['wallet']['used_cash'];
                }
        }
        if (isset($_SESSION['cart']['pdrmgh_cscart_wallet'])) {
        } else {
            Registry::get('view')->assign('show_wallet', "yes");
        }
    }
}
