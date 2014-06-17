/* var i = 1; */

$('#cloneButton').click(function() {
  $('#cloneMe').clone().find('input').each(function() {

    $(this).val("");
    $(this).attr('id', "");
      /*
       -- using array[] otherwise can use this for id/name incrementing:
        'id': function(_, id) { return id + i },
        'name': function(_, name) { return name + i },
      */
  }).end().removeClass('hidden').appendTo('#appendToMe');
  /* i++; */
});

/* Move rows up and down */
$(".up,.down").click(function(){
    var row = $(this).parents("tr:first");
    if ($(this).is(".up")) {
        row.insertBefore(row.prev());
    } else {
        row.insertAfter(row.next());
    }
});

/* Remove row */
$(".remove").click(function(){
    var row = $(this).parents("tr:first");
    row.remove();
});
