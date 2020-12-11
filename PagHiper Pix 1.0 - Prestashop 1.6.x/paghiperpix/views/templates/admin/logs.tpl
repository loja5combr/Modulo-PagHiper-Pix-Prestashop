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

<style>
.fix_icons {
	width:30px !important;
	height:30px !important;
	font-size:24px !important;
}
</style>

<div class="alert alert-info">Logs de erros e debug PagHiper Pix, lembre-se que para n&atilde;o salvar mais logs desative o modo <b>Debug</b> na <a href="{$link->getAdminLink('AdminModules', true)}&configure=paghiperpix">configura&ccedil;&atilde;o</a> do mesmo.</div>

<div class="panel panel-default">
<div class="panel-heading">
Logs
</div>
<div class="panel-body">

<table style="width:100%" class="tabelas table table-striped"> 
<thead>
<tr>
<th>ID</th> 
<th>Tipo</th>
<th>URL / A&ccedil;&atilde;o</th> 
<th>Status</th>
<th>Data</th>
<th></th>
</tr>
</thead> 
<tbody> 
{foreach $logs as $log}
	<tr>
	<td>{$log['id']}</td>
	<td>{$log['metodo']}</td>
	<td>{$log['url']}</td>
	<td>
	{if $log['http_status']==200 || $log['http_status']==201}
		<span class="label label-success">{$log['http_status']}<span>
	{elseif $log['http_status']==500}
		<span class="label label-danger">{$log['http_status']}<span>
	{else}
		<span class="label label-warning">{$log['http_status']}<span>
	{/if}
	</td>
	<td>{date('d/m/Y H:i',strtotime($log['data']))}</td>
	<td><a class="btn btn-info btn-xs" href="{$link->getAdminLink('AdminPagHiperPixLogs', true)}&acao=ver_logs&id={$log['id']}"><i class="icon-list-alt"></i> Ver logs</a></td>
	</tr>
{/foreach}
{if count($logs)==0}
	<tr><td colspan="10">Nenhum registro encontrado!</td></tr>
{/if}
</tbody> 

</table>

<nav style="text-align: center;" aria-label="Page navigation">
  <ul class="pagination">
	<!-- vai -->
	{if $pagina > 1}
		<li><a href="{$link->getAdminLink('AdminPagHiperPixLogs', true)}&pagina={$pagina-1}" aria-label="Voltar"><span aria-hidden="true">&laquo;</span></a></li>
	{else}
		<li class="disabled"><span aria-hidden="true">&laquo;</span></li>
	{/if}
	<!-- paginacao -->
	{if $paginas <= 20}
		{for $foo=1 to $paginas}
			<li class="{if $foo==$pagina}active{/if}"><a href="{$link->getAdminLink('AdminPagHiperPixLogs', true)}&pagina={$foo}">{$foo}</a></li>
		{/for}
	{else}
		{for $foo=1 to 8}
			<li class="{if $foo==$pagina}active{/if}"><a href="{$link->getAdminLink('AdminPagHiperPixLogs', true)}&pagina={$foo}">{$foo}</a></li>
		{/for}
			<li class=""><a href="#">...</a></li>
			{if $pagina > 8 && $pagina < ($paginas-7)}
				<li class="active"><a href="{$link->getAdminLink('AdminPagHiperPixLogs', true)}&pagina={$pagina}">{$pagina}</a></li>
				<li class=""><a href="#">...</a></li>
			{/if}
		{for $foo=($paginas-7) to $paginas}
			<li class="{if $foo==$pagina}active{/if}"><a href="{$link->getAdminLink('AdminPagHiperPixLogs', true)}&pagina={$foo}">{$foo}</a></li>
		{/for}
	{/if}
	<!-- voltar -->
	{if $pagina > 0 AND $pagina < $paginas}
		<li><a href="{$link->getAdminLink('AdminPagHiperPixLogs', true)}&pagina={$pagina+1}" aria-label="Voltar"><span aria-hidden="true">&raquo;</span></a></li>
	{else}
		<li class="disabled"><span aria-hidden="true">&raquo;</span></li>
	{/if}
  </ul>
</nav>

</div>
</div>