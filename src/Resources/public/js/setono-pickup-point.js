let pickupPoints = {
  pickupPointShippingMethods: document.querySelectorAll('input.input-shipping-method[data-pickup-point-provider]'),
  pickupPointsField: document.querySelectorAll('div.setono-sylius-pickup-point-field')[0],
  pickupPointsFieldInput: document.querySelectorAll('div.setono-sylius-pickup-point-field > input.setono-sylius-pickup-point-field-input')[0],
  pickupPointsFieldChoices: document.querySelectorAll('div.setono-sylius-pickup-point-field-choices')[0],
  pickupPointsFieldChoicePrototype: document.querySelectorAll('div.setono-sylius-pickup-point-field-choice-prototype')[0].innerHTML,
  shippingMethods: document.querySelectorAll('input.input-shipping-method'),
  pickupPointChoices: {},
  init: function (args) {
    this.searchUrl = args.searchUrl;
  },
  searchAndStorePickupPoints: function (input) {
    self.pickupPointChoices[input.getAttribute('value')] = {};
    let pickupPointChoices = this.pickupPointChoices;
    let inputSearchUrl = this.searchUrl;
    inputSearchUrl = inputSearchUrl.replace('{providerCode}', input.getAttribute('data-pickup-point-provider'));
    inputSearchUrl = inputSearchUrl.replace('{_csrf_token}', input.getAttribute('data-csrf-token'));

    const xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
      if (xhttp.readyState === 4) {
        pickupPointChoices[input.getAttribute('value')] = JSON.parse(xhttp.response);
      }
    }
    // Use synchronous xhttp request since we need the result to continue the process
    xhttp.open('GET', inputSearchUrl, false);
    xhttp.send();

    self.pickupPointChoices = pickupPointChoices;
  },
  valuesToRadio(values) {
    let content = ``;

    values.forEach(function (value) {
      let radio = self.pickupPointsFieldChoicePrototype.replaceAll('{value}', value.id);
      radio = radio.replaceAll('{label}', value.location);

      content += radio;
    });

    return content;
  },
  process: function () {
    self = this;
    self.pickupPointShippingMethods.forEach(function (element) {
      self.searchAndStorePickupPoints(element);
    });

    self.shippingMethods.forEach(function (element) {
      element.addEventListener('change', function () {
        let selectedElement = document.querySelectorAll('input.input-shipping-method:checked');
        selectedElement = selectedElement[0];
        const values = self.pickupPointChoices[selectedElement.getAttribute('value')];
        if (values !== undefined && values.length > 0) {
          self.pickupPointsField.style.display = 'block';
          self.pickupPointsFieldChoices.innerHTML = self.valuesToRadio(values);

          if (self.pickupPointsFieldInput.value !== undefined && self.pickupPointsFieldInput.value.length) {
            document.querySelectorAll(`input.setono-sylius-pickup-point-field-choice-field[value="${self.pickupPointsFieldInput.value}"]`)[0].checked = true;
          }

          const choices = document.querySelectorAll('input.setono-sylius-pickup-point-field-choice-field');
          choices.forEach(function (choice) {
            choice.addEventListener('change', function () {
              self.pickupPointsFieldInput.value = choice.getAttribute('value');
            });
          });
        } else {
          self.pickupPointsField.style.display = 'none';
          self.pickupPointsFieldChoices.innerHTML = '';
        }
      });
      const changeEvent = new Event('change');
      element.dispatchEvent(changeEvent);
    });
  }
};
