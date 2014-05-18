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
		'lat' => compileCoordinate($lat),
		'lng' => compileCoordinate($long),
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


function compileCoordinate($data) {

	$coords = array();

	foreach ($data as $key => $val) {
		$t = explode('/', $val);
		$coords[$key] = $t[0] / $t[1];
	}

	return $coords[0] + ($coords[1]/60) + (($coords[2])/3600);

}

function clog($filename, $msg) {
	echo sprintf('[%s] %s %s', $filename, $msg, PHP_EOL);
}


