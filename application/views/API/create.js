
/**
 * API/Create.js
 **/
var createFrmID = "@@createFrmID@@";
var createTitle = "@@createTitle@@";
var createController = "@@createController@@";
var createAction = "@@createAction@@";
var createSaveText = "@@createSaveText@@";
var createTinyMCEInputID = "@@createTinyMCEInputID@@";
var createAutoOpenForm = "@@createAutoOpenForm@@";
var deleteButtonClass = "@@deleteButtonClass@@";

// @TODO height, width, modal

/**
 * Create.js - Apply 'Dialog' to form (jUI modal)
 **/
$('#' + createFrmID).dialog({
  autoOpen: createAutoOpenForm,
  height: "auto",
  width: "auto",
  modal: true,
  title: createTitle,
  open: function() {
    if ($(this).parent().height() > $(window).height()) {
        $(this).height($(window).height()*0.8);
    }
    $(this).dialog({position: "center"});
  },
  buttons: [
      {
          text: "Delete",
          click: function() {
            console.log('Calling API...');
            if (confirm('Are you sure you want to PERMANENTLY DELETE this?')) {
                callAPI(createController, 'delete', createFrmID);
                $(this).dialog('close');
            }
          },
          class: deleteButtonClass
      },
      {
            text: "@@createSaveText@@",
            click: function() {

                // @TODO: Array of tinyMCE input ID's to save instead of assuming 1
                if (createTinyMCEInputID.length > 0)
                {
                  console.log('Preparing tinyMCE...' + createTinyMCEInputID);
                  tinymce.get(createTinyMCEInputID).save();
                }

                console.log('Calling API...');
                callAPI(createController, createAction, createFrmID);
                $(this).dialog('close');
            }
        },
        {
        text: "Cancel",
            click: function() {
                $(this).dialog('close');
            }
        }
  ]  
});