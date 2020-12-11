<?php
class PagHiperPixIpnModuleFrontController extends ModuleFrontController
{
    public $display_column_left = false;
	public $display_column_higt = false;
    public $display_header = false;
    public $display_header_javascript = false;
    public $display_footer = false;
    public $ssl = true;
    public function postProcess()
    {
		//se debug ativo ou erro 
		if(Configuration::get("PAGHIPERPIX_DEBUG") && isset($_POST)){
			$sql = "INSERT INTO `" ._DB_PREFIX_. "loja5_logs` SET modulo = 'paghiperpix', url='(ipn)', metodo = 'POST', http_status = '200', enviado = '[]', recebido = '".pSQL(json_encode($_POST))."', data = NOW();";
			Db::getInstance()->execute($sql);
		}
		//se retornar os dados nescessarios
        if(isset($_POST['transaction_id']) && isset($_POST['notification_id']) && isset($_POST['apiKey']) && $_POST['apiKey']==trim(Configuration::get("PAGHIPERPIX_KEY"))){
			//json consulta a transacao pix
			$json = array();
			$json['token'] = trim(Configuration::get("PAGHIPERPIX_TOKEN"));
			$json['apiKey'] = trim(Configuration::get("PAGHIPERPIX_KEY"));
			$json['transaction_id'] = trim($_POST['transaction_id']);
			$json['notification_id'] = trim($_POST['notification_id']);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://pix.paghiper.com/invoice/notification/');
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
			$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$retorno = @json_decode($response,true);
			curl_close($ch);
			//processa o resultado 
			if(isset($retorno['status_request']['result']) && $retorno['status_request']['result']=='success'){
				//se debug ativo ou erro 
				if(Configuration::get("PAGHIPERPIX_DEBUG") || $httpcode!=201){
					$sql = "INSERT INTO `" ._DB_PREFIX_. "loja5_logs` SET modulo = 'paghiperpix', url='/invoice/notification/', metodo = 'POST', http_status = '200', enviado = '".pSQL(json_encode($json))."', recebido = '".pSQL(json_encode($retorno))."', data = NOW();";
					Db::getInstance()->execute($sql);
				}
				//pega o id do pedido por o id da transacao 
				$sql = 'SELECT * FROM `'._DB_PREFIX_.'paghiperpix_pedidos` WHERE `transacao` = "'.pSQL($json['transaction_id']).'"';
				$dados = Db::getInstance()->getRow($sql);
				$order = new Order((int)$dados['id_pedido']);
				if($order){
					if($dados['status']=='iniciado' && $retorno['status_request']['status']=='paid'){
						//se pago
						if($order->getCurrentState()!=Configuration::get("PAGHIPERPIX_PAGO")){
							$history = new OrderHistory();
							$history->id_order = (int)$order->id;
							$history->changeIdOrderState(Configuration::get("PAGHIPERPIX_PAGO"), $order);
							$history->addWithemail(true, null);
						}
						Db::getInstance()->execute("UPDATE `"._DB_PREFIX_."paghiperpix_pedidos` SET status = 'pago' WHERE transacao = '".pSQL($json['transaction_id'])."'");		
					}elseif(($dados['status']=='iniciado' || $dados['status']=='pago') && $retorno['status_request']['status']=='canceled'){
						//se cancelado
						if($order->getCurrentState()!=Configuration::get("PAGHIPERPIX_CANCELADO")){
							$history = new OrderHistory();
							$history->id_order = (int)$order->id;
							$history->changeIdOrderState(Configuration::get("PAGHIPERPIX_CANCELADO"), $order);
							$history->addWithemail(true, null);
						}
						Db::getInstance()->execute("UPDATE `"._DB_PREFIX_."paghiperpix_pedidos` SET status = 'cancelado' WHERE transacao = '".pSQL($json['transaction_id'])."'");
					}elseif($dados['status']=='pago' && $retorno['status_request']['status']=='refunded'){
						//se devolvido
						if($order->getCurrentState()!=Configuration::get("PAGHIPERPIX_DEVOLVIDO")){
							$history = new OrderHistory();
							$history->id_order = (int)$order->id;
							$history->changeIdOrderState(Configuration::get("PAGHIPERPIX_DEVOLVIDO"), $order);
							$history->addWithemail(true, null);
						}
						Db::getInstance()->execute("UPDATE `"._DB_PREFIX_."paghiperpix_pedidos` SET status = 'devolvido' WHERE transacao = '".pSQL($json['transaction_id'])."'");
					}
				}else{
					echo 'N.F.L';
				}
			}else{
				//erro ao consultar
				$sql = "INSERT INTO `" ._DB_PREFIX_. "loja5_logs` SET modulo = 'paghiperpix', url='/invoice/notification/', metodo = 'POST', http_status = '".$httpcode."', enviado = '".pSQL(json_encode($json))."', recebido = '".pSQL(json_encode($retorno))."', data = NOW();";
				Db::getInstance()->execute($sql);
			}
        }
        die('IPN PagHiper Pix');
    }
}
