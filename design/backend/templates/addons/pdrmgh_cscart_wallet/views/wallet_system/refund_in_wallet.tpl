{include file="views/profiles/components/profiles_scripts.tpl"}

{capture name="mainbox"}

{capture name="tabsbox"}

{** /Item menu section **}


<form id='form' action="{$config.admin_index}?dispatch=pdrmgh_cscart_wallet.refund" method="post" name="wallet_refund_form" class="form-horizontal form-edit  cm-disable-empty-files" enctype="multipart/form-data" id="wallet_refund_form">

<div style="margin:0 auto;display:table;padding:10px 80px 0px 0px;margin-top:5%; background:#EFEFEF;">
    <div class="control-group">
    <label class="control-label cm-required" for="elm_order_id">{__("order_id")}:</label>
        <div class="controls">
            <input type="text" name="wallet_refund[order_id_show]" id="elm_order_id" value="{if $order_id}{$order_id}{/if}" disabled/>
            <input type="hidden" name="wallet_refund[order_id]" id="elm_order_id" value="{if $order_id}{$order_id}{/if}"/>
        </div>
    </div>

    <div class="control-group">
    <label class="control-label cm-required" for="elm_amount">{__("amount")}&nbsp;({$currencies.$primary_currency.symbol nofilter}):</label>
        <div class="controls">
            <input type="text" name="wallet_refund[refund_amount]" id="elm_amount" value="{if $amount}{fn_format_price($amount)}{else}0{/if}" />
        </div>
    </div>

    <div class="control-group">
    <label class="control-label cm-required" for="elm_reason">{__("reason")}:</label>
        <div class="controls">
            <input type="text" name="wallet_refund[refund_reason]" id="elm_reason" value="{if $reason}{$reason}{/if}" />
        </div>
    </div>

    <div class="control-group">
        <div class="controls" style="display:inline; float:left">
            {include file="buttons/button.tpl" but_meta="cm-no-ajax dropdown-toggle" but_text=__("refund") but_role="submit-link" but_target_form="wallet_refund_form" but_name="dispatch[pdrmgh_cscart_wallet.refund]" save=true}</div>           
        </div>
    </div>
</div>            

</form>


{/capture}

{include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox group_name="companies" active_tab=$smarty.request.selected_section track=true}
{/capture}


{include file="common/mainbox.tpl" title=__("make_a_refund") content=$smarty.capture.mainbox sidebar=$smarty.capture.sidebar buttons=$smarty.capture.buttons}