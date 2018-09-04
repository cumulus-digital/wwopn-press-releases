(function($, window, undefined){

	/**
	 * Enable datepickers
	 */
	$(function() {
		$('.datepicker').datepicker();
	})

	/**
	 * Handle autosaving custom metafield data
	 */
	$(function(){

		var $wpn_metas = $([
			'.wpn_meta_autosave input',
			'.wpn_meta_autosave select',
			'.wpn_meta_autosave textarea'
		].join(', '));

		// Store original loaded values
		$wpn_metas.each(function(){
			var $this = $(this);
			$this.data('original', $this.val());
		});

		// any time the meta fields change, register an autosave
		$wpn_metas.on('change keyup', function() {
			var $this = $(this),
				original = $this.data('original');

			if ($this.val() !== original) {
				shouldConfirmLeave = true;
				$this.data('do_autosave', true);
			}
		});

		// Hook into WP's autosave heartbeat
		$(window.document).on('heartbeat-tick.autosave', function() {
			if ( ! ajaxurl) {
				return;
			}
			var changed = false,
				post_id = $('#post_ID').val(),
				post_data = {
					post_ID: post_id,
					action: 'autosave_wwopn_pressreleases_meta',
				};

			// Determine which meta fields we need to save
			$wpn_metas.each(function() {
				var $meta = $(this);
				if ($meta.data('do_autosave')) {
					post_data[$meta.attr('name')] = $meta.val();
					changed = true;
				}
			});

			if (changed) {
				console.log(post_data);
				// Values have changed, save them
				$.ajax({
					data: post_data,
					type: 'POST',
					url: ajaxurl,
					complete: function() {
						shouldConfirmLeave = false;
					},
				});
			}
		});

		// prompt before leaving
		var shouldConfirmLeave = false;
		$(window).on('beforeunload', function() {
			if (shouldConfirmLeave) {
				return 'You have unsaved changes, are you sure you want to leave?';
			}
			return;
		});
		$('#submitpost input, #submitpost .submitdelete')
			.on('click', function() {
				shouldConfirmLeave = false;
			});

	});

}(jQuery, window.self));