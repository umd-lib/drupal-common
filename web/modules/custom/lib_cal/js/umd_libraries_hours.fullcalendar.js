(function ($) {
  Drupal.fullcalendar.plugins.umd_libraries_hours = {
    options: function (fullcalendar, settings) {
      return {
        views: {
          listYear: {
            visibleRange: function (currentDate) {
              return {
                start: currentDate
              };
            }
          }
        }
      };
    }
  };
}(jQuery));