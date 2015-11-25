
KindEditor.plugin('selectPro', function(K) {
	var self = this, name = 'selectPro';
	self.plugin.selectPro = {
		edit : function() {
			var lang = self.lang(name + '.'),
				html = '<div style="padding:20px;">' +
					//url
					'<div class="ke-dialog-row">' +
					'<label for="proType_1" style="width:40px;">' + lang.proType_1 +'</label>' + 
					'<input class="ke-input-text" type="radio" id="proType_1" name="proType" value="0" checked/>' + 
					'&nbsp;&nbsp;<label for="proType_2" style="width:40px;">' + lang.proType_2 +'</label>' + 
					'<input class="ke-input-text" type="radio" id="proType_2" name="proType" value="1" />' + 
					'<div class="ke-dialog-row">' +
					'<label for="proName" style="width:60px;">' + lang.proName + '</label>' + 
					'<input class="ke-input-text" type="text" id="proName" name="proName" value="" style="width:260px;" /><span class="ke-button-common ke-button-outer ke-dialog-no" title="'+lang.proSearch+'"><input class="ke-button-common ke-button" type="button" name="proSearch" value="'+lang.proSearch+'"></span></div>' +
					'</div>' +
					'<div id="queryRes" style="height:250px;"></div></div>',
				dialog = self.createDialog({
					name : name,
					width : 450,
					title : self.lang(name),
					body : html,
					yesBtn : {
						name : self.lang('yes'),
						click : function(e) {
							var selectedPro = $("input:radio[name=proId]:checked").val();

							if (selectedPro <= 0) {
								alert(lang.notSelected);
							};

							self.exec('inserthtml', '{hasPro:'+selectedPro+'}<br>').hideDialog().focus();
						}
					}
				}),
				div = dialog.div;
				var getProBtn = K('[name=proSearch]', div),
					proName = K('[name=proName]', div);

				getProBtn.click(function(e){
					var proType = $('input:radio[name=proType]:checked').val();

					if (!proName.val()) {
						alert(lang.emptyProName);return false;
					};

					$.ajax({
						type:"POST",
						url:getProUrl,
						data:{type:proType, name:proName.val()},
						beforeSend:function(XMLHttpRequest){

						},
						success:function(data){
							var dataJson = JSON.parse(data);
							if (dataJson.status == '0') {
								$('#queryRes').html(dataJson.html);
							}else{
								alert(dataJson.msg);
							}
						},complete:function(XMLHttpRequest){
						}
					});
				});
		},
		'delete' : function() {
			self.exec('unlink', null);
		}
	};
	self.clickToolbar(name, self.plugin.selectPro.edit);
});
