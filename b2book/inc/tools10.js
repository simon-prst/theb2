$(document).ready(function() {
	
	$('#dailyButton').click(function(e){
		$("#content").fadeOut(500, function() {
			window.location.replace("?do=daily");
		});
	});

	$("body").on("click", ".headerlinked", function(e){
		var rel = $(this).attr('rel');
		$('#headerform').load('tpl/'+rel+'.html', function() {
			$('#headerform').slideDown("slow");
		});
	});

	$(".awesometer_top").mouseenter(function(e){
		if($(this).prev().attr('perso') == -1){
			var logContent = 'notez ce post !';
		}else{
			var noteperso = Math.round($(this).prev().attr('perso')*10/65);
			var logContent = 'ta note actuelle : '+noteperso+'/10';
		}
		$(this).parent().next().show().html(logContent);
  	});

  	$(".awesometer_top").mousemove(function(e){
		var offset = $(this).prev().offset();
		$(this).prev().children().css('width', Math.min(e.pageX-offset.left+1, 65));
  	});

  	$(".awesometer_top").mouseout(function(e){
		var awesomeness = $(this).prev().attr('global');
		$(this).prev().children().css('width', awesomeness);
		$(this).parent().next().hide();
  	});

});

// Personnal library
// -----------------

function getComment(link)
{
	var hash = link.attr("rel");
	
	link.append('<div class="comment"><span>Chargement ...</span></div>');
	
	var data = ajax('comments.php?hash='+hash);
	data = data.split("<who>");
	var who = data[1];
	data = data[0].split("<com>");
	return [who, data];
}

function showComment(lien)
{
	var link = lien.parents(".b2post");
	var comments = getComment(link);
	var commentsdiv = '';
	$.each(comments[1], function(count, item) {
		var bob = item.split('<info>');
		commentsdiv  += '<div class="comment';
		if(comments[0] == bob[0]) commentsdiv  += ' deletable';
		commentsdiv  += '"><div class="commentButton" rel="' + bob[2] + '"></div><span>' + bob[0] + ' </span>' + decode(bob[1]) + '</div>';
	});
	hideComment(lien);
	link.append(commentsdiv);
	if(comments[0]!='') link.append('<div class="comment"><textarea placeholder="Write a comment ..."></textarea><div class="hiddendiv"></div></div>');
	
	bindComments();
	lien.attr('onclick', 'hideComment($(this));');
}


function hideComment(lien)
{
	var comments = lien.parents(".b2post").children('.comment');
	comments.remove();
	lien.attr('onclick', 'showComment($(this));');
}

function bindComments()
{
	$('.comment textarea').on('keypress', function(e) {
		if(e.keyCode == 13){
			var link = $(this).parents(".b2post");
			var tag = $(".comcount", link);
			var hash = link.attr('rel');
			var data = 'comment='+encode($(this).val())+'&hash='+hash;
			var res = ajax('comments.php', data);
			showComment(tag.parent());
			tag.html(1+parseInt(tag.html()));
		}else{
			content = $(this).val();
			content = content.replace(/\n/g, '<br>');
			$('.comment textarea ~ div').html(content);
			$(this).css('height', $('.comment textarea ~ div').height());
		}
	});

	$('.commentButton').on('click', function() {
		var link = $(this).parents(".b2post");
		var tag = $(".comcount", link);
		var hash = link.attr('rel');
		var id = $(this).attr('rel');
		var data = 'id=' + id + '&hash=' + hash;
		var res = ajax('comments.php', data);
		showComment(tag.parent());
		tag.html(parseInt(tag.html())-1);
	});

	$('.deletable').hover(
		function() { $(this).children('.commentButton').show(); },
		function() { $(this).children('.commentButton').hide(); }
	);
}

function bookmark(lien)
{
	var hash = lien.parents(".b2post").attr("rel");
	var data = ajax('comments.php', 'bookmark='+hash);
	lien.css("background-image", "url('./images/bookmark2.png')");
	lien.attr("onclick", "unbookmark($(this))");
	lien.attr("title", "Un-bookmark this post");
}

function unbookmark(lien)
{
	var hash = lien.parents(".b2post").attr("rel");
	var data = ajax('comments.php', 'unbookmark='+hash);
	lien.css("background-image", "url('./images/bookmark.png')");
	lien.attr("onclick", "bookmark($(this))");
	lien.attr("title", "Bookmark this post");
}


function encode(ch) {
	ch = ch.replace(/\+/g, "@@plus@@");
	ch = ch.replace(/&/g, "@@etcom@@");
	return ch
}

function decode(ch) {
	ch = ch.replace(/@@plus@@/g, "+");
	ch = ch.replace(/@@etcom@@/g, "&");
	return ch
}

function like(what){
	var howmuch = $("div", "#star"+what).width();
	var reponse = ajax('comments.php', 'awesome='+what+'&howmuch='+howmuch);
	if (reponse == 'autolike') {
		$("#log"+what).html('désolé, pas d\'autolike !');
	}else{
		var am_infos = reponse.split('##');
		$("#awesomecount"+what).attr('title', 'Vote de '+am_infos[1]+' personne(s)');
		$("#awesomecount"+what).html(am_infos[1]);
		$("#star"+what).attr('global', am_infos[0]);
		$("#star"+what).attr('perso', howmuch);
		$("#log"+what).html('ta note actuelle : '+Math.round(howmuch*10/65)+'/10');
	}
}

function confirmDeleteLink() {
	var agree=confirm("Are you sure you want to delete this link ?"); if (agree) return true ; else return false ;
}

function ajax(file, data){
	var xhr_object = null;
	
	if(window.XMLHttpRequest){ 			// Firefox
		xhr_object = new XMLHttpRequest();
	}else if(window.ActiveXObject){		// Internet Explorer
		xhr_object = new ActiveXObject("Microsoft.XMLHTTP");
	}else{ 								// XMLHttpRequest non supporté par le navigateur
	   return "XMLHttpRequest non supportée";
	}
	
	if(file.indexOf('?') == -1){
		file += '?' + (new Date()).getTime();
	}else{
		file += '&' + (new Date()).getTime();
	}
	xhr_object.open('POST', file, false);
	if(data){
		xhr_object.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		xhr_object.send(data);
	}else{
		xhr_object.send(null);
	}
	return xhr_object.responseText;
}