{capture name="section"}

<div class="sidebar-row">
    <h6>{__("transaction_search")}</h6>
    <form action="{""|fn_url}" name="wallet_search_form" method="get">
    
    {capture name="simple_search"}

        <div class="sidebar-field">
            <label for="wallet_email">{__("email")}:</label>
            <input type="text" name="email" id="wallet_email" value="{$search.email}" size="20">
        </div>


        <div class="sidebar-field">
            <label for="wallet_credit_type">{__("credit_type")}:</label>
            <select name="credit_type" id="wallet_credit_type">
                <option value="" {if $search.credit_type==''} selected {/if}>{__("both")}</option>
                <option value="credit" {if isset($search.credit_type) && $search.credit_type=='credit'} selected {/if}>{__("credit")}</option>
                <option value="debit" {if isset($search.credit_type) && $search.credit_type=='debit'} selected {/if}>{__("debit")}</option>
            </select>
        </div>
         {include file="common/period_selector.tpl" period=$search.period display="form"}
    {/capture}

    {capture name="advanced_search"}
       
    {/capture}
    
    {include file="common/advanced_search.tpl" advanced_search=$smarty.capture.advanced_search simple_search=$smarty.capture.simple_search dispatch="pdrmgh_cscart_wallet.wallet_transaction" view_type="events"}

    </form>
</div>

{/capture}
{include file="common/section.tpl" section_content=$smarty.capture.section}