<?php //====以下為副程式====  
	function addpadding($string, $blocksize = 32) {             
	 	$len = strlen($string);             
	 	$pad = $blocksize - ($len % $blocksize);             
	 	$string .= str_repeat(chr($pad), $pad);             
	 	return $string;         
	}         
	function curl_work($url = "", $parameter = "") {             
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

	$date = new DateTime(); 
	//====以上為副程式====          
	$post_data_array = array(
		//post_data 欄位資料             
		"RespondType" => "JSON",             
		"Version" => "1.4",             
		"TimeStamp" => time(), //請以  time()  格式             
		"TransNum" => "",             
		"MerchantOrderNo" => "201409170000006", 
		"BuyerName" => "王大品",             
		"BuyerUBN" => "99112233", 
		"BuyerAddress" => "台北市南港區南港路一段 99 號",             
		"BuyerEmail" => "mrjesuserwinsuarez@gmail.com",             
		"BuyerPhone" => "0955221144",             
		"Category" => "B2B",             
		"TaxType" => "1",             
		"TaxRate" => "5",             
		"Amt" => "490",             
		"TaxAmt" => "10",             
		"TotalAmt" => "500",             
		"CarrierType" => "", 
		"CarrierNum" => rawurlencode(""),             
		"LoveCode" => "",             
		"PrintFlag" => "Y", 
		"ItemName" => "商品一|商品二", //多項商品時，以「|」分開
		"ItemCount" => "1|2", //多項商品時，以「|」分開 
		"ItemUnit" => "個|個", //多項商品時，以「|」分開 
		"ItemPrice" => "300|100", //多項商品時，以「|」分開 
		"ItemAmt" => "300|200", //多項商品時，以「|」分開 
		"Comment" => "TEST，備註說明", 
		"Status" => "1", //1=立即開立，0=待開立，3=延遲開立             
		"CreateStatusTime" => "", 
		"NotifyEmail" => "1", //1=通知，0=不通知 
	);

	print "<pre>";
	print_r($post_data_array);  
	print "</pre>";

	$post_data_str = http_build_query($post_data_array); //轉成字串排列 
	$key = "f2VyML8oSxa82gYhGV9t20CjO51trVWH"; //商店專屬串接金鑰 HashKey 值    
	$iv = "YRgKDp8vWjEYADgh"; //商店專屬串接金鑰 HashIV 值    
	$post_data = trim(bin2hex(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, addpadding($post_data_str), MCRYPT_MODE_CBC, $iv))); //加密 
	$url = "https://cinv.pay2go.com/API/invoice_issue";         
	$MerchantID = "3405394"; //商店代號         
	$transaction_data_array = array( 
		//送出欄位             
	    "MerchantID_" =>$MerchantID,             
	    "PostData_" => $post_data         
	);         

	$transaction_data_str = http_build_query($transaction_data_array);         
	$result = curl_work($url, $transaction_data_str); //背景送出         
	print_r($result);
?>

