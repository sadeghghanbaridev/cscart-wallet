{capture name="mainbox"}

<form action="{""|fn_url}" method="post" enctype="multipart/form-data" name="wallet_list_form">

{include file="common/pagination.tpl" save_current_url=true}

{assign var="c_url" value=$config.current_url|fn_query_remove:"sort_by":"sort_order"}
{assign var="c_icon" value="<i class=\"exicon-`$search.sort_order_rev`\"></i>"}
{assign var="c_dummy" value="<i class=\"exicon-dummy\"></i>"}

{if $wallet_transaction}
<div class="table-responsive-wrapper">
<table class="table table-middle sortable table-responsive">
<thead>
    <tr>
     
        <th width="1%">{__("credit_debit_id")}</th>    

        <th width="15%">{__("transaction_type")}</th>

       <th width="16%">{__("reference_id")}</th>
    
       <th width="10%">{__("debit")}</th>

       <th width="10%">{__("credit")}</th>
       
       <th width="13%">{__("total_cash")}</th>
       
       <th width="15%">{__("date")}</th>
        
    </tr>
</thead>

<tbody>

{foreach from=$wallet_transaction item="transaction"}
{assign var=extra_info value=unserialize($transaction.extra_info)}
{assign var=wallet_user_email value=$transaction.wallet_id|fn_pdrmgh_cscart_wallet_get_wallet_user_email_id}
<tr class="cm-row-status">
{if isset($transaction.credit_id)}
    
    <td data-th='{__("credit_debit_id")}' class="row-status">{$transaction.credit_id}{if !empty($transaction.refund_reason)}{include file="common/tooltip.tpl" tooltip=$transaction.refund_reason}{/if}</td>
    
    <td data-th='{__("transaction_type")}' class="row-status"><b>{__("credit")}</b>&nbsp;{if isset($extra_info.sender_email)}({__("bytransfer")}){else}({__({$transaction.source})}){/if}</td>

    {if !empty($transaction.source_id)}
    <td data-th='{__("reference_id")}' class="row-status">{if $transaction.source eq 'refund_rma'}{__("return_id")}<a href="?dispatch=rma.details&return_id={$transaction.source_id}">&nbsp;#{$transaction.source_id}</a>{else}{__("order_id")}<a href="?dispatch=orders.details&order_id={$transaction.source_id}">&nbsp;#{$transaction.source_id}</a>{/if}</td>
    {else}
    <td data-th='{__("reference_id")}' class="row-status">{if isset($extra_info.sender_email)}
                        <b>{__("sender")}:</b> {$extra_info.sender_email}{else}{$wallet_user_email}
                        {/if}</td>
    {/if}   
    <td data-th='{__("debit")}' class="row-status"><b>-</b></td>
    <td data-th='{__("credit")}' class="row-status">{include file="common/price.tpl" value=$transaction.credit_amount}</td> 
    <td data-th='{__("total_cash")}' class="row-status">{include file="common/price.tpl" value=$transaction.total_amount}</td> 
    <td data-th='{__("date")}' class="row-status"> {$transaction.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}</td> 

{else if isset($transaction.debit_id)}

    <td data-th='{__("credit_debit_id")}' class="row-status">{$transaction.debit_id}{if !empty($transaction.debit_reason)}{include file="common/tooltip.tpl" tooltip=$transaction.debit_reason}{/if}</td>
    
    <td data-th='{__("transaction_type")}' class="row-status"><b>{__("debit")}</b>{if !empty($extra_info.reciever_email)}&nbsp;({__("bytransfer")}){else}{if !empty($transaction.source)}({__({$transaction.source})}){/if}{/if}</td>
    {if !empty($transaction.order_id)}
    <td data-th='{__("reference_id")}' class="row-status">{__("order_id")}<a href="?dispatch=orders.details&order_id={$transaction.order_id}">&nbsp;#{$transaction.order_id}</a></td>
    {else}
    <td data-th='{__("reference_id")}' class="row-status">{if !empty($extra_info.reciever_email)}<b>{__("reciever")}: </b>{$extra_info.reciever_email}{else}{$wallet_user_email}{/if}</td>
    {/if}

    <td data-th='{__("debit")}' class="row-status">{include file="common/price.tpl" value=$transaction.debit_amount}</td>
    <td data-th='{__("credit")}' class="row-status"><b>-</b></td> 
    <td data-th='{__("total_cash")}' class="row-status">{include file="common/price.tpl" value=$transaction.remain_amount}</td> 
    <td data-th='{__("date")}' class="row-status"> {$transaction.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}</td> 

{/if}  
</tr>
{/foreach}
</tbody>
</table>
<div class="statistic clearfix" id="orders_total">
        <table class="pull-right ">
            <tr>
                <td class="shift-right"><hr></td>
                <td><hr></td>
            </tr>
            <tr>
                <td class="shift-right">{__("gross_credit_total")}:</td>
                <td>{include file="common/price.tpl" value=$credit_total}</td>
            </tr>
            <tr>
                <td class="shift-right">{__("gross_debit_total")}:</td>
                <td>-{include file="common/price.tpl" value=$debit_total}</td>
            </tr>
            <tr>
                <td class="shift-right"><h4>{__("gross_wallet_cash")}:</h4></td>
                <td class="price">{include file="common/price.tpl" value=$credit_total-$debit_total}</td>
            </tr>
        </table>
</div>
</div>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}


{include file="common/pagination.tpl"}

{capture name="sidebar"}
    {include file="common/saved_search.tpl" dispatch="pdrmgh_cscart_wallet.wallet_transaction" view_type="pdrmgh_cscart_wallet"}
    {include file="addons/pdrmgh_cscart_wallet/views/pdrmgh_cscart_wallet/components/wallet_transaction.tpl"}
{/capture}
{capture name="wallet_dabit_credit"}
    {include file="addons/pdrmgh_cscart_wallet/views/pdrmgh_cscart_wallet/wallet_dabit_credit1.tpl"}
{/capture}
{capture name="adv_buttons"}
     {include file="common/popupbox.tpl" id="wallet_dabit_credit" text=__("wallet_dabit_credit") title=__("wallet_dabit_credit") content=$smarty.capture.wallet_dabit_credit act="general" link_class="cm-dialog-auto-size" icon="icon-plus" link_text=""}
{/capture}
</form>

{/capture}
{include file="common/mainbox.tpl" title=__("wallet_transaction") content=$smarty.capture.mainbox title_extra=$smarty.capture.title_extra tools=$smarty.capture.tools sidebar=$smarty.capture.sidebar adv_buttons=$smarty.capture.adv_buttons buttons=$smarty.capture.buttons}