<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Copy/Waste</title>

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap.min.css"
          integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap-theme.min.css"
          integrity="sha384-6pzBo3FDv/PJ8r2KRkGHifhEocL+1X2rVCTTkUfGk7/0pbek5mMa1upzvWbrUbOZ" crossorigin="anonymous">

    <!-- Latest compiled and minified JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/js/bootstrap.min.js"
            integrity="sha384-aJ21OjlMXNL5UyIl/XNwTMqvzeRMZH2w8c5cRVpzpU8Y5bApTppSuUkhZXN0VxHd"
            crossorigin="anonymous"></script>

    <link rel="stylesheet" href="/copywaste.css">

    <!-- Latest compiled and minified JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://unpkg.com/htmx.org@2.0.3"></script>

    <script>
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
        var copyText = document.getElementById("message");
        copyText.select();
        document.execCommand("copy");
      }
    </script>
</head>

<body>
<a href="/"><img src="/img/copywaste_logo.png"/></a>
<div class="container-fluid">
    <div class="well col-md-2 col-xs-12 hidden-xs">
        <dl id="info" class="">

            <dt>What?</dt>
            <dd>This is a little application that helps you to share snippets of text. It can also be used to work on a
                document with multiple people. CopyWastes expire in 24 hours.
            </dd>
            <dt>Why?</dt>
            <dd>
                I needed a way to copy/paste stuff from 1 laptop to another.<br> I used to send myself mails from 1
                computer to the other, just so i could copy paste an url or emailadress.
            </dd>
            <dt>How?</dt>
            <dd>
                <ul>
                    <li>Just put the text you want to share in the textarea and click on <a
                                class="btn btn-xs btn-success savebutton">Save</a></li>
                    <li>Then go to your other computer, open a browser and type in the <strong>URL</strong> you find
                        above the textarea.
                    </li>
                    <li>You'll see the text you typed and you can copy it to the clipboard by pressing <a
                                class="btn btn-xs btn-primary copybutton">Copy to clipboard</a></li>
                    <li>If you edited or inserted new text in the textarea on 1 browser, you can easily get it in
                        another one by pressing <a class="btn btn-xs btn-primary syncbutton">Reload</a></li>
                    <li>CopyWastes are kept for <strong>1</strong> day.</li>
                </ul>
            </dd>
        </dl>
    </div>
    <div class="col-md-7 col-xs-12">
        <h4>Message</h4>
        <div class="container-fluid">
            <form>
                <div>
                    <?php include_once 'messagepanel.php'; ?>
                </div>
                <div id="buttonbar" class="btn-group" aria-label="action buttons">
                    <button id="save" class="btn btn-success" hx-put="/api/v2/waste/<?= $id ?>" hx-target="#message">💾
                        Save
                    </button>
                    <button id="reload" class="btn btn-primary" hx-get="/waste/<?= $id ?>" hx-target="#message"
                            hx-select="#message" hx-swap="outerHTML">🔃 Reload
                    </button>
                    <button id="copy" class="btn btn-primary" onclick="copyToClipboard(); return false;">📄 Copy to
                        clipboard
                    </button>
                    <!--                        <button id="mailbtn" class="btn btn-primary">📧 Mail</button>-->
                    <button id="clear" class="btn btn-danger"
                            onclick="document.getElementById('message').value=''; return false;">𝗫 Clear
                    </button>


                </div>
                <span class="alert alert-success pull-right htmx-indicator" role="alert">Message saved!</span>

            </form>
        </div>
    </div>

    <div class="col-md-3 col-xs-12">
        <div id="qrcode">
            <h4>QRCode</h4>
            <img src="/qrcodes/<?= $id ?>.png" alt="QR Code" width="150" height="150"/>
        </div>
        <?php include_once 'uploadpanel.php'; ?>
    </div>
</div>
</body>

</html>