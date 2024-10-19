/* @author Remy Sharp
* @url http://remysharp.com/2007/01/25/jquery-tutorial-text-box-hints/
*
* better val() method added by Matt Chisholm, 2009/07/27
* http://glyphobet.net/blog/essay/878
*/

(function ($) {

$.fn.hint = function (blurClass) {
  var PASSCLASS = 'hint_password';
  var TEMPCLASS = 'temp_password';
  if (!blurClass) {
    blurClass = 'blur';
  }

  return this.each(function () {
    // get jQuery version of 'this'
    var $input = $(this),

    // capture the rest of the variable to allow for reuse
      title = $input.attr('title'),
      $form = $(this.form),
      $win = $(window);
	
    function remove() {
	
      if ($input.realval() === title && $input.hasClass(blurClass)) {
        $input.val('').removeClass(blurClass);
      }
    }

    // only apply logic if the element has the attribute
    if (title) {
      // on blur, set value to title attr if text is blank
      $input.blur(function () {
        if (this.value === '') {		
		  //New by Jeeba, if input type = "password" then create a temporal input and hide the original password  
		  if($input.attr('type')=='password'){
		    if('undefined'!=$input.attr('size')){
		      DOM_SIZE=' size='+$input.attr('size')+' ';
		    }
		    if('undefined'!=$input.attr('tabindex')){
          DOM_TABINDEX=' tabindex='+$input.attr('tabindex')+' ';
        }
				$input.hide().after("<input type='text' "+DOM_TABINDEX+" class='"+TEMPCLASS+" textInput'"+DOM_SIZE+" />");
				var $temp = $input.next('input.'+TEMPCLASS);
				$temp.addClass(blurClass).val(title).focus(function(){
					if($(this).hasClass(TEMPCLASS)){
						$(this).val('').remove();
						$input.val('').removeClass(blurClass).show().focus();
					}
				});
			}
		else{
			$input.addClass(blurClass).val(title);
		}	  
      }
		
      }).focus(remove).blur(); // now change all inputs to title

      // clear the pre-defined text when form is submitted
      $form.submit(function(){
	  	remove();
		//@Jeeba: que delete the content of the temporal classes if they are empty
		$('.'+TEMPCLASS).val('');	
	  }
	  );
      $win.unload(function(){
	  	remove();
		//@Jeeba: que delete the content of the temporal classes if they are empty
		$('.'+TEMPCLASS).val('');

	  }
	  
	  ); // handles Firefox's autocomplete
    }
  });
};

$.fn.realval = $.fn.val;

$.fn.val = function (value) {
  var i = $(this);
  if (value === undefined) {
    return (i.realval() === i.attr('title')) ? '' : i.realval();
  } else {
    return i.realval(value);
  }
}

})(jQuery);

