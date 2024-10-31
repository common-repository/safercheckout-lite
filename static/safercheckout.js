/**
 +===========================================================================+
 |    ____         __            ____ _               _               _      |
 |   / ___|  __ _ / _| ___ _ __ / ___| |__   ___  ___| | _____  _   _| |_    |
 |   \___ \ / _` | |_ / _ \ '__| |   | '_ \ / _ \/ __| |/ / _ \| | | | __|   |
 |    ___) | (_| |  _|  __/ |  | |___| | | |  __/ (__|   < (_) | |_| | |_    |
 |   |____/ \__,_|_|  \___|_|   \____|_| |_|\___|\___|_|\_\___/ \__,_|\__|   |
 |                                                                           |
 | (c) NinTechNet Limited ~ https://nintechnet.com/safercheckout/            |
 +===========================================================================+ 2024-09-30
*/

jQuery( document ).ready(function( $ ) {
	'use strict';

	/**
	 * TipTip tooltip.
	 */
	$('.woocommerce-help-tip').tipTip( {'attribute': 'aria-label'} );

	/**
	 * Order threshold values.
	 */
	$('#safercheckout_order_price_threshold_1').change(function() {
		if ( parseInt( $('#safercheckout_order_price_threshold_1').val() ) > parseInt( $('#safercheckout_order_price_threshold_2').val() ) ) {
			$('#safercheckout_order_price_threshold_2').val( $('#safercheckout_order_price_threshold_1').val()  );
		}
	});
	$('#safercheckout_order_price_threshold_2').change(function() {
		if ( parseInt( $('#safercheckout_order_price_threshold_1').val() ) > parseInt( $('#safercheckout_order_price_threshold_2').val() ) ) {
			$('#safercheckout_order_price_threshold_1').val( $('#safercheckout_order_price_threshold_2').val() );
		}
	});
	$('#safercheckout_order_quantity_threshold_1').change(function() {
		if ( parseInt( $('#safercheckout_order_quantity_threshold_1').val() ) > parseInt( $('#safercheckout_order_quantity_threshold_2').val() ) ) {
			$('#safercheckout_order_quantity_threshold_2').val( $('#safercheckout_order_quantity_threshold_1').val()  );
		}
	});
	$('#safercheckout_order_quantity_threshold_2').change(function() {
		if ( parseInt( $('#safercheckout_order_quantity_threshold_1').val() ) > parseInt( $('#safercheckout_order_quantity_threshold_2').val() ) ) {
			$('#safercheckout_order_quantity_threshold_1').val( $('#safercheckout_order_quantity_threshold_2').val() );
		}
	});

	/**
	 * Risk scores.
	 */
	var low_risk		= 'input[type=number][name=safercheckout_score_low]';
	var medium_risk	= 'input[type=number][name=safercheckout_score_medium]';
	var high_risk		= $(medium_risk).val();
	high_risk++;
	var count;
	$(low_risk).change(function() {
		if ( $(low_risk).val() > 98 ) {
			alert( safercheckouti18n.low_max_98 );
			$('#safercheckout_score_low').val(98);
		} else if ( $(low_risk).val() >=  $(medium_risk).val() ) {
			alert( safercheckouti18n.low_max_medium );
			count = $(medium_risk).val();
			count--;
			$('#safercheckout_score_low').val( count );
			$('#sc_score_medium').text( $(medium_risk).val() );
		} else {
			count = $(low_risk).val();
			count++;
			$('#sc_score_medium').text( count );
		}
	});
	$(medium_risk).change(function() {
		if ( $(medium_risk).val() > 99 ) {
			alert( safercheckouti18n.medium_max_99 );
			$('#safercheckout_score_medium').val(99);
		} else if ( $(medium_risk).val() <=  $(low_risk).val() ) {
			alert( safercheckouti18n.medium_max_low );
			count = $(low_risk).val();
			count++;
			$('#safercheckout_score_medium').val( count );
			count++;
			$('#sc_score_high').text( count );
		} else {
			count = $(medium_risk).val();
			count++;
			$('#sc_score_high').text( count );
		}
	});

	/**
	 * Form submission checks (settings page).
	 */
	$('#mainform').on('submit', function() {

		var settings = $('#safercheckout-settings').val();

		// Location
		if ( settings == 'settings-location') {

			$('#geo-apply').css( {
					'color': '#3c434a',
					'background-color': 'unset',
					'border-radius': '0',
					'padding': '0'
			} );
			$('#address-apply').css( {
					'color': '#3c434a',
					'background-color': 'unset',
					'border-radius': '0',
					'padding': '0'
			} );

			if ( $('#total-items').text() > 0 ) {
				if (! $('#safercheckout_address_blacklist_ip').prop('checked') &&
					! $('#safercheckout_address_blacklist_bill').prop('checked') &&
					! $('#safercheckout_address_blacklist_ship').prop('checked')
				) {
					alert( safercheckouti18n.geo_error );

					$('#geo-apply').css( {
						'color': 'white',
						'background-color': '#d63638',
						'border-radius': '3px',
						'padding': '2px'
					} );

					return false;
				}
			}
			var address = $('#order_blacklist_address').val().trim(); // Trim user input
			if ( address != '') {
				if (! $('#safercheckout_order_blacklist_bill').prop('checked') &&
					! $('#safercheckout_order_blacklist_ship').prop('checked')
				) {
					alert( safercheckouti18n.address_error );

					$('#address-apply').css( {
						'color': 'white',
						'background-color': '#d63638',
						'border-radius': '3px',
						'padding': '2px'
					} );

					return false;
				}
			}

		// Customer
		} else if ( settings == 'settings-customer') {

			$('#name-apply').css( {
					'color': '#3c434a',
					'background-color': 'unset',
					'border-radius': '0',
					'padding': '0'
			} );

			var name = $('#customer_blacklist_name').val().trim();
			if ( name != '') {
				if (! $('#safercheckout_customer_blacklist_name_billing').prop('checked') &&
					! $('#safercheckout_customer_blacklist_name_shipping').prop('checked')
				) {
					alert( safercheckouti18n.name_error );

					$('#name-apply').css( {
						'color': 'white',
						'background-color': '#d63638',
						'border-radius': '3px',
						'padding': '2px'
					} );

					return false;
				}
			}
		}
	});
});


/**
 * Toggle an element by ID.
 */
function safercheckout_toggle( id ) {
	jQuery('#'+ id).toggle( {duration: 400} );
}

/**
 * Geolocation.
 */
function safercheckout_check( checkall ) {
	var cur_val = 0;
	var nodes = document.getElementById('td-countries').getElementsByTagName('input');
	for(var i = 0; i < nodes.length; i++){
		if ( checkall == 0 ) {
			nodes[i].checked = false;
		} else {
			nodes[i].checked = true;
			++cur_val;
		}
		jQuery('#total-items').text( cur_val );
	}
}
function safercheckout_update_counter( what ) {
	var cur_val = jQuery('#total-items').text();
	if ( what.checked == true ) {
		jQuery('#total-items').text( ++cur_val );
	} else {
		jQuery('#total-items').text( --cur_val );
	}
}

// =====================================================================
// EOF
