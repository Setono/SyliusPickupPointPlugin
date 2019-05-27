(function ($) {
  'use strict';

  let pickupPoints = {};
  let cached = false;

  $(function () {
    let deferreds = [];

    let populateTimer = setTimeout(populate, 1000);

    // cache pickup points
    $('input.input-shipping-method').each(function () {
      let $element = $(this);
      let provider = $element.data('pickup-point-provider');
      let url = $element.data('pickup-point-provider-url');
      let csrfToken = $element.data('csrf-token');

      if (!url) {
        return;
      }

      deferreds.push($.ajax({
        method: 'GET',
        cache: false,
        url: url,
        data: {
          _csrf_token: csrfToken
        },
        success: function (response) {
          if (!pickupPoints.hasOwnProperty(provider)) {
            pickupPoints[provider] = [];
          }
          response.forEach(function (element) {
            pickupPoints[provider].push(element);
          });
        },
      }));
    }).on('change', populate);

    $.when.apply($, deferreds).always(function () {
      cached = true;
      clearTimeout(populateTimer);
      populate();
    });

    function populate() {
      if(!cached) {
        return;
      }

      let $selectedMethod = $('input.input-shipping-method:checked');
      let provider = $selectedMethod.data('pickup-point-provider');
      let $pickupPointContainer = $('.input-pickup-point-id').closest('.field');

      if(!provider) {
        $pickupPointContainer.find('select').empty();
        $pickupPointContainer.hide();
        return;
      }

      if(!pickupPoints.hasOwnProperty(provider)) {
        $pickupPointContainer.find('select').empty();
        $pickupPointContainer.hide();
        return;
      }

      let html = '';
      pickupPoints[provider].forEach(function(pickupPoint) {
        html += '<option value="' + pickupPoint.id + '">' + pickupPoint.name + ', ' + pickupPoint.address + ', ' + pickupPoint.zipCode + ' ' + pickupPoint.city + '</option>';
      });

      $pickupPointContainer.find('select').html(html);
      $pickupPointContainer.show();
    }
  });
})(jQuery);
