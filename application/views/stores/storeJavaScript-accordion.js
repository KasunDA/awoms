/* Custom Accordion */

(function( $, undefined ) {

// Some classes we work with throughout the accordion
// customization.  Define them here instead of in the
// widget since they're 'static'.
var headerActive = 'ui-accordion-header-active' +
                   ' ui-state-active',
    panelActive = 'ui-accordion-content-active';

$.widget( 'ab.accordion', $.ui.accordion, {

    // Called when a section is expanding or collapsing.
    _eventHandler: function( e ) {

        // Not expandable, do nothing.
        if ( !this.options.expandable ) {
            return this._super( e );
        }

        var header  = $( event.currentTarget ),
            panel   = header.next(),
            expanded = panel.hasClass( panelActive ),
            icons    = this.options.icons;

        e.preventDefault();

        // This forces _toggle() to use the oldPanel argument.
        this.prevShow = $();

        // The section is already expanded, so we're 
        // collapsing.
        if ( expanded ) {

            // There is no new panel when collapsing.  The old
            // panel is the one we're collapsing.
            this._toggle({
                newPanel: $(),
                oldPanel: panel
            });

            header.removeClass( headerActive );
            
            if ( icons ) {
                header.children( '.ui-accordion-header-icon' )
                       .removeClass( icons.activeHeader )
                       .addClass( icons.header );
            }

        // The section is collapsed.  We're expanding.
        } else {

            this.headers.not( header )
                        .removeClass( headerActive );

            // The new panel is the one we're expanding.
            // There is on old panel since expanding a
            // section no longer forces the last section
            // to collapse.
            this._toggle({
                newPanel: panel,
                oldPanel: $()
            });

            header.removeClass( 'ui-corner-all' )
                   .addClass( headerActive + ' ui-corner-top' );
            
            if ( icons ) {
                header.children( '.ui-accordion-header-icon' )
                       .removeClass( icons.header )
                       .addClass( icons.activeHeader );
            }
            panel.addClass( panelActive );
        }
    },
});

})( jQuery );

$(function() {
    $( '#accordion' ).accordion({
                expandable: true,
        header: "h1",
        collapsible: true,
        heightStyle: "content",
        navigation: true
    });
    $('#accordion .ui-accordion-content').show();
});