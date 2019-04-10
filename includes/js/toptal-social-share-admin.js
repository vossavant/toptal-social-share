jQuery(function($) {
	$('[name="ttss_icon_color"]').change(function() {
		$('.ttss-colorpicker__wrap').toggleClass('ttss-visible');
	});

	if ($('[name="ttss_icon_color"][value!="default"]').is(':checked')) {
		$('.ttss-colorpicker__wrap').addClass('ttss-visible');
	}

	$('.ttss-colorpicker__input').spectrum({
		color: $('.ttss-custom-color').val(),
		preferredFormat: "hex3",
		showInput: true,
		showInitial: true,

		change: function(color) {
			$('.ttss-custom-color').val(color.toHexString());
		}
	});

	$('.ttss-ui-sortable').sortable({
		axis: 'y',
		cursor: 'move',
		opacity: 0.6,
		update: function () {
			var order = $(this).sortable('toArray').filter(Boolean),
				sortableList = $('.ttss-ui-sortable'),
				networkName,
				networkOrder,
				networkHiddenField;

			$.each(order, function(index, value) {
				networkName = sortableList.find($('#' + value)).find('.ttss-ui-sortable__label').text();
				networkHiddenField = sortableList.find('[type="hidden"][value="' + networkName + '"]');
				networkOrder = networkHiddenField.attr('name');
				networkHiddenField.attr('name', 'ttss_icon_order[' + index + ']');
			});
		}
	});
});