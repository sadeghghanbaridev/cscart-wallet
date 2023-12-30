{if isset($show_wallet)}
<div>
	<h3 class="ty-step__title-active clearfix">
	    <span class="ty-step__title-left"><img src="images/wallet/icon.png" width="20"></span>
	    <span class="ty-step__title-txt">&nbsp;&nbsp;{__("user_wallet")}</span>
	</h3>
    <span style="font-size:18px"></i>&nbsp;&nbsp;&nbsp;&nbsp;{__("available_wallet_cash")}:&nbsp;<b>{include file="common/price.tpl" value=$cart.wallet.current_cash}</b></span>

    {if isset($cart.wallet.used_cash)}
    
    <span style="color:green">&nbsp;&nbsp;&nbsp;&nbsp;<i class="ty-icon-ok" ></i>{__("applied_wallet_cash")}:&nbsp;<b>{include file="common/price.tpl" value=$cart.wallet.used_cash}</b>&nbsp;<a href="{$config.customer_index}?dispatch=pdrmgh_cscart_wallet.remove_wallet_cash" class="cm-ajax cm-ajax-force cm-ajax-full-render" data-ca-target-id="checkout_*"><i title="Remove" class="ty-icon-cancel-circle" style="color:red; font-size:16px"></i></a></span>
    {else}
        {if $cart.wallet.current_cash > 0.00}
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="{$config.customer_index}?dispatch=pdrmgh_cscart_wallet.apply_wallet_cash" class="ty-btn ty-btn__secondary cm-ajax cm-ajax-force cm-ajax-full-render" data-ca-target-id="checkout_*">{__("use_wallet")}</a>
        {/if}
    {/if} 
    <br>
    <br>
</div>
<div class="ty-checkout-buttons ty-checkout-buttons__submit-order"></div>
<br><br>
<!--cm-ajax cm-ajax-force cm-ajax-full-render  -->
<!-- <i class="ty-icon-cancel" style="color:red"> -->
{/if}