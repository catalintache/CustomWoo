jQuery(document).ready(function($) {
    $(document).on('click', '.quantity .plus', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        var $input = $(this).siblings('input.qty');
        var currentVal = parseInt($input.val(), 10) || 0;
        // La primul pas adăugăm 39 (1 + 39 = 40), apoi 40 la fiecare clic
        var addition = currentVal === 1 ? 39 : 40;
        $input.val(currentVal + addition).trigger('change');
    });

    $(document).on('click', '.quantity .minus', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        var $input = $(this).siblings('input.qty');
        var currentVal = parseInt($input.val(), 10) || 0;
        if ( currentVal === 40 ) {
            $input.val(1).trigger('change');
        } else if ( currentVal > 40 ) {
            $input.val(currentVal - 40).trigger('change');
        }
    });
});