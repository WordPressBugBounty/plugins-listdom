(function($)
{
    /**
     *
     * Upsert Map
     *
     */

    // Post Message
    $('.lsd-update-listener').on('change', function()
    {
        var message = JSON.stringify(
        {
            address: $('#lsd_object_type_address').val(),
            latitude: $('#lsd_object_type_latitude').val(),
            longitude: $('#lsd_object_type_longitude').val(),
            object_type: $('#lsd_object_type').val(),
            zoomlevel: $('#lsd_object_type_zoomlevel').val(),
            shape_type: $('#lsd_shape_type').val(),
            shape_paths: $('#lsd_shape_paths').val(),
            shape_radius: $('#lsd_shape_radius').val(),
        });

        if(typeof window !== 'undefined' && typeof window.ReactNativeWebView !== 'undefined') window.ReactNativeWebView.postMessage(message);
    });

    // Trigger on load
    $('#lsd_object_type_latitude').trigger('change');

    /**
     *
     * Search Map
     *
     */

    // Post Message
    $('body').on('lsd-mapsearch', function(event, args)
    {
        var message = JSON.stringify(args);
        if(typeof window !== 'undefined' && typeof window.ReactNativeWebView !== 'undefined') window.ReactNativeWebView.postMessage(message);
    });
}(jQuery));
