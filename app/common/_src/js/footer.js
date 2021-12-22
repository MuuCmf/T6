
  //授权查询页 底部扫码领取方案不显示
  var address = "/muu/nslookup/index.html";
  if (window.location.pathname == address) {
    $(".wemreceive").css({
      "display": "none"
    });
  }
  //用户协议页面底部不显示
  var serviceagreement = "/ucenter/Common/agreement.html"
  if (window.location.pathname == serviceagreement) {
    $(".tabbar").css({
      "display": "none"
    });
  }
  // 领取方案
  function mobile() {
    var mobile = document.getElementById("mobile").value;
    var myreg = /^[1][3,5,7,8,9][0-9]{9}$/;
    if (myreg.test(mobile)) {
      alert("提交成功");
      $("#mobile").val("");
      console.log(mobile)
    } else {
      alert("手机号码不正确")
    }
  }
  // 领取方案
  // 点击复制
  function email() {
    var email = document.getElementById("e-mail");
    email.select(); // 选择对象
    document.execCommand("Copy"); // 执行浏览器复制命令
    alert('复制成功')
  };
  // 点击复制
  // 点击复制qq群号码
  function groupnumber() {
    var groupnumber = document.getElementById("groupnumber");
    groupnumber.select();
    document.execCommand("Copy");
    alert('复制成功')
  };
  // 点击复制qq群号码

    var SEPARATION = 70, AMOUNTX = 60, AMOUNTY = 60;

    var container;
    var camera, scene, renderer;

    var particles, particle, count = 0;

    var mouseX = 0, mouseY = 0;

    var windowHalfX = window.innerWidth / 2;
    var windowHalfY = 362 / 2;

    init();
    animate();

    function init() {

      container = document.createElement( 'div' );
      document.getElementById('wemreceive').appendChild(container);
      camera = new THREE.PerspectiveCamera( 75, window.innerWidth / 362, 1, 10000 );
      camera.position.z = 1000;
      scene = new THREE.Scene();
      particles = new Array();
      var PI2 = Math.PI * 2;
      var material = new THREE.ParticleCanvasMaterial( {
        color: 0x03b8cf,
        program: function ( context ) {
          context.beginPath();
          context.arc( 0, 0, 1, 0, PI2, true );
          context.fill();

        }

      } );

      var i = 0;

      for ( var ix = 0; ix < AMOUNTX; ix ++ ) {

        for ( var iy = 0; iy < AMOUNTY; iy ++ ) {

          particle = particles[ i ++ ] = new THREE.Particle( material );
          particle.position.x = ix * SEPARATION - ( ( AMOUNTX * SEPARATION ) / 2 );
          particle.position.z = iy * SEPARATION - ( ( AMOUNTY * SEPARATION ) / 2 );
          scene.add( particle );

        }

      }

      renderer = new THREE.CanvasRenderer();
      renderer.setSize( window.innerWidth, 362 );
      container.appendChild( renderer.domElement );

      document.addEventListener( 'mousemove', onDocumentMouseMove, false );
      document.addEventListener( 'touchstart', onDocumentTouchStart, false );
      document.addEventListener( 'touchmove', onDocumentTouchMove, false );

      //

      window.addEventListener( 'resize', onWindowResize, false );

    }

    function onWindowResize() {

      windowHalfX = window.innerWidth / 2;
      windowHalfY = 362 / 2;
      camera.aspect = window.innerWidth / 362;
      camera.updateProjectionMatrix();
      renderer.setSize( window.innerWidth, 362 );

    }

    function onDocumentMouseMove( event ) {

      mouseX = event.clientX - windowHalfX;
      mouseY = event.clientY - windowHalfY;

    }

    function onDocumentTouchStart( event ) {

      if ( event.touches.length === 1 ) {

        event.preventDefault();

        mouseX = event.touches[ 0 ].pageX - windowHalfX;
        mouseY = event.touches[ 0 ].pageY - windowHalfY;
      }
    }
    function onDocumentTouchMove( event ) {
      if ( event.touches.length === 1 ) {
        event.preventDefault();
        mouseX = event.touches[ 0 ].pageX - windowHalfX;
        mouseY = event.touches[ 0 ].pageY - windowHalfY;
      }
    }
    function animate() {
      requestAnimationFrame( animate );
      render();
    }
    function render() {
      camera.position.x += ( mouseX - camera.position.x ) * .05;
      camera.position.y += ( - mouseY - camera.position.y ) * .05;
      camera.lookAt( scene.position );
      var i = 0;
      for ( var ix = 0; ix < AMOUNTX; ix ++ ) {
        for ( var iy = 0; iy < AMOUNTY; iy ++ ) {
          particle = particles[ i++ ];
          particle.position.y = ( Math.sin( ( ix + count ) * 0.3 ) * 50 ) + ( Math.sin( ( iy + count ) * 0.5 ) * 50 );
          particle.scale.x = particle.scale.y = ( Math.sin( ( ix + count ) * 0.3 ) + 1 ) * 2 + ( Math.sin( ( iy + count ) * 0.5 ) + 1 ) * 2;
        }
      }
      renderer.render( scene, camera );
      count += 0.1;
    }