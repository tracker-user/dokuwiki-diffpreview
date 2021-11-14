/*
 * diffpreview plugin
 */

jQuery(function() {
  jQuery('#edbtn__changes').on('click', function() {
    window.onbeforeunload = '';
    window.textChanged = false;
    window.keepDraft = true; // needed to keep draft on page unload
  });
});
