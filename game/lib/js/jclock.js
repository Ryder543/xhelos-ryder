var iGlobalTime = 0;
$(document).ready(function(){
  addTime = function(){ iGlobalTime++ };
  setInterval(function() {addTime();}, 1000);
});