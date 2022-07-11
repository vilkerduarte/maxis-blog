/* ============ Formatando Dados ================ */
var cont = 0;
function number_format($elem){
	var AssimSera = '';
	var format = $($elem).attr('data-format');
	var numb = $elem.value;
	var leg = numb.length;
	var obj = document.getElementById('demo');
	var arr_f = format.match(/[\d\W\w\s]/g);
	var arr_n = numb.match(/\d/g);
	arr_f.forEach(function( i, k){
		if(i == 'd' && cont <= leg){
			if(arr_n[cont]){
				AssimSera += arr_n[cont];
				cont = cont+1;
			}
		}else{
			if(arr_n[cont]){
				AssimSera += i;
			}
		}
		$elem.value = AssimSera;
	});
	cont=0;

}
var cont = 0;
function set_format($elem,$format){
		if($format == 'number'){
		$Ptt = /\d/g;
	}else if($format == 'alpha'){
		$Ptt = /[A-z]/g;
	}else{
		$Ptt = /\w/g;
	}

	var AssimSera = '';
	var format = $($elem).attr('data-format');;
	var numb = $elem.value;
	var leg = numb.length;
	var arr_f = format.match(/[\d\W\w\s]/g);
	var arr_n = numb.match($Ptt);
	arr_f.forEach(function( i, k){
		if(i == 'd' && cont <= leg){
			if(arr_n[cont]){
				AssimSera += arr_n[cont];
				cont = cont+1;
			}
		}else{
			if(arr_n[cont]){
				AssimSera += i;
			}
		}
		$elem.value = AssimSera;
	});
	cont=0;
}
function cpf_cnpj($elem){
	var AssimSera = '';
	var format = $elem.value.length > 14 ? 'dd.ddd.ddd/dddd-dd' : 'ddd.ddd.ddd-dd';
	var numb = $elem.value;
	var leg = numb.length;
	var obj = document.getElementById('demo');
	var arr_f = format.match(/[\d\W\w\s]/g);
	var arr_n = numb.match(/\d/g);
	arr_f.forEach(function( i, k){
		if(i == 'd' && cont <= leg){
			if(arr_n[cont]){
				AssimSera += arr_n[cont];
				cont = cont+1;
			}
		}else{
			if(arr_n[cont]){
				AssimSera += i;
			}
		}
		$elem.value = AssimSera;
	});
	cont=0;

}
var MODAL_OPEN = false;
var PAGE_MODAL_OPEN = false;
var CONFIRM_MODAL_OPEN = false;
function close_modal(){
	MODAL_OPEN = false;
	CONFIRM_MODAL_OPEN = false;
	$('div.frame-modal').removeClass('bounceInDown');
	$('div.frame-modal').addClass('bounceOutUp');
	$('div.fundo-modal').fadeOut(1500);
	setTimeout(function (){
		$('div.fundo-modal').addClass('force-hide');
		$('div.frame-modal').removeClass('bounceOutUp');
	},1500);
}
function close_page_modal(){
	PAGE_MODAL_OPEN = false;
	$('div.frame-page-modal').removeClass('bounceInDown');
	$('div.frame-page-modal').addClass('bounceOutUp');
	$('div.fundo-page-modal').fadeOut(1500);
	setTimeout(function (){
		$('div.fundo-page-modal').addClass('force-hide');
		$('div.frame-page-modal').removeClass('bounceOutUp');
	},1500);
}
$('a.page-scroll').bind('click', function(event) {
	var $anchor = $(this);
	$('html, body').stop().animate({
		scrollTop: $($anchor.attr('href')).offset().top
	}, 1500, 'easeInOutExpo');
	event.preventDefault();
});
$(window).scroll(function () {
  if ($(this).scrollTop() > 250) {
	  $('#back-top').fadeIn();
  } else {
	  $('#back-top').fadeOut();
  }
});

