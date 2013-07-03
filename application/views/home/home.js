
/**
 * Get Latest Articles
 */
var divResultsArticles = $('#homeTopArticles');
var divResultsRefreshTime = $('#lastRefreshTime');
// Ajax call
function getLatestArticles() {
  var params = { 'm': 'ajax'};
  var go = $.ajax({
    url: 'APIURL/articles/getArticles',
    type: 'POST',
    dataType: 'json',
    contentType: 'application/json;chartset=utf-8',
    data: JSON.stringify(params)
  })
 .done(function(results) {
     var now = new Date();
     divResultsRefreshTime.html(now.toString());
     divResultsArticles.html(results);
     divResultsArticles.css('border', '3px solid black');
 })
 .fail(function(msg) {
   divResultsRefreshTime.html('Oops!');
   console.debug(msg);
 })
 .always(function() {
 });
}
// Initial Load
getLatestArticles();
// Timer
setInterval(getLatestArticles, 30000); // 1000 = 1s