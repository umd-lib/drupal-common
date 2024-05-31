/**
 * @file
 * Javascript to integrate the clipboard.js library with Drupal.
 */

window.ClipboardJS = window.ClipboardJS || Clipboard;

(function ($, Drupal, drupalSettings) {

  'use strict';

  Drupal.behaviors.clipboardjs = {
    attach: function (context, settings) {
      let elements = context.querySelectorAll('a.clipboardjs-button, input.clipboardjs-button, button.clipboardjs-button');
      let alert_id = document.getElementById("clipboardjs-alert");
      let error_id = document.getElementById("copy-error");

      $(elements).click(function(event){
        event.preventDefault();
      });

      Drupal.clipboard = new ClipboardJS(elements);

      // Process successful copy.
      Drupal.clipboard.on('success', function (e) {
        console.log("Copy Success");
        let alertStyle = e.trigger.dataset.clipboardAlert;
        let alertText = e.trigger.dataset.clipboardAlertText;
        alertText = alertText ? alertText : Drupal.t('Copied.')

        // Display as alert.
        if (alert_id) {
          alert_id.textContent=" (Copied)";
        }
      });

      // Process unsuccessful copy.
      Drupal.clipboard.on('error', function (e) {
        console.log("Copy Error");
        let actionMsg = '';
        let actionKey = (e.action === 'cut' ? 'X' : 'C');

        if (/iPhone|iPad/i.test(navigator.userAgent)) {
          actionMsg = Drupal.t('This device does not support HTML5 Clipboard Copying. Please copy manually.');
        }
        else {
          if (/Mac/i.test(navigator.userAgent)) {
            actionMsg = Drupal.t('Press ⌘-@key to @action', {'@key': actionKey, '@action': e.action});
          }
          else {
            actionMsg = Drupal.t('Press Ctrl-@key to @action', {'@key': actionKey, '@action': e.action});
          }
        }

        if (error_id) {
          error_id.textContent=actionMsg;
        }
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
