<?php
/*<makotostudio />*/

if(!defined('ABSPATH')){
	exit;
}

if(!class_exists('CWP2GEI')):

class CWP2GEI extends WC_Payment_Gateway{

	function __construct(){

   
		if(strpos($_SERVER['SERVER_ADDR'], '192.168')===false){
			ini_set('log_errors', 'On');
			ini_set('display_errors', 'Off');
			ini_set('error_log', dirname(__FILE__).'/error_log.log');
		}

		$this->option=$this->GetOptions();

		if(!isset($this->option->enable))$this->option->enable=false;
		if(!isset($this->option->status))$this->option->status=false;
		if(!isset($this->option->sandbox))$this->option->sandbox=false;
		if(!isset($this->option->flag))$this->option->flag=false;

		$this->DefaultOrganization();

		add_filter('get_love_code', array($this, 'GetLoveCode'), 10, 1);

		/*
		'SetExtraLoveCode' is a testing for WordPress add_filter/apply_filters.
		*/
		add_filter('get_love_code', array($this, 'SetExtraLoveCode'), 11, 1);

		if(is_admin()){

			add_filter('woocommerce_admin_order_actions', array($this, 'ElectronicInvoiceOrderAction'), 10, 2);

			add_action('admin_head', array($this, 'AddAdminStyles'), 99);
			add_action('admin_head', array($this, 'AddAdminScripts'), 100);
			add_action('admin_menu', array($this, 'AdminMenu'), 1);

			add_action('admin_head', array($this, 'AddChosen'));

			add_action('woocommerce_update_options_payment_gateways_'.$this->id, array($this, 'process_admin_options'));

			add_action('wp_ajax_DataSubmit', array($this, 'DataSubmit'));

			add_action('wp_ajax_cw-pay2go-ei_action', array($this, 'PostElectronicInvoice'));

			//add_action('woocommerce_process_shop_order_meta', array($this, 'PostElectronicInvoice'));

		}else{

			if($this->option->enable!=='true')return;

			add_action('wp_head', array($this, 'AddStyles'));
			add_action('wp_head', array($this, 'AddScripts'));

//			add_action('woocommerce_after_order_notes', array($this, 'ElectronicInvoiceFields'));

			add_action('woocommerce_checkout_update_order_meta', array($this, 'UpdateOrderMeta'));

			add_action('woocommerce_checkout_order_processed', array($this, 'ElectronicInvoiceExtraInfo'));

			add_action('woocommerce_after_checkout_validation', array($this, 'PostElectronicInvoice'));

		}

		if($this->option->status=='0'){
			add_action('woocommerce_order_status_processing', array($this, 'PostElectronicInvoice'));
		}

		add_action('woocommerce_order_status_completed', array($this, 'PostElectronicInvoice'));

	}

	function ElectronicInvoiceExtraInfo($order_id){

		/*
		選擇自訂開立時，在後台按下笑臉不會執行以下程式碼
		*/
		if($this->option->enable!=='true')return;

		//if($this->option->status!='99')return;

		//error_log(print_r($_POST, true));

		$arrPost=$_POST;
		$arrPost['ei_status']=$this->option->status;
		if($this->option->status=='1'||$this->option->status=='3'){
			$arrPost['post-to-pay2go']=1;
		}

		update_post_meta($order_id, '_cw-pay2go-ei_extra-info', $arrPost);
	}

	function ElectronicInvoiceOrderAction($actions=array(), $order=false){

		$arrExtraInfo=get_post_meta($order->id, '_cw-pay2go-ei_extra-info', true);

		if($arrExtraInfo){

			if(isset($arrExtraInfo['post-to-pay2go'])&&$arrExtraInfo['post-to-pay2go']=='1'){
				$actions['posted_to_pay2go']=array(
					'name'		=>'已開立發票',
					'action'	=>'post_to_pay2go posted');

			}else{
				$actions['post_to_pay2go']=array(
					'url'=>wp_nonce_url(
						admin_url(
							add_query_arg(
								array(
									'action'		=>'cw-pay2go-ei_action', 
									'order_id'	=>$order->id), 
								'admin-ajax.php')), 
								'cw-pay2go-ei'),

					'name'		=>'智付寶電子發票',
					'action'	=>'post_to_pay2go'
				);
			}

		}

		return $actions;

	}

