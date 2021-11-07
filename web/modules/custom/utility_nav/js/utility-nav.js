(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.utilityNavBehavior = {
    attach: function (context, settings) {
      var accountsNavItem = $(context).find('.utility-nav-accounts');
      var accountsMenuBlock = $(context).find('.utility-nav-accounts-menu');

      accountsNavItem.click(function (e) {
        accountsMenuBlock.toggle();
        e.preventDefault();
      })
    }
  };
})(jQuery, Drupal, drupalSettings);


