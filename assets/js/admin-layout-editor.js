(function($) {

$(document).on('ready', function() {

	var element = $('#acf-field_57dab64ad6e4a')

	var block = $('#wpb_block_metabox .blocks .block:first-child')
	var blocks = $('#wpb_block_metabox .blocks')

	if (block.length) {

		var buid = block.attr('data-block-buid')
		if (buid) {
			element.find('[value="' + buid + '"]').attr('selected', 'selected')
		}

	}

	element.on('change', function() {

		var buid = $(this).val()

		if (block.length == 0) {

			wpb_appendBlock(blocks, buid, null, null, function(result) {
				block = result
			})

			return
		}

		var postId = block.attr('data-post-id')
		var pageId = block.attr('data-page-id')

		wpb_replaceBlock(postId, buid, function(result) {
			block = result
		})
	})

})

})(jQuery);