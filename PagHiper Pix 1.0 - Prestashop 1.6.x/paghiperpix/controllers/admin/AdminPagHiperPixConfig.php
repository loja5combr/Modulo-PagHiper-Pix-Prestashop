<?php
/*
* @package    PagHiper Pix Prestashop
* @version    1.0
* @license    BSD License (3-clause)
* @copyright  (c) 2020
* @link       https://www.paghiper.com/
* @dev        Bruno Alencar - Loja5.com.br
*/

class AdminPagHiperPixConfigController extends ModuleAdminController
{
	public function __construct()
	{
        $this->bootstrap = true;
		parent::__construct();
	}
	public function initContent()
    {
        parent::initContent();
		//redireciona
		Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&configure=paghiperpix');
		exit;
	}
}
?>