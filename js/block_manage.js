// vim: set ts=2 sw=2 sts=2 et:

/**
 * @file
 * Block manage controller
 *
 * @author    Creative Development LLC <info@cdev.ru>
 * @copyright Copyright (c) 2011 Creative Development LLC <info@cdev.ru>. All rights reserved
 */

(function ($) {

/**
 * Hide or show elements for different block types
 */
Drupal.behaviors.lcConnectorBlockType = {
  attach: function (context) {
    $('#edit-lc-block-type', context).bind('change', function () {

      var type = this.options[this.selectedIndex].value;

      if (type) {

        var bodyText = $('.text-format-wrapper');
        var widgetSettings = $('#edit-lc-widget-details');

        (bodyText && 'lc_widget' == type) ? bodyText.hide() : bodyText.show();
        (widgetSettings && 'lc_widget' != type) ? widgetSettings.hide() : widgetSettings.show();
      }

    }).change();
  }
};

/**
 * Hide or show elements for different widget classes
 */
Drupal.behaviors.lcConnectorWidgetClass = {
  attach: function (context) {
    $('#edit-lc-class', context).bind('change', function () {

      $('[id]').filter(function() { return this.id.match(/^lc_block_\w+/); }).css('display', 'none');

      var widgetClass = this.options[this.selectedIndex].value.replace(/\\/g, '_');

      if (widgetClass) {
        $('#lc_block_' + widgetClass.replace(/^_+/, '')).css('display', '');
      }

    }).change();
  }
};

})(jQuery);
