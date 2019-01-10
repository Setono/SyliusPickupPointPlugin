(function ($) {
  $(function () {
    $('input.input-shipping-method').findPickupPoints();
    $('input.input-shipping-method:checked').change();
  });
})(jQuery);

(function ($) {
  'use strict';

  $.fn.extend({
    findPickupPoints: function () {
      return this.each(function () {
        let $element = $(this);
        let $container = $(this).closest('.item');
        let url = $element.data('pickup-point-provider-url');
        let csrfToken = $element.data('csrf-token');

        if (!url) {
          return;
        }

        $element.api({
          method: 'GET',
          on: 'change',
          cache: false,
          url: url,
          beforeSend: function (settings) {
            settings.data = {
              _csrf_token: csrfToken
            };

            removePickupPoints($container);
            $container.addClass('loading');

            return settings;
          },
          onSuccess: function (response) {
            addPickupPoints($container, response);
            $('.ui.fluid.selection.dropdown').dropdown('setting', 'onChange', function () {
              let id = ($('.ui.fluid.selection.dropdown').dropdown('get value'));
              $(".pickup-point-id").val(id);
            });
          },
          onFailure: function (response) {
            console.log(response);
          },
          onComplete: function () {
            $container.removeClass('loading');
          }
        });
      });
    }
  });

  function removePickupPoints($container) {
    $container.find('.pickup-points').remove();
  }

  function addPickupPoints($container, pickupPoints) {
    if (document.querySelector('.ui.fluid.selection.dropdown') == null) {
      let list = '<div class="ui fluid selection dropdown pickup-point-dropdown" style="width:250px">' +
        '<input type="hidden" name="pickupPoint">' +
        '<i class="dropdown icon"></i>' +
        '<div class="default text">Select Pickup Point</div>' +
        '<div class="menu">'
      ;

      pickupPoints.forEach(function (element) {
        list += '<div class="item" data-value="' + element.id + '">';
        list += ' ' + element.name;
        list += ' ' + element.address;
        list += ' ' + element.zipCode;
        list += ' ' + element.country + '</div>'
      });

      list += '</div>' +
        '</div>' +
        '</div>'
      ;

      $container.find('.content').append(list);

      let $dropdown = $('.ui.fluid.selection.dropdown');

      $dropdown.dropdown();

      let id = $(".pickup-point-id").val();

      $dropdown.dropdown('set selected', id);
    }
  }
})(jQuery);
