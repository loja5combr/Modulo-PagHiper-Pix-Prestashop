<?php
/*
* @package    PagHiper Pix Prestashop
* @version    1.0
* @license    BSD License (3-clause)
* @copyright  (c) 2020
* @link       https://www.paghiper.com/
* @dev        Bruno Alencar - Loja5.com.br
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class PagHiperPix extends PaymentModule
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'paghiperpix';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'PagHiper.com';
        $this->need_instance = 0;
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('PagHiper Pix');
		$this->limited_currencies = array('BRL');
        $this->description = $this->l('Módulo de pagamentos PagHiper Pix.');
        $this->confirmUninstall = $this->l('Tem certeza em remover o módulo?');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
		$this->itens_por_pagina = 40;
    }
	
	public function hookDisplayBackOfficeHeader()
    {
        $this->context->controller->addCss($this->_path.'views/css/tab.css');
    }

    public function install()
    {
		//curl 
        if (extension_loaded('curl') == false) {
            $this->_errors[] = $this->l('Ops, ative o curl em sua hospedagem!.');
            return false;
        }
		
		//menu principal 
		$parent_tab0 = new Tab();
        foreach(Language::getLanguages(false) as $lang){
            $parent_tab0->name[(int)$lang['id_lang']] = $this->l('PagHiper Pix');
        }
		$parent_tab0->class_name = 'AdminPagHiperPixPedidos';
		$parent_tab0->id_parent = 0;
		$parent_tab0->module = $this->name;
		$parent_tab0->add();
		
		//menu 1
		$parent_tab1 = new Tab();
        foreach(Language::getLanguages(false) as $lang){
            $parent_tab1->name[(int)$lang['id_lang']] = $this->l('Pedidos');
        }
		$parent_tab1->class_name = 'AdminPagHiperPixPedidos';
		$parent_tab1->id_parent = $parent_tab0->id;
		$parent_tab1->module = $this->name;
		$parent_tab1->add();
		
		//menu 2
		$parent_tab3 = new Tab();
        foreach(Language::getLanguages(false) as $lang){
            $parent_tab3->name[(int)$lang['id_lang']] = $this->l('Logs');
        }
		$parent_tab3->class_name = 'AdminPagHiperPixLogs';
		$parent_tab3->id_parent = $parent_tab0->id;
		$parent_tab3->module = $this->name;
		$parent_tab3->add();
		
		//menu 3
		$parent_tab4 = new Tab();
        foreach(Language::getLanguages(false) as $lang){
            $parent_tab4->name[(int)$lang['id_lang']] = $this->l('Configurações');
        }
		$parent_tab4->class_name = 'AdminPagHiperPixConfig';
		$parent_tab4->id_parent = $parent_tab0->id;
		$parent_tab4->module = $this->name;
		$parent_tab4->add();
		
        include(dirname(__FILE__).'/sql/install.php');
        return parent::install() &&
            $this->registerHook('header') &&
			$this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayOrderDetail') &&
            $this->registerHook('payment') &&
            $this->registerHook('paymentReturn') &&
            $this->registerHook('actionPaymentConfirmation') &&
            $this->registerHook('displayAdminOrder') &&
            $this->registerHook('displayFooter') &&
            $this->registerHook('displayHeader') &&
            $this->registerHook('displayPayment') &&
            $this->registerHook('displayPaymentReturn');
    }
	
	public function hookdisplayAdminOrder($params)
	{
        return;
    }
    
    public function hookdisplayOrderDetail($params)
	{
        return;
    }

    public function uninstall()
    {
		// remove os menus
		$moduleTabs = Tab::getCollectionFromModule($this->name);
		if (!empty($moduleTabs)) {
			foreach ($moduleTabs as $moduleTab) {
				$moduleTab->delete();
			}
		}
        include(dirname(__FILE__).'/sql/uninstall.php');
        return parent::uninstall();
    }

    public function getContent()
    {
		$output = '';
        if (((bool)Tools::isSubmit('submitModule')) == true) {
			$output .= $this->displayConfirmation($this->l('Dados do módulo atualizados com sucesso!'));
            $this->postProcess();
        }
        $url_loja = Tools::getShopDomainSsl(true, true).__PS_BASE_URI__;
        $this->context->smarty->assign('module_dir', $this->_path);
        $this->context->smarty->assign('url_loja', $url_loja);
        $output .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
		return $output.$this->renderForm();
    }

    protected function renderForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    private function campos_extras()
    {
        //querys
        $campos[] = array('id'=>'','campo'=>'Cliente informa manual');
        $clientes = Db::getInstance()->executeS("SHOW COLUMNS FROM `" . _DB_PREFIX_ . "customer`");	
        foreach($clientes AS $k=>$v){
            $input = _DB_PREFIX_.'customer.'.$v['Field'].'';
            $campos[] = array('id'=>$input,'campo'=>$input);
        }
        $enderecos = Db::getInstance()->executeS("SHOW COLUMNS FROM `" . _DB_PREFIX_ . "address`");
        foreach($enderecos AS $k=>$v){
            $input = _DB_PREFIX_.'address.'.$v['Field'].'';
            $campos[] = array('id'=>$input,'campo'=>$input);
        }
        return $campos;
    }
    
    protected function getConfigForm()
    {
		$extras = $this->campos_extras();
		$config = array(
			'form' => array(
				'legend' => array(
				'title' => $this->l('Configurações'),
				'icon' => 'icon-cogs',
				),
				'input' => array(
					array(
						'type' => 'switch',
						'label' => $this->l('Ativar PagHiper Pix'),
						'name' => 'PAGHIPERPIX_STATUS',
						'is_bool' => true,
						'desc' => $this->l('Ativa o método de pagamento PagHiper Pix em sua loja.'),
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => true,
								'label' => $this->l('Ativo')
							),
							array(
								'id' => 'active_off',
								'value' => false,
								'label' => $this->l('Inativo')
							)
						),
					),
					array(
						'col' => 6,
						'type' => 'text',
						'desc' => $this->l('Nome do método de pagamento a exibir ao cliente no checkout (Ex: Pague com Pix).'),
						'name' => 'PAGHIPERPIX_TITULO',
						'label' => $this->l('Titulo a Exibir'),
					),
					array(
						'col' => 6,
						'type' => 'text',
						'desc' => $this->l('Chave de acesso a API da PagHiper, a mesma pode ser consultada acessando sua conta PagHiper e depois o menu "Minha Conta > Credênciais".'),
						'name' => 'PAGHIPERPIX_KEY',
						'label' => $this->l('ApiKey PagHiper'),
					),
					array(
						'col' => 6,
						'type' => 'text',
						'desc' => $this->l('Token de acesso a API da PagHiper, a mesma pode ser consultada acessando sua conta PagHiper e depois o menu "Minha Conta > Credênciais".'),
						'name' => 'PAGHIPERPIX_TOKEN',
						'label' => $this->l('Token PagHiper'),
					),
					array(
						'col' => 3,
						'type' => 'text',
						'class' => 'dinheiro',
						'default' => 3.00,
						'desc' => $this->l('Total minimo para usar o módulo, por padrão o valor mínimo aceito para recebimento Pix junto a PagHiper é de 3.00, portanto não configure um valor menor que este.'),
						'name' => 'PAGHIPERPIX_MINIMO',
						'label' => $this->l('Total Mínimo (0.00)'),
					),
					array(
						'col' => 2,
						'type' => 'text',
						'default' => 3,
						'desc' => $this->l('Prazo de validade em dias para o Pix (Ex: 3).'),
						'name' => 'PAGHIPERPIX_VALIDADE',
						'label' => $this->l('Validade em Dias'),
					),
					array(
						'type' => 'select',
						'name' => 'PAGHIPERPIX_FISCAL1',
						'desc' => $this->l('Campo customizado qual o CPF é salvo na loja!'),
						'label' => $this->l('Origem CPF'),
						'options' => array(
							'query' => $extras,
							'id' => 'id',
							'name' => 'campo'
						)
					),
					array(
						'type' => 'select',
						'name' => 'PAGHIPERPIX_FISCAL2',
						'desc' => $this->l('Campo customizado qual o CNPJ é salvo na loja!'),
						'label' => $this->l('Origem CNPJ'),
						'options' => array(
							'query' => $extras,
							'id' => 'id',
							'name' => 'campo'
						)
					),
					array(
						'col' => 3,
						'type' => 'text',
						'class' => 'dinheiro',
						'default' => 0.00,
						'desc' => $this->l('Desconto para pagamento por Pix.'),
						'name' => 'PAGHIPERPIX_DESCONTO',
						'label' => $this->l('Desconto por Pix %'),
					),
					array(
						'type' => 'select',
						'name' => 'PAGHIPERPIX_INICIADA',
						'desc' => $this->l('Status customizado ou já existente!'),
						'label' => $this->l('Status Aguardando Pagamento'),
						'options' => array(
							'query' => $this->nomes_status_pagamentos(),
							'id' => 'id_order_state',
							'name' => 'name'
						)
					),
					array(
						'type' => 'select',
						'name' => 'PAGHIPERPIX_PAGO',
						'desc' => $this->l('Status customizado ou já existente!'),
						'label' => $this->l('Status Pago'),
						'options' => array(
							'query' => $this->nomes_status_pagamentos(),
							'id' => 'id_order_state',
							'name' => 'name'
						)
					),
					array(
						'type' => 'select',
						'name' => 'PAGHIPERPIX_DEVOLVIDO',
						'desc' => $this->l('Status customizado ou já existente!'),
						'label' => $this->l('Status Devolvido'),
						'options' => array(
							'query' => $this->nomes_status_pagamentos(),
							'id' => 'id_order_state',
							'name' => 'name'
						)
					),
					array(
						'type' => 'select',
						'name' => 'PAGHIPERPIX_CANCELADO',
						'desc' => $this->l('Status customizado ou já existente!'),
						'label' => $this->l('Status Cancelado'),
						'options' => array(
							'query' => $this->nomes_status_pagamentos(),
							'id' => 'id_order_state',
							'name' => 'name'
						)
					),
					array(
						'type' => 'switch',
						'label' => $this->l('Ativar Modo Debug'),
						'name' => 'PAGHIPERPIX_DEBUG',
						'is_bool' => true,
						'desc' => $this->l('Ativa o modo desenvolvedor, com o mesmo ativo o módulo passa a salvar logs de transações que por sua vez podem ser consultados no menu "PagHiper Pix > Logs", recomenda-se desativar quando a loja estiver em produção.'),
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => true,
								'label' => $this->l('Ativo')
							),
							array(
								'id' => 'active_off',
								'value' => false,
								'label' => $this->l('Inativo')
							)
						),
					),
				),
				'submit' => array(
					'title' => $this->l('Salvar'),
				),
			),
		);
		return $config;
    }

    protected function getConfigFormValues()
    {
        $inputs = array();
        $form   = $this->getConfigForm();
        foreach ($form['form']['input'] as $v) {
            $chave          = $v['name'];
            $inputs[$chave] = Configuration::get($chave, '');
        }
        return $inputs;
    }

    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    public function hookDisplayPayment($params)
    {     
        //se ativo ou nao
        if ($this->active == false || !(bool)Configuration::get('PAGHIPERPIX_STATUS')){
            return;
        }
		
		//verifica se e uma moeda aceita
        $currency_id = $params['cart']->id_currency;
        $currency = new Currency((int)$currency_id);
        if (in_array($currency->iso_code, $this->limited_currencies) == false){
            return false;
        }
		
        //dados do pedido
        $carrinho = $params['cart'];
		$cliente = Context::getContext()->customer;
		$endereco_id = $carrinho->id_address_invoice;
        $endereco = new Address((int)($endereco_id));
        $link = Context::getContext()->link;
        $total = $carrinho->getOrderTotal(true, 3);
        $frete = $carrinho->getOrderTotal(true, 5);
        
        //tira o frete
        $total_pix_sem = ($total-$frete);
        
        //descontos e taxas boleto
        $desconto = (float)Configuration::get('PAGHIPERPIX_DESCONTO');
        if($desconto > 0){
            $total_pix_sem = ($total_pix_sem-(($total_pix_sem/100)*abs($desconto)));
        }
		
		//captura o cpf ou cnpj
		$numero_fiscal = preg_replace('/\D/', '', $this->cpf_cnpj());
		$array_cobranca = array_merge((array)$cliente,(array)$endereco);
        $campo_fiscal1 = explode('.',Configuration::get("PAGHIPERPIX_FISCAL1"));
		$fiscal1 = isset($campo_fiscal1[1])?$campo_fiscal1[1]:'cpf';
		$campo_fiscal2 = explode('.',Configuration::get("PAGHIPERPIX_FISCAL2"));
		$fiscal2 = isset($campo_fiscal2[1])?$campo_fiscal2[1]:'cnpj';
		$array_cobranca = array_merge((array)$cliente,(array)$endereco);
		if(isset($array_cobranca[$fiscal1]) && !empty($this->so_numeros($array_cobranca[$fiscal1]))){
			$numero_fiscal = $this->so_numeros($array_cobranca[$fiscal1]);
		}elseif(isset($array_cobranca[$fiscal2]) && !empty($this->so_numeros(($array_cobranca[$fiscal2])))){
			$numero_fiscal = $this->so_numeros($array_cobranca[$fiscal2]);
		}
		
        //aplica no template
		$url_loja = Tools::getShopDomainSsl(true, true).__PS_BASE_URI__;
        $this->smarty->assign('module_dir', $this->_path);
		$this->smarty->assign('url_loja', $url_loja);
		$this->smarty->assign('desconto_pix', $desconto);
        $this->smarty->assign('total_pix', number_format(($total_pix_sem+$frete), 2, '.', ''));
		$this->smarty->assign('titulo_pix', trim(Configuration::get("PAGHIPERPIX_TITULO")));
		$this->smarty->assign('fiscal', $numero_fiscal);
		$this->smarty->assign('fiscal_size', strlen($numero_fiscal));
		$this->smarty->assign('this_path_ssl', Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/');
		$this->smarty->assign('pagador', $cliente->firstname.' '.$cliente->lastname);

        return $this->display(__FILE__, 'views/templates/hook/payment.tpl');
    }
	
	public function removerVoucherErro($cart)
	{
		$cart_rules = $cart->getCartRules();
        $rule_cod = 'V0C'.(int)($cart->id_customer).'O'.(int)($cart->id);
		foreach($cart_rules as $rule){
			if($rule['name']==$rule_cod && $cart->id_customer==$rule['id_customer']){
				$cart->removeCartRule($rule['id_cart_rule']);
				$sql = array();
				$sql[] = "DELETE FROM `"._DB_PREFIX_."cart_rule` WHERE id_cart_rule = '".(int)$rule['id_cart_rule']."'";
				$sql[] = "DELETE FROM `"._DB_PREFIX_."cart_rule_lang` WHERE id_cart_rule = '".(int)$rule['id_cart_rule']."'";
				foreach ($sql as $query) {
					Db::getInstance()->execute($query);
				}
			}
		}
	}
	
	public function aplicarDesconto($cart)
	{
		if(CartRule::cartRuleExists('DESCONTOPIXPH'.$cart->id)){
            return false;
        }
        $rule = 'V0C'.(int)($cart->id_customer).'O'.(int)($cart->id);
        if(CartRule::cartRuleExists($rule)){
            return false;
        }
		$total = (float)Configuration::get("PAGHIPERPIX_DESCONTO");
		$name='DESCONTOPIXPH'.$cart->id;
		$tipoDesconto = 1;
		$languages=Language::getLanguages();
		foreach ($languages as $key => $language) {
			$arrayName[$language['id_lang']]= 'V0C'.(int)($cart->id_customer).'O'.(int)($cart->id);
		}
		$voucher=new CartRule();
		$voucher->description=(string)($name);
		$voucher->reduction_amount = ($tipoDesconto == 2 ? $total : '');
		$voucher->reduction_percent = ($tipoDesconto == 1 ? $total : '');
		$voucher->name=$arrayName;
		$voucher->id_customer=(int)($cart->id_customer);
		$voucher->id_currency=(int)($cart->id_currency);
		$voucher->quantity=1;
		$voucher->quantity_per_user=1;
		$voucher->cumulable=0;
		$voucher->cumulable_reduction=0;
		$voucher->minimum_amount=0;
		$voucher->active=1;
		$now=time();
		$voucher->date_from=date("Y-m-d H:i:s",$now);
		$voucher->date_to=date("Y-m-d H:i:s",$now+(3600*24));
        $voucher->code='V'.(int)($voucher->id).'C'.(int)($cart->id_customer).'O'.$cart->id;
		if($voucher->add()){
            $cart->addCartRule((int)$voucher->id);
        }
	}
	
	public function so_numeros($a)
	{
		return preg_replace('/\D/', '', $a);
	}
    
    public function nomes_status_pagamentos() 
    {
		global $cookie;
		return Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'order_state` AS a,`'._DB_PREFIX_.'order_state_lang` AS b WHERE b.id_lang = "'.$cookie->id_lang.'" AND a.deleted = "0" AND a.id_order_state=b.id_order_state');
	}
    
    public function validar_fiscal($fiscal)
    {
        require_once(dirname(__FILE__).'/include/class-valida-cpf-cnpj.php');
        $cpf_cnpj = new ValidaCPFCNPJ($this->so_numeros($fiscal));
        return $cpf_cnpj->valida();
    }
	
	public function cpf_cnpj()
	{
        $sql = "SELECT * FROM `"._DB_PREFIX_."paghiperpix` WHERE id_cliente = '".(int)Context::getContext()->customer->id."'";
        $row = Db::getInstance()->getRow($sql);
        if(isset($row['fiscal']) && !empty($row['fiscal'])){
            return $row['fiscal'];
        }else{
            return '';
        }
    }

    public function hookPaymentReturn($params)
    {
		//se ativo
        if ($this->active == false){
            return;
		}
        
		//pedido
        $order = $params['objOrder'];
        
		//dados pix		
		$sql = "SELECT * FROM `"._DB_PREFIX_."paghiperpix_pedidos` WHERE id_pedido = '".(int)$order->id."'";
        $dados = Db::getInstance()->getRow($sql);

		//aplica o layout
        $this->smarty->assign(array(
            'id_order' => $order->id,
            'reference' => $order->reference,
            'params' => $params,
			'dados' => $dados,
            'total' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
        ));
        return $this->display(__FILE__, 'views/templates/hook/confirmation.tpl');
    }
}
