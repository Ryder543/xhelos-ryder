//console.log(dummy_report)
$(document).ready(function(){
});

function textAdvice(text){
    var textDom = $('<p class="textAdvice">'+text+'</p>');
    $('#Wrapper').append(textDom);
    $(textDom).position( {my:"center center",at:"center center",of:$("#Wrapper")} );
    $(textDom).animate({fontSize:"800%"},1500).effect("explode", 1500); 
}
function damageText(text,unit){
    var textDom = $('<p class="damageText" style="position:absolute">'+text+'</p>');
    $('#Wrapper').append(textDom);
    $(textDom).position( {my:"center center",at:"center center",of:$(unit)} );
    $(textDom).animate({top:'-=50'},2000).fadeOut(1000,function(){$('.damageText').remove()});
}

function eRecover(unit_id, recoveredAmount, movement, callback) {
	var unit = getArmy(unit_id);
	var parent = $(unit).parent();
  
	// Crear y mostrar texto flotante "+X"
	var txt = $("<div class='recover-text'>+" + recoveredAmount + "</div>")
	  .css({
		position: 'absolute',
		left: parent.offset().left + parent.width() / 2,
		top: parent.offset().top - 10,
		color: '#00cc00',
		'font-weight': 'bold',
		'pointer-events': 'none',
		'z-index': 999,
		transform: 'translateX(-50%)'
	  })
	  .appendTo('body');
  
	// Mostrar ícono de recuperación (imagen estática)
	var imgId = 'recovering-icon';
	var img = $('#' + imgId);
  
	if (!img.length) {
	  var img_raw = `<img id="${imgId}" src="img/action/energy.png" alt="recover icon" style="position:absolute; width:32px; height:32px; z-index:998;">`;
	  $('body').append(img_raw);
	  img = $('#' + imgId);
	}
  
	// Posicionar el ícono encima de la unidad
	$(img).show().position({
	  my: "center bottom",
	  at: "center top",
	  of: $(parent)
	});
  
	// Animar el texto y luego limpiar
	txt.animate({ top: '-=30', opacity: 0 }, 1000, function () {
	  txt.remove();
	});
  
	// Ocultar el ícono después de un rato
	img.delay(1500).fadeOut(100, callback);
  }

function eRangeAttack(attacker_id,defender_id,effect,callback){
    //console.log(effect,'effects->eAttack');

    var defender =  getArmy(defender_id);
    //var attacker =  getArmy(attacker_id);
    var defender_parent = $(defender).parent();

    //var attacker_death = effect['movement_raw']['effect']['army'][attacker_id][4];
    var defender_death = effect['movement_raw']['effect']['army'][defender_id][4];

    //damageText(attacker_death,attacker);
    damageText(defender_death,defender);

    var img = $('#attacking_range');
    if ($(img).length){
        img.attr('src',img.attr('src'));
    }
    else{
        var img_raw = "<img id='attacking_range' src='img/action/animated_range.gif' alt='range attack'/>";
        $('body').append(img_raw);
        img = $('#attacking_range');
    }
    $(img).show().position({my:"left top",at:"left top",of:$(defender_parent)}).delay(2500).fadeOut(13,callback);
}

function eAttack(attacker_id,defender_id,effect,callback){
    //console.log(effect,'effects->eAttack');

    var defender =  getArmy(defender_id);
    var attacker =  getArmy(attacker_id);
    var defender_parent = $(defender).parent();

    var attacker_death = effect['movement_raw']['effect']['army'][attacker_id][4];
    var defender_death = effect['movement_raw']['effect']['army'][defender_id][4];

    damageText(attacker_death,attacker);
    damageText(defender_death,defender);
    
    var img = $('#attacking');
    if ($(img).length){
        img.attr('src',img.attr('src'));
    }
    else{
        var img_raw = "<img id='attacking' src='img/action/animated_attack2.gif' alt='attack'/>";
        $('body').append(img_raw);
        img = $('#attacking');
    }
    $(img).show().position({my:"left top",at:"left top",of:$(defender_parent)}).delay(2500).fadeOut(13,callback);
}

