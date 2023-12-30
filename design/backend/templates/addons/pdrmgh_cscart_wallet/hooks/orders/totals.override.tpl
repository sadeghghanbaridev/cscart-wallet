
  {hook name="orders:totals"}
            <div class="order-notes statistic">

            <div class="clearfix">
            <table class="pull-right">
                <tr class="totals">
                    <td>&nbsp;</td>
                    <td width="100px"><h4>{__("totals")}</h4></td>
                </tr>

                <tr>
                    <td>{__("subtotal")}:</td>
                    <td data-ct-totals="subtotal">{include file="common/price.tpl" value=$order_info.display_subtotal}</td>
                </tr>

                {if $order_info.display_shipping_cost|floatval}
                    <tr>
                        <td>{__("shipping_cost")}:</td>
                        <td data-ct-totals="shipping_cost">{include file="common/price.tpl" value=$order_info.display_shipping_cost}</td>
                    </tr>
                {/if}

                {if $order_info.discount|floatval}
                    <tr>
                        <td>{__("including_discount")}:</td>
                        <td data-ct-totals="including_discount">{include file="common/price.tpl" value=$order_info.discount}</td>
                    </tr>
                {/if}

                {if $order_info.subtotal_discount|floatval}
                    <tr>
                        <td>{__("order_discount")}:</td>
                        <td data-ct-totals="order_discount">{include file="common/price.tpl" value=$order_info.subtotal_discount}</td>
                    </tr>
                {/if}

                {if $order_info.coupons}
                    {foreach from=$order_info.coupons key="coupon" item="_c"}
                        <tr>
                            <td>{__("discount_coupon")}:</td>
                            <td data-ct-totals="discount_coupon">{$coupon}</td>
                        </tr>
                    {/foreach}
                {/if}

                {if $order_info.taxes}
                    <tr>
                        <td>{__("taxes")}:</td>
                        <td>&nbsp;</td>
                    </tr>

                    {foreach from=$order_info.taxes item="tax_data"}
                        <tr>
                            <td>&nbsp;<span>&middot;</span>&nbsp;{$tax_data.description}&nbsp;{include file="common/modifier.tpl" mod_value=$tax_data.rate_value mod_type=$tax_data.rate_type}{if $tax_data.price_includes_tax == "Y" && ($settings.Appearance.cart_prices_w_taxes != "Y" || $settings.General.tax_calculation == "subtotal")}&nbsp;{__("included")}{/if}{if $tax_data.regnumber}&nbsp;({$tax_data.regnumber}){/if}</td>
                            <td data-ct-totals="taxes-{$tax_data.description}">{include file="common/price.tpl" value=$tax_data.tax_subtotal}</td>
                        </tr>
                    {/foreach}
                {/if}

                {if $order_info.tax_exempt == "Y"}
                    <tr>
                        <td>{__("tax_exempt")}</td>
                        <td>&nbsp;</td>
                    </tr>
                {/if}

                {if $order_info.payment_surcharge|floatval && !$take_surcharge_from_vendor}
                    <tr>
                        <td>{$order_info.payment_method.surcharge_title|default:__("payment_surcharge")}:</td>
                        <td data-ct-totals="payment_surcharge">{include file="common/price.tpl" value=$order_info.payment_surcharge}</td>
                    </tr>
                {/if}

                {hook name="orders:totals_content"}
                {/hook}
                <tr>
                    <td><h4>{__("total")}:</h4></td>
                    <td class="price" data-ct-totals="total">{include file="common/price.tpl" value=$order_info.total}</td>
                </tr>
                 {if $order_info.payment_id==6 && $order_info.wallet.used_cash>0}
                     <tr>
                        <td><h4>{__("un_paid_amount")}:</h4></td>
                        <td class="price" data-ct-totals="total">{include file="common/price.tpl" value=$order_info.total-$order_info.wallet.used_cash}</td>
                    </tr>        
                {/if}
               
            </table>
            </div>

            <div class="note clearfix">
                <div class="span6">
                    <label for="notes">{__("customer_notes")}</label>
                    <textarea class="span12" name="update_order[notes]" id="notes" cols="40" rows="5">{$order_info.notes}</textarea>
                </div>
                <div class="span6">
                    <label for="details">{__("staff_only_notes")}</label>
                    <textarea class="span12" name="update_order[details]" id="details" cols="40" rows="5">{$order_info.details}</textarea>
                </div>
            </div>

            </div>
        {/hook}
