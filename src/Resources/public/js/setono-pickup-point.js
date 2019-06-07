(function ($) {
  'use strict';

  $.fn.extend({
    setonoSyliusPickupPointAutoComplete() {
      this.each((idx, el) => {
        const element = $(el);
        const choiceName = element.data('choice-name');
        const choiceValue = element.data('choice-value');
        const autocompleteValue = element.find('input.autocomplete').val();

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

        if (autocompleteValue.split(',').filter(String).length > 0) {
          const menuElement = element.find('div.menu');

          menuElement.api({
            on: 'now',
            method: 'GET',
            url: element.data('url'),
            beforeSend(settings) {
              const selectedMethod = $('input.input-shipping-method:checked');

              /* eslint-disable-next-line no-param-reassign */
              settings.urlData = {
                providerCode: selectedMethod.data('pickup-point-provider'),
                _csrf_token: selectedMethod.data('csrf-token'),
              };

              /* eslint-disable-next-line no-param-reassign */
              settings.data[choiceValue] = autocompleteValue.split(',').filter(String);

              return settings;
            },
            onSuccess(response) {
              response.forEach((item) => {
                menuElement.append((
                  $(`<div class="item" data-value="${item[choiceValue]}">${item[choiceName]}</div>`)
                ));
              });
            },
          });
        }

        window.setTimeout(() => {
          element.dropdown('set selected', element.find('input.autocomplete').val().split(',').filter(String));
        }, 5000);

      });
    },
  });
})(jQuery);
