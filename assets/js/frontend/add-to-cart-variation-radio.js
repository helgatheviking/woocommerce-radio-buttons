jQuery(document).ready(function($) {
 
    /**
     * Variations form handling
     */
    $( '.variations_form' )

        .on( 'wc_variation_form', function( event, form ) {
            $( this ).trigger( 'check_radio_variations' );
        } )
             
         // Upon changing an option
        .on( 'change', '.variations input:radio', function( event ) {

            var $form    = $(this).closest( '.variations_form' );
            var useAjax  = false === $form.data( 'product_variations' );

            $form.find( 'input[name="variation_id"], input.variation_id' ).val( '' ).trigger( 'change' );
            $form.find( '.wc-no-matching-variations' ).remove();

            if ( useAjax ) {
                $form.trigger( 'check_radio_variations' );
            } else {
                $form.trigger( 'woocommerce_variation_select_change' );
                $form.trigger( 'check_radio_variations' );
            }

            // Custom event for when variation selection has been changed
            $form.trigger( 'woocommerce_variation_has_changed' );
     
        } )

 
        // On clicking the reset variation button
        .on( 'click', '.reset_variations', function( event ) {

            event.preventDefault();

            $(this).closest('.variations_form').find( '.variations .radio__variations--list' ).each( function() {
                $(this).find('input:radio').prop( 'checked', false );

                var $first_input = $(this).find( '.radio__variations--item:first-child input:radio' );

                if( '' === $first_input.val() ) {
                    $first_input.prop( 'checked', true );
                }
                $first_input.trigger( 'change' );

            } );
        } )

        // Custom callback to tell Woo to check variations with our radio attributes.
        .on( 'check_radio_variations', function ( event ) {
            var chosenAttributes = radioGetChosenAttributes( $(this) );
             $(this).trigger( 'check_variations', chosenAttributes );
        } );


         /**
         * Get chosen attributes from form.
         * @return array
         */
        var radioGetChosenAttributes = function( $form ) {
            var data   = {};
            var count  = 0;
            var chosen = 0;

            $form.find( '.variations .radio__variations--list' ).each( function() {
                var attribute_name = $( this ).data( 'attribute_name' ) || $( this ).find( 'input:radio' ).first().attr( 'name' );
                var value          = $( this ).find( 'input:checked' ).val() || '';

                if ( value.length > 0 ) {
                    chosen ++;
                }

                count ++;
                data[ attribute_name ] = value;
            });

            return {
                'count'      : count,
                'chosenCount': chosen,
                'data'       : data
            };
        };
});