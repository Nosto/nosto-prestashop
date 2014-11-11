$(function() {
    if (typeof nostojs === 'function') {
        nostojs(function(api) {
            api.listen("postrender", function() {
                var $center_column = $('#center_column'),
                    $hidden_elements = $('#hidden_nosto_elements'),
                    reloadRecommendations = false;
                if ($center_column && $hidden_elements) {
                    $hidden_elements.find('.prepend .hidden_nosto_element').each(function() {
                        var $slot = $(this),
                            nostoId = $slot.data('nosto-id');
                        if (nostoId && !$('#'+nostoId).length) {
                            $slot.attr('id', nostoId);
                            $slot.attr('class', 'nosto_element');
                            $slot.prependTo($center_column);
                            reloadRecommendations = true;
                        }
                    });
                    $hidden_elements.find('.append .hidden_nosto_element').each(function() {
                        var $slot = $(this),
                            nostoId = $slot.data('nosto-id');
                        if (nostoId && !$('#'+nostoId).length) {
                            $slot.attr('id', nostoId);
                            $slot.attr('class', 'nosto_element');
                            $slot.appendTo($center_column);
                            reloadRecommendations = true;
                        }
                    });
                }
                $hidden_elements.remove();
                if (reloadRecommendations) {
                    api.loadRecommendations();
                }
            });
        });
    }
});