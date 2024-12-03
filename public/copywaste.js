// A function to copy the text from the textarea to the clipboard
function copyToClipboard() {
  if (!navigator.clipboard) {
    fallbackCopyTextToClipboard();
    return;
  }

  navigator.clipboard.writeText(document.getElementById("message").value).then(function () {
    console.log('Async: Copying to clipboard was successful!');

  }, function (err) {
    console.error('Async: Could not copy text: ', err);
  });
}

function fallbackCopyTextToClipboard() {
  let copyText = document.getElementById("message");
  copyText.select();
  document.execCommand("copy");
}