jQuery( document ).ready( function () {

    // Listener for Ethnicity Field.
    jQuery('li.gchoice_8_146_2').click(function() {
        // Disable inputs.
        jQuery('li.gchoice_8_146_0 input').attr('disabled', true);
        jQuery('li.gchoice_8_146_1 input').attr('disabled', true);
    });
    
    // Listener for Race Field.
    jQuery('li.gchoice_8_155_1').click(function() {
        // Disable inputs.
        jQuery('li.gchoice_8_149_1 input').attr('disabled', true);
        jQuery('li.gchoice_8_151_1 input').attr('disabled', true);
        jQuery('li.gchoice_8_152_1 input').attr('disabled', true);
        jQuery('li.gchoice_8_153_1 input').attr('disabled', true);
        jQuery('li.gchoice_8_154_1 input').attr('disabled', true);
    });
    
    // Listener for Sex Field.
    jQuery('li.gchoice_8_148_2').click(function() {
        // Disable inputs.
        jQuery('li.gchoice_8_148_0 input').attr('disabled', true);
        jQuery('li.gchoice_8_148_1 input').attr('disabled', true);
    });
});