jQuery(document).ready(function($) {
    $(document).on('click', '.quantity .plus', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        var $input = $(this).siblings('input.qty');
        var currentVal = parseInt($input.val(), 10) || 0;
        // La primul pas adăugăm 65 (1 + 65 = 66), apoi 66 la fiecare clic
        var addition = currentVal === 1 ? 65 : 66;
        $input.val(currentVal + addition).trigger('change');
    });

    $(document).on('click', '.quantity .minus', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        var $input = $(this).siblings('input.qty');
        var currentVal = parseInt($input.val(), 10) || 0;
        if ( currentVal === 66 ) {
            $input.val(1).trigger('change');
        } else if ( currentVal > 66 ) {
            $input.val(currentVal - 66).trigger('change');
        }
    });
});