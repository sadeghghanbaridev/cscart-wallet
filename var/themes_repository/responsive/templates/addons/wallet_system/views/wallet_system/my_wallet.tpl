<style type="text/css">
	.ty-wallet-my_wallet{
		margin-left: 35%;
		border: 1px solid grey;
		display: table;
		padding: 30px;
	}
</style>
<div class="ty-wallet-my_wallet">

<center><span><img src="images/wallet/icon.png" width="23" /></span>&nbsp;&nbsp;
<strong style="font-size:20px;">{__("total_amount")}&nbsp;&nbsp;{include file="common/price.tpl" value=$total_cash}</strong></center>
<br>
	<form action="{"pdrmgh_cscart_wallet.cash_add_wallet"|fn_url}" method="post" name="my_wallet_add_cash">
		<br><br>
	    <label class="cm-required ty-control-group__title" for="this_wallet_recharge_amount">{__("enter_amount")}</label>
	    <input type="text" name="pdrmgh_cscart_wallet[recharge_amount]" id="this_wallet_recharge_amount" class="ty-input-text" value="{if $current_cash_to_add}{$current_cash_to_add}{/if}" style="width:100%">
	    <input type="text" name="pdrmgh_cscart_wallet[total_cash]" id="wallet_total_cash" class="ty-input-text hidden" value="{$total_cash}" style="width:100%">
	    <br><br>
	    <div style="text-align:center;">
	        {include file="buttons/button.tpl" but_text=__("add_money") but_name="wallet_submit"}
	    </div>
	</form><br>
<center><strong></strong><a href="?dispatch=pdrmgh_cscart_wallet.wallet_transactions">{__("wallet_transactions")}</a></strong></center>
{if isset($enable_transfer)}
<center><strong><a href="?dispatch=pdrmgh_cscart_wallet.wallet_transfer_user_to_user">{__("wallet_transfer")}</a></strong></center>
{/if}</div>

{capture name="mainbox_title"}{__("my_wallet")}{/capture}