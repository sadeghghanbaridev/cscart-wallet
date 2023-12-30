{if $return_info.status == $smarty.const.RMA_DEFAULT_STATUS}
    <a data-ca-dispatch="dispatch[rma.add_wallet]" class="btn cm-process-items cm-submit cm-confirm" data-ca-target-form="return_info_form">{__("add_wallet_money")}</a>
{else}
    {include file="buttons/button.tpl" but_text=__("add_wallet_money") but_name="dispatch[rma.add_wallet]" but_role="button_main" but_meta="cm-process-items cm-confirm"}
{/if}

<script>
    $(document).ready(function(){
        if($("input[name='dispatch[rma.create_gift_certificate]']") || $("a[data-ca-dispatch='dispatch[rma.create_gift_certificate]']"))
        {
            $("input[name='dispatch[rma.create_gift_certificate]']").hide();
            $("a[data-ca-dispatch='dispatch[rma.create_gift_certificate]']").hide();
        }
    });
</script>
