(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.utilityNavThemeBehavior = {
    attach: function (context, settings) {
      function setDisplay(elem_id, val) {
        let elem = document.getElementById(elem_id)
        if (elem) {
          elem.style.display = val;
        }
      }
      function getDisplay(elem_id) {
        let elem = document.getElementById(elem_id)
        if (elem) {
          return elem.style.display;
        }
      }
      function setBackground(elem_id, val) {
        let elem = document.getElementById(elem_id)
        if (elem) {
          elem.style.background = val;
        }
      }
      function setFocus(elem_id) {
        let elem = document.getElementById(elem_id)
        if (elem) {
          elem.focus();
        }
      }

      function toggleBlockAndButton(block, button) {
        if (getDisplay(block) === "block") {
          setDisplay(block, "none");
          setBackground(button, "#fff");
        } else {
          setDisplay(block, "block");
          setFocus(block);
          setBackground(button, "#eee");
        }
        var blocks = ["showHours", "showSystem", "showHelp", "showInfo", "showAccounts"];
        var buttons = ["u-hours-btn", "u-system-btn", "u-help-btn", "u-info-btn", "u-accounts-btn"]
        blocks.filter(b => b != block).forEach(block => setDisplay(block, "none"));
        buttons.filter(b => b != button).forEach(button => setBackground(button, "#fff"));
      }

      function exitBlockAndButton(block, button) {
        setDisplay(block, "none");
        setBackground(button, "#fff");
      }

      // Toggle Hours //
      var hoursToggle = function () {
        toggleBlockAndButton("showHours", "u-hours-btn");
      }

      // Toggle System //
      var systemToggle = function () {
        toggleBlockAndButton("showSystem", "u-system-btn");
      }

      // Toggle Help //
      var helpToggle = function () {
        toggleBlockAndButton("showHelp", "u-help-btn");
      }
      // Toggle Info //
      var infoToggle = function () {
        toggleBlockAndButton("showInfo", "u-info-btn");
      }
      // Toggle Accounts //
      var accountsToggle = function () {
        toggleBlockAndButton("showAccounts", "u-accounts-btn");
      }

      var exitHours = function () {
        exitBlockAndButton("showHours", "u-hours-btn");
      }
      var exitSystem = function () {
        exitBlockAndButton("showSystem", "u-system-btn");
      }
      var exitInfo = function () {
        exitBlockAndButton("showInfo", "u-info-btn");
      }
      var exitAccounts = function () {
        exitBlockAndButton("showAccounts", "u-accounts-btn");
      }
      var exitHelp = function () {
        exitBlockAndButton("showHelp", "u-help-btn");
      }

      $(context).find('#u-hours-btn').click(hoursToggle);
      $(context).find('#u-system-btn').click(systemToggle);
      $(context).find('#u-help-btn').click(helpToggle);
      $(context).find('#u-info-btn').click(infoToggle);
      $(context).find('#u-accounts-btn').click(accountsToggle);

      $(context).find('#showHours .close-btn').click(exitHours);
      $(context).find('#showSystem .close-btn').click(exitSystem);
      $(context).find('#showHelp .close-btn').click(exitHelp);
      $(context).find('#showInfo .close-btn').click(exitInfo);
      $(context).find('#showAccounts .close-btn').click(exitAccounts);
    }
  };
})(jQuery, Drupal, drupalSettings);

