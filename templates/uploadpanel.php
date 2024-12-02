<?php
function human_filesize($filename, $decimals = 0)
{
    $sza = ['bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    $bytes = filesize($filename);
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . ' ' . $sza[$factor];
}

?>
<div id="uploadpanel">
    <h4>Uploads</h4>
    <div class="list-group">
        <?php
        foreach ($uploads as $upload) {
            ?>
            <div class="list-group-item">
                <a href="/api/v2/download/<?= $id ?>/<?= base64_encode(basename($upload)) ?>" target="_top">
                    <h5 class="list-group-item-heading"
                        style="text-overflow: ellipsis; overflow: hidden; white-space: nowrap;"><?= basename($upload) ?></h5>
                </a>
                <p class="list-group-item-text">
                    <?= human_filesize($upload) ?> | <?= date("F d Y H:i", filemtime($upload)) ?>h
                    <button type="button" class="close" aria-label="Delete upload"
                            hx-delete="/api/v2/upload/<?= $id ?>/<?= base64_encode(basename($upload)) ?>"
                            hx-target="#uploadpanel"
                            title="Delete upload"
                            hx-confirm="Are you sure you want to delete this upload?">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </p>

            </div>
            <?php
        }
        ?>

    </div>
    <form id='form' enctype="multipart/form-data" hx-encoding='multipart/form-data' hx-post='/api/v2/upload/<?= $id ?>'
          hx-target="#uploadpanel" hx-swap="outerHTML">
        <input type='file' name='uploadfile' class="">
        <button class="btn btn-primary" style="margin-top:20px">
            Upload
        </button>
        <progress id='progress' value='0' max='100' class="htmx-indicator"></progress>
    </form>
    <script>
      htmx.on('#form', 'htmx:xhr:progress', function (evt) {
        htmx.find('#progress')
          .setAttribute('value', evt.detail.loaded / evt.detail.total * 100)
      });
    </script>
</div>