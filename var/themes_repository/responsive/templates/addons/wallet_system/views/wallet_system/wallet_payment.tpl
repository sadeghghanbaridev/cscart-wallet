{if isset($show_wallet)}
<div class="litecheckout__item" id="checkout_pdrmgh_cscart_wallet">
    <span style="font-size:18px"></i>&nbsp;&nbsp;&nbsp;&nbsp;{__("available_wallet_cash")}:&nbsp;<b>{include file="common/price.tpl" value=$cart.wallet.current_cash}</b></span>

    {if isset($cart.wallet.used_cash)}
    
    <span>&nbsp;&nbsp;&nbsp;&nbsp;<i class="ty-icon-ok" ></i>{__("applied_wallet_cash")}:&nbsp;<b>{include file="common/price.tpl" value=$cart.wallet.used_cash}</b>&nbsp;<a href="{$config.customer_index}?dispatch=pdrmgh_cscart_wallet.remove_wallet_cash" class="cm-ajax cm-ajax-force cm-ajax-full-render" data-ca-target-id="checkout_*,litecheckout_*"><i title="Remove" class="ty-icon-cancel-circle" style="color:red; font-size:16px"></i></a></span>
    {else}
        {if $cart.wallet.current_cash > 0.00}
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="{$config.customer_index}?dispatch=pdrmgh_cscart_wallet.apply_wallet_cash" class="ty-btn ty-btn__secondary cm-ajax cm-ajax-force cm-ajax-full-render" data-ca-target-id="checkout_*,litecheckout_*">{__("use_wallet")}</a>
        {/if}
    {/if} 
<!--checkout_pdrmgh_cscart_wallet--></div>

{/if}
فرو