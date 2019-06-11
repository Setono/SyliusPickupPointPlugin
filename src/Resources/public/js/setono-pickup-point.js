(function ($) {
  'use strict';

  $.fn.extend({
    setonoSyliusPickupPointLabel() {
      this.each((idx, el) => {
        const element = $(el);
        const pickupPointId = element.data('pickup-point-id');

        if (pickupPointId.length > 0) {
          element.api({
            on: 'now',
            method: 'GET',
            url: element.data('url'),
            onSuccess(response) {
              element
                .append('<i>"' + response.full_name + '"</i>')
                .show()
              ;
            },
          });
        }
      });
    },

    setonoSyliusPickupPointAutoComplete() {
      this.each((idx, el) => {
        const element = $(el);
        const choiceName = element.data('choice-name');
        const choiceValue = element.data('choice-value');

        element.dropdown({
          delay: {
            search: 250,
          },
          forceSelection: false,
          apiSettings: {
            dataType: 'JSON',
            cache: false,
            beforeSend(settings) {
              const selectedMethod = $('input.input-shipping-method:checked');

              /* eslint-disable-next-line no-param-reassign */
              settings.urlData = {
                providerCode: selectedMethod.data('pickup-point-provider'),
                _csrf_token: selectedMethod.data('csrf-token'),
              };

              return settings;
            },
            onResponse(response) {
              return {
                success: true,
                results: response.map(item => ({
                  name: item[choiceName],
                  value: item[choiceValue],
                })),
              };
            },
          },
        });

        window.setTimeout(() => {
          element.dropdown('set selected', element.find('input.autocomplete').val().split(',').filter(String));
        }, 5000);

      });
    },
  });
})(jQuery);
