var Settings = {
	iframe: {
		height: "450px",
		animateDownTime: 1000,   //ms
		animateUpTime: 1000
	}
};

var measure_string = function(str, font){
	var canvas = document.createElement('canvas');
	var ctx = canvas.getContext("2d");
	ctx.font = font;
	return ctx.measureText(str).width;
}

var calc_length = function(str, font, w, emitter) {
	if(measure_string(str, font) > w) {
		var ic = Math.floor(w / measure_string('i'));
		var mc = Math.floor(w / measure_string('m'));
		for(var i = ic; i > 0; i--) {
			var s = str.substring(0, i) + emitter;
			if(measure_string(s, font) < w) {
				return i;
			}
		}
	}
	else {
		return str.length;
	}
}

var get_font = function(e) {
    var fs = $(e).css('font-style');
    var fv = $(e).css('font-variant');
    var fz = $(e).css('font-size');
    var ff = $(e).css('font-family');
    return [fs, fv, fz, ff].join(' ');
}

$.fn.emit = function(){
	$(this).each(function(){
		var font = get_font(this);
		var pl = $(this).css('padding-left').replace('px', '');
		var pr = $(this).css('padding-right').replace('px', '');
		var w = $(this).width();
        if($(this).children("a").length) {
            $(this).children("a").emit();
        }
        else {
            if(measure_string($(this).text(), font) < w) {
                return;
            }
            var emitter = ' ... ';
            var c = calc_length($(this).text(), font, w - pl - pr, emitter);
            var str = $(this).text();
            if(str.length > 0) {
                $(this).attr('title', str);
                $(this).text(str.substring(0, c) + emitter);
            }
        }
	});
}

if ($.isFunction($.fn.emit)) {
	$('#datatable tbody td').emit();
}

function getIframeState() {
	var key = 'datatable_iframe_states_';
	key += window.location.pathname;
	return JSON.parse(localStorage.getItem(key));
}

function saveIframeState(state) {
	var key = 'datatable_iframe_states_';
	key += window.location.pathname;
	localStorage.setItem(key, JSON.stringify(state));
}

var iframe_containers = [];
iframe_containers.state = {};
if(getIframeState()){
	iframe_containers.state = getIframeState();
}

function datatable_action_column(data, type, row, meta) {
	var actionUrl = datatable_conf.columns[meta.col].action;
	var refer = datatable_conf.columns[meta.col].refer;
	if(refer) {
		if(actionUrl)
			return "<a data-id='" + row[refer] + "' href='"+ actionUrl + "/" + row[refer] +"'>"+ data +"</a>"
		else
			return "<a class='no_text_decoration' data-id='" + row[refer] + "'>"+ data +"</a>"
	}
	return "<a href='"+ actionUrl + "/" + data +"'>"+ data +"</a>"
}

function datatable_toggle_column(data, type, row, meta) {
	var actionUrl = datatable_conf.columns[meta.col].action;
	var refer = datatable_conf.columns[meta.col].refer;
	if(refer) {
		return "<a class='btn btn-default datatable-toggle' data-id='"+data+"' href='"+ actionUrl + "/" + row[refer] +"?nohead=true'>"+ data +"</a>"
	}
	else
		return "<a class='btn btn-default datatable-toggle' data-id='"+data+"' href='"+ actionUrl + "/" + data +"?nohead=true'><i class='glyphicon glyphicon-chevron-down'></i></a>"
}

// Begin the Datatable initialise script
$('#datatable').on('click', '.datatable-toggle', function(e){
	e.preventDefault();

	var tr = $(this).parentsUntil('tr').parent();
	var td_length = tr.children('td').length;
	var tbody = tr.parent();
	var index = $(this).attr('data-id');
	var iframe;
	var iframe_td;
	var iframe_container;

	iframe_container = iframe_containers[index];
	if(iframe_container) {
		iframe = iframe_container.iframe;
	}

	if($(this).hasClass('datatable-toggled')) {
		$(this).html("<i class='glyphicon glyphicon-chevron-down'></i>");
		$(this).removeClass('datatable-toggled');
		var state = new Object();
		state.opened = false;
		iframe_containers.state["iframe"+index] = state;
		saveIframeState(iframe_containers.state);
		iframe.animate({
			"height":"0px",
			"display":"none"
		}, Settings.iframe.animateUpTime, function(){
			iframe_container.css({
				// "visibility": "collapse"
				"display": "none"
			});
		});
	}
	else {
		$(this).html("<i class='glyphicon glyphicon-chevron-up'></i>");
		$(this).addClass('datatable-toggled');

		var is_iframe_initialised = tbody.find('tr#datatable-iframe-container-'+index).length;
		if(!is_iframe_initialised) {
			iframe_container = $('<tr></tr>');
			iframe_container.attr('id','datatable-iframe-container-'+index);
			iframe_container.attr('class','datatable-iframe-container');
			iframe_td = $('<td></td>')
			iframe_td.attr('colspan',td_length);
			iframe = $('<iframe/>').attr('src', $(this).attr('href')).addClass('datatable-toggle-frame');
			iframe_div = $('<div/>');

			iframe_td.append(iframe);
			iframe_container.append(iframe_td);
			tr.after(iframe_container);
			// iframe_container.hide();
			iframe.on('load', function(){
				iframe_container.show();
				var doc = iframe.contents();
				doc.find('body').attr('trid', 'datatable-iframe-container-'+index);
				doc.find('body').addClass('iframe');

				$(this).trigger('iframe.ready', [iframe, doc]);

				iframe.animate({
					"height":Settings.iframe.height,
					"display":"block"
				}, Settings.iframe.animateDownTime, function(){
					iframe_container.css({
						// "visibility": "visible"
						"display": "table-row"
					});
				});
			});

			iframe_container.iframe = iframe;
			iframe_containers[index] = iframe_container;
		}else {
			console.dir(Settings.iframe.animateUpTime);
			iframe_container.css({
				// "visibility": "visible"
				"display": "table-row"
			});
			iframe.animate({
				"height":Settings.iframe.height,
				"display":"block"
			},Settings.iframe.animateUpTime, function(){
				iframe_container.css({
					// "visibility": "visible"
					// "display": "table-row"
				});
			});
		}
		var state = new Object();
		state.opened = true;
		iframe_containers.state["iframe"+index] = state;
		saveIframeState(iframe_containers.state);
	}
	return false;
});
$('#datatable').on('click', 'tr', function(){
	$(this).addClass('ui-selected').siblings().removeClass('ui-selected');
});