function eSpecialAttack(attacker_id,defender_id,effect,callback){
    //console.log(effect,'effects->eSpecialAttack');

    var defender =  getArmy(defender_id);
    var attacker =  getArmy(attacker_id);
    var defender_parent = $(defender).parent();

    // Solo aplicamos daño al defensor, no al atacante
    var defender_death = effect['movement_raw']['effect']['army'][defender_id][4];

    // Solo mostramos el texto de daño para el defensor
    damageText(defender_death,defender);
    
    // Usar un ID diferente para el ataque especial
    var imgId = 'attacking_special';
    var img = $('#' + imgId);
    if ($(img).length){
        img.attr('src',img.attr('src'));
    }
    else{
        // Usar la misma imagen pero con un ID diferente para distinguirlo
        var img_raw = `<img id="${imgId}" src="img/action/energy.png" alt="atack icon" style="position:absolute; width:32px; height:32px; z-index:998;">`;
        $('body').append(img_raw);
        img = $('#' + imgId);
    }
    
    // Añadir un efecto visual adicional para diferenciar del ataque normal
    $(defender_parent).effect("highlight", {color: "#ff0000"}, 1000);
    $(img).show().position({my:"left top",at:"left top",of:$(defender_parent)}).delay(2500).fadeOut(13,callback);
}

// Nuevo poder: Escudo Energético
function eEnergyShield(unit_id, shieldAmount, movement, callback) {
    var unit = getArmy(unit_id);
    var parent = $(unit).parent();
    
    // Crear y mostrar texto flotante "¡ESCUDO!"
    var txt = $("<div class='shield-text'>¡ESCUDO!</div>")
      .css({
        position: 'absolute',
        left: parent.offset().left + parent.width() / 2,
        top: parent.offset().top - 20,
        color: '#0088ff',
        'font-weight': 'bold',
        'font-size': '16px',
        'pointer-events': 'none',
        'z-index': 999,
        transform: 'translateX(-50%)'
      })
      .appendTo('body');
    
    // Mostrar valor del escudo
    var valueTxt = $("<div class='shield-value'>+" + shieldAmount + "</div>")
      .css({
        position: 'absolute',
        left: parent.offset().left + parent.width() / 2,
        top: parent.offset().top,
        color: '#0088ff',
        'font-weight': 'bold',
        'pointer-events': 'none',
        'z-index': 999,
        transform: 'translateX(-50%)'
      })
      .appendTo('body');
    
    // Mostrar ícono de escudo
    var imgId = 'shield-icon';
    var img = $('#' + imgId);
    
    if (!img.length) {
      // Usar una imagen de escudo (puedes cambiarla por una más apropiada)
      var img_raw = `<img id="${imgId}" src="img/action/energy.png" alt="shield icon" style="position:absolute; width:32px; height:32px; z-index:998;">`;
      $('body').append(img_raw);
      img = $('#' + imgId);
    }
    
    // Posicionar el ícono encima de la unidad
    $(img).show().position({
      my: "center bottom",
      at: "center top",
      of: $(parent)
    });
    
    // Añadir efecto de brillo azul alrededor de la unidad
    $(parent).effect("highlight", {color: "#0088ff"}, 2000);
    
    // Crear un efecto de escudo (círculo) alrededor de la unidad
    var shield = $("<div class='energy-shield'></div>")
      .css({
        position: 'absolute',
        left: parent.offset().left + parent.width() / 2 - 30,
        top: parent.offset().top + parent.height() / 2 - 30,
        width: '60px',
        height: '60px',
        'border-radius': '50%',
        'border': '3px solid #0088ff',
        'box-shadow': '0 0 10px #0088ff',
        'pointer-events': 'none',
        'z-index': 997,
        opacity: 0.7
      })
      .appendTo('body');
    
    // Animar el texto y luego limpiar
    txt.animate({ top: '-=20', opacity: 0 }, 1500);
    valueTxt.animate({ top: '+=20', opacity: 0 }, 1500, function () {
      txt.remove();
      valueTxt.remove();
    });
    
    // Animar el escudo (pulsar)
    shield.animate({ width: '70px', height: '70px', left: '-=5px', top: '-=5px', opacity: 0.3 }, 1000)
          .animate({ width: '60px', height: '60px', left: '+=5px', top: '+=5px', opacity: 0.7 }, 1000, function() {
              shield.remove();
          });
    
    // Ocultar el ícono después de un rato
    img.delay(2000).fadeOut(500, callback);
}

