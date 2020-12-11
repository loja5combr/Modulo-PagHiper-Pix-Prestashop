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

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-maskmoney/3.0.2/jquery.maskMoney.min.js"></script>
<script>
{literal}
  $(function() {
    $('.dinheiro').maskMoney({thousands:'', decimal:'.', allowZero:true, allowNegative: true});
  })
{/literal}
</script>

<!-- Nav tabs -->
<ul class="nav nav-tabs" role="tablist">
<li class="active"><a href="#template_1" role="tab" data-toggle="tab">Inicio</a></li>
<li><a href="#template_2" role="tab" data-toggle="tab">Sobre</a></li>
</ul>

<!-- Tab panes -->
<div class="tab-content">
<div class="tab-pane active" id="template_1">{include file='./template_1.tpl'}</div>
<div class="tab-pane" id="template_2">{include file='./template_2.tpl'}</div>
</div>
