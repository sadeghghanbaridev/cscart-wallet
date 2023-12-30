<div class="ty-wallet-credit cm-pagination cm-history cm-pagination-button">
    <form action="{""|fn_url}" method="post" name="wallet_transaction_form">
        {assign var="c_url" value=$config.current_url|fn_query_remove:"sort_by":"sort_order"}
        {assign var="c_icon" value="<i class=\"exicon-`$search.sort_order_rev`\"></i>"}
        {assign var="c_dummy" value="<i class=\"exicon-dummy\"></i>"}
        {include file="common/pagination.tpl"}
        <table class="ty-table ty-wallet-credit__table">
            <thead>    
                <tr>
                    <th style="width: 10%"><a >{__("credit_debit_id")}</a></th>
                    <th style="width: 15%">{__("transaction_type")}</th>
                    <th style="width: 15%">{__("reference_id")}</th>
                    <th style="width: 10%">{__("credit")}</th>
                    <th style="width: 10%">{__("debit")}</th>
                    <th style="width: 10%">{__("total_cash")}</th>
                    <th style="width: 15%">{__("timestamp")}</th>
                </tr>
            </thead>
            <tbody>
            {foreach from=$wallet_transactions item="transaction"}
                {assign var=extra_info value=unserialize($transaction.extra_info)}
                {assign var=wallet_user_email value=$transaction.wallet_id|fn_pdrmgh_cscart_wallet_get_wallet_user_email_id}
                <tr>
                {if isset($transaction.credit_id)}
                    <td><strong>#{$transaction.credit_id}</strong></a>{include file="common/tooltip.tpl" tooltip=$transaction.refund_reason}</td>

                    <td><b>{__("credit")}</b>{if isset($extra_info.sender_email)}({__("bytransfer")}){else}({__({$transaction.source})}){/if}</td>
                    {if !empty($transaction.source_id)}
                    <td>{if $transaction.source eq 'refund_rma'}{__("return_id")}<a href="?dispatch=rma.details&return_id={$transaction.source_id}">&nbsp;#{$transaction.source_id}</a>{else}{__("order_id")}<a href="?dispatch=orders.details&order_id={$transaction.source_id}">&nbsp;#{$transaction.source_id}</a>{/if}</td>
                    {else}
                    <td>{if isset($extra_info.sender_email)}
                        {__("sender")}: {$extra_info.sender_email}{else}{$wallet_user_email}
                        {/if}</td>
                    {/if}
                    <td>{include file="common/price.tpl" value=$transaction.credit_amount}</td>
                    <td>-</td>
                    <td>{include file="common/price.tpl" value=$transaction.total_amount}</td>
                    <td>{$transaction.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}</td>

                {else if isset($transaction.debit_id)} 
                
                    <td class="row-status"><strong>#{$transaction.debit_id}</strong></a>{if !empty($transaction.debit_reason)}{include file="common/tooltip.tpl" tooltip=$transaction.debit_reason}{/if}</td>
                 
                    <td class="row-status"><b>{__("debit")}</b>{if !empty($extra_info.reciever_email)}&nbsp;({__("bytransfer")}){else}{if !empty($transaction.source)}({__({$transaction.source})}){/if}{/if}</td>
                    {if !empty($transaction.order_id)}
                    <td class="row-status">{__("order_id")}<a href="?dispatch=orders.details&order_id={$transaction.order_id}">&nbsp;#{$transaction.order_id}</a></td>
                    {else}
                    <td class="row-status">{if !empty($extra_info.reciever_email)}{__("reciever")}: {$extra_info.reciever_email}&nbsp;({__("transfer")}){else}{$wallet_user_email}{/if}</td>
                    {/if}
                    <td class="row-status">-</td> 
                    <td class="row-status">{include file="common/price.tpl" value=$transaction.debit_amount}</td> 
                    <td class="row-status">{include file="common/price.tpl" value=$transaction.remain_amount}</td>
                    <td class="row-status"> {$transaction.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}</td> 
                {/if}       
                </tr>
            {foreachelse}
                <tr class="ty-table__no-items">
                    <td colspan="7"><p class="ty-no-items">{__("no_data")}</p></td>
                </tr>
            {/foreach}
            </tbody>
        </table>
        {include file="common/pagination.tpl"}
    </form>
 {capture name="mainbox_title"}{__("wallet_transactions")}{/capture}
</div>


