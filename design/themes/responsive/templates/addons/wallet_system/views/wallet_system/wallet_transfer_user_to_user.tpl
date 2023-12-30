<style type="text/css">
	.ty-wallet-my_wallet{
		margin-left: 35%;
		border: 1px solid grey;
		display: table;
		padding: 30px;
	}
</style>
<div class="ty-wallet-my_wallet">

{capture name="mainbox_title"}{__("transfer_wallet_cash")}{/capture}
<center><span><img src="images/wallet/icon.png" width="23" /></span>&nbsp;&nbsp;
<strong style="font-size:20px;">{__("total_amount")}&nbsp;&nbsp;{include file="common/price.tpl" value=$total_cash}</strong></center>
	<form action="{"pdrmgh_cscart_wallet.create_transfer"|fn_url}" method="post" name="my_wallet_create_transfer">
		<br>
	    <label class="cm-required cm-email ty-control-group__title" for="this_wallet_enter_email">{__("enter_email_address")}</label>
	    <input type="text" name="wallet_transfer_system[transfer_email]" id="this_wallet_enter_email" class="ty-input-text" value="{$transfer_email}" style="width:100%">
	    <br>
	    <label class="cm-required ty-control-group__title" for="this_wallet_enter_amount">{__("enter_amount")}</label>
	    <input type="text" name="wallet_transfer_system[transfer_amount]" id="this_wallet_enter_amount" class="ty-input-text" value="{$transfer_amount}" style="width:100%">
	    <br>
	    <br>
	    <div style="text-align:center;">
	        {include file="buttons/button.tpl" but_text=__("transfer") but_name="wallet_transfer_submit" but_meta="cm-confirm" }
	   
	    </div>
	</form>
</div>