jQuery(function(){
	if ($.isFunction($.fn.tooltip)) {
		$('.pinet-toolbar .faq').tooltip({
		    viewport: {
		      selector: '#layout-workbench',
		      padding: 0
		    }
	  	});
	};

	//For toolbar datatable search
	var toolbar_search_init = function(options) {
		var defaults = {
			search_selector: "#field_search",
			btn_selector: "[control='datatable-search']",
			refresh_btn_selector: "[control='datatable-refresh']"
		}

		var op = $.extend(true, defaults, options);

		$(".datatable").each(function(i){
			var datatable = window.pinet_datatables[i].api;
			var settings = datatable.settings();
			var element = window.pinet_datatables[i].el;

			var search_input_value = "";
			if (settings[0] && settings[0].oPreviousSearch) {
				search_input_value = settings[0].oPreviousSearch.sSearch;
			};


			if ($(op.search_selector).length > 0) {
			var search_input = $(op.search_selector);
				search_input.val(search_input_value);
			};

			datatable.off('draw.dt').on('draw.dt', function(){
				$('#datatable tbody td').emit();
				// datatable.column(0).nodes().each(function(cell, i){
				//   cell.innerHTML = i + 1;
				// })
			});

			datatable.off('init.dt').on('init.dt', function(){
				// console.log('init.dt');
			});

			$(op.btn_selector).on('click',function(){
				datatable.search(search_input.val()).draw();
			});

			$(op.refresh_btn_selector).on('click', function(){
				datatable.draw();
			});
		});
	}

	toolbar_search_init();
})