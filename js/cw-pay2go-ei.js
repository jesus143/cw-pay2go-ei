var CWP2GEI, self;

var info, bif, flag, bifn;

var data={};

(function($){

	CWP2GEI={

		Init:function(){
			self=this;

			info	=$('#cw-pay2go-ei_billing-need-ubn-info');
			bif		=$('#cw-pay2go-ei_bif');
			flag	=$('#cw-pay2go-ei_billing-invoice-flag');
			bifn	=$('#cw-pay2go-ei_bifn');
			org		=$('#cw-pay2go-ei_org');

			//this.CheckNeedUBN();

			this.DisableFlagNumber();

		}, 



		/*
		CheckNeedUBN:function(){
			var sel;
			var ShowField=function(intValue){
				if(intValue=='0'){
					info.hide();
					bif.show();
					self.CheckOtherField(flag.val());
				}else{
					info.show();
					bif.hide();
					bifn.hide();
					org.hide();
				}
			};
			if($('#cw-pay2go-ei_billing-need-ubn').length>0){
				sel=$('#cw-pay2go-ei_billing-need-ubn');
				ShowField(sel.val());
				sel.change(function(){
					ShowField(sel.val());
				});
			}
		}, 
		CheckOtherField:function(intValue){
			if(intValue=='2'||intValue=='3'||intValue=='-1'){
				bifn.hide();
				if(intValue=='3'){
					org.show();
				}else{
					org.hide();
				}
			}else if(intValue=='0'||intValue=='1'){
				org.hide();
				if(bifn.css('display', 'none')){
					bifn.show();
				}
			}
		}, 
		*/

		DisableFlagNumber:function(){

			var input;

			var DisableField=function(intValue){
				switch(intValue){

					/*
					info: 	請輸入統一編號
					input:	
					bif:		電子發票索取方式
					bifn:		載具編號，手機|自然人憑證
					org:		受贈發票單位
					*/

					case '0':		// 手機條碼
						bifn.show();
						info.hide();
						input.attr('placeHolder', '請輸入手機條碼');
						org.hide();
						break;

					case '1':		// 自然人憑證
						bifn.show();
						info.hide();
						input.attr('placeHolder', '請輸入自然人憑證條碼');
						org.hide();
						break;

					case '3':		// 捐贈發票
						bifn.hide();
						info.hide();
						org.show();
						break;

					case '2':		// 會員載具
					case '-1':	// 索取紙本發票
						bifn.hide();
						info.hide();
						org.hide();
						break;

					case '99':	// 統一編號
						bifn.hide();
						info.show();
						org.hide();
						break;

					default:
				}
			};

			/*
			var SetPlaceHolder=function(intValue){
				self.CheckOtherField(intValue);
				switch(intValue){
					case '0':
						input.attr('placeHolder', '請輸入手機條碼');
						break;
					case '1':
						input.attr('placeHolder', '請輸入自然人憑證條碼');
						break;
					default:
						input.attr('placeHolder', '');
				}
			};
			*/

			if(flag.length>0){

				input=$('#cw-pay2go-ei_billing-invoice-flag-num');

				DisableField(flag.val());
				//SetPlaceHolder(flag.val());

				flag.change(function(){
					DisableField(this.value);
					//SetPlaceHolder(this.value);
				});
			}

		}, 

	};

	$(document).ready(function(){
		CWP2GEI.Init();
	});

})(jQuery);