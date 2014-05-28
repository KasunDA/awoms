<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta http-equiv="Content-Language" content="en-us">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        
        <title><?php
        // @TODO
        // include('meta.php');
        // 
          if (!empty($article['articleName'])) {
            echo $article['articleName'];
          } else {
            if (empty($title)) {
              $title = 'DEBUG';
            }
            echo $title;
          }?></title>
        <meta name="description" content="<?php
          if (empty($article['articleLongDescription'])) {
            if (empty($article['articleShortDescription'])) {
              // Use default description
              echo "Default description";
            } else {
              // Use Short Description
              echo $article['articleShortDescription'];
            }
          } else {
            // Use Long Description
            echo $article['articleLongDescription'];
          }
          ?>">
        <meta name="keywords" content="<?php
          if (!empty($article['articleName'])) {
            echo $article['articleName'];
          } else {
            echo "default, keywords";
          }?>">
        <meta name="viewport" content="width=device-width">

        <!-- Styles -->
        <link rel="stylesheet" href="/css/normalize.min.css">
        <link rel="stylesheet" href="/css/gpfc/jquery-ui-1.10.4.custom.min.css">
        <!-- <link rel="stylesheet" href="/css/main.css"> -->
        <link rel="stylesheet" href="/css/custom.css">

        <!-- Modernizr -->
        <script src="/js/libs/modernizr-respond/2.6.2-respond-1.1.0/modernizr-respond.min.js"></script>

        <?php
        // Site Verification
        if (file_exists('googleSiteVerification.php')) {
          include('googleSiteVerification.php');
        }
        ?>
    </head>
    <body>
        <!--[if lt IE 7]>
            <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
        <![endif]-->

        <div class="header-container">
            <header class="wrapper clearfix">
                <h1 class="title">Vino Default Template: <?php echo $title; ?></h1>
                <nav>
                    
                    <!-- CSS Menu from http://cssmenumaker.com/builder/1917619 -->
                    <div id='cssmenu'>
                        <ul>
                            <li class='active'><a href='<?php echo BRAND_URL; ?>'>Home</a></li>
                            <li class='has-sub'>
                                <a href='<?php echo BRAND_URL; ?>pages/viewall'>Pages</a>
                                <ul>
                                    <li class='has-sub'>
                                        <a href='<?php echo BRAND_URL; ?>pages/viewall'>Pages</a>
                                        <ul>
                                            <li><a href='<?php echo BRAND_URL; ?>pages/create'>Add Page</a></li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>

                </nav>
            </header>
        </div>
        
        <div class="main-container">
            <div class="main wrapper clearfix">

<?php
if (!empty($resultsMsg)) {
  echo $resultsMsg;
}

$pageJavaScript[] = "
    $('#cssmenu').prepend('<div id=\"menu-button\">Menu</div>');
        $('#cssmenu #menu-button').on('click', function(){
            var menu = $(this).next('ul');
            if (menu.hasClass('open')) {
                menu.removeClass('open');
            }
            else {
                menu.addClass('open');
            }
        });";