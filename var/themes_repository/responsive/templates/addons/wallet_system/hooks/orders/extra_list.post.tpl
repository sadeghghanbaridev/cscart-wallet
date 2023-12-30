    {if isset($wallet_recharge)}
                            <tr class="ty-valign-top">
                                <td>
                                    {__("wallet_recharge")}
                                    
                                </td>
                                <td class="ty-right">
                                    {if $product.extra.exclude_from_calculate}{__("free")}{else}{include file="common/price.tpl" value=$order_info.total-$order_info.payment_surcharge}{/if}
                                </td>
                                <td class="ty-center">&nbsp;1</td>
                                {if $order_info.use_discount}
                                    <td class="ty-right">
                                        {if $product.extra.discount|floatval}{include file="common/price.tpl" value=$product.extra.discount}{else}-{/if}
                                    </td>
                                {/if}
                                {if $order_info.taxes && $settings.General.tax_calculation != "subtotal"}
                                    <td class="ty-center">
                                        {if $product.tax_value|floatval}{include file="common/price.tpl" value=$product.tax_value}{else}-{/if}
                                    </td>
                                {/if}
                                <td class="ty-right">
                                     &nbsp;{include file="common/price.tpl" value=$order_info.total-$order_info.payment_surcharge}
                                 </td>
                            </tr>
    {/if}                        