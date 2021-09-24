(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.systemStatusBehavior = {
    attach: function (context, settings) {
      /**
       * Retrieves the systems status from the given endpoint, and updates
       * the "Systems Status" entry in the utility navigation menu
       */
      function retrieveStatus(systemStatusUrl) {
        var utilityNavItem = $(context).find('.utility-nav-systems-status');

        if (utilityNavItem === undefined || utilityNavItem[0] === undefined) {
          // Status menu item not on page
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


