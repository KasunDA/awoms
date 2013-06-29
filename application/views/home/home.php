<div id='divResults'></div>
<h1>Welcome to <?= BRAND; ?>, world!</h1>

<cite>
  Latest Articles last refreshed at <div id='lastRefreshTime'></div>
</cite>
<div id='homeTopArticles'></div>

<?php
$pageJavaScript[] = str_replace('APIURL', APIURL, file_get_contents(dirname(__FILE__).'/home.js'));

var_dump($pageJavaScript);