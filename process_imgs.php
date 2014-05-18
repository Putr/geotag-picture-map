<?php
//
//
// CONFIGURATION
// 
// 
$start = time();

// Path where your photos are located
define('ORIGINAL_PATH', 'original_img/');

// Path where thumbnails will be generated
define('WEB_IMG_PATH', 'web/img/');

// Size of thumbnail
define('IMG_WIDTH', 300);


//
// END OF CONFIGURATION
//
require_once 'SimpleImage.php';

$fileList         = array();
$fileList['root'] = array();

foreach (new DirectoryIterator(ORIGINAL_PATH) as $fileInfo) {
    if($fileInfo->isDot()) continue;

    if ($fileInfo->isDir()) {
    	$subDirFiles = array();
    	foreach (new DirectoryIterator($fileInfo->getPathname()) as $fileInfo2) {
    		if($fileInfo2->isDot()) continue;
    		$subDirFiles[] = $fileInfo2->getFilename();
    	}
    	$dir = str_replace(' ', '_', $fileInfo->getFilename());
    	$fileList[$dir] = $subDirFiles;
    }

    if ($fileInfo->getFilename() == '.gitignore') continue;

    if ($fileInfo->isFile()) {
    	$fileList['root'][] = $fileInfo->getFilename();
    }
}

$data  = array();
$count = array(
	'total' => 0,
	'err'   => 0
);
foreach ($fileList as $dir => $files) {
	foreach ($files as $fileName) {

		$count['total']++;

		if ($dir == 'root') {
			$filePath = ORIGINAL_PATH . $fileName;
		} else {
			$filePath = sprintf('%s%s/%s', ORIGINAL_PATH, $dir, $fileName);
		}

		$exifData = exif_read_data ( $filePath );

		if (!isset($exifData['GPSLatitude']) || !isset($exifData['GPSLongitude'])) {
			clog($dir, $fileName, 'This image has no GPS data');
			$count['err']++;
			continue;
		}

		// Build EXIF database
		// 
		$lat = $exifData['GPSLatitude'];
		$long = $exifData['GPSLongitude'];

		$ext = pathinfo($filePath, PATHINFO_EXTENSION);
		$newFileName = sprintf('%s_%s.%s', $dir, substr(md5($fileName), 0, 8), $ext);

		$data[] = array(
			'lat' => compileCoordinate($lat),
			'lng' => compileCoordinate($long),
			'img' => $newFileName
		);

		$image = new SimpleImage();
		$image->load( $filePath);
		$image->resizeToWidth(IMG_WIDTH);
		$image->save(WEB_IMG_PATH . $newFileName);
		echo ".";
	}
}

$data = json_encode($data);

file_put_contents('web/locations.json', $data);

echo sprintf('%sFound %s images, %s had errors so only %s were processed. %s', PHP_EOL, $count['total'], $count['err'], $count['total'] - $count['err'], PHP_EOL);
$end = time();

$time = $end - $start;
$avg = $time / $count['total'];
echo sprintf('Execution took %s seconds, on average %s per image. %s', $time, $avg, PHP_EOL);

function compileCoordinate($data) {

	$coords = array();

	foreach ($data as $key => $val) {
		$t = explode('/', $val);
		$coords[$key] = $t[0] / $t[1];
	}

	return $coords[0] + ($coords[1]/60) + (($coords[2])/3600);

}

function clog($dir, $filename, $msg) {
	echo sprintf('%s[%s][%s] %s %s', PHP_EOL, $dir, $filename, $msg, PHP_EOL);
}


