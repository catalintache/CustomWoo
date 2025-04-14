jQuery(document).ready(function($) {
    // Funcția updatePrice: calculează prețul actualizat pe baza cantității și evidențiază rândul activ
    function updatePrice() {
        var $priceElement = $('.product-price');
        if ($priceElement.length === 0) return;
        
        var basePrice = parseFloat($priceElement.data('base-price'));
        if (isNaN(basePrice)) return;
        
        // Preluăm discount tiers din atributul data-discount-tiers (format JSON)
        var tiers = $priceElement.data('discount-tiers');
        if (typeof tiers === 'string') {
            try {
                tiers = JSON.parse(tiers);
            } catch(e) {
                tiers = {};
            }
        }
        
        var $qtyInput = $('input.qty');
        if ($qtyInput.length === 0) return;
        var currentQty = parseInt($qtyInput.val(), 10);
        if (isNaN(currentQty) || currentQty < 1) currentQty = 1;
        
        console.log("updatePrice: currentQty = " + currentQty);
        
        // Calculăm factorul de discount pe baza pragurilor definite în tiers
        var discountFactor = 1;
        var thresholds = Object.keys(tiers).map(function(key) {
            return parseInt(key, 10);
        });
        thresholds.sort(function(a, b) { return a - b; });
        for (var i = 0; i < thresholds.length; i++) {
            if (currentQty >= thresholds[i]) {
                discountFactor = tiers[ thresholds[i] ];
            }
        }
        
        var newPrice = basePrice * discountFactor;
        $priceElement.text(newPrice.toFixed(2) + ' lei');
        
        // Evidențiem rândul activ în tabelul de discounturi
        $('.custom-discount-table tbody tr').removeClass('active');
        $('.custom-discount-table tbody tr').each(function(){
            var min = parseInt($(this).data('min'), 10);
            var max = parseInt($(this).data('max'), 10);
            console.log("Row: min = " + min + ", max = " + max);
            if (currentQty >= min && currentQty <= max) {
                $(this).addClass('active');
                console.log("Active row set for range " + min + " - " + max);
            }
        });
    }
    
    // Evenimente pentru inputul de cantitate (schimbare, input, keyup)
    $(document).on('change input keyup', 'input.qty', updatePrice);
    // Pentru butoanele de plus și minus, folosim un timeout pentru a actualiza prețul după modificare
    $(document).on('click', '.quantity .plus, .quantity .minus', function(){
        setTimeout(updatePrice, 150);
    });
    
    // Evenimentul de click pe rândurile din tabelul de discounturi
    // Folosim event delegation pe containerul cu clasa "custom-discount-table"
    $(document).on('click', '.custom-discount-table tbody tr', function(e) {
        e.preventDefault();
        e.stopPropagation();
        // În cadrul tabelului curent, eliminăm clasa "active" de pe toate rândurile
        $(this).closest('table').find('tbody tr').removeClass('active');
        // Adăugăm clasa "active" pe rândul selectat
        $(this).addClass('active');
        
        // Preluăm valoarea minimă din atributul data-min al rândului
        var minQty = parseInt($(this).attr('data-min'), 10);
        console.log("Row clicked: data-min = " + minQty);
        if (!isNaN(minQty) && minQty > 0) {
            // Setăm inputul de cantitate la valoarea minimă și declanșăm evenimentul change
            $('input.qty').val(minQty).trigger('change');
        }
    });
    
    // Apelăm updatePrice la încărcarea paginii pentru a inițializa totul
    updatePrice();
});