$(function(){
	if($.isFunction($.fn.selectBoxIt)) {
		$("select").each(function(i){
			var self = $(this);
			var relName = self.attr("data-rel");
			if (relName && relName != '') {
				$("[name=" + relName + "]").attr('data-no-selectBoxIt', 'true');
			};
			var isFF = 'MozAppearance' in document.documentElement.style;
			if (isFF) {
				if (self.children('option[selected]').length > 0) {
					// console.log('has default value');
					// console.log(self.attr('name'));
					// self.val(self.children('option[selected]').val());
					// self.children('option[selected]').selected = true;
					$( 'option[selected]' ).prop( 'selected', 'selected' );
				}
				else {
					// console.log('not has default value');
					self.find("option").first()[0].selected = true;
				}
			};
		});
		$("select:not([data-no-selectBoxIt])").each(function(){
			$(this).selectBoxIt({
				"autoWidth": false
			});
		});
	}
	if($.isFunction($.fn.jqBootstrapValidation)) {
    	$("input,textarea").not("[type=image],[type=submit],[type=file]").jqBootstrapValidation();
	}
	if($.isFunction($.fn.inputmask)) {
		$("input[data-inputmask]").not("[type=image],[type=submit],[type=file]").inputmask().addClass('pinet-input-mask');
	}
	if($.isFunction($.fn.pinet_cascadeSelect)) {
		$("select[data-rel]").each(function(i){
			var self = $(this);
			var relName = self.attr("data-rel");
			$('[name=' + relName + ']').each(function(){
				var rel = $(this);
				// if (rel.children('option[selected]').length < 1) {
				// 	rel.find("option").first()[0].selected = true;
				// }
				// else {
				// 	// ?Firefox bug
				// 	rel.children('option[selected]')[0].selected = true;
				// }
				rel.selectBoxIt({
					"autoWidth": false
				});
			});

			$('[name=' + relName + ']').on("change", function(){
				var rel = $(this);
				var val = parseInt(rel.val());
				if (val > 0) {
					data = {};
					data[relName] = val;
				}
				else {
					data = {};
					data[relName] = -1;
				}
				self.changeValue(data);
				self.trigger("change");
			})
		});

		$.fn.changeValue = function(detail) {
			var self = $(this);
			var selectBox = self.data("selectBox-selectBoxIt");
			self.find('option').remove();

			if (selectBox) {
				var selectRelData 	= {
					"field": self.attr("name"),
					"detail": detail
				};

				$.ajax({
					url: self.attr("url"),
					type: "GET",
					headers: {
						Pinet: "Select"
					},
					dataType: "json",
					data: selectRelData
				}).done(function(data){
					self.find('option').remove();
					// console.log(self.attr('name') );
					// console.log(data);
					// console.log(Object.keys(data).length);
					$.each(data, function(i, option){
							if (i == -1) {
								self.prepend($('<option value=' + i +'>' + option + '</option>'));
							}
							else {
								self.append($('<option value=' + i +'>' + option + '</option>'));
							}
					});
					if (self.children('option[selected]').length < 1) {
						self.find("option").first()[0].selected = true;
					}
					selectBox.refresh();
				});
			};
		}
	}
});
