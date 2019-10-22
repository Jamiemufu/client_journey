//work around for IE oninvalid not being supported
function validate(element) {
    if (element.name == 'car_reg' && element.value.length > 0 && element.value.length < 4) {
        element.setCustomValidity("Please enter at least 4 characters OR leave this field blank")
    } 
    else {
        element.setCustomValidity("");
    }
}