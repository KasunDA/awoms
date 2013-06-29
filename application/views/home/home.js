
/**
 * Get Latest Articles
 */
var divResultsArticles = $('#homeTopArticles');
var divResultsRefreshTime = $('#lastRefreshTime');
// Ajax call
function getLatestArticles() {
 var go = $.ajax({
     type: 'POST',
     url: 'APIURL/articles/getArticles',
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