(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.utilityNavBehavior = {
    attach: function (context, settings) {
      var accountsNavItem = $(context).find('.utility-nav-accounts');
      var accountsMenuBlock = $(context).find('.utility-nav-accounts-menu');

      /* The following section can be removed after incorporating
       * the utility nav theming changes.
       */
      // accountsNavItem.click(function (e) {
      //   accountsMenuBlock.toggle();
      //   e.preventDefault();
      // })
    }
  };
})(jQuery, Drupal, drupalSettings);


