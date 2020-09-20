// This script will allow microphone on all activities on this page
$(() => {
  const callback = (mutationList) => {
    for (let mut of mutationList) {
      if (mut.target.className !== "jsinput") {
        continue;
      }

      let iframe = $(mut.target).find("iframe")[0];
      
      const TEXT = '<iframe allow="camera *; microphone *"';
      if (iframe.outerHTML.indexOf(TEXT) !== 0) {
        iframe.outerHTML = iframe.outerHTML.replace("<iframe", TEXT);
      }
    }
  }

  const observer = new MutationObserver(callback);
  observer.observe($("body")[0], {childList: true, subtree: true});
});
