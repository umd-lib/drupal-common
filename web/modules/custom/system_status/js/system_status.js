(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.systemStatusBehavior = {
    attach: function (context, settings) {
      /**
       * Retrieves the systems status from the given endpoint, and updates
       * the "Systems Status" entry in the utility navigation menu, and the
       * system status block.
       */
      function retrieveStatus(systemStatusUrl) {
        var utilityNavItem = $(context).find('.utility-nav-systems-status');
        var systemStatusDate = $(context).find('.system-status-date');
        var systemStatusOperational = $(context).find('.system-status-operational > .status');
        var systemStatusProblem = $(context).find('.system-status-maintenance > .status');
        var systemStatusOutage = $(context).find('.system-status-outage > .status');

        if (utilityNavItem === undefined || utilityNavItem[0] === undefined) {
          // Status menu item not on page
          return;
        }

        $.getJSON(systemStatusUrl, function (data) {
          if (data === undefined) {
            return;
          }

          var nonNormalCount = data['non_normal']
          if (nonNormalCount > 0) {
            var currentCaption = utilityNavItem[0].textContent
            var nonNormalCaption = '<span class="badge">' + nonNormalCount + '</span>';
            utilityNavItem.html(currentCaption + " " + nonNormalCaption);
          }

          systemStatusDate.html(getFormattedDate());
          systemStatusOperational.html(`${data['normal']}/${data['total']}`);
          var problemHtml = "<ul>"
          data['non_normal_list'].forEach(element => problemHtml += `<li>${element}</li>`);
          problemHtml += "</ul>"
          systemStatusProblem.html(problemHtml);
          systemStatusOutage.html(`${data['outage']}/${data['total']}`);
        });
      }

      $('html').once('systemStatusBehavior').each(function () {
        var systemStatusUrl = drupalSettings.system_status.system_status_url;
        retrieveStatus(systemStatusUrl);
      });

      function getFormattedDate(date) {
        if (date == undefined) {
          date = new Date();
        }
        let mo = new Intl.DateTimeFormat('en', { month: 'long' }).format(date);
        let da = new Intl.DateTimeFormat('en', { day: 'numeric' }).format(date);
        let ye = new Intl.DateTimeFormat('en', { year: 'numeric' }).format(date);
        let time = new Intl.DateTimeFormat('en', { hour: 'numeric', minute: 'numeric' }).format(date);
        return `${mo} ${da}, ${ye} at ${time}`;
      }
    }
  };
})(jQuery, Drupal, drupalSettings);


