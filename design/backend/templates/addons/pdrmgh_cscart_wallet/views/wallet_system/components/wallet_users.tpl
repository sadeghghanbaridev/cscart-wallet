{capture name="section"}

<div class="sidebar-row">
    <h6>{__("users_search")}</h6>
    <form action="{""|fn_url}" name="wallet_search_form" method="get">
    
    {capture name="simple_search"}

        <div class="sidebar-field">
            <label for="wallet_email">{__("email")}:</label>
            <input type="text" name="email" id="wallet_email" value="{$search.email}" size="20">
        </div>

    {/capture}

    {capture name="advanced_search"}
       
    {/capture}
    
    {include file="common/advanced_search.tpl" advanced_search=$smarty.capture.advanced_search simple_search=$smarty.capture.simple_search dispatch="pdrmgh_cscart_wallet.wallet_users" view_type="events"}

    </form>
</div>

{/capture}
{include file="common/section.tpl" section_content=$smarty.capture.section}