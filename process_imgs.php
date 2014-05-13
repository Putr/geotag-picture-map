<?php
//
//
// CONFIGURATION
// 
// 

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

$files = scandir(ORIGINAL_PATH);

$data = array();

$count = array(
	'total' => 0,
	'err' => 0
);
foreach ($files as $fileName) {

	$count['total']++;

	if ($fileName == '.' || $fileName == '..' || $fileName == '.gitignore' ) {
		continue;
	}
	//clog($fileName, ' Starting processing ... ');

	$exifData = exif_read_data ( ORIGINAL_PATH . $fileName );

	if (!isset($exifData['GPSLatitude']) || !isset($exifData['GPSLongitude'])) {
		clog($fileName, 'This image has no GPS data');
		$count['err']++;
		continue;
	}

	// Build EXIF database
	// 
	$lat = $exifData['GPSLatitude'];
	$long = $exifData['GPSLongitude'];

	$data[] = array(
		'lat' => $lat[0] + ($lat[1]/60) + (($lat[2]/100)/3600),
		'lng' => $long[0] + ($long[1]/60) + (($long[2]/100)/3600),
		'img' => $fileName
	);
	//clog($fileName, 'Found coordinates');

	// Create thumbnail
	//clog($fileName, 'Generating thumbnail');
	$image = new SimpleImage();
	$image->load( ORIGINAL_PATH . $fileName);
	$image->resizeToWidth(IMG_WIDTH);
	$image->save(WEB_IMG_PATH . $fileName);
}

$data = json_encode($data);

file_put_contents('web/locations.json', $data);

clog('', sprintf('Found %s images, %s had errors so only %s were processed', $count['total'], $count['err'], $count['total'] - $count['err']));

function clog($filename, $msg) {
	echo sprintf('[%s] %s %s', $filename, $msg, PHP_EOL);
}


