<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta http-equiv="Content-Language" content="en-us">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

        <title><?php
            //@TODO Dynamic Meta / SEO !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            if (!empty($article['articleName'])) {
                echo $article['articleName'];
            } else {
                if (empty($title)) {
                    $title = BRAND_DOMAIN;
                }
                echo $title;
            }
            ?></title>

        <meta name="description" content="<?php
        if (empty($article['articleLongDescription'])) {
            if (empty($article['articleShortDescription'])) {
                // Use default description
                echo BRAND_DOMAIN;
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
            echo BRAND_DOMAIN;
        }
        ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <!-- Reset and Admin Styles -->
        <link rel="stylesheet" href="/css/normalize.min.css">
        <link rel="stylesheet" href="/css/main.css">
        <link rel="stylesheet" href="/css/admin/jquery-ui-1.10.4.custom.min.css">
        
<?php
if (!empty($_SESSION['user_logged_in'])
        && $_SESSION['user_logged_in'] == 1)
{
    if ($_SESSION['user']['usergroup']['usergroupName'] == "Administrators")
    {
        echo "<link rel='stylesheet' href='/css/admin/admin.css'>";    
    }
}
?>    

        <!-------------------------------->
        <!-- Brand Style -->
        <!-------------------------------->
        <link rel="stylesheet" href="/css/Hut8/Hut8.css">

        <!-- Wow slider -->
        <link rel="stylesheet" href="/css/Hut8/old/wowslider/style.css"/>

        <!-- FONT: Miso -->
        <link rel="stylesheet" href="/css/Hut8/old/fonts/miso/stylesheet.css"/>

        <!-- FONT: Comfortaa -->
        <link rel="stylesheet" href="/css/Hut8/old/fonts/Comfortaa/stylesheet.css"/>

        <!-- FONT: Eurostile -->
        <link rel="stylesheet" href="/css/Hut8/old/fonts/eurostile/stylesheet.css"/>
        <!-------------------------------->
        <!-------------------------------->

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

        <div id="wrapper">
            
            <div id="top">
                <div id='top-nav'>
                    <ul class='menu_horizontal menu_top'>
                        <li>
                            <a href="https://www.facebook.com/hutno8" target="_parent">
                                <div class="fb-like" data-href="https://www.facebook.com/hutno8" data-send="false" data-layout="button_count" data-width="65" data-show-faces="false" data-font="segoe ui"></div>
                            </a>
                        </li>
                        <li><a href="">Shopping Cart (0)</a></li>
                        <li><a href="">Checkout</a></li>
                    </ul>
                </div>
            </div>

            <header>

<?php
    // Home page has different header (doesnt have bottom to hang over wowslider images)
    if ($this->action == 'home') {
        $class = "header";
    } else {
        $class = "header-full";
    }
?>
                    <div class="<?php echo $class; ?>">

                        <nav>
                            <ul class="menu_horizontal menu_header menu_hover">

<?php
    if (!empty($_SESSION['user_logged_in']))
    {
        // Authenticated User Navigation
?>
                                <li><a href="/" target="_parent" title="Home">Home</a></li>
                                <li><a href="/" target="_parent" title="Owners Home">Owners Home</a></li>
                                <li><a href="/" target="_parent" title="Store Info">Store Info</a></li>
                                <li><a href="/" target="_parent" title="Owners Store">Owners Store</a></li>
                                <li><a href="/users/logout" title="Log Out">Log Out</a></li>
<?php
    }
    else
    {
        // Default Navigation
?>
                                <li><a href="/" target="_parent" title="Home">Home</a></li>
                                <li><a href="/" target="_parent" title="About Hut no. 8">About Us</a></li>
                                <li><a href="/" target="_parent" title="Buy &amp; Sell Clothing">Buy &amp; Sell Clothing</a></li>
                                <li><a href="/" target="_parent" title="Franchises">Franchises</a></li>
                                <li><a href="/" target="_parent" title="Store Locator">Store Locator</a></li>
<?php
    }
?>

                            </ul>
                        </nav>
                    </div>
            </header>

<?php
// @TODO VERIFY (wowslider probably)
// Home gets special treatment, rest of pages get generated as follows
#if ($this->action != 'home') {
?>
            <div id="main-container" class="body-text">
                <a style="display:none" name='top'></a>
<?php
#}
?>
<!-- END TEMPLATE HEADER -->