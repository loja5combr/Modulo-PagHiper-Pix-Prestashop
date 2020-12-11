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

<div class="alert alert-info">Detalhes do log PagHiper Pix, lembre-se que para n&atilde;o salvar mais logs desative o modo <b>Debug</b> na <a href="{$link->getAdminLink('AdminModules', true)}&configure=paghiperpix">configura&ccedil;&atilde;o</a> do mesmo.</div>

<div class="panel panel-default">
<div class="panel-heading">
Enviado ({$logs['metodo']} {$logs['url']})
<div class="btn-group pull-right">
<a href="{$link->getAdminLink('AdminPagHiperPixLogs', true)}" class="btn btn-danger btn-xs"><i class="icon-rotate-right"></i> Voltar</a>
</div>
</div>
<div class="panel-body">
<code>{nl2br($logs['enviado'])}</code>
</div>
</div>

<div class="panel panel-default">
<div class="panel-heading">
Recebido ({if $logs['http_status']==200 || $logs['http_status']==201}<span class="label label-success">{$logs['http_status']}<span>{elseif $logs['http_status']==500}<span class="label label-danger">{$logs['http_status']}<span>{else}<span class="label label-warning">{$logs['http_status']}<span>{/if})
</div>
<div class="panel-body">
<code>{nl2br($logs['recebido'])}</code>
</div>
</div>