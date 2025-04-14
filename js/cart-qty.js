jQuery(document).ready(function($) {
    function applyCustomAttributes() {
        $('td.product-quantity .quantity').each(function(){
            var $div = $(this);
            var $input = $div.find('input.qty');
            var $label = $div.find('label.screen-reader-text');
            var labelText = $label.text() || "";
            if (!$input.length) return;
            if (!$input.data('incrementSet')) {
                if ( labelText.indexOf("Peleți") !== -1 ) {
                    $input.attr({
                        'data-increment': 66,
                        'data-min': 66,
                        'step': 66,
                        'min': 66
                    }).data('incrementSet', true);
                    $div.find('.plus').val('+1 palet').text('+1 palet');
                    $div.find('.minus').val('-1 palet').text('-1 palet');
                } else if ( labelText.indexOf("Brichete") !== -1 ) {
                    $input.attr({
                        'data-increment': 40,
                        'data-min': 40,
                        'step': 40,
                        'min': 40
                    }).data('incrementSet', true);
                    $div.find('.plus').val('+1 palet').text('+1 palet');
                    $div.find('.minus').val('-1 palet').text('-1 palet');
                } else {
                    $input.attr({
                        'data-increment': 1,
                        'data-min': 1,
                        'step': 1,
                        'min': 1
                    }).data('incrementSet', true);
                    $div.find('.plus').val('+').text('+');
                    $div.find('.minus').val('-').text('-');
                }
            }
        });
    }
    
    setTimeout(applyCustomAttributes, 500);
    $(document.body).on('updated_wc_div', function(){
        setTimeout(applyCustomAttributes, 500);
    });
    
    $('td.product-quantity .quantity')
        .off('click.customCart', '.plus')
        .on('click.customCart', '.plus', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var $input = $btn.siblings('input.qty');
            if (!$input.length) return;
            var currentVal = parseInt($input.val(), 10) || 0;
            var inc = parseInt($input.attr('data-increment'), 10) || 1;
            var targetVal = currentVal + inc;
            console.log("Plus clicked. Current: " + currentVal + " | Increment: " + inc + " -> Target: " + targetVal);
            // Ascunde input-ul pentru a nu afișa modificarea intermediară
            $input.css('opacity', 0);
            // Setează valoarea și declanșează change/blur
            $input.val(targetVal).trigger('change').trigger('blur');
            // După 150ms, setează valoarea finală și revine la opacitate 1
            setTimeout(function(){
                $input.val(targetVal).trigger('change').trigger('blur');
                $input.css('opacity', 1);
            }, 150);
        })
        .off('click.customCart', '.minus')
        .on('click.customCart', '.minus', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var $input = $btn.siblings('input.qty');
            if (!$input.length) return;
            var currentVal = parseInt($input.val(), 10) || 0;
            var inc = parseInt($input.attr('data-increment'), 10) || 1;
            var minValue = parseInt($input.attr('data-min'), 10) || 1;
            var targetVal = currentVal - inc;
            if(targetVal < minValue) {
                targetVal = minValue;
            }
            console.log("Minus clicked. Current: " + currentVal + " | Increment: " + inc + " | Min: " + minValue + " -> Target: " + targetVal);
            $input.css('opacity', 0);
            $input.val(targetVal).trigger('change').trigger('blur');
            setTimeout(function(){
                $input.val(targetVal).trigger('change').trigger('blur');
                $input.css('opacity', 1);
            }, 150);
        });
});