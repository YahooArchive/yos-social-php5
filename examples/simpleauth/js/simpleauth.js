YUI({combine: true, timeout: 10000}).use('node', 'event', 'io', 'overlay', 'dump', 'json-parse', function (Y) {

    if(Y.Node.get('#ysimpleauth-login'))
    {
      Y.on('click', function (e) {

        // prevent default
        e.preventDefault();

        // create overlay + loading indicator
        var overlay = new Y.Overlay({
              width:  '100%',
              height: '100%',
              bodyContent: '<div style="text-align: center; padding: 10px;">Signing in... <img width="43px" height="11px" src="img/indicator.gif" alt="Please finish signing in..." /></div>',
              zIndex:  10000,
              visible: true
            });
        overlay.render('body');
        overlay.show();

        // open popup window
        var height = 500;
        var width = 500;

        var left =  Math.max(0, Math.floor((e.target.get('winWidth') - width) / 2));
        var top  = Math.max(0, Math.floor((e.target.get('winHeight') - height) / 2));

        var simpleauth = window.open('simpleauth.php?openid_mode=discover&popup=true', 'simpleauth', 'location=yes,status=yes,resizable=true,width=' + width + ',height=' + height + ',left=' + left + ',top=' + top);

        // hide overlay when popup closes

        // focus popup
        simpleauth.window.focus();

        // wait until oauth completes

        // close popup window and refresh page for access token
        popupMonitor = window.setTimeout(checkPopup, 500);
        function checkPopup() {

         if(false == simpleauth.closed)
         {
           simpleauth.window.focus();

           popupMonitor = window.setTimeout(checkPopup, 500);
         }
         else
         {
           overlay.hide();
           //  window.location = 'simpleauth.php?openid_mode=oauth';
           window.clearInterval();
         }
        }

      }, '#ysimpleauth-login');
    }

});