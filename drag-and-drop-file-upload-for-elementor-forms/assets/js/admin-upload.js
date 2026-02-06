(function($) {
"use strict";
  jQuery(document).ready(function($){ 
    $("body").on("change","#elementor_yeeaddons_drobox_client_id",function(e){
        var vl = $(this).val()
        $("#yeeaddons_dropbox_api_getcode").attr("href","https://www.dropbox.com/oauth2/authorize?client_id="+vl+"&response_type=code&token_access_type=offline");
    })
    $("body").on("click","#elementor_pro_yeeaddons_dropbox_api_key_button",function(e){
      e.preventDefault();
      var clientId = $("#elementor_yeeaddons_drobox_client_id").val();
      var clientSecret = $("#elementor_yeeaddons_dropbox_client_secret").val();
      var authorizationCode = $("#elementor_yeeaddons_dropbox_access_code").val();
      var nonce = $(this).data("nonce");
      if(clientId == "" ) {
        alert("Empty Client id");
        return;
      }
      if(clientSecret == "" ) {
        alert("Empty Client secret");
        return;
      }
      if(authorizationCode == "" ) {
        alert("Empty Access Code");
        return;
      }
      $(this).addClass("loading");
      var button = $(this);
       $.ajax({
           url: ajaxurl,
           'type': "POST",
           data: {
               action: 'yeeaddons_drobox_client_id_validate',
               clientId: clientId,
               clientSecret: clientSecret,
               authorizationCode: authorizationCode,
               _nonce: nonce,
           },
           success: function(msg){
                  if(msg.success){
                      alert("Authentication successful");
                  }else{
                    alert(msg.data);
                  }
                 button.removeClass("loading");
           },
           error: function(msg){
                 alert(msg.data);
                 button.removeClass("loading");
           },
       });
    })
  })
})(jQuery);



