
/**
 * API/Create.js
 **/
var createFrmID = "@@createFrmID@@";
var createTitle = "@@createTitle@@";
var createController = "@@createController@@";
var createAction = "@@createAction@@";
var createSaveText = "@@createSaveText@@";

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
      console.log('Calling API...');
      callAPI(createController, createAction, createFrmID);
      $(this).dialog('close');
    },
    Cancel: function() {
      $(this).dialog('close');
    }
  }
});