	public function SetExtraLoveCode($arrOptions){

		$arrOptions['195185']	='財團法人台灣省私立台灣盲人重建院';
		$arrOptions['1799']		='財團法人台灣癌症基金會';
		$arrOptions['531']		='財團法人董氏基金會';

		return $arrOptions;
	}

	public function GetLoveCode($arrOptions=array()){
		$arrOptions=get_option('_cw-pay2go-ei_lovecode');
		return $arrOptions;
	}

	public function DefaultOrganization(){

		if(!get_option('_cw-pay2go-ei_lovecode')){

			$arrOrganization=array(
				'599'				=>'台灣動物不再流浪協會',
				'52668'			=>'社團法人臺北市支持流浪貓絕育計畫協會',
				'199185'		=>'財團法人惠光導盲犬教育基金會', 
				'5299'			=>'社團法人台灣導盲犬協會',
				'391'				=>'台灣預防醫學學會', 
				'860713'		=>'保護動物協會', 
				'17885'			=>'台灣公益服務協會');

			update_option('_cw-pay2go-ei_lovecode', $arrOrganization);
		}
	}

	public function ErrorNotice($arrError){
		foreach($arrError as $value){
			wc_add_notice($value, 'error');
		}

		ob_start();
		wc_print_notices();
		$messages=ob_get_clean();

		$response=array(
			'result'		=>'failure',
			'messages' 	=>$messages,
			'refresh' 	=>'false',
			'reload'    =>'false'
		);

		wp_send_json($response);
		exit();
	}

	function GetOrderInfo($order, $intTaxRate){

		//$arrOrderInfo['amount']=$order->get_total();

		$arrOrderInfo=array(
			'amount'					=>$order->get_total(), 
			'totalitemprice'	=>0);

		foreach($order->get_items() as $value){

			if((int)$value['line_total']>0){

				if(isset($arrOrderInfo['itemname'])){
					$arrOrderInfo['itemname'].='|'.$value['name'];
				}else{
					$arrOrderInfo['itemname']=$value['name'];
				}

				if(isset($arrOrderInfo['itemcount'])){
					$arrOrderInfo['itemcount'].='|'.$value['qty'];
				}else{
					$arrOrderInfo['itemcount']=$value['qty'];
				}

				if(isset($arrOrderInfo['itemunit'])){
					$arrOrderInfo['itemunit'].='|件';
				}else{
					$arrOrderInfo['itemunit']='件';
				}

				if(isset($arrOrderInfo['itemprice'])){
					$arrOrderInfo['itemprice'].='|'.($value['line_total']/$value['qty']);
				}else{
					$arrOrderInfo['itemprice']=($value['line_total']/$value['qty']);
				}

				if(isset($arrOrderInfo['itemamount'])){
					$arrOrderInfo['itemamount'].='|'.$value['line_total'];
				}else{
					$arrOrderInfo['itemamount']=$value['line_total'];
				}

				$arrOrderInfo['totalitemprice']+=(int)$value['line_total'];
			}
		}

		if($this->option->taxtype=='1'){
			$arrOrderInfo['totalamount']	=$order->get_total();
			$arrOrderInfo['amount']				=round($order->get_total()/(1+$intTaxRate*0.01));
			$arrOrderInfo['taxamount']		=$order->get_total()-$arrOrderInfo['amount'];

		}elseif($this->option->taxtype=='1.1'){

			$this->option->taxtype	=1;

			$arrOrderInfo['totalamount']	=$order->get_total();
			$arrOrderInfo['amount']				=$arrOrderInfo['totalitemprice'];
			$arrOrderInfo['taxamount']		=$arrOrderInfo['totalamount']-$arrOrderInfo['amount'];

		}else{
			$arrOrderInfo['taxamount']		=round($order->get_total()*$intTaxRate*0.01);
			$arrOrderInfo['totalamount']	=$arrOrderInfo['totalitemprice']+$arrOrderInfo['taxamount'];
		}

		return $arrOrderInfo;

	}

