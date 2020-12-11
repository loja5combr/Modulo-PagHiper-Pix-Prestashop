<form action="" onsubmit="return processar_paghiperpix();" method="post" id="form-paghiperpix">

	<div class="form-group">
		<div class="col-md-6 colunas_paghiperpix">
			<label>{l s='Pagador:' mod='paghiperpix'}</label>
			<input class="no-copy-paste campos_formatados" type="text" value="{$pagador}" readonly>
		</div>

		<div class="col-md-6 colunas_paghiperpix">
			<label>{l s='CPF ou CNPJ:' mod='paghiperpix'}</label>
			<input placeholder="000.000.000-00" onkeyup="mascarapix(this,'soNumeros')" class="no-copy-paste campos_formatados" maxlength="18" type="text" id="fiscal" name="fiscal" value="{$fiscal}" size="18">
		</div>
	</div>
	
	{if $desconto > 0}
	<div class="form-group">
		<div class="col-md-12 colunas_paghiperpix">
		<label>Total a Pagar:</label>
		<input class="no-copy-paste campos_formatados"type="text" value="{$total_formatado}{if $desconto > 0} ({$desconto}% de desconto){/if}" size="50" readonly>
		</div>
	</div>
	{/if}
	
	<div class="form-group">
		<div class="col-md-12 colunas_paghiperpix">
			<p>Finalize seu pedido e pague por Pix direto no App de seu banco preferido.</p>
		</div>
	</div>

</form>

<p>&nbsp;</p>