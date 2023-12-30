{capture name="mainbox"}

<form action="{""|fn_url}" method="post" enctype="multipart/form-data" name="wallet_users_form">

{include file="common/pagination.tpl" save_current_url=true}

{assign var="c_url" value=$config.current_url|fn_query_remove:"sort_by":"sort_order"}
{assign var="c_icon" value="<i class=\"exicon-`$search.sort_order_rev`\"></i>"}
{assign var="c_dummy" value="<i class=\"exicon-dummy\"></i>"}

{if $wallet_users}
<div class="table-responsive-wrapper">
<table class="table table-middle sortable table-responsive">
<thead>
    <tr>
     
        <th width="8%">{__("wallet_id")}</th>    

        <th width="8%">{__("user_id")}</th>

        <th width="20%">{__("user_email")}</th>
        <th width="20%">{__("total_credit")}</th>
        <th width="20%">{__("total_debit")}</th>
       <th width="19%">{__("current_cash")}</th>
       <th width="5%">&nbsp;</th>
        
    </tr>
</thead>

<tbody>
{foreach from=$wallet_users item="wallet_user"}

{assign var="user_info" value=fn_get_user_info($wallet_user.user_id)}
{if $user_info}
    <tr class="cm-row-status">
        <td data-th='{__("wallet_id")}' class="row-status">{$wallet_user.wallet_id}</td>
        <td data-th='{__("user_id")}' class="row-status">{$wallet_user.user_id}</td>
        <td data-th='{__("user_email")}' class="row-status">{if $user_info}{fn_get_user_email(fn_get_wallet_user_id($wallet_user.wallet_id))}{else}{__('user_deleted')}{/if}</td>
        <td data-th='{__("total_credit")}' class="row-status">{include file="common/price.tpl" value=fn_get_total_credit_wallet($wallet_user.wallet_id)}</td> 
        <td data-th='{__("total_debit")}' class="row-status">{include file="common/price.tpl" value=fn_get_total_debit_wallet($wallet_user.wallet_id)}</td> 
        <td data-th='{__("current_cash")}' class="row-status">{include file="common/price.tpl" value=$wallet_user.total_cash}</td>
        <td class="nowrap">
            {if $user_info}
            {capture name="tools_items"}
            {hook name="companies:list_extra_links"}           
                
                <li>{btn type="list" href="pdrmgh_cscart_wallet.debit_wallet_manually?wallet_id=`$wallet_user.wallet_id`" text=__("debit")}</li>   
                <li>{btn type="list" href="pdrmgh_cscart_wallet.credit_wallet_manually?wallet_id=`$wallet_user.wallet_id`" text=__("credit")}</li>           
            
            {/hook}
            {/capture}
            <div class="hidden-tools">
                {dropdown content=$smarty.capture.tools_items}
            </div>
            {/if}
        </td> 
    </tr>
{/if}
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

{capture name="wallet_dabit_credit"}
    {include file="addons/pdrmgh_cscart_wallet/views/pdrmgh_cscart_wallet/wallet_dabit_credit1.tpl"}
{/capture}
{capture name="sidebar"}
    {include file="common/saved_search.tpl" dispatch="pdrmgh_cscart_wallet.wallet_users" view_type="pdrmgh_cscart_wallet"}
    {include file="addons/pdrmgh_cscart_wallet/views/pdrmgh_cscart_wallet/components/wallet_users.tpl"}
{/capture}
{capture name="adv_buttons"}
     {include file="common/popupbox.tpl" id="wallet_dabit_credit" text=__("wallet_dabit_credit") title=__("wallet_dabit_credit") content=$smarty.capture.wallet_dabit_credit act="general" link_class="cm-dialog-auto-size" icon="icon-plus" link_text=""}
{/capture}
</form>

{/capture}
{include file="common/mainbox.tpl" title=__("wallet_users") content=$smarty.capture.mainbox title_extra=$smarty.capture.title_extra tools=$smarty.capture.tools sidebar=$smarty.capture.sidebar adv_buttons=$smarty.capture.adv_buttons buttons=$smarty.capture.buttons}