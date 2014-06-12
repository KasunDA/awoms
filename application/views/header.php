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
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <?php
        // Dynamic Meta Tags (from template 'header_meta'
        // Note: include code needed here to read template data such as $template or $page
        $fileLocations = Utility::getTemplateFileLocations('header_meta');
        foreach ($fileLocations as $fileLoc){include $fileLoc;}

        // Dynamic Meta Tags (from template 'header_style'
        echo $headerStyle;

        // Google Site Verification (from template 'googleSiteVerification'
        echo $headerGoogleSiteVerification;
        ?>
        <!-- Modernizr (keep after styles and in header) -->
        <script src="/js/libs/modernizr-respond/2.6.2-respond-1.1.0/modernizr-respond.min.js"></script>
    </head>
    <body>
        <!--[if lt IE 7]>
            <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
        <![endif]-->

        <div id="wrapper">

            <?php
            // Dynamic Heading Top (from template 'header_top'
            echo $headerTop;

            // Dynamic Heading  Nav (from template 'header_nav'
            // Note: include code needed here to read template data such as $template or $page
            $fileLocations = Utility::getTemplateFileLocations('header_nav');
            foreach ($fileLocations as $fileLoc){include $fileLoc;}
            ?>

            <div id="main-container" class="body-text">
                <a style="display:none" name='top'></a>

                <?php
                // Error or results messages
                if (!empty($_SESSION['ErrorMessage'])) {
                    echo $_SESSION['ErrorMessage'];
                #    if (!empty($_SESSION['ErrorRedirect']))
                #    {
                #        echo "<META http-equiv='refresh' content='0;URL=".$_SESSION['ErrorRedirect']."'>";
                #    }
                }
                if (!empty($resultsMsg)) {
                    echo $resultsMsg;
                }
