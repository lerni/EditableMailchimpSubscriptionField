(function($) {
	$checkboxes = $('.newsletter-group');
	if (!$('.newsletter-toggle').attr("checked")) {
		$checkboxes.hide();
	}
	$('.newsletter-toggle').change(function () {
		console.log($(this).attr("checked"))
		if ($(this).attr("checked")) {
			$checkboxes.slideDown();
		} else {
			$checkboxes.slideUp();
		}
	});
})(jQuery);