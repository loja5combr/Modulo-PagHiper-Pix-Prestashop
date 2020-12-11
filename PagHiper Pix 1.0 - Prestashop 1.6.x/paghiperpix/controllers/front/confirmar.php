<?php
/*
* @package    PagHiper Pix Prestashop
* @version    1.0
* @license    BSD License (3-clause)
* @copyright  (c) 2020
* @link       https://www.paghiper.com/
* @dev        Bruno Alencar - Loja5.com.br
*/

class PagHiperPixConfirmarModuleFrontController extends ModuleFrontController
{
    public $display_column_left = false;
	public $display_column_higt = false;
    public $display_header = false;
    public $display_header_javascript = false;
    public $display_footer = false;

    public function postProcess(){
        //dados do pedido 
        $carrinho = $cart = Context::getContext()->cart;
        $cliente = Context::getContext()->customer;
		$endereco_id = $carrinho->id_address_invoice;
        $endereco = new Address((int)($endereco_id));
        $estado = new State((int)($endereco->id_state));
		
		//valida o carrinho
		$json = array();
		if(!$carrinho || $carrinho->id==0){
			$json['status'] = false;
            $json['erro'] = 'Ops, problema no carrinho de compras, atualize a página e tente novamente!';
			die(json_encode($json));
		}
		
		//valida os posts
		if(!isset($_POST['fiscal']) || !$this->module->validar_fiscal($_POST['fiscal'])){
			$json['status'] = false;
            $json['erro'] = 'Ops, informe o CPF ou CNPJ do pagador válido e tente novamente!';
			die(json_encode($json));
		}
		
		//se tem desconto 
        $boleto_desconto = (float)Configuration::get("PAGHIPERPIX_DESCONTO");
		if($boleto_desconto > 0){
			$this->module->aplicarDesconto($carrinho);
			$carrinho = new Cart($carrinho->id);
		}
		
		//totais 
		$frete = $carrinho->getOrderTotal(true, 5);
        $total = $carrinho->getOrderTotal(true, Cart::BOTH);
		$desconto = $carrinho->getOrderTotal(true, Cart::ONLY_DISCOUNTS);
		
		//captura o cpf ou cnpj
		$numero_fiscal = $this->module->so_numeros($_POST['fiscal']);
		$sql = "SELECT * FROM `"._DB_PREFIX_."paghiperpix` WHERE id_cliente = '".(int)$cliente->id."'";
        $row = Db::getInstance()->getRow($sql);
        if(!isset($row['fiscal'])){
            $query = "INSERT INTO `"._DB_PREFIX_."paghiperpix` (`id_cliente`, `fiscal`) VALUES ('".(int)$cliente->id."', '".$numero_fiscal."');";
            Db::getInstance()->execute($query);
        }else{
            $query = "UPDATE `"._DB_PREFIX_."paghiperpix` SET `fiscal` = '".$numero_fiscal."' WHERE `id_cliente` = '".(int)$cliente->id."'";
            Db::getInstance()->execute($query);
        }
		
		//tratar o telefone
		$telefone = (!empty($endereco->phone)?$this->module->so_numeros($endereco->phone):$this->module->so_numeros($endereco->phone_mobile));
        
        //dados do pix
        $json = array();
		$json['apiKey'] = trim(Configuration::get("PAGHIPERPIX_KEY"));
		$json['order_id'] = $carrinho->id;
		$json['payer_email'] = $cliente->email;
		$json['payer_name'] = $cliente->firstname.' '.$cliente->lastname;
		$json['payer_cpf_cnpj'] = $numero_fiscal;
		$json['payer_phone'] = $telefone;
		$json['notification_url'] = Context::getContext()->link->getModuleLink('paghiperpix', 'ipn', array('ajax'=>'true','id'=>$carrinho->id,'key'=>$carrinho->secure_key));
		$json['discount_cents'] = number_format(abs($desconto), 2, '', '');
		$json['shipping_price_cents'] = number_format($frete, 2, '', '');
		$json['days_due_date'] = (int)Configuration::get("PAGHIPERPIX_VALIDADE");
        
        //produtos
		$i=1;
		foreach($carrinho->getProducts() AS $produto){
            $json['items'][$i]['item_id'] = $produto['id_product'];
            $json['items'][$i]['description'] = $produto['name'];
            $json['items'][$i]['price_cents'] = number_format($produto['price_wt'], 2, '', '');
            $json['items'][$i]['quantity'] = $produto['cart_quantity'];
            $i++;
		}
		
		//faz o request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://pix.paghiper.com/invoice/create/');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);  
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Accept: application/json',
			'Content-Type: application/json'
		));
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $retorno = @json_decode($response,true);
        if(!$retorno){
            $retorno = $response;
        }
        curl_close($ch);
        $pix = array('status'=>$httpcode,'erro'=>$error,'enviado'=>$json,'retorno'=>$retorno);
		
		//se debug ativo ou erro 
		if(Configuration::get("PAGHIPERPIX_DEBUG") || $pix['status']!=201){
			$sql = "INSERT INTO `" ._DB_PREFIX_. "loja5_logs` SET modulo = 'paghiperpix', url='/invoice/create/', metodo = 'POST', http_status = '".$pix['status']."', enviado = '".pSQL(json_encode($json))."', recebido = '".pSQL(json_encode($pix['retorno']))."', data = NOW();";
			Db::getInstance()->execute($sql);
		}
		
		//json de confirmacao
        $json = array();
		$json['http'] = $pix['status'];
        if($pix['status']==201 && isset($pix['retorno']['pix_create_request']['result']) && $pix['retorno']['pix_create_request']['result']=='success'){
			//pedido ok
            $json['status'] = true;
            $id   = $pix['retorno']['pix_create_request']['transaction_id'];
			$link = $pix['retorno']['pix_create_request']['pix_code']['pix_url'];
			$qr   = $pix['retorno']['pix_create_request']['pix_code']['qrcode_base64'];
			$emv  = $pix['retorno']['pix_create_request']['pix_code']['emv'];
			$pagador = $cliente->firstname.' '.$cliente->lastname;
			
			//cria o pedido
            $extraVars = array(
				'{segunda_via_pix}' => $link,
				'{link_pix}' => $link,
			);
            $this->module->validateOrder($carrinho->id, (int)Configuration::get("PAGHIPERPIX_INICIADA"), $total, "PagHiper Pix", null, $extraVars, null, false, $cliente->secure_key);
			
			//consulta o pedido criado
            $order = new Order($this->module->currentOrder);
			
			//log
			$msglog = '[API] Transação PagHiper #'.$id.' via Pix ('.Tools::displayPrice($total).')';
			
			//cria um log para o pedido
			$msg = new Message();
			$message = strip_tags($msglog, '<br>');
			if (Validate::isCleanHtml($message)){
				$msg->message = $message;
				$msg->id_order = intval($order->id);
				$msg->private = 1;
				$msg->add();
			}
			
			//cria o pedido no banco de dados 
			$sql = "INSERT INTO `" ._DB_PREFIX_. "paghiperpix_pedidos` (`id`, `id_pedido`, `id_carrinho`, `transacao`, `pagador`, `status`, `valor`, `emv`, `qrcode`, `url`) VALUES (NULL, '".$order->id."', '".$carrinho->id."', '".$id."', '".$pagador."', 'iniciado', '".$total."', '".$emv."', '".$qr."', '".$link."');";
			Db::getInstance()->execute($sql);
			
			//url cupom ok
			$json['cupom'] = Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'index.php?controller=order-confirmation&id_cart='.(int)($carrinho->id).'&id_module='.(int)($this->module->id).'&id_order='.$this->module->currentOrder.'&transacao='.$id.'&hash='.sha1($id).'&key='.$cliente->secure_key;
			
			//dados pix
			$json['id'] = $id;
			$json['resultado'] = $pix['retorno']['pix_create_request'];	

        }elseif(isset($pix['retorno']['response_message'])){
			//erro
            $json['status'] = false;
            $json['erro'] = 'Erro no pagamento Pix do PagHiper: '.$pix['retorno']['response_message'];
        }elseif(!empty($pix['erro'])){
			//erro
            $json['status'] = false;
            $json['erro'] = 'Erro de conectividade no pagamento Pix do PagHiper: '.$pix['erro'];
        }else{
			//erro
            $json['status'] = false;
            $json['erro'] = 'Erro desconhecido ao processar pagamento Pix junto a PagHiper! (ver logs)';
        }
		
		//remove o cupom se existir
		if($json['status']==false){
			$this->module->removerVoucherErro($carrinho);
		}
		
		//json final
        die(json_encode($json));
    }
}
