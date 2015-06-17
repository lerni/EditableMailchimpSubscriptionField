(function($) {
	$checkboxes = $('.newsletter-group .fieldgroup .fieldgroup-field:eq(1)');
	if (!$('.newsletter-toggle').attr("checked")) {
		$checkboxes.hide();
	}
	$('.newsletter-toggle').change(function () {
		if ($(this).attr("checked")) {
			$checkboxes.slideDown();
		} else {
			$checkboxes.slideUp();
		}
	});
})(jQuery);