<!--
/*
* @package    PagHiper Pix Prestashop
* @version    1.0
* @license    BSD License (3-clause)
* @copyright  (c) 2020
* @link       https://www.paghiper.com/
* @dev        Bruno Alencar - Loja5.com.br
*/
-->

<p>
	- {l s='Valor' mod='paghiperpix'} : <span class="price"><strong>{$total}</strong></span>
	<br />- {l s='Referência' mod='paghiperpix'} : <span class="reference"><strong>{$reference|escape:'html':'UTF-8'}</strong></span>
    <br />- {l s='Status' mod='paghiperpix'} : <span class="status"><strong>Aguardando Pagamento</strong></span>
    <br />- {l s='Método' mod='paghiperpix'} : <span class="venda"><strong>Pix a vista</strong></span>
    <br />- {l s='ID PagHiper' mod='paghiperpix'} : <span class="venda"><strong>{$dados.transacao}</strong></span>

	{if $dados.status=='iniciado'}
	<br>
		- QrCode PIX (Pague atrav&eacute;s do App do banco preferido):<br>
		<span id="img-pix">
		<img class="img-thumbnail" src="data:image/png;base64,{$dados.qrcode}"/>
		</span>
	<br>
		- C&oacute;digo Pix (Copiar/Colar e Pague atrav&eacute;s do App do banco preferido):<br>
		<span id="selectablepix-span">
		<code title="Clique para copiar o código!" id="selectablepix" onclick="selectText();">{$dados.emv}</code>
		</span>
		<input type="hidden" value="{$dados.emv}" id="pix-copiar-colar">
	{/if}
	
	<div class="alert alert-info"><i class="fa fa-exclamation-circle"></i> Pedidos realizados por dispositivos moveis use o c&oacute;digo copiar/colar acima para realizar o pagamento no App do seu banco preferido.</div>
    
	<br />{l s='Enviamos um e-mail com detalhes de seu pedido.' mod='paghiperpix'}
	<br /><br />{l s='Para qualquer duvida ou informação ' mod='paghiperpix'} <a href="{$link->getPageLink('contact', true)|escape:'html':'UTF-8'}">{l s='clique aqui e entre em contato com nosso atendimento.' mod='paghiperpix'}</a>
</p>
<hr />
<script>
	function selectText() {
		copyToClipboard();
		var containerid = 'selectablepix';
		if (document.selection) { // IE
			var range = document.body.createTextRange();
			range.moveToElementText(document.getElementById(containerid));
			range.select();
		} else if (window.getSelection) {
			var range = document.createRange();
			range.selectNode(document.getElementById(containerid));
			window.getSelection().removeAllRanges();
			window.getSelection().addRange(range);
		}
	}
	function copyToClipboard() {
	  var element = $('#pix-copiar-colar');
	  var $temp = $("<input>");
	  $("body").append($temp);
	  $temp.val(element.val()).select();
	  document.execCommand("copy");
	  $temp.remove();
	  console.log('copiado: '+element.val()+'');
	}
</script>