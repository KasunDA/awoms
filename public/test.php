<button id='btnGetArticles' class='callAPI' name='articles' value='getArticles'>Test1 (Get Articles)</button>
<div id='divResults'></div>

<!-- jQuery -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="/js/vendor/jquery-1.9.1.min.js"><\/script>')</script>
<!-- jQuery UI -->
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>

<script type='text/javascript'>
  $(document).ready(function() {

    /**
     * Call API Button Trigger
     */
    $('.callAPI').click(function() {
      // Controller = name
      var controller = $(this).attr('name');
      // Action = value
      var action = $(this).val();
      // Call API
      callAPI(controller, action);
    });

    /**
     * Call API Ajax Handler
     * 
     * @param {type} controller
     * @param {type} action
     * @returns {undefined}
     */
    function callAPI(controller, action) {
     console.debug('callAPI: ' + controller + ' action: ' + action);
     // Results div
     var divResults = $('#divResults');
     // Ajax execute
     var go = $.ajax({
         type: 'POST',
         url: controller + '/' + action,
         dataType: 'json',
         data: {
            m: 'ajax'
         }
     })
     .done(function(results) {
       // Handle Results
        $.each(results['articles'], function(index, element) {
          var articleName = element['articleName'];
          var articleBody = results['articleBodies'][index]['bodyContentText']
          divResults.append('<hr />Name: ' + articleName + '<br />' + articleBody);
        });
        // CSS
        divResults.css('border', '3px solid green');
     })
     .fail(function(msg) {
        // Error results
        divResults.append('Sorry! We ran into an issue processing your request. The webmaster has been alerted.');
        console.debug(msg);
        // CSS
        divResults.css('border', '3px solid red');
     })
     .always(function() {
     });
    }
    
  });
</script>