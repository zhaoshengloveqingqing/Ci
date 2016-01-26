jQuery(function(){

	$('nav.navigations').on('mouseenter','a',function(){
		var self = $(this);
		var picture = $(this).find('picture');
		if(picture.length > 0){
			var img = picture.find('img');
			var imageName = picture.attr('src').replace('.png','');
			var imagePath = img.attr('src').replace(imageName, imageName + '-hover');
			if(!self.hasClass('active')) {
		  		img.attr('src', imagePath);
			}
		}
	});

	$('nav.navigations').on('mouseleave','a',function(){
		var self = $(this);
		var picture = $(this).find('picture');
		if(picture.length > 0){
			var img = picture.find('img');
			var imagePath = img.attr('src').replace('-hover', '');
			if(!self.hasClass('active')) {
			  	img.attr('src', imagePath);
			}
		}
	});

	$('nav.navigations').on('click', 'a', function(e){
		e.preventDefault();
		var self = $(e.currentTarget);
		var href = self.attr('href');

		if($("#datatable").length > 0) {
			var datatable = $('#datatable').DataTable();
			datatable.search("");
			datatable.state.save();
		}

		setTimeout(function(){
			window.location.href = href;
		}, 300);
	});

})