
{if isset($order_info.wallet)}

	{if $order_info.total == $order_info.wallet.used_cash}
		<div class="control-group">
		    <div class="control-label">{__("method")}</div>
		    <div id="tygh_payment_info" class="controls">{__("none")}</div>
		</div>
	{else}
		<div class="control-group">
		    <div class="control-label">{__("amount")}</div>
		    <div id="tygh_payment_info" class="controls">{include file="common/price.tpl" value=$order_info.total-$order_info.wallet.used_cash}</div>
		</div>		
	{/if}

	<div class="control-group shift-top">
	    <div class="control-label">
	        <h5>{__("wallet_payment_prepaid")}</h5>
	    </div>
	</div>
	<div class="control-group">
	    <div class="control-label">{__("amount")}</div>
	    <div id="tygh_payment_info" class="controls">{include file="common/price.tpl" value=$order_info.wallet.used_cash}</div>
	</div>
{/if}