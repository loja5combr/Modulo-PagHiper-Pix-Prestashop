<?php
/*
* @package    PagHiper Pix Prestashop
* @version    1.0
* @license    BSD License (3-clause)
* @copyright  (c) 2020
* @link       https://www.paghiper.com/
* @dev        Bruno Alencar - Loja5.com.br
*/

class AdminPagHiperPixPedidosController extends ModuleAdminController
{
	public function __construct()
	{
        $this->bootstrap = true;
		parent::__construct();
	}
	
	public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();
        if (empty($this->display)) {
        }
    }
    
    public function createTemplate($tpl_name) 
    {
        if (file_exists($this->getTemplatePath() . $tpl_name) && $this->viewAccess())
                return $this->context->smarty->createTemplate($this->getTemplatePath() . $tpl_name, $this->context->smarty);
            return parent::createTemplate($tpl_name);
    }

	public function initContent()
    {
        parent::initContent();
        $acao = isset($_GET['acao'])?$_GET['acao']:'';
        switch($acao){
			case 'resultados':
			$this->resultados();
			break;
            default:
            $this->index();
        }
    }
	
	public function resultados()
	{
		//paginacao
		$por_pagina = isset($_REQUEST['length'])?$_REQUEST['length']:'25';
		$draw = isset($_REQUEST['draw'])?$_REQUEST['draw']:'1';
		$inicio = isset($_REQUEST['start'])?$_REQUEST['start']:'';
		$coluna_index = isset($_REQUEST['order'][0]['column'])?$_REQUEST['order'][0]['column']:'';
		$coluna_nome = isset($_REQUEST['columns'][$coluna_index]['data'])?$_REQUEST['columns'][$coluna_index]['data']:'';
		$coluna_ordem = isset($_REQUEST['order'][0]['dir'])?$_REQUEST['order'][0]['dir']:'';
		$tag_busca = isset($_REQUEST['search']['value'])?$_REQUEST['search']['value']:'';
		//filtros 
		$where = '';
		if(!empty($tag_busca)){
			$where .= ' AND (id_pedido LIKE "%'.$tag_busca.'%" OR id_carrinho LIKE "%'.$tag_busca.'%" OR valor LIKE "%'.$tag_busca.'%" OR transacao LIKE "%'.$tag_busca.'%" OR pagador LIKE "%'.$tag_busca.'%")';
		}
		//consulta total registros mysql 
		$sql = "SELECT * FROM `" ._DB_PREFIX_. "paghiperpix_pedidos` WHERE 1=1 $where";
		$total_registros = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
		//consulta mysql 
		$sql = "SELECT * FROM `" ._DB_PREFIX_. "paghiperpix_pedidos` WHERE 1=1 $where ORDER BY $coluna_nome $coluna_ordem LIMIT $inicio, $por_pagina";
		$retornos = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
		//processa as colunas 
		$columns = array();
		foreach($retornos as $key => $linha){
			$order = new Order($linha['id_pedido']);
			if(isset($_REQUEST['columns'])){
				$linhas = array();
				foreach($_REQUEST['columns'] as $k=>$v){
					//pega o valor do campo 
					$valor_campo = (isset($linha[$v['data']])?$linha[$v['data']]:$v['data']);
					//dados customizados
					if($v['data']=='url'){
						if($linha['status']=='iniciado'){
							$valor_campo = "<a target='_blank' class='btn btn-sucesso btn-sm' href='".$linha['url']."'>Link do QrCode</a>";
						}else{
							$valor_campo = "";
						}
					}
					if($v['data']=='status'){
						$valor_campo = ucfirst($linha['status']);
					}
					//pega o valor padrao de acordo com a linha
					$linhas = array_merge($linhas,array($v['data']=>$valor_campo));
				}
				$columns[$key] = $linhas;
			}
		}
		//exibe o json
		$json_data = array(
			"sql"             => $sql,
			"draw"            => $draw,
			"recordsTotal"    => count( $total_registros ),
			"recordsFiltered" => count( $total_registros ),
			"data"            => $columns
		);
		die(json_encode($json_data));
	}
    
    public function index()
    {
        $tpl = $this->createTemplate('pedidos.tpl');
		$auto_filtrar = '';
		$tpl->assign(array(
			'erro' => (isset($_GET['erro'])?$_GET['erro']:''),
			'auto_filtrar' => $auto_filtrar,
			'sucesso' => (isset($_GET['sucesso'])?$_GET['sucesso']:''),
			'link_url' => $this->context->link->getAdminLink('AdminPagHiperPixPedidos', true),
		));
		$this->context->smarty->assign(array('content' => $tpl->fetch()));
    }

}
