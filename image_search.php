<?php
/*
require('includes/application_top.php');

$api_key = 'AIzaSyAFC0Ub5pt7zfayYJPrZp_sChkGX0Tqzwk';
$cx = '008295955371458697235:krcwmjszxve';
$url = 'https://www.googleapis.com/customsearch/v1?q=';

$query = tep_db_query("SELECT pub_name, pub_id, pub_pictures FROM `pubs` WHERE `pub_pictures` = ''");

$results = array();
while ($row = tep_db_fetch_array($query)) {
    $results[] = $row;
}

echo '<pre>'; print_r($results); die();

$images = array();

for ($i=0; $i <= count($results); $i++) {
    $q = urlencode($results[$i]['pub_name']);
    $link = $url.$q.'&cx='.$cx.'&imgSize=medium&searchType=image&googlehost=.co.nz&key='.$api_key;

    // echo $link; die();

    // Image url
    $code = get_image($link, 1);

    if (get_code($code) >= 200 AND get_code($code) < 400) {
        $images[$i]['img_url'] = $code;
    } else {
        $retry = get_image($link, 2);
        if (get_code($retry) >= 200 AND get_code($retry) < 400) {
            $images[$i]['img_url'] = $retry;
        } else {
            $images[$i]['img_url'] = get_image($link, 3);
        }
    }

    // Image extension
    preg_match('/(.jpg|.jpeg|.png)/', $images[$i]['img_url'], $matches);
    $images[$i]['img_ext'] = !empty($matches[0]) ? $matches[0] : '.jpg';

    // Image name
    $img_name = preg_replace('/\s+/', '-', strtolower($results[$i]['pub_name']));
    $images[$i]['img_name'] = preg_replace('/\W\&amp;/', '', $img_name);

    // Image full name
    $images[$i]['img_file'] = $images[$i]['img_name'].$images[$i]['img_ext'];

    // Save path
    $images[$i]['save_path'] = 'images/pub_full/'.$images[$i]['img_file'];

    // Pub id
    $images[$i]['pub_id'] = $results[$i]['pub_id'];

    // if ($i > 40) break;
}

// echo '<pre>'; print_r($images); die();

function get_image($link, $num) {
    $url = $link . '&num=' . $num;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    curl_close($ch);

    // Get CURL results as array
    $result = json_decode($output);
    unset($output);

    // Get image url
    return $result->items[$num-1]->link;
}

function get_code($url) {
    $headers = get_headers($url);
    return substr($headers[0], 9, 3);
}

$count = 0;
foreach ($images as $key => $img) {
    $save = save_image($img['save_path'], $img['img_url']);
    // Save image
    if ($save) {
        $count++;
    } else {
        $retry = save_image($img['save_path'], $img['img_url']);
        if($retry !== true) continue;
        // die($count . " Cannot save file " . $img['save_path'] . ' <br> ' . $img['img_url']);
    }

    // echo "img Id: " . $img['pub_id']; die();

    $sql = array('pub_pictures' => $img['img_file']);
    tep_db_perform(TABLE_PUBS, $sql, 'update', "pub_id = '".$img['pub_id']."'");
}
// echo $count . ' <br> ';

function save_image($save_path, $img_url) {
    $img_url = file_get_contents($img_url);

    if (file_put_contents($save_path, $img_url)) {
        return true;
    } else {
        return false;
    }
}
/*
function make_thumb($save_path, $img_file, $img_ext) {
    $error = TRUE;
	require(DIR_WS_CLASSES . 'zebra_image.php');
	$image = new Zebra_Image();

    $args = [
        ['w' => 107, 'h' => 87, 'path' => 'images/pub_thumb/'],
        ['w' => 250, 'h' => 0, 'path' => 'images/pub_big/']
    ];

    for ($i=0; $i <= count($args); $i++) {
    	$image->source_path = $save_path;
    	$image->target_path = $args[$i]['path'].$img_file;
        if ($img_ext === 'jpg') $image->jpeg_quality = 100;
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
    }

    return $error;
}
*/
