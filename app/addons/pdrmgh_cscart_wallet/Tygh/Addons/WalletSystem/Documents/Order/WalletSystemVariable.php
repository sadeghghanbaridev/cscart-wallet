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

namespace Tygh\Addons\WalletSystem\Documents\Order;


use Tygh\Template\Document\Order\Context;
use Tygh\Template\IActiveVariable;
use Tygh\Template\IVariable;
use Tygh\Tools\Formatter;

/**
 * Class WalletSystemVariable
 * @package Tygh\Addons\WalletSystem\Documents\Order
 */
class WalletSystemVariable implements IVariable
{
    public $paid_by_wallet;
    
    public function __construct(Context $context, Formatter $formatter)
    {
        $order = $context->getOrder();
    //    fn_print_r($order);
        if (!empty($order->data['pay_by_wallet_amount'])) {
            $this->paid_by_wallet = $formatter->asPrice($order->data['pay_by_wallet_amount']); 
        }
    }

    /**
     * @inheritDoc
     */
   
}