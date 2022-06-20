$(document).ready(function() {
	if ($('#history').length > 0) {
		$('#history').DataTable({
			columnDefs: [
				{
					target: 0,
					visible: false
				}
			],
			searching: false
		});
	}
	
	if ($('#calculator_form').length > 0 && $('#form_is_custom').val() == '1') {
		$('#form_vat_rate').removeAttr('readonly').attr('required', 'required');
		$('#form_country_rate').parent().hide();
		$('#type_switcher').prop('checked', 'checked');
	}
	
	$('.clc-sbm').click(function() {
		$('.clc-sbm').removeAttr('data-clicked');
		$(this).attr('data-clicked', 1);
	});
	
	$('#warning_delete, #success_delete').delay(3200).fadeOut(300);
	
	$('#calculator_form').submit(function(event) {
		event.preventDefault();
		var $btn = $(this).find('.clc-sbm[data-clicked=1]');
		if ($btn.length > 0 && $btn.attr('id')) {
			var $clicker = $('#calculator_form').find('#form_' + $btn.attr('id'));
			if ($clicker.length > 0) $clicker.val(1);
		}
		$(this).unbind('submit').submit();
	});
	
	
	$('#form_based_on, #form_vat_rate').on('keydown keyup', function(event) {
		var keyCode = (event.which) ? event.which : event.keyCode;
		var excludedKeys = [8, 37, 39, 46, 110, 190];
		if (!((keyCode >= 48 && keyCode <= 57) || (keyCode >= 96 && keyCode <= 105) || (excludedKeys.includes(keyCode)))) {
			event.preventDefault();
		}
	});
	
	$('#form_vat_rate').on('change', function(event) {
		if ($(this).val() > 100) {
			$(this).val(100);
		} else if ($(this).val() < 0) {
			$(this).val(0);
		}
	});
	
	$('#results').submit(function(event) {
		event.preventDefault();
	});
	
	$('#history_form').submit(function(event) {
		event.preventDefault();
		var $btn = $(this).find('#delete_history[data-clicked=1], #download_history[data-clicked=1]');
		if ($btn.length > 0 && $btn.attr('id')) {
			var $action = $('#history_form').find('#action');
			if ($action.length > 0) {
				if ($btn.attr('id') == 'delete_history') {
					var to_delete = confirm('Are you sure you want to delete all the calculations?');
					if (to_delete) {
						$action.val('delete');
						$(this).unbind('submit').submit();
					}
				} else {
					$.ajax({
						url : '/download', 
						type: 'POST',
						success: function(data){
							var downloadLink = document.createElement('a');
							var fileData = ['\ufeff'+data];

							var blobObject = new Blob(fileData,{
								 type: 'text/csv;charset=utf-8;'
							 });
							var url = URL.createObjectURL(blobObject);
							downloadLink.href = url;
							downloadLink.download = 'calculations.csv';
							document.body.appendChild(downloadLink);
							downloadLink.click();
							document.body.removeChild(downloadLink);
						}
					});
				}
				
			}
		}
	});
	
	$('#form_country_rate').change(function() {
		var $country = $(this).find(':selected');
		var $vat_rate = $('#form_vat_rate');
		if ($vat_rate.length > 0) {
			if ($country.val() !== '') {
				$vat_rate.val(parseFloat($country.attr('data-rate'), 10));
			} else {
				$vat_rate.val('');
			}
		}
	});
	
	 $('#type_switcher').change(function() {
		var $vat_rate = $('#form_vat_rate');
		var $country_rate = $('#form_country_rate');
		var $is_custom = $('#form_is_custom');
		if ($vat_rate.length > 0 && $country_rate.length > 0 && $is_custom.length > 0) {
			$vat_rate.val('');
			$country_rate.val('');
			if($(this).is(':checked')) {
				$vat_rate.removeAttr('readonly').attr('required', 'required');
				$country_rate.parent().hide();
				$country_rate.removeAttr('required');
				$is_custom.val(1);
			} else {
				$('#form_vat_rate').removeAttr('required').attr('readonly', 'readonly');
				$country_rate.parent().show();
				$is_custom.val(0);
			}
		}
	});
	
	$('#delete_history, #download_history').click(function() {
		$('#delete_history, #download_history').removeAttr('data-clicked');
		$(this).attr('data-clicked', 1);
	});
});