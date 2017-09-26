(function($) {
	$(document).ready(function() {
		$schedule_interval_name = 'wp_auto_updater_options[schedule][interval]';

		$('input[name="' + $schedule_interval_name + '"]:radio').on( 'change', function(e) {
			$interval = $( this ).val();
			_form_controller($interval);
		});

		function init() {
			$interval = $('input[name="' + $schedule_interval_name + '"]:radio:checked').val();
			_form_controller($interval);
		}

		function _form_controller($interval) {
			if ( $interval == 'twicedaily' ) {
				$('.schedule_day').hide();
				$('.schedule_weekday').hide();
			}
			else if ( $interval == 'daily' ) {
				$('.schedule_day').hide();
				$('.schedule_weekday').hide();
			}
			else if ( $interval == 'weekly' ) {
				$('.schedule_day').hide();
				$('.schedule_weekday').show();
			}
			else if ( $interval == 'monthly' ) {
				$('.schedule_day').show();
				$('.schedule_weekday').hide();
			}
		}

		init();
	});
})(jQuery);
