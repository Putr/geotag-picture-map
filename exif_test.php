<?php

/*
 * USAGE
 *
 * php exif_test.php path/to/img.JPG
 */

$fileName = $argv[1];

$exifData = exif_read_data ( $fileName );

var_dump($exifData); die;