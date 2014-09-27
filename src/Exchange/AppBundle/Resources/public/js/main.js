(function() {
    function ScaleContentToDevice(){
        scroll(0, 0);
        var content = $.mobile.getScreenHeight() - $('#page-map [data-role=header]').outerHeight() - $('#page-map [data-role=footer]').outerHeight() - $('#page-map .ui-content').outerHeight() + $('#page-map .ui-content').height();
        $('#page-map .ui-content').height(content);
        console.log('..content', content);
    }

    $(document).on( "pagecontainershow", function(){
        ScaleContentToDevice();
    });

    $(window).on("resize orientationchange", function(){
        ScaleContentToDevice();
    });
})();