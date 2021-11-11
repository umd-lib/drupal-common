(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.systemStatusBehavior = {
    attach: function (context, settings) {

      var systemStatusUrl = drupalSettings.system_status.system_status_url;
      var utilityNavItem = $(context).find('.utility-nav-systems-status');
      var systemStatusLoading = $(context).find('.systems-status-loading');
      var systemStatusLoaded = $(context).find('.systems-status-loaded');

      /**
       * Retrieves the systems status from the given endpoint, and updates
       * the "Systems Status" entry in the utility navigation menu, and the
       * system status block.
       */
      function retrieveStatus(systemStatusUrl) {
        var utilityNavItemStatus = $(context).find('.utility-nav-systems-status > .status');
        var systemStatusDate = $(context).find('.systems-status-date');
        var systemStatusOperational = $(context).find('.systems-status-operational > .status');
        var systemStatusProblem = $(context).find('.systems-status-maintenance > .status');
        var systemStatusOutage = $(context).find('.systems-status-outage > .status');

        if (utilityNavItem === undefined || utilityNavItem[0] === undefined) {
          // Status menu item not on page
          return;
        }

        systemStatusDate.html(getFormattedDate());

        $.getJSON(systemStatusUrl, function (data) {
          if (data === undefined) {
            return;
          }
          if (data['error']) {
            console.log('Error retrieving system status!')
            return;
          }

          var nonNormalCount = data['non_normal']
          if (nonNormalCount > 0) {
            var nonNormalCaption = '<span class="badge">' + nonNormalCount + '</span>';
            utilityNavItemStatus.html(nonNormalCaption);
          }
          systemStatusOperational.html(`${data['normal']}/${data['total']}`);
          var problemHtml = "<ul>"
          data['non_normal_list'].forEach(element => problemHtml += `<li>${element}</li>`);
          problemHtml += "</ul>"
          systemStatusProblem.html(problemHtml);
          systemStatusOutage.html(`${data['outage']}/${data['total']}`);
          systemStatusLoading.hide()
          systemStatusLoaded.show()
        });
      }

      $('html').once('systemStatusBehavior').each(function () {
        retrieveStatus(systemStatusUrl);
      });

      /* The following section can be removed after incorporating
       * the utility nav theming changes.
       */
      // utilityNavItem.click(function (e) {
      //   retrieveStatus(systemStatusUrl);
      //   systemStatusBlock.toggle();
      //   e.preventDefault();
      // })

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