	function PostElectronicInvoice($order_id=false){ /* 有 $order_id 的狀況: status to processing */


		/*=====檢查會員載具=====*/
		if(isset($_POST['cw-pay2go-ei_billing-invoice-flag'])){
			if($_POST['cw-pay2go-ei_billing-invoice-flag']=='99'){
				if(empty(trim($_POST['cw-pay2go-ei_billing-ubn']))){
					$this->ErrorNotice(array('請輸入統一編號'));
				}
			}elseif($_POST['cw-pay2go-ei_billing-invoice-flag']=='0'||$_POST['cw-pay2go-ei_billing-invoice-flag']=='1'){
				if(empty(trim($_POST['cw-pay2go-ei_billing-invoice-flag-num']))){
					$this->ErrorNotice(array('請輸入載具'));
				}
			}
		}
		/*=====檢查會員載具=====*/


		$strCurrentFilter=current_filter();
		if($strCurrentFilter=='woocommerce_process_shop_order_meta'){
			
		}elseif($strCurrentFilter=='woocommerce_after_checkout_validation'){
			return;

		}else{
			/* 手動開立 */
			if($_GET['order_id'])$order_id=$_GET['order_id'];
		}

		$arrExtraInfo=false;

		if($order_id){ /* 手動開立、付款後開立 */
			$arrExtraInfo=get_post_meta($order_id, '_cw-pay2go-ei_extra-info', true);

			//if($arrExtraInfo['post-to-pay2go']=='1')return;
			if(isset($arrExtraInfo['post-to-pay2go'])&&$arrExtraInfo['post-to-pay2go']=='1')return;

		}

		$intTime=time();
		$intTotalItemPrices=0;

		switch($this->option->taxtype){
			case '1':
			case '1.1':
				$intTaxRate=5;
				break;
			default:
				$intTaxRate=0;
		}

		//if($this->option->status=='99'){
		if($arrExtraInfo){ /* 手動開立、付款後開立 */

			if($arrExtraInfo['ei_status']=='99'){
				if(!isset($_GET['action'])){
					return;
				}else{
					if($_GET['action']!='cw-pay2go-ei_action')return;
				}
			}

			$arrPost=get_post_meta($order_id, '_cw-pay2go-ei_extra-info', true);

			$order=new WC_Order($order_id);

			$arrOrderInfo=$this->GetOrderInfo($order, $intTaxRate);

			$intStatus=1;

		}else{

			/* 當下 status 為付款後開立，而訂單剛成立時並未建立 _cw-pay2go-ei_extra-info，故直接返回 */
			if($this->option->status=='0'||$this->option->status=='99'||$this->option->status=='100')return;

			/* 下單時開立 與 預約開立 */
			$arrPost=$_POST;

			$order=new WC_Order($order_id);



			//$intAmount=$order->get_total();
			$arrOrderInfo=$this->GetOrderInfo($order, $intTaxRate);



			$intStatus=$this->option->status;
		}

		if($arrPost['cw-pay2go-ei_billing-invoice-flag']=='99'){
			$strNeedUBN='1';
		}

		if($strNeedUBN==='1'){
			$strCategory='B2B';
			$strUBN=$arrPost['cw-pay2go-ei_billing-ubn'];
			$intInvoiceFlag='-1';
		}else{
			$strCategory='B2C';
			$strUBN='';
			$intInvoiceFlag=$arrPost['cw-pay2go-ei_billing-invoice-flag'];
		}

		$intPrintFlag		='N';
		$intCarruerType	=$intInvoiceFlag;
		$intFlagNum			='';
		$strLoveCode		='';

		switch($intInvoiceFlag){
			case '0':
				$intFlagNum			=$arrPost['cw-pay2go-ei_billing-invoice-flag-num'];
				break;
			case '1':
				$intFlagNum			=$arrPost['cw-pay2go-ei_billing-invoice-flag-num'];
				break;
			case '2':
				$intFlagNum			=$arrPost['billing_email'];
				break;
			case '3':
				$intCarruerType	='';
				$strLoveCode		=$arrPost['cw-pay2go-ei_organization'];
				break;
			case '-1':
				$intPrintFlag		='Y';
				$intCarruerType	='';
				break;
		}

		if(!isset($arrPost['billing_first_name']))	$arrPost['billing_first_name']='';
		if(!isset($arrPost['billing_last_name']))		$arrPost['billing_last_name']='';

		if(!isset($arrPost['billing_address_1']))	$arrPost['billing_address_1']='';
		if(!isset($arrPost['billing_address_2']))	$arrPost['billing_address_2']='';
		if(!isset($arrPost['billing_city']))			$arrPost['billing_city']='';
		if(!isset($arrPost['billing_state']))			$arrPost['billing_state']='';


		$strCreateStatusDate='';
		if($this->option->status=='3'){
			$strCreateStatusDate=date('Y-m-d', strtotime('+'.$this->option->{'create-status-time'}.' days'));
		}

		if(empty($arrPost['cw-pay2go-ei_billing-ubn-title'])){
			$strBuyersName=$arrPost['billing_last_name'].$arrPost['billing_first_name'];
		}else{
			$strBuyersName=$arrPost['cw-pay2go-ei_billing-ubn-title'];
		}

		$arrPostData=array(
			'RespondType'				=>'JSON', 
			'Version'						=>'1.3', 
			'TimeStamp'					=>$intTime, 
			'TransNum'					=>'', 
			'MerchantOrderNo'		=>$intTime, 

			//'BuyerName'					=>$arrPost['billing_last_name'].$arrPost['billing_first_name'], 
			'BuyerName'					=>$strBuyersName, 

			'BuyerUBN'					=>$strUBN, 

			'BuyerAddress'			=>$arrPost['billing_state'].$arrPost['billing_city'].$arrPost['billing_address_1'].$arrPost['billing_address_2'], 
			'BuyerEmail'				=>$arrPost['billing_email'], 
			'BuyerPhone'				=>$arrPost['billing_phone'], 
			'Category'					=>$strCategory, 
			'TaxType'						=>$this->option->taxtype, 
			'TaxRate'						=>$intTaxRate, 
			'Amt'								=>$arrOrderInfo['amount'], 
			'TaxAmt'						=>$arrOrderInfo['taxamount'], 
			'TotalAmt'					=>$arrOrderInfo['totalamount'], 
			'CarrierType'				=>$intCarruerType, 
			'CarrierNum'				=>$intFlagNum, 
			'LoveCode'					=>$strLoveCode, 
			'PrintFlag'					=>$intPrintFlag, 
			'ItemName'					=>$arrOrderInfo['itemname'], 
			'ItemCount'					=>$arrOrderInfo['itemcount'], 
			'ItemUnit'					=>$arrOrderInfo['itemunit'], 
			'ItemPrice'					=>$arrOrderInfo['itemprice'], 
			'ItemAmt'						=>$arrOrderInfo['itemamount'], 
			'Comment'						=>'',

			'Status'						=>$intStatus, 

			//'NotifyEmail'				=>'0', 
			'CreateStatusTime'	=>$strCreateStatusDate);

		$strPostData=http_build_query($arrPostData);

		$intLength=strlen($strPostData);
		$intPadding=32-($intLength%32);

		$strPostData.=str_repeat(chr($intPadding), $intPadding);

		$strPostData=trim(bin2hex(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->option->{'hash-key'}, $strPostData, MCRYPT_MODE_CBC, $this->option->{'hash-iv'})));

