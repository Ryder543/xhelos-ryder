  $(document).ready(function() { 
   $("#loginAjax").hide();
   $("#registerAjax").hide();
   //$('#xhelosLogin').click(ingresar);
   //$('#xhelosRegister').click(registrar);
   
    $('#login-tab').click(function() {
        $('.tab').removeClass('active');
        $(this).addClass('active');
        $('#register-form').addClass('hidden');
        $('#login-form').removeClass('hidden');
    });

    $('#register-tab').click(function() {
        $('.tab').removeClass('active');
        $(this).addClass('active');
        $('#login-form').addClass('hidden');
        $('#register-form').removeClass('hidden');
    });

	 // Initialize tippy.js for input buttons with class textInput
	 tippy('.textInput', {
        content(reference) {
            return reference.getAttribute('title');
        }
    });

});

function ingresar(){
	
	var name = $('#loginName').val();
	var pass = $('#loginPass').val();
	loginImageStart();
	
	$.ajax({ //Envio hacia el server
		type: "POST",
		url: 'game/lib/ajaxLogin.php',
		cache: false,
		data:{name:name,pass:pass},
		success: function(json_raw){
			var json = json_parse(json_raw);
			if (typeof(json) == 'string') {//Si lo que devuelve JSON son errores de PHP
			 loginMessage('Error en envio');
			}
			else {
				if (json['adquired'] == 'yes') {
					//loginMessage('Accedio');
					//window.location = 'http://www.iasoftgroup.com/xeno/game/view/main.php';
					window.location = 'game/view/main.php';
				}
				else {
					loginMessage('No Accedio');
					loginImageEnd();
				}
			}
		},
		error:function(){
		  loginImageEnd();
		  loginMessage('Error en sl Server');
		}
	});
	
}
function registrar(){
	var name = $('#registerName').val();
	var pass = $('#registerPass').val();
	var mail = $('#registerMail').val();
	registerImageStart();
	
	$.ajax({ //Envio hacia el server
		type: "POST",
		url: 'game/lib/ajaxRegister.php',
		cache: false,
		data:{name:name,pass:pass,mail:mail},
		success: function(json_raw){
			var json = json_parse(json_raw);
			if (typeof(json) == 'string') {//Si lo que devuelve JSON son errores de PHP
				registerMessage('Error en tu navegador - Presione F5 y vuelva a intentarlo');
				registerImageEnd();
				//$tabs.tabs('select', 2);
			}
			else{
				if(json['registered']=='yes'){
				window.location = 'http://www.iasoftgroup.com/xeno/game/view/main.php';
			}
				else{
				  registerMessage(json['error']);
					registerImageEnd();
				}
			}
			
		},
		error:function(){
		  registerImageEnd();
		  registerMessage('Error en el Servidor');
			
		}
	});
	return false;
}
function loginImageStart(){
   $('#loginMessage').hide();  
   $("#loginAjax").show();
}
function loginImageEnd(){
   $("#loginAjax").hide();
}
function loginMessage(texto){
  $('#loginMessage').show().text(texto);
}


function registerImageStart(){
   $('#registerMessage').hide();  
   $("#registerAjax").show();
}
function registerImageEnd(){
   $("#registerAjax").hide();
}
function registerMessage(texto){
  $('#registerMessage').show().text(texto);
}

$.getScript('lib/js/player_view.js');

console.log("JS cargado correctamente");
