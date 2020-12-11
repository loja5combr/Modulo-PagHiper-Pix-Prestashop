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

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.11/css/dataTables.bootstrap.css" />
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.11/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.11/js/dataTables.bootstrap.min.js"></script>
<link rel="stylesheet" href="//cdn.datatables.net/plug-ins/1.10.12/integration/font-awesome/dataTables.fontAwesome.css" />

<style>
.fix_icons {
	width:30px !important;
	height:30px !important;
	font-size:24px !important;
}
</style>

<div class="alert alert-info">Lista dos pedidos Pix PagHiper finalizados em sua loja.</div>

{if !empty($erro)}
<div class="alert alert-danger">{$erro}</div>
{/if}

{if !empty($sucesso)}
<div class="alert alert-success">{$sucesso}</div>
{/if}

<div class="panel panel-default">
<div class="panel-heading"><span class="icon-edit" aria-hidden="true"></span> Lista de Pedidos Pix</div>
<div class="panel-body">

<table style="width:100%" class="tabelas table table-striped"> 
<thead>
<tr>
<th style="width:50px;">ID</th> 
<th>ID Pedido</th>
<th>ID Carrinho</th>
<th>Total</th>
<th>Transa&ccedil;&atilde;o</th>
<th>Status</th>
<th>Pagador</th>
<th></th>
</tr>
</thead> 
<tbody> 
</tbody>
</table>

</div>
</div>

<script>
var url_link = "{$link->getAdminLink('AdminPagHiperPixPedidos', true)}";
var auto_filtrar = "{$auto_filtrar}";
{literal}
$(document).ready(function() {
    var tabelas = $('.tabelas').DataTable({
        "stateSave": true,
		"pageLength": 25,
		"processing": true,
        "serverSide": true,
		"ajax": {
			"url": url_link+"&acao=resultados&ajax=true",
		},
		"columns": [
			{ "data": "id" },
			{ "data": "id_pedido" },
			{ "data": "id_carrinho" },
			{ "data": "valor" },
			{ "data": "transacao" },
			{ "data": "status" },
			{ "data": "pagador", "orderable": false },
			{ "data": "url", "orderable": false }
		],
        "order": [[ 0, "desc" ]],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Portuguese.json"
        }
    });
	if(auto_filtrar!=''){
		tabelas.search(auto_filtrar).draw();
	}
});
{/literal}
</script>