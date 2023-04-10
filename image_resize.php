<?php
/*
require('includes/application_top.php');

$folder = 'images/pub_full/';

$dir = new DirectoryIterator($folder);

$images = array();

foreach ($dir as $key => $fileinfo) {
    if (!$fileinfo->isDot()) {
        // if (file_exists('images/pub_thumb/' . $fileinfo->getFilename()) === FALSE) {
            $images[$key]['file'] = $fileinfo->getFilename();
            $images[$key]['ext'] = $fileinfo->getExtension();
        // }
    }
}
// echo '<pre>'; print_r($images); die();
// echo '<img src="'.$folder.$images[3]['file'].'">'; die();

$images = array_values($images);
// echo '<pre>'; print_r($images); die();

require(DIR_WS_CLASSES . 'zebra_image.php');
$image = new Zebra_Image();

for ($j=400; $j <= count($images); $j++) {
    $error = TRUE;

    $args = [
        ['w' => 110, 'h' => 110, 'path' => 'images/pub_edit/']
    ];
    // $args = [
    //     ['w' => 107, 'h' => 87, 'path' => 'images/pub_thumb/'],
    //     ['w' => 250, 'h' => 0, 'path' => 'images/pub_big/']
    // ];

    for ($i=0; $i <= count($args); $i++) {
        if (file_exists($folder.$images[$j]['file'])) {
        	$image->source_path = $folder.$images[$j]['file'];
        	$image->target_path = $args[$i]['path'].$images[$j]['file'];
            if ($images[$j]['ext'] === 'jpg') $image->jpeg_quality = 100;
        	$image->preserve_aspect_ratio = true;
        	$image->enlarge_smaller_images = true;
        	$image->preserve_time = true;

        	if (!$image->resize($args[$i]['w'], $args[$i]['h'], ZEBRA_IMAGE_NOT_BOXED)) {
        		switch ($image->error) {
        			case 1: $error.= 'Source file could not be found!'; break;
        			case 2: $error.= 'Source file is not readable!'; break;
        			case 3: $error.= 'Could not write target file!'; break;
        			case 4: $error.= 'Unsupported source file format!'; break;
        			case 5: $error.= 'Unsupported target file format!'; break;
        			case 6: $error.= 'Unsupported file format!'; break;
        			case 7: $error.= 'GD library is not installed!'; break;
        		}
        	}
        } else {
            continue;
        }
    }

    // if ($j >= 399) break;
}

echo '<pre>'; print_r($error);
*/
