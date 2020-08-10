let pickupPoints = {
  pickupPointShippingMethods: document.querySelectorAll('input.input-shipping-method[data-pickup-point-provider]'),
  pickupPointsField: document.querySelectorAll('div.setono-sylius-pickup-point-field')[0],
  pickupPointsFieldInput: document.querySelectorAll('div.setono-sylius-pickup-point-field > input.setono-sylius-pickup-point-field-input')[0],
  pickupPointsFieldChoices: document.querySelectorAll('div.setono-sylius-pickup-point-field-choices')[0],
  pickupPointsFieldChoicePrototype: document.querySelectorAll('div.setono-sylius-pickup-point-field-choice-prototype')[0],
  shippingMethods: document.querySelectorAll('input.input-shipping-method'),
  pickupPointChoices: {},
  lastChosenPickupPointId: null,
  init: function (args) {
    self = this;
    self.searchUrl = args.searchUrl;

    if (0 === self.pickupPointShippingMethods.length) {
      return;
    }

    self.pickupPointShippingMethods.forEach(function (element) {
      self.searchAndStorePickupPoints(element);
    });

    self.shippingMethods.forEach(function (element) {
      element.addEventListener('change', function () {
        if (0 !== self.pickupPointsFieldInput.value.length) {
          self.lastChosenPickupPointId = self.pickupPointsFieldInput.value;
        }
        self.pickupPointsFieldInput.value = null;
        self.render();
      });
    });

    self.render();
  },
  searchAndStorePickupPoints: function (input) {
    let shippingMethodCode = input.getAttribute('value');
    self.pickupPointChoices[shippingMethodCode] = {};

    let pickupPointChoices = this.pickupPointChoices;
    let inputSearchUrl = this.searchUrl;
    inputSearchUrl = inputSearchUrl.replace('{providerCode}', input.getAttribute('data-pickup-point-provider'));
    inputSearchUrl = inputSearchUrl.replace('{_csrf_token}', input.getAttribute('data-csrf-token'));

    const xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
      if (4 === xhttp.readyState && 200 === xhttp.status) {
        pickupPointChoices[shippingMethodCode] = JSON.parse(xhttp.response);
      }
    }
    // Use synchronous xhttp request since we need the result to continue the process
    // @todo Convert to async as synchronous requests deprecated by browsers
    xhttp.open('GET', inputSearchUrl, false);
    xhttp.send();

    self.pickupPointChoices = pickupPointChoices;
  },
  render: function () {
    let selectedElement = document.querySelectorAll('input.input-shipping-method:checked');
    selectedElement = selectedElement[0];
    let currentShippingMethodCode = selectedElement.getAttribute('value');

    const values = self.pickupPointChoices[currentShippingMethodCode];
    if (undefined === values || undefined === values.length || 0 === values.length) {
      self.pickupPointsField.style.display = 'none';
      self.pickupPointsFieldChoices.innerHTML = '';
      return;
    }

    self.pickupPointsField.style.display = 'block';
    self.pickupPointsFieldChoices.innerHTML = self.valuesToRadio(values);

    var currentPickupPointId = self.pickupPointsFieldInput.value;
    if (null === currentPickupPointId || 0 === currentPickupPointId.length) {
      currentPickupPointId = self.lastChosenPickupPointId;
    }

    var currentPickupPointRadio = document.querySelector(`input.setono-sylius-pickup-point-field-choice-field[value="${currentPickupPointId}"]`);
    if (null !== currentPickupPointRadio) {
      currentPickupPointRadio.checked = true;
    }

    const choices = document.querySelectorAll('input.setono-sylius-pickup-point-field-choice-field');
    choices.forEach(function (choice) {
      choice.addEventListener('change', function () {
        self.pickupPointsFieldInput.value = choice.getAttribute('value');
      });
    });
  },
  valuesToRadio(values) {
    let content = ``;

    values.forEach(function (value) {
      let prototype = self.pickupPointsFieldChoicePrototype.innerHTML;
      let radio = prototype.replace(/{value}/g, value.id);
      radio = radio.replace(/{label}/g, value.location);

      content += radio;
    });

    return content;
  },
};
