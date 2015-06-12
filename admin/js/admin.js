jQuery(function() {
	/* -------- Sortable rows for the new photos -----------------------------*/
	jQuery('.sort').sortable({
		cursor: 'move',
		axis:   'y',
		placeholder: 'tr-placeholder',
		stop: function(event, table) {
			jQuery('tbody .date').each(function( index ) {
				jQuery(this).val(index);
			});
		}
	});
	
	/* -------- Autocomplete for the description -----------------------------*/
	var availableDescriptions = [];
    jQuery('.description-autocomplete')
	.autocomplete({
		source: availableDescriptions
    })
    .focusout(function() {
		var val = jQuery(this).val();
		if (val.length > 0 && jQuery.inArray(val, availableDescriptions) == -1) {
			availableDescriptions.push(val);
		}
    });
});
