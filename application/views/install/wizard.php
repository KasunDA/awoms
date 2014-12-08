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
        <title>Install Wizard</title>
        <link rel="stylesheet" href="/css/normalize.min.css">
        <link rel="stylesheet" href="/css/main.css">
        <!-- Modernizr (keep after styles and in header) -->
        <script src="/js/libs/modernizr-respond/2.6.2-respond-1.1.0/modernizr-respond.min.js"></script>
        <script src='/js/libs/jquery/2.1.1/jquery.min.js'></script>
    </head>
    <body style='margin: 50px;'>

        <?php
        // Step 1
        if (empty($_POST)) {
            ?>    
            <div id="step1">

                <h1>Install Wizard</h1>

                <h2>Server Test</h2>
                
                <p class="muted">These tests will ensure your server has all the required functions before installing.</p>
                
                <div id='serverTestResults'>
                    <p>Please wait while server tests run...</p>
                    <div id="facebookG" style="margin:25px 50px;">
                        <div id="blockG_1" class="facebook_blockG"></div>
                        <div id="blockG_2" class="facebook_blockG"></div>
                        <div id="blockG_3" class="facebook_blockG"></div>
                    </div>
                </div>

                <script>
                    $(document).ready(function() {
                        // Ajax execute
                        var go = $.ajax({
                            type: 'GET',
                            url: '/tests/servertest?m=ajax'
                        })
                        .done(function(results) {
                            // Handle Results
                            var divResults = $('#serverTestResults');
                            divResults.html(results);
                        })
                        .fail(function(msg) {
                            // Error results
                            var divResults = $('#serverTestResults');
                            divResults.html(msg);
                            console.log(msg);
                            // CSS
                            divResults.css('border', '3px solid red');
                        })
                        .always(function() {
                        });
                    });
                </script>

                <form class='wizard_form hidden' method='POST'>
                    <input type='hidden' name='step' value='2' />

                    <h2>Main Brand</h2>
                    
                    <p class="muted">This main brand is your Parent Company and Domain Name that Administrators will log in to.</p>
                    
                    <table class="bordered">

                        <tr>
                            <td>
                                Brand Name
                            </td>
                            <td>
                                <input type='text' id='inp_brandName' name='inp_brandName' size='60' placeholder="My Company" value="Goin' Postal Franchise Corporation"/>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                Brand Label
                                <p class="muted">This unique brand identifier is used for Theme folders and should <b>not</b> contain spaces, e.g. GPFC</p>
                            </td>
                            <td>
                                <input type='text' id='inp_brandLabel' name='inp_brandLabel' size='60' placeholder="MyCo" value="GPFC"/>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                Domain Name
                                <p class="muted">Main brand's domain name, excluding the www prefix</p>
                            </td>
                            <td>
                                <input type='text' id='inp_domainName' name='inp_domainName' value='<?php
                                echo $_SERVER['HTTP_HOST'];
                                ?>' size='60' placeholder="<?php echo $_SERVER['HTTP_HOST']; ?>"/>
                            </td>
                        </tr>

                    </table>

                    <h2>Global Administrator</h2>
                    <p class="muted">This account will be able to administrate all brands.</p>
                    <table class="bordered">
                        <tr>
                            <td>
                                Administrator Username
                            </td>
                            <td>
                                <input type='text' id='inp_adminUsername' name='inp_adminUsername' size='60' />
                            </td>
                        </tr>

                        <tr>
                            <td>
                                Administrator Passphrase
                            </td>
                            <td>
                                <input type='password' id='inp_adminPassphrase' name='inp_adminPassphrase' size='60' />
                            </td>
                        </tr>

                        <tr>
                            <td>
                                Administrator Email
                            </td>
                            <td>
                                <input type='text' id='inp_adminEmail' name='inp_adminEmail' placeholder="admin@myco.com" size='60' value="admin@<?php echo $_SERVER['HTTP_HOST']; ?>"/>
                            </td>
                        </tr>

                    </table>

                    <!-- Form Action Buttons -->
                    <table class="form_actions">
                        <tr>
                            <td>
                                <button id="step1submit" type='submit' class="button_save">Install</button>
                            </td>
                        </tr>
                    </table>

                </form>

            </div>

            <?php
        } else {
            ?>
            <div id="step2">
                <h1>Success!</h1>
                <p>Welcome <strong><?php echo $_POST['inp_brandName']; ?></strong>!</p>
                <p><a href='/admin/home'>Administration Home</a> or would you like <a href="/install/sample" id="sampledata_submit">Sample Data</a>?</p>
                <p>Sample data will add a few brands, domains and users for you to jump right into testing the functionality.</p>
            </div>

            <?php
        }
        ?>

        <!-- Loading image -->
        <div id="loading" class="hidden">
            <h1 id="loading_title"></h1>
            <p>
                <small><b>Note:</b> this may take up to a minute to complete, <strong>do NOT leave or refresh</strong> the page while it is loading.</small>
            </p>
            <div id="facebookG" style="margin-left:100px;">
                <div id="blockG_1" class="facebook_blockG"></div>
                <div id="blockG_2" class="facebook_blockG"></div>
                <div id="blockG_3" class="facebook_blockG"></div>
            </div>
        </div>  

        <script>
            $('#step1submit').on('click', function() {
                $('#step1').addClass('hidden');
                $('#loading').removeClass('hidden');
                $('#loading_title').html('Installing...');
            });

            $('#sampledata_submit').on('click', function() {
                $('#step2').addClass('hidden');
                $('#loading').removeClass('hidden');
                $('#loading_title').html('Installing Sample Data...');
            });
        </script>
    </body>
</html>
