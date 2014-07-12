$('.events [data-toggle="tooltip"]').tooltip({
  placement: 'left',
  trigger: 'hover click'
});

$('.events [data-toggle="pill"]').click(function () {
  var $pill = $(this);
  var category = $pill.text().trim();
  var $events = $pill.closest('.timeline').find('.event');
  if (!category) {
    $events.show();
  } else {
    var $selected = $events.filter(function (i, e) {
      return $(e).data('category') === category;
    });
    var $other = $events.not($selected);
    $selected.show();
    $other.hide();
  }
});
