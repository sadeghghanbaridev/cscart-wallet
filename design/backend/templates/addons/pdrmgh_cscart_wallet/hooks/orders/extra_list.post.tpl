
{if isset($wallet_recharge)}

<tr>
                <td>
                    <div class="order-product-image">
                        {include file="common/image.tpl" image=$oi.main_pair.icon|default:$oi.main_pair.detailed image_id=$oi.main_pair.image_id image_width=50 href="products.update?product_id=`$oi.product_id`"|fn_url}
                    </div>
                    <div class="order-product-info">
                        {__("wallet_recharge")}
                    </div>
                </td>
                <td class="nowrap">{include file="common/price.tpl" value=$order_info.display_subtotal}
                    </td>
                <td class="center">1
                    &nbsp;
                </td>
                {if $order_info.use_discount}
                <td class="nowrap">
                    {if $oi.extra.discount|floatval}{include file="common/price.tpl" value=$oi.extra.discount}{else}-{/if}</td>
                {/if}
                {if $order_info.taxes && $settings.General.tax_calculation != "subtotal"}
                <td class="nowrap">
                    {if $oi.tax_value|floatval}{include file="common/price.tpl" value=$oi.tax_value}{else}-{/if}</td>
                {/if}
                <td class="right"><span>{include file="common/price.tpl" value=$order_info.display_subtotal}</span></td>
            </tr>

{else} 
{if $show_wallet_refund && ($order_info.pay_by_wallet_amount gt 0.0 ||$order_info.wallet_refunded_amount gt 0.0)}
<div>{__('total_refunded_amount')}= {include file="common/price.tpl" value=$order_info.wallet_refunded_amount}</div>
<div><a href="{$config.admin_index}?dispatch=pdrmgh_cscart_wallet.refund_in_wallet&order_id={$order_info.order_id}" class="btn
btn-primary">{__("wallet_refund")}</a></div>  
{/if}         
{/if}            