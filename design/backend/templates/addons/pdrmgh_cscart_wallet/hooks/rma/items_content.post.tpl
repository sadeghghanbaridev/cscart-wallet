{if $return_info.extra.wallet}
    <div class="control-group">
    
        <label class="control-label"><span>{__("refunded_wallet_money")}</span></label>
        <div class="controls">
        {assign var="return_current_url" value=$config.current_url|escape:"url"}
        {foreach from=$return_info.extra.wallet item="wallet" key="wallet_key"}
            <div>{include file="common/price.tpl" value=$wallet.amount}</div> 
        {/foreach}
        </div>
    </div>
{/if}