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

