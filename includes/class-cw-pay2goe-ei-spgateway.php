<?php
/*<makotostudio />*/

if(!defined('ABSPATH')){
	exit;
}

if(!class_exists('CWP2GEI_SPGATEWAY')):

class CWP2GEI_SPGATEWAY extends WC_Payment_Gateway{

	public $option = [];
	public $post_data_array = [];
	public $hashKey = '';
	public $hashIv = '';
	public $merchantId = '';
	public $enable = false;
	public $taxtype = 0;
	public $status = 0;
	public $createStatusTime = 0;
	public $sandbox = 0;
	public $url = '';
	function __construct() {

		 if(strpos($_SERVER['SERVER_ADDR'], '192.168')===false){
		 	ini_set('log_errors', 'On');
		 	ini_set('display_errors', 'Off');
		 	ini_set('error_log', dirname(__FILE__).'/error_log.log');
		 }

	 	$this->option	        = $this->GetOptions();
		$this->enable 	        = $this->option->{'enable'};
		$this->sandbox          = $this->option->{'sandbox'};
		$this->merchantId       = $this->option->{'metchant-id'};
		$this->hashKey 	        = $this->option->{'hash-key'};
		$this->hashIv 	        = $this->option->{'hash-iv'};
		$this->createStatusTime = $this->option->{'create-status-time'};
		$this->status           = $this->option->{'status'};
		$this->taxtype          = $this->option->{'taxtype'};


//		print "<pre>";
//		print_r($this->option);
//		print "</pre>";
		//		exit;

		if($this->sandbox == true) {
			$this->url = "https://cinv.pay2go.com/API/invoice_issue";
		} else {
			$this->url = "https://inv.pay2go.com/API/invoice_issue";
		}
	}

	public function setParameter($data)
	{
		//
		$date = new DateTime();

		//		if( $this->sandbox == false) {
		//			$this->post_data_array = array(
		//				//post_data 欄位資料
		//					"RespondType" => "JSON",
		//					"Version" => "1.4",
		//					"TimeStamp" => time(), //請以  time()  格式
		//					"TransNum" => "",
		//					"MerchantOrderNo" => $date->getTimestamp(),  //"201409170000009",
		//					"BuyerName" => "王大品",
		//					"BuyerUBN" => "99112233",
		//					"BuyerAddress" => "台北市南港區南港路一段 99 號",
		//					"BuyerEmail" => "mrjesuserwinsuarez@gmail.com",
		//					"BuyerPhone" => "0955221144",
		//					"Category" => "B2B",
		//					"TaxType" => "1",
		//					"TaxRate" => "5",
		//					"Amt" => "490",
		//					"TaxAmt" => "10",
		//					"TotalAmt" => "500",
		//					"CarrierType" => "",
		//					"CarrierNum" => rawurlencode(""),
		//					"LoveCode" => "",
		//					"PrintFlag" => "Y",
		//					"ItemName" => "商品一|商品二", //多項商品時，以「|」分開
		//					"ItemCount" => "1|2", //多項商品時，以「|」分開
		//					"ItemUnit" => "個|個", //多項商品時，以「|」分開
		//					"ItemPrice" => "300|100", //多項商品時，以「|」分開
		//					"ItemAmt" => "300|200", //多項商品時，以「|」分開
		//					"Comment" => "TEST，備註說明",
		//					"Status" => "1", //1=立即開立，0=待開立，3=延遲開立
		//					"CreateStatusTime" => "",
		//					"NotifyEmail" => "1", //1=通知，0=不通知
		//			);
		//		} else {
		$this->post_data_array  = $data;
		//		}
	}
	public function postInvoice()
	{

		//		print "<pre>";
		//
		//		print "post invooice now";
		//			print_r($this->post_data_array);
		//		print "</pre>";

		$transaction_data_str = http_build_query(
			[
				'MerchantID_' => $this->merchantId,
				'PostData_' => trim(bin2hex(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->hashKey, $this->addPadding(http_build_query($this->post_data_array)), MCRYPT_MODE_CBC, $this->hashIv))) //加密
			]
		);

		$result = $this->curlWork($this->url, $transaction_data_str); //背景送出

//		print "<br><br> status </br>";
//		print_r($result);

	}
	function addPadding($string, $blocksize = 32)
	{
	 	$len = strlen($string);
	 	$pad = $blocksize - ($len % $blocksize);
	 	$string .= str_repeat(chr($pad), $pad);
	 	return $string;
	}
	function curlWork($url = "", $parameter = "")
	{
	 	$curl_options = array(
	 	    CURLOPT_URL => $url,
	 	    CURLOPT_HEADER => false,
	 	    CURLOPT_RETURNTRANSFER => true,
	 	    CURLOPT_USERAGENT => "Google Bot",
	 	    CURLOPT_FOLLOWLOCATION => true,
	 	    CURLOPT_SSL_VERIFYPEER => FALSE,
	 	    CURLOPT_SSL_VERIFYHOST => FALSE,
	 	    CURLOPT_POST => "1",
	 	    CURLOPT_POSTFIELDS => $parameter
		);
		$ch = curl_init();
		curl_setopt_array($ch, $curl_options);
		$result = curl_exec($ch);
		$retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_error = curl_errno($ch);
		curl_close($ch);
		$return_info = array(
	     	"url" => $url,
	     	"sent_parameter" => $parameter,
	     	"http_status" => $retcode,
	     	"curl_error_no" => $curl_error,
	     	"web_info" => $result
	    );
		return $return_info;
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
}
endif;