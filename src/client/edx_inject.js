// This script will allow microphone on all activities on this page

(() => {
  function act() {
    $('.jsinput').each((i, value) => {
      if ($(value).attr('data-processed') === 'true') {
        return;
      }

      let $iframe = $(value).find('iframe');

      if (!$iframe.attr('allow') || !~$iframe.attr('allow').indexOf('camera') ||
          !~$iframe.attr('allow').indexOf('microphone')) {
        $iframe.attr('allow', 'camera; microphone; ' + $iframe.attr('allow'));
      }

      $iframe[0].outerHTML =
          $iframe[0]
              .outerHTML;  // Causing element refresh. Is there a better way?

      // JSInput.jsinputConstructor($value);
    });
  }

  function init() {
    let interval = setInterval(act, 10);
    setTimeout(() => clearInterval(interval), 400);
  }

  if ($.isReady) {
    init();
  } else {
    $(document).ready(init);
  }
})();
