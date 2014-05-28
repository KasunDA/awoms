
/**
 * API/Create.js
 **/
var createFrmID = "@@createFrmID@@";
var createTitle = "@@createTitle@@";
var createController = "@@createController@@";
var createAction = "@@createAction@@";
var createSaveText = "@@createSaveText@@";
var createTinyMCEInputID = "@@createTinyMCEInputID@@";

/**
 * Create.js - Apply 'Dialog' to form (jUI modal)
 **/
$('#' + createFrmID).dialog({
  autoOpen: false,
  height: 600,
  width: 850,
  modal: true,
  title: createTitle,
  buttons: {
    "@@createSaveText@@": function() {

      // @TODO: Array of tinyMCE input ID's to save instead of assuming 1
      if (createTinyMCEInputID.length > 0)
      {
        console.log('Preparing tinyMCE...' + createTinyMCEInputID);
        tinymce.get(createTinyMCEInputID).save();
      }

      console.log('Calling API...');
      callAPI(createController, createAction, createFrmID);
      $(this).dialog('close');
    },
    Cancel: function() {
      $(this).dialog('close');
    }
  }
});