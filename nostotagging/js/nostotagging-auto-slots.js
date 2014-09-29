$(function() {
    var $center_column=$('#center_column'),
        $hidden_elements=$('#hidden_nosto_elements');
    if($center_column && $hidden_elements) {
        $hidden_elements.find('.prepend .nosto_element').each(function() {
            if (!$center_column.find('#'+$(this).attr('id')).length)
                $(this).prependTo($center_column);
        });
        $hidden_elements.find('.append .nosto_element').each(function() {
            if (!$center_column.find('#'+$(this).attr('id')).length)
                $(this).appendTo($center_column);
        });
        $hidden_elements.remove();
    }
});