jQuery(function ($) {
	'use strict';
	// Enable continue button when required fields are filled.
    $('.wz-bel-setup-content').on('change', 'input, select, textarea', function () {
	var form = $(this).closest('form');
	var continue_btn = form.find('.button-next');
	var required_inputs = form.find('input[required], select[required], textarea[required]');
	var is_complete = true;
	required_inputs.each(function () {
	if (!$(this).val()) {
	is_complete = false;
	return false;
}


}

);
	continue_btn.prop('disabled', !is_complete);
}

);
	// Confirm skip setup for "Not right now" button only.
    $('#wz-bel-not-now').on('click', function () {
	if (confirm(wzBelWizard.skip_setup)) {
	window.location.href = wzBelWizard.settings_url;
}


}

);
	// Add smooth scrolling for step navigation.
    $('.wz-bel-setup-steps li').on('click', function () {
	var step = $(this).data('step');
	if (step && $(this).hasClass('done')) {
	window.location.href = wzBelWizard.settings_url + '&step=' + step;
}


}

);
}

);
