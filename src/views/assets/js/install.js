jQuery.fn.shake = function(interval,distance,times){
   interval = typeof interval == "undefined" ? 100 : interval;
   distance = typeof distance == "undefined" ? 10 : distance;
   times = typeof times == "undefined" ? 3 : times;
   var jTarget = $(this);
   jTarget.css('position','relative');
   for(var iter=0;iter<(times+1);iter++){
      jTarget.animate({ left: ((iter%2==0 ? distance : distance*-1))}, interval);
   }
   return jTarget.animate({ left: 0},interval);
}

var errors = {
  "pw_length" : "Your password is too long, please keep it less than 50 characters."
};

$(function() {
  $("#flow-db").submit(function(e) {
    e.preventDefault();
    var data = $(this).serializeArray();
    $.ajax({url: "", method: "POST", data: data })
    .done(function(d) {
      $("#flow-db, #flow-site").animate({left:"-100%"});
    }).fail(function(d) {
      $("#flow-db").shake();
      d = d.responseJSON;
      if(d.error === "version") {
        $("#flow-db-error-ver").html(d.minver);
        $("#flow-db-error").fadeIn();
      }
    });
  });
  
  $("#flow-site").submit(function(e) {
    e.preventDefault();
    var data = $(this).serializeArray();
    $.ajax({url: "", method: "POST", data : data })
    .done(function(d) {
      $("#flow-success").fadeIn();
      setTimeout(function() {
        document.location = "/admin";
      }, 4000);
    }).fail(function(d) {
      $(".ns-error").html(errors[d.responseJSON.error]);
      $("#flow-site").shake();
      $(".ns-error").fadeIn();
      setTimeout(() => $(".ns-error").fadeOut(), 5000);
    });
  })
});