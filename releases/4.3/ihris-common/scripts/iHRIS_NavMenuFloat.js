window.addEvent('domready', function() {
    if ( !$('navBar') ) {
        $('inlineNavMenu').addClass('inactiveMenu');
    } else {
        var navBarSeen = false;
        $('inlineNavMenu').addEvent('click', function() {
            if ( navBarSeen ) {
                navBarSeen = false;
                $('navBar').hide();
                $('inlineNavMenu').removeClass('open');
            } else {
                navBarSeen = true;
                $('navBar').show();
                $('inlineNavMenu').addClass('open');
                $('siteOuterWrap').addEvent('click', function() {
                    navBarSeen = false;
                    $('navBar').hide();
                    $('inlineNavMenu').removeClass('open');
                });
            }
        });
    }
});
