<?php
$CWP2GEI=new CWP2GEI();
$stdOption=$CWP2GEI->option;
?>

	<div id="cw-pay2go-ei">
		<h1>智付寶電子發票 by cloudwp</h1>
		<div class="cw-pay2go-ei_rows">
			<p class="cw-pay2go-ei_rows-checkbox">
				<span>啟用</span>
			</p>
			<div>
				<label>
					<?php $strChecked=$stdOption->enable==='true'?' checked="checked"':'';?>
					<input type="checkbox" name="cw-pay2go-ei_enable"<?php echo $strChecked;?> />
					<span>啟用智付寶電子發票</span>
				</label>
			</div>
		</div>

		<div class="cw-pay2go-ei_rows">
			<p class="cw-pay2go-ei_rows-checkbox">
				<!--<span>測試模式</span>-->
				<label for="cw-pay2go-ei_sandbox">測試模式</label>
			</p>
			<div>
				<label>
					<?php $strChecked=$stdOption->sandbox==='true'?' checked="checked"':'';?>
					<input type="checkbox" id="cw-pay2go-ei_sandbox" name="cw-pay2go-ei_sandbox"<?php echo $strChecked;?> />
					<span>啟用測試模式</span>
				</label>
			</div>
		</div>

		<div class="cw-pay2go-ei_rows">
			<p class="cw-pay2go-ei_rows-text">
				<span>Merchant ID</span>
			</p>
			<div>
				<label>
					<?php $strMerchantID=isset($stdOption->{'metchant-id'})?$stdOption->{'metchant-id'}:NULL;?>
					<input type="text" name="cw-pay2go-ei_metchant-id" value="<?php echo $strMerchantID;?>" />
					<span>請輸入智付寶提供的商店代號</span>
				</label>
			</div>
		</div>

		<div class="cw-pay2go-ei_rows">
			<p class="cw-pay2go-ei_rows-text">
				<span>Hash Key</span>
			</p>
			<div>
				<label>
					<?php $strHashKey=isset($stdOption->{'hash-key'})?$stdOption->{'hash-key'}:NULL;?>
					<input type="text" name="cw-pay2go-ei_hash-key" value="<?php echo $strHashKey;?>" />
					<span>請輸入智付寶提供的 HashKey</span>
				</label>
			</div>
		</div>

		<div class="cw-pay2go-ei_rows">
			<p class="cw-pay2go-ei_rows-text">
				<span>Hash IV</span>
			</p>
			<div>
				<label>
					<?php $strHashIV=isset($stdOption->{'hash-iv'})?$stdOption->{'hash-iv'}:NULL;?>
					<input type="text" name="cw-pay2go-ei_hash-iv" value="<?php echo $strHashIV;?>" />
					<span>請輸入智付寶提供的 HashIV</span>
				</label>
			</div>
		</div>

		<div class="cw-pay2go-ei_rows">
			<p class="cw-pay2go-ei_rows-select">
				<span>開立發票時機</span>
			</p>
			<div>
				<?php
				$arrOptions=array(
					'0'		=>'訂單處理中', 
					'100'	=>'訂單完成後', 
					'1'		=>'訂單成立時', 
					'99'	=>'手動', 
					'3'		=>'預約天數');
				?>
				<select name="cw-pay2go-ei_status">
					<?php
					foreach($arrOptions as $key=>$value):
						$strSelected='';
						if($key==$stdOption->status)$strSelected=' selected="selected"';
					?>
					<option value="<?php echo $key;?>"<?php echo $strSelected;?>><?php echo $value;?></option>
					<?php
					endforeach;
					?>
				</select>
			</div>
		</div>

		<div class="cw-pay2go-ei_rows" data-visible="select[name=cw-pay2go-ei_status]" data-value="3">
			<p class="cw-pay2go-ei_rows-number">
				<span>預約天數</span>
			</p>
			<div>
				<label>
					<?php $intCreateStatusTime=isset($stdOption->{'create-status-time'})?$stdOption->{'create-status-time'}:1;?>
					<input type="number" name="cw-pay2go-ei_create-status-time" value="<?php echo $intCreateStatusTime;?>" />
					<span>單位 - 天</span>
				</label>
			</div>
		</div>

		<div class="cw-pay2go-ei_rows">
			<p class="cw-pay2go-ei_rows-select">
				<span>稅別</span>
			</p>
			<div>
				<?php
				$arrOptions=array(
					'1'		=>'應稅 ( 5% )', 
					'1.1'	=>'應稅 ( 0% )', 
					'2'		=>'零稅率', 
					'3'		=>'免稅');
				?>
				<select name="cw-pay2go-ei_taxtype">
					<?php
					foreach($arrOptions as $key=>$value):
						$strSelected='';
						if($key==$stdOption->taxtype)$strSelected=' selected="selected"';
					?>
					<option value="<?php echo $key;?>"<?php echo $strSelected;?>><?php echo $value;?></option>
					<?php
					endforeach;
					?>
				</select>
			</div>
		</div>

		<div class="cw-pay2go-ei_rows">
			<p class="cw-pay2go-ei_rows-select">
				<span>載具</span>
			</p>
			<div>
				<?php
				$arrOptions=$CWP2GEI->GetFlags();
				?>
				<select name="cw-pay2go-ei_flag" data-placeholder="請選擇載具" class="chosen-select" multiple tabindex="4" style="width:500px;">
					<?php
					foreach($arrOptions as $key=>$value):
						if(is_array($stdOption->flag)){
							$strSelected='';

							foreach($stdOption->flag as $xvalue){
								if($xvalue==$key){
									$strSelected=' selected="selected"';
									break;
								}
							}
						}
					?>
					<option value="<?php echo $key;?>"<?php echo $strSelected;?>><?php echo $value;?></option>
					<?php
					endforeach;
					?>
				</select>
			</div>
		</div>

		<div class="cw-pay2go-ei_rows">
			<p class="cw-pay2go-ei_rows-select">
				<span>發票捐贈機構</span>
			</p>
			<div>

				<?php
				$arrOptions=apply_filters('get_love_code', NULL);
				?>

				<select name="cw-pay2go-ei_organization" data-placeholder="請選擇捐贈機構" class="chosen-select" multiple tabindex="4" style="width:500px;">
					<?php
					foreach($arrOptions as $key=>$value):
						if(is_array($stdOption->organization)){
							$strSelected='';

							foreach($stdOption->organization as $xvalue){
								if($xvalue==$key){
									$strSelected=' selected="selected"';
									break;
								}
							}
						}
					?>
					<option value="<?php echo $key;?>"<?php echo $strSelected;?>><?php echo $value;?></option>
					<?php
					endforeach;
					?>
				</select>
			</div>
		</div>

		<div class="cw-pay2go-ei_rows">
			<div>
				<input type="button" id="cw-pay2go-ei_submit" name="cw-pay2go-ei_submit" value="送出" />
				<img src="<?php echo CWP2GEI_URL;?>/images/ajax-loader.gif" />
				<span id="cw-pay2go-ei_submit-result"></span>
			</div>
		</div>

	</div>