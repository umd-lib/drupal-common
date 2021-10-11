Drupal.behaviors.hoursWeeklyNav = {
  attach: function (context, settings) {
    $('input.hours-weekly-nav').each(function () {
      let elem = $(this);
      let form = elem.closest('form');
      let selected_date = form.find('input[name=field_hours_end_value]').val();
      let nav_date = new Date();
      if (selected_date !== "") {
        nav_date = new Date(selected_date)
      }
      if (elem.val() === 'next') {
        nav_date.setDate(nav_date.getDate() + 7);
        console.log(`Adding Date to ${selected_date}`);
        nav_date_str = nav_date.toISOString().substr(0, 10);
        elem.get(0).addEventListener(
          'click',
          function (e) {
            e.preventDefault();
            e.stopPropagation();
            form.find('input[name=field_hours_end_value]').val(nav_date_str);
            form.find('input[type=submit]').not('.hours-weekly-nav').trigger('click');
          },
          true);
      }
      if (elem.val() === 'previous') {
        nav_date.setDate(nav_date.getDate() - 7);
        console.log(`Subtracting Date from ${selected_date}`);
        nav_date_str = nav_date.toISOString().substr(0, 10);
        elem.get(0).addEventListener(
          'click',
          function (e) {
            e.preventDefault();
            e.stopPropagation();
            form.find('input[name=field_hours_end_value]').val(nav_date_str);
            form.find('input[type=submit]').not('.hours-weekly-nav').trigger('click');
          },
          true);
      }
    });
  }
};