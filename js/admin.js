( function() {
	document.querySelectorAll('input[name="wp_auto_updater_options[schedule][interval]"]').forEach( function (el) {
		el.addEventListener( 'change', function(e) {
			_form_controller();
		} );
	} );

	function _form_controller() {
		const $interval = document.querySelector('input[name="wp_auto_updater_options[schedule][interval]"]:checked').value;

		if ( $interval == 'twicedaily' ) {
			document.querySelector('.schedule_day').style.display = 'none';
			document.querySelector('.schedule_weekday').style.display = 'none';
		}
		else if ( $interval == 'daily' ) {
			document.querySelector('.schedule_day').style.display = 'none';
			document.querySelector('.schedule_weekday').style.display = 'none';
		}
		else if ( $interval == 'weekly' ) {
			document.querySelector('.schedule_day').style.display = 'none';
			document.querySelector('.schedule_weekday').style.display = 'block';
		}
		else if ( $interval == 'monthly' ) {
			document.querySelector('.schedule_day').style.display = 'block';
			document.querySelector('.schedule_weekday').style.display = 'none';
		}
	}

	_form_controller();
} )();
