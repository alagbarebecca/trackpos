<?php
$file = "/tmp/trackpos-install.tar.gz";
if (file_exists($file)) {
    header("Content-Type: application/gzip");
    header("Content-Disposition: attachment; filename=trackpos-install.tar.gz");
    header("Content-Length: " . filesize($file));
    readfile($file);
    exit;
}
echo "File not found";

