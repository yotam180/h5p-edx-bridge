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



$(() => {
  const callback = (mutationList, observer) => {
    console.log(mutationList, observer);
    for (let mut of mutationList) {
      if (mut.target.className !== "jsinput") {
        continue;
      }

      console.log("JS input");
      let iframe = $(mut.target).find("iframe")[0];
      
      const TEXT = '<iframe allow="camera *; microphone *"';
      if (iframe.outerHTML.indexOf(TEXT) !== 0) {
        iframe.outerHTML = iframe.outerHTML.replace("<iframe", TEXT);
        console.log("Refreshed iframe");
      }
      else {
        console.log("iframe is good");
      }
    }
  }

  const observer = new MutationObserver(callback);
  observer.observe($("body")[0], {childList: true, subtree: true});
})
