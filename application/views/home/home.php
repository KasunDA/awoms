<div id='divResults'></div>
<h1>Welcome to <?= BRAND; ?>, world!</h1>

<cite>
  Latest Articles last refreshed at <div id='lastRefreshTime'></div>
</cite>
<div id='homeTopArticles'></div>

<?php
$pageJavaScript[] = "
  /**
   * Get latest articles
   */
  var divResultsArticles = $('#homeTopArticles');
  var divResultsRefreshTime = $('#lastRefreshTime');
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
        divResultsRefreshTime.html(now.toString());
        divResultsArticles.html(results);
        divResultsArticles.css('border', '3px solid black');
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
  setInterval(getLatestArticles, 30000); // 1000 = 1s
";