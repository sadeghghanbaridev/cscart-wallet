{if $auth.user_id}
        	<li class="ty-wallet-info__item ty-dropdown-box__item"><a class="ty-wallet-info__a underlined" href="{"pdrmgh_cscart_wallet.my_wallet"|fn_url}" rel="nofollow">{__("my_wallet")} ({include file="common/price.tpl" value=fn_get_wallet_amount(null,$smarty.session.auth.user_id)})
        	</a></li>

{/if}