		$arrTransactionData=array(
			'MerchantID_'	=>$this->option->{'metchant-id'}, 
			'PostData_'		=>$strPostData);

		$strTransactionData=http_build_query($arrTransactionData);

		if($this->option->sandbox==='true'){
			$strURL='https://cinv.pay2go.com/API/invoice_issue';
		}else{
			$strURL='https://inv.pay2go.com/API/invoice_issue';
		}

		$arrResult=$this->CurlPost($strTransactionData, $strURL);

		$arrResult=json_decode($arrResult);

		$stdResult=json_decode($arrResult->Result);

		if($arrResult->Status==='SUCCESS'){

			if((isset($_GET['action'])&&$_GET['action']=='cw-pay2go-ei_action')||(isset($arrExtraInfo['ei_status'])&&($arrExtraInfo['ei_status']=='0'||$arrExtraInfo['ei_status']=='100'))){

				$arrPostMeta=get_post_meta($order_id, '_cw-pay2go-ei_extra-info', true);

				$arrPostMeta=(array)$stdResult+$arrPostMeta;

				update_post_meta($order_id, '_cw-pay2go-ei_extra-info', $arrPostMeta);

				$this->UpdateOrderMeta($order_id);

				if(is_admin()){
					wp_safe_redirect(wp_get_referer()?wp_get_referer():admin_url('edit.php?post_type=shop_order'));
					//die();
				}

			}else{ // 訂單成立時開立
				$arrPostMeta=(array)$stdResult+$_POST;
				update_post_meta($order_id, '_cw-pay2go-ei_extra-info', $arrPostMeta);

				return $arrPostMeta;

			}

		}else{

			if((isset($_GET['action'])&&$_GET['action']=='cw-pay2go-ei_action')||(isset($arrExtraInfo['ei_status'])&&($arrExtraInfo['ei_status']=='0'||$arrExtraInfo['ei_status']=='100'))){
				$order->add_order_note($arrResult->Message);
				wp_safe_redirect(wp_get_referer()?wp_get_referer():admin_url('edit.php?post_type=shop_order'));
				die();

			}else{
				$this->ErrorNotice(array($arrResult->Message));
			}
		}
	}

	function UpdateOrderMeta($order_id){

		$order=new WC_Order($order_id);

		$arrPost=get_post_meta($order_id, '_cw-pay2go-ei_extra-info', true);

		if($arrPost){

			if(isset($arrPost['post-to-pay2go'])&&$arrPost['post-to-pay2go']=='1')return;

			$arrPost['post-to-pay2go']=1;
			update_post_meta($order_id, '_cw-pay2go-ei_extra-info', $arrPost);

			$intStatus=$arrPost['ei_status'];

			if($intStatus=='99'){
				if(!isset($_GET['action'])){
					return;
				}else{
					if($_GET['action']!='cw-pay2go-ei_action'){
						return;
					}
				}
			}

		}else{ // 訂單成立時開立

			$intStatus=$this->option->status;

			if($intStatus=='0'||$intStatus=='99'||$intStatus=='100')return;

			$arrPost=$this->PostElectronicInvoice($order_id);

		}

		if($arrPost['cw-pay2go-ei_billing-invoice-flag']=='99'){
			$strNeedUBN=true;
			$intNeedUBN=1;
		}else{
			$strNeedUBN=false;
			$intNeedUBN=0;
		}

		switch($intStatus){
			case '0':
			case '1':
			case '99':
			case '100':
				$strOrderNote='此訂單已開立電子發票';
				break;
			case '3':
				$strOrderNote='此訂單為預約開立電子發票';
				break;
		}

		$strOrderNote.='<br />發票號碼 - '.$arrPost['InvoiceNumber'].'<br />';

		if($strNeedUBN===true){
			$strFlag='統一編號';
		}else{
			switch($arrPost['cw-pay2go-ei_billing-invoice-flag']){
				case '0':
					$strFlag='手機條碼<br />載具編號 - '.$arrPost['cw-pay2go-ei_billing-invoice-flag-num'];
					break;
				case '1':
					$strFlag='自然人憑證條碼<br />載具編號 - '.$arrPost['cw-pay2go-ei_billing-invoice-flag-num'];
					break;
				case '2':
					$strFlag='會員載具<br />載具編號 - '.$arrPost['billing_email'];
					break;
				case '3':
					$arrOrganization=apply_filters('get_love_code', NULL);
					$strFlag='捐贈發票<br />愛心碼 - '.$arrPost['cw-pay2go-ei_organization'].'<br />社福團體 - '.$arrOrganization[$arrPost['cw-pay2go-ei_organization']];
					break;
				case '-1':
					$strFlag='索取紙本發票';
					break;
			}

		}

		$strOrderNote.='索取方式 - '.$strFlag;

		if($intNeedUBN===1){
			$strOrderNote.='<br />';
			$strOrderNote.='買受人名稱 - '.$arrPost['cw-pay2go-ei_billing-ubn-title'];
			$strOrderNote.='<br />';
			$strOrderNote.='買受人統編 - '.$arrPost['cw-pay2go-ei_billing-ubn'];
		}



		$order->add_order_note($strOrderNote);



		update_post_meta($order_id, '_cw-pay2go-ei_billing-need-ubn', sanitize_text_field($intNeedUBN));

		update_post_meta($order_id, '_cw-pay2go-ei_billing-ubn', sanitize_text_field($arrPost['cw-pay2go-ei_billing-ubn']));
		update_post_meta($order_id, '_cw-pay2go-ei_billing-invoice-flag', sanitize_text_field($arrPost['cw-pay2go-ei_billing-invoice-flag']));
		update_post_meta($order_id, '_cw-pay2go-ei_billing-invoice-flag-num', sanitize_text_field($arrPost['cw-pay2go-ei_billing-invoice-flag-num']));
	}

	function CurlPost($strTransactionData, $strURL){

		$arrCurlOptions=array(

			CURLOPT_URL							=>$strURL, 

			CURLOPT_HEADER					=>false, 
			CURLOPT_RETURNTRANSFER	=>true, 
			CURLOPT_USERAGENT				=>'Google Bot', 
			CURLOPT_FOLLOWLOCATION	=>true, 
			CURLOPT_SSL_VERIFYPEER	=>false, 
			CURLOPT_SSL_VERIFYHOST	=>false, 
			CURLOPT_POST						=>1, 
			CURLOPT_POSTFIELDS			=>$strTransactionData);

		$ch=curl_init();

		curl_setopt_array($ch, $arrCurlOptions);

		$arrResult=curl_exec($ch);

		$strReturn=curl_getinfo($ch, CURLINFO_HTTP_CODE);

		$strError=curl_errno($ch);

		curl_close($ch);

		return $arrResult;
	}

	function GetFlags(){

		$arrFlag=array(
			'99'	=>'統一編號',
			'2'		=>'會員載具',
			'0'		=>'手機條碼',
			'1'		=>'自然人憑證條碼',
			'3'		=>'捐贈發票',
			'-1'	=>'索取紙本發票');

		$arrCustomFlag=array();

		if(is_array($this->option->flag)){
			foreach($this->option->flag as $value){
				$arrCustomFlag[$value]=$arrFlag[$value];
			}
		}

		if(is_admin())$arrCustomFlag=$arrCustomFlag+$arrFlag;

		return $arrCustomFlag;

	}

	function GetOptions(){

		$arrOption=get_option('_cw-pay2go-ei_settings', true);

		$stdOption=new stdClass();

		if(is_array($arrOption)){
			foreach($arrOption as $key=>$value){
				$key=str_replace('cw-pay2go-ei_', '', $key);
				$stdOption->{$key}=$value;
			}
		}

		return $stdOption;

	}

	function ElectronicInvoiceFields($checkout){

		if($this->option->enable==='true'){
			echo '<div id="cw-pay2go-ei_fields">';
			echo '<h3>發票資訊</h3>';

			echo '<div id="cw-pay2go-ei_bif">';

			$intFlag=$checkout->get_value('cw-pay2go-ei_billing-invoice-flag');

			$arrFlag=$this->GetFlags();

			woocommerce_form_field(
				'cw-pay2go-ei_billing-invoice-flag', 
				array(
					'type'	=>'select',
					'label'	=>'電子發票索取方式',
					'options'=>$arrFlag), 
				$intFlag);

			echo '</div>';

			echo '<div id="cw-pay2go-ei_billing-need-ubn-info">';

			woocommerce_form_field(
				'cw-pay2go-ei_billing-ubn-title', 
				array(
					'type'				=>'text',
					'placeholder'	=>'買受人名稱',
					'required'		=>false,
					'default'			=> ''), 
				$checkout->get_value('cw-pay2go-ei_billing-ubn-title'));

			woocommerce_form_field(
				'cw-pay2go-ei_billing-ubn', 
				array(
					'type'				=>'text',
					'placeholder'	=>'請輸入統一編號',
					'required'		=>false,
					'default'			=> ''), 
				$checkout->get_value('cw-pay2go-ei_billing-ubn'));

			echo '電子發票將寄送至您的電子郵件地址，請自行列印';
			echo '</div>';

			echo '<div id="cw-pay2go-ei_bifn">';

			woocommerce_form_field(
				'cw-pay2go-ei_billing-invoice-flag-num', 
				array(
					'type'				=>'text',
					'label'				=>'載具編號',
					'placeholder'	=>'電子發票通知將寄送至您的電子郵件地址',
					'required'		=>false,
					'default'			=>''), 
				$checkout->get_value('cw-pay2go-ei_billing-invoice-flag-num'));

			echo '</div>';


			$strStyle='';
			if($intFlag!='3')$strStyle=' style="display:none;"';

			echo '<div id="cw-pay2go-ei_org"'.$strStyle.'>';

			$arrCustomOrganization=array();

			$arrOrganization=apply_filters('get_love_code', NULL);

			if(is_array($this->option->organization)){
				foreach($this->option->organization as $value){
					$arrCustomOrganization[$value]=$arrOrganization[$value];
				}
			}

			woocommerce_form_field(
				'cw-pay2go-ei_organization', 
				array(
					'type'				=>'select',
					'label'				=>'捐贈團體',
					'options'			=>$arrCustomOrganization), 
				$checkout->get_value('cw-pay2go-ei_organization'));

			echo '</div>';

			echo '</div>';
		}
	}

	function DataSubmit(){

		foreach($_POST as $key=>$value){
			if(strpos($key, 'cw-pay2go-ei_')===0){
				$arrData[$key]=$value;
			}
		}

		$intResult=false;
		if(count($arrData)>0){
			$intResult=update_option('_cw-pay2go-ei_settings', $arrData);
		}

		if($intResult){
			echo json_encode(array('result'=>'success'));
		}else{
			echo json_encode(array('result'=>'fail'));
		}
		exit();
	}

	function AddChosen(){
		wp_register_style('cw-pay2go-ei-chosen-style', CWP2GEI_URL.'chosen/chosen.css');
		wp_enqueue_style('cw-pay2go-ei-chosen-style');

		wp_register_script('cw-pay2go-ei-chosen-script', CWP2GEI_URL.'chosen/chosen.jquery.js', array('jquery'));
		wp_enqueue_script('cw-pay2go-ei-chosen-script');
		
	}

	function AddStyles(){
		wp_register_style('cw-pay2go-ei-style', CWP2GEI_URL.'css/cw-pay2go-ei.css');
		wp_enqueue_style('cw-pay2go-ei-style');
	}

	function AddScripts(){
		wp_register_script('cw-pay2go-ei-script', CWP2GEI_URL.'js/cw-pay2go-ei.js', array('jquery'));
		wp_enqueue_script('cw-pay2go-ei-script');

		wp_localize_script(	
			'cw-pay2go-ei-script', 
			'CWP2GEI_vars',
			array(
				'ajaxurl'	=>admin_url('admin-ajax.php')));
	}

	function AddAdminStyles(){
		wp_register_style('cw-pay2go-ei-admin-style', CWP2GEI_URL.'css/cw-pay2go-ei_admin.css');
		wp_enqueue_style('cw-pay2go-ei-admin-style');
	}

	function AddAdminScripts(){
		wp_register_script('cw-pay2go-ei-admin-script', CWP2GEI_URL.'js/cw-pay2go-ei_admin.js', array('jquery'));
		wp_enqueue_script('cw-pay2go-ei-admin-script');

		wp_localize_script(	
			'cw-pay2go-ei-admin-script', 
			'CWP2GEI_vars',
			array(
				'ajaxurl'	=>admin_url('admin-ajax.php')));

	}

	function AdminMenu(){	
		add_menu_page('CWP Pay2Go Electronic Invoice', '智付寶電子發票', 'manage_options', 'cwp2gei', create_function('', 'require_once \''.CWP2GEI_DIR.'/templates/cw-pay2go-ei_admin.php\';'), NULL, 56);
	}

}

return new CWP2GEI();

endif;