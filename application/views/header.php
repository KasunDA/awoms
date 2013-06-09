<html>
    <head>
        <meta http-equiv="Content-Language" content="en-us">
        <meta name="keywords" content="keywords, here">
        <meta name="Description" content="desc here">
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
        <title><?php echo BRAND; ?></title>
        <style>
            body {
              background-color: #333;
              color: #ccc;
            }
            #header {
              border-bottom: 1px solid #000;
            }
            #footer {
              border-top: 1px solid #000;
            }
            pre, .xdebug-var-dump {
              background-color: #ccc;
              color: #000;
            }
            a {
              color: #20a724;
            }
        </style>
        <!-- <link rel="stylesheet" type="text/css" href="css/cart_style.php?p&t" /> -->
    </head>
    
    <body>
      
      <div id='header'>
        <ul>
          <li>
            <a href='<?php echo BRANDURL; ?>home'><?= BRAND; ?></a>
          </li>
          <li>
            <a href='<?php echo BRANDURL; ?>articles'>Articles</a>
          </li>
        </ul>
        
        <h1><?php echo $title; ?></h1>

      </div>
      