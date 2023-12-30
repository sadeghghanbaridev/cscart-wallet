{if isset($cart.wallet.used_cash) && !empty($cart.wallet.used_cash)}
    <tr>
            <td class="ty-checkout-summary__item">
                {__(wallet_cash_applied)}
            </td>
            <td class="ty-checkout-summary__item ty-right">
                -{include file="common/price.tpl" value=$cart.wallet.used_cash}
            </td>
        </tr>
{/if}