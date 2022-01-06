(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.systemStatusBehavior = {
    attach: function (context, settings) {

      var systemStatusUrl = drupalSettings.system_status.system_status_url;
      var utilityNavItem = $(context).find('.utility-nav-systems-status');

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
          $("#systems-status-spinner").hide();
          $("#systems-status-content").show();
          $("#systems-status-spinner-mobile").hide();
          $("#systems-status-content-mobile").show();
          
          if (nonNormalCount > 0) {
            var nonNormalCaption = '<span class="badge">' + nonNormalCount + '</span>';
            utilityNavItemStatus.html(nonNormalCaption);
            $("#system-no-issues").hide();
            $("#system-issues").show();
            $("#system-no-issues-mobile").hide();
            $("#system-issues-mobile").show();
          } else {
            $("#system-issues").hide();
            $("#system-no-issues").show();
            $("#system-issues-mobile").hide();
            $("#system-no-issues-mobile").show();
          }
          var problemHtml = "<ol>"
          data['non_normal_list'].forEach(element => problemHtml += `<li>${element}</li>`);
          problemHtml += "</ol>"
          systemStatusProblem.html(problemHtml);
        });
      }

      // $('body').once('systemStatusBehavior').each(function () {
      //   retrieveStatus(systemStatusUrl);
      // });

      $(context).find("#showSystem").once('systemStatusBehavior').each(function() {
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


