(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	 $(function() {
		 $('.proper-profile-tooltip').tooltipster({
	 		interactive: true,
			contentAsHTML: true,
			theme: 'tooltipster-shadow',
	 		content: 'Loading...',
	     // 'instance' is basically the tooltip. More details in the "Object-oriented Tooltipster" section.
	     functionBefore: function(instance, helper) {

	         var $origin = $(helper.origin);

	         // we set a variable so the data is only loaded once via Ajax, not every time the tooltip opens
	         if ($origin.data('loaded') !== true) {

	             $.get('/wp-admin/admin-ajax.php?action=get_proper_profile&email=' + $origin.attr('data-email'), function(data) {

	                 // call the 'content' method to update the content of our tooltip with the returned data.
	                 // note: this content update will trigger an update animation (see the updateAnimation option)
	                 instance.content(data);

	                 // to remember that the data has been loaded
	                 $origin.data('loaded', true);
	             });
	         }
	     }
	 	});
	 });



})( jQuery );
