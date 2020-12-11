<?php
/*
* @package    PagHiper Pix Prestashop
* @version    1.0
* @license    BSD License (3-clause)
* @copyright  (c) 2020
* @link       https://www.paghiper.com/
* @dev        Bruno Alencar - Loja5.com.br
*/

class AdminPagHiperPixLogsController extends ModuleAdminController
{
	public function __construct()
	{
        $this->bootstrap = true;
		parent::__construct();
	}
	
	public function initPageHeaderToolbar(){
        parent::initPageHeaderToolbar();
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
			case 'ver_logs';
			$this->ver_logs();
			break;
            default:
            $this->index();
        }
    }
	
	private function ver_logs(){
		//layout
        $tpl = $this->createTemplate('ver_logs.tpl');
		//registros
		$sql = "SELECT * FROM `" . _DB_PREFIX_ . "loja5_logs` WHERE modulo='paghiperpix' AND id = '".(int)$_GET['id']."'";
		$logs = Db::getInstance()->getRow($sql);
		$tpl->assign(array(
			'logs' => $logs
		));
		$this->context->smarty->assign(array('content' => $tpl->fetch()));
	}
	
	private function index(){
		//filto 
		$where = '';
		//layout
        $tpl = $this->createTemplate('logs.tpl');
		//qtd registros
		$sql = "SELECT COUNT(*) AS total FROM `" . _DB_PREFIX_ . "loja5_logs` WHERE modulo='paghiperpix' $where";
        $row = Db::getInstance()->getRow($sql);
		$total_logs = $row['total'];
		//inicio paginacao 
		$pagina = (int)(isset($_GET['pagina'])?$_GET['pagina']:1);
		$registros = $this->module->itens_por_pagina;
		$inicio = ($registros*($pagina-1));
		$paginas = ceil( $total_logs / $registros );
		//registros
		$sql = "SELECT * FROM `" . _DB_PREFIX_ . "loja5_logs` WHERE modulo='paghiperpix' $where ORDER BY id DESC LIMIT $inicio,$registros";
		$logs = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
		$tpl->assign(array(
			'logs' => $logs,
			'pagina' => $pagina,
			'total_logs' => $total_logs,
			'paginas' => $paginas
		));
		$this->context->smarty->assign(array('content' => $tpl->fetch()));
	}

}
