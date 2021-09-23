(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.systemStatusBehavior = {
    attach: function (context, settings) {
      function retrieveStatus(systemStatusUrl) {
        if (systemStatusUrl === undefined) {
          return;
        }
        $.getJSON( systemStatusUrl, function( data ) {
          if (data === undefined) {
            return;
          }

          var nonNormalCount = data['non_normal']
          if (nonNormalCount === undefined) {
            return;
          }

          if (nonNormalCount == 0) {
            // No further action if all systems are operational
            return;
          }

          var utilityNavItem = $(context).find('.umd-utility-nav-status');
          if (utilityNavItem === undefined) {
            return;
          }


          var currentCaption = utilityNavItem[0].textContent
          var nonNormalCaption = '<span class="badge">'+nonNormalCount+'</span>';
          utilityNavItem.html(currentCaption + " " + nonNormalCaption);
        });
      }

      $('html').once('systemStatusBehavior').each(function () {
        var systemStatusUrl = drupalSettings.system_status.system_status_url;
        retrieveStatus(systemStatusUrl);
      });
    }
  };
})(jQuery, Drupal, drupalSettings);