function modal($title,$text){
	$('#title-modal').html($title);
	$('#text-modal').html($text);
	$('div.fundo-modal').hide();
	$('div.fundo-modal').removeClass('force-hide');
	$('div.fundo-modal').fadeIn();
	$('div.frame-modal').addClass('bounceInDown');
	MODAL_OPEN = true;
	setTimeout(function (){
		close_modal();
	},20000);

}
function page_modal($link,$GET){
	if($link[0] == '/'){
		$link = $link.split('');
		$link[0] = '';
		$link = $link.join('');
	}
	$.post('/modal',{"page":$link,"GET":$GET},function($info){
		$('#content-page-modal').html($info);
		$('div.fundo-page-modal').hide();
		$('div.fundo-page-modal').removeClass('force-hide');
		$('div.fundo-page-modal').fadeIn();
		$('div.frame-page-modal').addClass('bounceInDown');
	});
	PAGE_MODAL_OPEN = true;
}
function modal_direct($link,$GET){
	$.get('/modal',{"page":$link,"GET":$GET},function($info){
		$('#content-page-modal').html($info);
	});
}
function confirm_modal($href){
	CONFIRM_MODAL_OPEN = true;
	modal("Tem certeza disso?",'Tem certeza que de deseja fazer isso?<br>Talvez essa operação não possa ser desfeita.<br><br><a href="' + $href +'"><button class="new_btn__">Sim</button></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a onClick="close_modal()" class="link_hover">Não</a>');

		$(document).keypress(function(e) {
            if(e.keyCode == 13 && CONFIRM_MODAL_OPEN){
				window.location.href = $href;
			}
        });
		return false;
}
function location_time($LINK){
	setTimeout(function(){
		window.location.href = $LINK;
	},3000)
}
function fixed__($elem){
	var x = parseFloat($elem.value);
	$elem.value = x.toFixed(2);
}
function location__($link){
	window.location.href = $link;
}
$(document).ready(function(e) {
    $('.fixed__js').change(function(){fixed__(this);});
	$(document).keyup(function(e) {
        if(e.keyCode == 27){
			if(MODAL_OPEN || CONFIRM_MODAL_OPEN){
				close_modal();
			}else if(PAGE_MODAL_OPEN){
				close_page_modal();
			}
		}
    });
});


// Atualização do dia 14/12/2021
// ====================================

$(document)[0].onclick = function(e){
	$('#menuRightClick').remove();
}

function strToCapitalize(str){
	str = str.replace(/[ ]{2,}/g,' ');
	str = str.trim();
	var ex = str.split(' ');
	for(var x=0;x<ex.length;x++){
		ex[x] = ex[x].toLowerCase();
		var ex2 = ex[x].split('');
		ex2[0] = ex2[0].toUpperCase();
		ex[x] = ex2.join('');
	}
	return ex.join(' ');
}


function ElementMaker(query,config){
	var attributtes = {
		"element":false,
		"class":[],
		"id":false,
		"extraConfigs":false
	}
	if(config !== undefined){
		attributtes.extraConfigs = config;
	}
	query = query.replace(/[ ]{1,}/g,'');
	var arr = query.match(/((.|#)[a-zA-Z\-\_0-9]{1,})/g);
	var elemento;
	var alpha = 'abcdefghijklmnopqrstuvwxyz';
	alpha += alpha.toUpperCase();
	alpha = alpha.split('');

	for(var x in arr){
		if(arr[x][0] == '.'){
			elemento.className += ' '+arr[x].replace('.','');
		}else if(arr[x][0] === '#'){
			elemento.id = arr[x].replace('#','');
		}else if(alpha.includes(arr[x][0])){
			elemento = document.createElement(arr[x]);
		}
	}
	if(attributtes.extraConfigs.childs !== undefined){
		for(var x in attributtes.extraConfigs.childs){
			elemento.appendChild(attributtes.extraConfigs.childs[x]);
		}
	}
	return elemento;
}

class MenuClickRight{
  constructor(elems){
    this.menu = document.createElement('div');
    this.menu.id = 'menuRightClick';
    var ul = document.createElement('ul');
    for(var x=0;x<elems.length;x++){
      ul.appendChild(elems[x]);
    }
    this.menu.appendChild(ul);
  }
  open(e,el){
		for (var i in this.menu.dataset) {
			delete this.menu.dataset[i];
		}
		for (var i in el.dataset) {
			this.menu.dataset[i] = el.dataset[i];
		}
    if(document.querySelector('#menuRightClick') !== null){
      document.querySelector('#menuRightClick').remove();
    }
    this.menu.style.top = e.pageY+'px';
		var larguraTotal = parseInt(e.pageX) + 320;

		if(larguraTotal > document.body.clientWidth){
			this.menu.style.left = (e.pageX - 200)+'px';
		}else{
			this.menu.style.left = e.pageX+'px';
		}

    document.body.appendChild(this.menu);
  }
  static createItem(txt,link,action){
    var a = document.createElement('a');
    a.href = link;
    if(action !== undefined){
      a.onclick = function(){
				var dados = document.querySelector('#menuRightClick').dataset;
        action(dados);
        return false;
      };
    }
    var li = document.createElement('li');
    li.innerText = txt;
    a.appendChild(li);
    return a;
  }
  static createGroup(txt,childs){
    var li = document.createElement('li');
    li.innerText = txt;
    var ul = document.createElement('ul');
    for(var x=0;x<childs.length;x++){
      ul.appendChild(childs[x]);
    }
    li.appendChild(ul);
    return li;
  }
}
