window.circleProgress = require('jquery-circle-progress');

$(function(){
  if ($('.chart-circle').length) {
    $('.chart-circle').each(function() {
      let $this = $(this);

      $this.circleProgress({
        fill: {
          color: tabler.colors[$this.attr('data-color')] || tabler.colors.blue
        },
        size: $this.height(),
        startAngle: -Math.PI / 4 * 2,
        emptyFill: '#F4F4F4',
        lineCap: 'round'
      });
    });
  }
});