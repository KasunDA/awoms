$('#storeImages').on('click', function() {

    console.log('StoreImages...');

    $.fancybox.open({
        href: 'iframe.html',
        type: 'iframe',
        padding: 5
    });

});