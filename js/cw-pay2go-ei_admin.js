var CWP2GEI, self;
var data={};

(function($){

	CWP2GEI={

		Init:function(){
			self=this;

			if($('.post_to_pay2go.posted').length>0){
				this.DisableClickPosted();
			}

			if($('#cw-pay2go-ei').length===0)return;

			this.DataVisible();
			this.DataSubmit();

			this.Chosen();

		}, 

		DisableClickPosted:function(){
			$('.post_to_pay2go.posted').each(function(){
				this.onclick=function(e){
					e.preventDefault();
				};
			});
		}, 

		Chosen:function(){

			var config={
  	    '.chosen-select'						:{},
				'.chosen-select-deselect'		:{allow_single_deselect:true},
				'.chosen-select-no-single'	:{disable_search_threshold:10},
				'.chosen-select-no-results'	:{no_results_text:'Oops, nothing found!'},
				'.chosen-select-width'			:{width:"95%"}};

			for(var selector in config){
				$(selector).chosen(config[selector]);
			}

		}, 

		DataVisible:function(){

			$('#cw-pay2go-ei').find('> div[data-visible]').each(function(){

				var div						=this, 
						strConnectTo	=this.getAttribute('data-visible'), 
						strValue			=this.getAttribute('data-value');

				var CheckVisible=function(){
					if($(strConnectTo).val()==strValue){
						$(div).show();
					}else{
						$(div).hide();
					}
				};

				if(/^select/.test(strConnectTo)){
					$(strConnectTo).change(function(){
						CheckVisible();
					});
					CheckVisible();
				}

			});
		}, 

		DataSubmit:function(){

			var input=$('#cw-pay2go-ei_submit');

			input.click(function(){
				this.className='active';
				data={action:'DataSubmit'};

				$('#cw-pay2go-ei_submit-result').removeAttr('class');

				$('.cw-pay2go-ei_rows').find('input').each(function(){
					switch(this.type){
						case 'text':
						case 'number':
						case 'hidden':
							data[this.name]=this.value;
							break;

						case 'checkbox':
						case 'radio':
							data[this.name]=false;
							if(this.checked)data[this.name]=true;
							break;
					}

				});

				$('.cw-pay2go-ei_rows').find('select').each(function(){
					data[this.name]=this.value;
				});

				$('ul.chosen-choices').each(function(){

					var div=this.parentNode, 
							sel=div.previousElementSibling;

					data[sel.name]=new Array();

					$(this).find('> li.search-choice').each(function(){
						data[sel.name].push(sel.querySelectorAll('option')[$(this).find('.search-choice-close').attr('data-option-array-index')].value);
					});

				});

				console.log(data);

				self.AjaxPost(function(json){
					$('#cw-pay2go-ei_submit-result').attr('class', json.result);
					input.removeAttr('class');
				});

			});
		}, 

		AjaxPost:function(callback){
			$.ajax({
				type:'POST',
				data:data,
				dataType:'json',
				url:CWP2GEI_vars.ajaxurl,

			}).always(function(response){
				//console.log('always', response);

			}).done(function(response){
				console.log('done', response);

				if(response){
					if(response.errormsg){
						$('#sms-errormsg').text(response.errormsg).css('display', 'block');
					}
				}

				if(typeof callback=='function')callback(response);

			}).fail(function(response, textStatus, errorThrown){
				console.log('fail', response);

			});
		}, 
	};

	$(document).ready(function(){
		CWP2GEI.Init();
	});

})(jQuery);