(function (Drupal, $) {
  Drupal.behaviors.customAjaxLoader = {
    attach: function (context, settings) {
      // Only attach once
      if (context !== document) return;

      // Show loader on ajax start
      $(document).ajaxSend(function (event, xhr, settings) {
        $('#loader').removeClass('hidden');
      });

      // Hide loader on ajax complete (both success and error)
      $(document).ajaxComplete(function (event, xhr, settings) {
        $('#loader').addClass('hidden');
      });
    }
  };    
})(Drupal, jQuery);
