<script>
  /**
   * Log all jQuery AJAX requests to Google Analytics
   * See: http://www.alfajango.com/blog/track-jquery-ajax-requests-in-google-analytics/
   */
  (function($) {
    $(document).ajaxSend(function(event, xhr, settings) {
      if (typeof _gaq !== "undefined" && _gaq !== null) {
        _gaq.push(['_trackPageview', settings.url]);
      }
    });
  })(jQuery);

  /**
   * Track JavaScript errors in Google Analytics
   */
  (function(window) {
    var undefined,
            link = function(href) {
      var a = window.document.createElement('a');
      a.href = href;
      return a;
    };
    window.onerror = function(message, file, row) {
      var host = link(file).hostname;
      _gaq.push([
        '_trackEvent',
        (host == window.location.hostname || host == undefined || host == '' ? '' : 'external ') + 'error',
        message, file + ' LINE: ' + row, undefined, undefined, true
      ]);
    };
  }(window));
</script>

<script>
  /**
   * Google analytics
   */
  (function(i, s, o, g, r, a, m) {
    i['GoogleAnalyticsObject'] = r;
    i[r] = i[r] || function() {
      (i[r].q = i[r].q || []).push(arguments)
    }, i[r].l = 1 * new Date();
    a = s.createElement(o),
            m = s.getElementsByTagName(o)[0];
    a.async = 1;
    a.src = g;
    m.parentNode.insertBefore(a, m)
  })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');

  ga('create', 'UA-41739032-1', 'awoms.com');
  ga('send', 'pageview');
</script>