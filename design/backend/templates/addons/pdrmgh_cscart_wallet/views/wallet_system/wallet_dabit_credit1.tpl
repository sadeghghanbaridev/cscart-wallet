<style type="text/css">
    
    .display_none{
        display: none;
    }
</style>
{assign var="c_url" value=fn_url($config.current_url)}

<form  action="{""|fn_url}" method="post" name="wallet_credit_debit_manually_form" class="form-horizontal form-edit  cm-disable-empty-files" enctype="multipart/form-data" id="wallet_credit_debit_manually_form">

<div style="margin:0 auto;display:table;padding:10px 80px 0px 0px;margin-top:5%; background:#EFEFEF;">
    <input type="hidden" name="return_url" value="{$c_url}">
    <div class="control-group">
        <label class="control-label cm-required" for="elm_amount">{__("amount")}&nbsp;({$currencies.$primary_currency.symbol nofilter}):</label>
        <div class="controls">
            <input type="text" name="wallet_credit_debit[amount]" id="elm_amount" value="{if $amount}{fn_format_price($amount)}{/if}" class="cm-value-decimal"/>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label cm-required" for="elm_reason">{__("reason")}:</label>
        <div class="controls">
            <input type="text" name="wallet_credit_debit[reason]" id="elm_reason" value="{if $reason}{$reason}{/if}" class="cm-trim"/>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label cm-required" for="demo">{__("choose_user")}:</label>
        <div class="controls">
        <fieldset>
          {include file="pickers/users/picker.tpl"  display="checkbox" but_text=__("choose_user") no_container=true but_meta="btn" shared_force=$users_shared_force input_name='wallet_credit_debit[user]'}
        </fieldset>
        </div>
    </div>
    <div class="control-group">  
      <div class="controls">
        <label class="radio inline" for="elm_id_credit"><input type="radio" id="elm_id_credit" value="" checked="checked" name="wallet_credit_debit[but_type]">{__("credit")}</label>
          <label class="radio inline" for="elm_id_debit"><input type="radio" id="elm_id_debit" value=""  style="margin-left:30px;" name="wallet_credit_debit[but_type]"> {__("debit")}</label>
      </div>
  </div>
    <div class="control-group">
        <div class="controls right" id="credit_but">
            {include file="buttons/button.tpl" but_meta="cm-no-ajax dropdown-toggle" but_text=__("credit") but_role="submit-link" but_target_form="wallet_credit_debit_manually_form" but_name="dispatch[pdrmgh_cscart_wallet.group_credit_wallet]" save=true}       
        </div>
         <div class="controls display_none right" id="debit_but">
            {include file="buttons/button.tpl" but_meta="cm-tab-tools" but_text=__("debit") but_role="submit-link" but_target_form="wallet_credit_debit_manually_form" but_name="dispatch[pdrmgh_cscart_wallet.group_debit_wallet]" save=true}
        </div>  
    </div> 
</div>   
</form>
<script>
    Tygh.$(document).ready(function(){

        Tygh.$('#elm_id_debit').click(function(){
            if(Tygh.$('#elm_id_debit').prop("checked"))
            {
                Tygh.$('#debit_but').removeClass('display_none');
                Tygh.$('#debit_but').show();
                Tygh.$('#credit_but').addClass('display_none');
                Tygh.$('#credit_but').hide();
            }
        });
        Tygh.$('#elm_id_credit').click(function(){
            if(Tygh.$('#elm_id_credit').prop("checked"))
            {
                Tygh.$('#credit_but').removeClass('display_none');
                Tygh.$('#credit_but').show();
                Tygh.$('#debit_but').addClass('display_none');
                 Tygh.$('#debit_but').hide();
            }
        });
    });
</script>
