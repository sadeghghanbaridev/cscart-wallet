{if $cart.pdrmgh_cscart_wallet}

{assign var="c_url" value=$config.current_url|escape:url}

{foreach from=$cart.pdrmgh_cscart_wallet item="gift" key="gift_key" name="f_gift_certificates"}
{assign var="obj_id" value=$gift.object_id|default:$gift_key}
{if !$smarty.capture.prods}
    {capture name="prods"}Y{/capture}
{/if}
<tr>   
       <td><img src="images/wallet/icon.png" width="50" height="50"></td>
       <td class="ty-cart-content__product-elem ty-cart-content__description" style="width: 50%;">
        
            <strong>{__("wallet_recharge")}</strong>

            <div class="ty-control-group">
                <label class="ty-control-group__label">{__("amount")}:</label><span class="ty-control-group__item">{include file="common/price.tpl" value=$gift.recharge_amount}</span>
            </div>
        
        </td>
        <td class="ty-cart-content__product-elem ty-cart-content__price cm-reload-{$obj_id}" id="price_display_update_{$obj_id}">
                        {include file="common/price.tpl" value=$product.display_price span_id="product_price_`$key`" class="ty-sub-price"}
                        <!--price_display_update_{$obj_id}--></td>
         <td class="ty-cart-content__product-elem ty-cart-content__qty">&nbsp;&nbsp;&nbsp;1</td>

        <td class="ty-cart-content__product-elem ty-cart-content__price cm-reload-{$obj_id}" id="price_display_update_{$obj_id}">
                        {include file="common/price.tpl" value=$gift.recharge_amount}
                        <!--price_display_update_{$obj_id}--></td>
</tr>
{/foreach}

{/if}
