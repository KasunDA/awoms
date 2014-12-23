<h1>File Management</h1>

<a href="/filemanager/dialog.php?type=0" class="btn iframe-btn" type="button">Open Filemanager</a>

<?php
$pageJavaScript[] = "
    $('.iframe-btn').fancybox({
        'width'		: 900,
        'height'	: 600,
        'type'		: 'iframe',
        'autoSize'  : false
    });";