function eRay(parent,xIn,yIn,xOut,yOut,callback){
	var idRandom = randomDivId();//ID RAndom para el SVG
	var o = $(parent).offset();
	var anim = 500;
	if(yOut==yIn){
		var top = Math.round(o.top);
		var down = $(parent).height();//40 es el tama�o de cada cell
		var y_animation =0;
		circle_y = 25;
		if (parseInt(xOut) > parseInt(xIn)) {//Movimiento es hacia la derecha
                        
			var left = Math.round(o.left);
			var right = getMapCoord('right') - left;
			var x_animation =anim;
			var circle_x = 25;
                        //console.log('xOut['+xOut+'] > xIn['+xIn+'] derecha ');
			
		}
		else {
			var left = getMapCoord('left');
			var right = (xIn) * $(parent).width();
			dir=-1;
			var x_animation =-anim;
			var circle_x = right;
                       // console.log('xOut['+xOut+'] < xIn['+xIn+'] izquierda ');
		}
                
                //console.log('left['+left+'] xIn['+right+'] ');
		/*var paper = Raphael(left, top, right, top);
		$('svg:last').attr('id', idRandom);	
		var c = paper.circle(circle_x, 25, 10).attr({fill: "#00FFFF",stroke: "#02A6E6","stroke-width": "5"	});
		c.animate({cy:y_animation,cx:x_animation,r: 50,	"stroke-width": "15"}, 2000, "<", function(){eTimeOut(idRandom);})
	*/}
	else{
		var left = Math.round(o.left);
		var right = Math.round($(parent).width());
		var x_animation =0;
		circle_x = 25;
		if(parseInt(yOut)>parseInt(yIn)){//Movimiento es de arriba hacia abajo
			var top = Math.round(o.top);
			var down = Math.round(getMapCoord('down'));
			var circle_y = 25;
			var y_animation = anim;
			
		}
		else{//Movimiento es de  abajo  hacia arriba
			var top = Math.round(getMapCoord('top'));
			var down = (yIn)*$(parent).width();
			var circle_y = down;
			var y_animation = -anim;
		}
	}
	var paper = Raphael(left,top,right,down);
	$('svg:last').attr('id',idRandom);
	var c = paper.circle(circle_x,circle_y,5).attr({fill:"#00FFFF",stroke:"#02A6E6","stroke-width":"20"});
	//paper.rect(1, , 50, 50, 10);
	c.animate({r: 20,"stroke-width":"5"},'1000',function(){secondAnim(c,idRandom,x_animation,y_animation,callback);});
}

function secondAnim(c,idRandom,x_animation,y_animation,callback){
	//c.animate({r:50});
	c.animate({cy: y_animation+25,cx:x_animation+25,"stroke-width":"20",stroke:"#FF0000",fill:"#FFFF00"}, 3000,"<",function(){eTimeOut(idRandom,callback);})
	//r.animate({height: y_animation},3000,"<");
}
function eTimeOut(idRandom,callback){
	$('svg#'+idRandom).remove();
        callback();
}
function getMapCoord(coord){
	
	var o = $('#BattleMap').offset();
	var top = Math.round(o.top);
	var left = Math.round(o.left);
	var right = left+$('#BattleMap').width( );
	var down = right+$('#BattleMap').height( );
	switch(coord)
	{
		case 'left':
		return left;
		break;
		
		case 'right':
		return right;
		break;
		
		case 'top':
		return top;
		break;
		
		case 'down':
		return down;
		break;
	}
		
}
function randomDivId() {
	var randomvar ='id'+parseInt(Math.random()*10000); 
    return randomvar; 
}

