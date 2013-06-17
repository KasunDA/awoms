<h1>Welcome to <?= BRAND; ?>, world!</h1>

<ul>
  <li>
    Check out <b>Articles</b> where you can <a href="<?= BRANDURL; ?>articles/viewall">view</a> or <a href="<?= BRANDURL; ?>articles/write">write</a> anything, even <b>anonymously</b> with no login
  </li>
</ul>

<cite>
  Latest Articles last refreshed at <div id='lastRefreshTime'></div>
</cite>
<div id='homeTopArticles'></div>

<?php
$pageJavaScript[] = "
  var divResults = $('#homeTopArticles');
  var divRefreshTimeResults = $('#lastRefreshTime');
  // Ajax call
  function getLatestArticles() {
    var go = $.ajax({
        type: 'POST',
        url: '".BRANDURL."articles/viewall',
        data: {
            m: 'ajax'
        }
    })
    .done(function(results) {
        var now = new Date();
        divRefreshTimeResults.html(now.toString());
        divResults.html(results);
        divResults.css('border', '3px solid black');
    })
    .fail(function(msg) {
        alert('Error: ' + msg);
    })
    .always(function() {
    });
  }
  // Initial Load
  getLatestArticles();
  // Timer
  setInterval(getLatestArticles, 3000); // 1000 = 1s
";