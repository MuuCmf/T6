  //手机号验证
//   function elicitcaptcha() {
//     var mobile = document.getElementById("mobile").value;
//     var myreg = /^[1][3,5,7,8,9][0-9]{9}$/;
//     if (!myreg.test(mobile)) {
//       alert("号码不正确");
//     } else {
//     }
//   }


//切换登录方式
$(document).ready(function() {
    $("#password-login").click(function() {
      $(".ewm").show();
      $(".wrapper").show();
      $(".password-login").hide();
      $(".ewm-login").hide();
    });
    $("#ewm").click(function() {
      $(".ewm").hide();
      $(".wrapper").hide();
      $(".password-login").show();
      $(".ewm-login").show();
      
    });
  });

  //忘记密码
  $(document).ready(function() {
    $(".login-method-captcha").click(function() {
      $("#forgetpassword").hide();
    });
    $(".login-method-password").click(function() {
      $("#forgetpassword").show();
    });
  });

  // $(function() {
  //   $("#login li:eq(0) a").tab("show");
  // });