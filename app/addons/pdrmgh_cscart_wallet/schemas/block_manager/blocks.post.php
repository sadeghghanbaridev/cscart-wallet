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

require_once Registry::get('config.dir.schemas') . 'block_manager/blocks.functions.php';

if (version_compare(PRODUCT_VERSION, 4.9) == '1') {
    $schema['lite_checkout_pdrmgh_cscart_wallet'] = array( 
        'show_on_locations' => ['checkout'],
        'templates'         => 'addons/pdrmgh_cscart_wallet/views/pdrmgh_cscart_wallet/wallet_payment.tpl',
        'wrappers'          => 'blocks/lite_checkout/wrappers',
    );
}

return $schema;