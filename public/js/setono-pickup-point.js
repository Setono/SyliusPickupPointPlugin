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
    this.searchUrl = args.searchUrl;

    if (0 === this.pickupPointShippingMethods.length) {
      return;
    }

    this.pickupPointShippingMethods.forEach((element) => {
      this.searchAndStorePickupPoints(element);
    });

    this.shippingMethods.forEach((element) => {
      element.addEventListener('change', () => {
        if (0 !== this.pickupPointsFieldInput.value.length) {
          this.lastChosenPickupPointId = this.pickupPointsFieldInput.value;
        }
        this.pickupPointsFieldInput.value = null;
        this.render();
      });
    });

    this.render();
  },
  searchAndStorePickupPoints: function (input) {
    let shippingMethodCode = input.getAttribute('value');
    this.pickupPointChoices[shippingMethodCode] = {};

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

    this.pickupPointChoices = pickupPointChoices;
  },
  render: function () {
    let selectedElement = document.querySelectorAll('input.input-shipping-method:checked');
    selectedElement = selectedElement[0];
    let currentShippingMethodCode = selectedElement.getAttribute('value');

    const values = this.pickupPointChoices[currentShippingMethodCode];
    if (undefined === values || undefined === values.length || 0 === values.length) {
      this.pickupPointsField.style.display = 'none';
      this.pickupPointsFieldChoices.innerHTML = '';
      return;
    }

    this.pickupPointsField.style.display = 'block';
    this.pickupPointsFieldChoices.innerHTML = this.valuesToRadio(values);

    var currentPickupPointId = this.pickupPointsFieldInput.value;
    if (null === currentPickupPointId || 0 === currentPickupPointId.length) {
      currentPickupPointId = this.lastChosenPickupPointId;
    }

    var currentPickupPointRadio = document.querySelector(`input.setono-sylius-pickup-point-field-choice-field[value="${currentPickupPointId}"]`);
    if (null !== currentPickupPointRadio) {
      currentPickupPointRadio.checked = true;
    }

    const choices = document.querySelectorAll('input.setono-sylius-pickup-point-field-choice-field');
    choices.forEach((choice) => {
      choice.addEventListener('change', () => {
        this.pickupPointsFieldInput.value = choice.getAttribute('value');
      });
    });
  },
  valuesToRadio(values) {
    let content = ``;

    values.forEach((value) => {
      let prototype = this.pickupPointsFieldChoicePrototype.innerHTML;
      let radio = prototype.replace(/{code}/g, value.code);
      radio = radio.replace(/{name}/g, value.name);
      radio = radio.replace(/{full_address}/g, value.full_address);
      radio = radio.replace(/{latitude}/g, value.latitude);
      radio = radio.replace(/{longitude}/g, value.longitude);

      content += radio;
    });

    return content;
  },
};
