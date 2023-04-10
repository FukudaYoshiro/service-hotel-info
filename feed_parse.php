<?php
require('includes/application_top.php');
require(DIR_WS_INCLUDES . 'template_top.php');
?>

<?php
function get_http_response_code($url) {
    $headers = get_headers($url);
    return substr($headers[0], 9, 3);
}
// Increase script execution time
set_time_limit(300);

include 'simple_html_dom.php';

$html = file_get_html('http://static.tab.co.nz/content/agencies/locations.html');
$links = array();

foreach($html->find('a') as $key => $link) {
    $links[$key]['url'] = preg_replace('/\.\//', 'http://static.tab.co.nz/content/agencies/', htmlentities($link->getAttribute('href')));
    $town = explode('/', $link->getAttribute('href'));
    $town = explode('-', $town[2]);
    $links[$key]['town'] = $town[0];
}

// echo '<pre>';
// print_r($links);
// die();


unset($html);
$count = 0;
$list = array();
for ($key = 300; $key <= count($links); $key++) {
    // if page not found, skip
    if (get_http_response_code($links[$key]['url']) === '404' OR get_http_response_code($links[$key]['url']) === '400') {
        continue;
    }
    // fetch data from url
    $data = file_get_html($links[$key]['url']);
    if (!is_object($data)) {
        continue;
    }
    foreach($data->find('.head') as $k => $pub) {
        // parent town
        $list[$key][$k]['town'] = $links[$key]['town'];
        // City name
        $list[$key][$k]['city'] = $data->find('.header', 0)->plaintext;
        // Pub name
        $list[$key][$k]['name'] = htmlentities($pub->plaintext);
        // Address
        $list[$key][$k]['address'] = strip_tags(trim($pub->parent()->next_sibling()->first_child()->innertext));
        // Phone extension
        $phone = strip_tags(trim($pub->parent()->next_sibling()->next_sibling()->first_child()->innertext));
        $ext = explode('ext', $phone);
        $list[$key][$k]['ext'] = isset($ext[1]) ? $ext[1] : '';
        // Phone number
        $phone_number = preg_replace('/\D+/', '', isset($ext[0]) ? $ext[0] : $phone);
        if (!empty($phone_number)) {
            $list[$key][$k]['phone'] = "(".substr($phone_number, 0, 2).") ".substr($phone_number, 2, 4)." ".substr($phone_number,6);
        } else {
            $list[$key][$k]['phone'] = '';
        }
        // Fax number
        $fax = strip_tags(trim($pub->parent()->next_sibling()->next_sibling()->first_child()->next_sibling()->innertext));
        $fax_number = preg_replace('/\D+/', '', $fax);
        if (!empty($fax_number)) {
            $list[$key][$k]['fax'] = substr($fax_number, 0, 2)."-".substr($fax_number, 2, 3)." ".substr($fax_number,5);
        } else {
            $list[$key][$k]['fax'] = '';
        }
        // Map coordinates
        $list[$key][$k]['latitude'] = $data->find('.acontent', $k)->glat;
        $list[$key][$k]['longitude'] = $data->find('.acontent', $k)->glng;
        // Pub type
        $list[$key][$k]['type'] = $pub->parent()->next_sibling()->last_child()->innertext;
        $count++;
    }
    // if ($key >= 300) break;
}

// echo '<pre>';
// print_r($list);
// die();

// unset($data);
// unset($links);

foreach ($list as $key => $value) {
    foreach ($value as $k => $pub) {
        $location_id_query = tep_db_query("select location_id from ".TABLE_LOCATIONS." where location_city = '".$pub['city']."'");
        $location_id = tep_db_fetch_array($location_id_query);

        if ($location_id['location_id'] === NULL OR $location_id['location_id'] === '') {
            $location_id_query = tep_db_query("select location_id from ".TABLE_LOCATIONS." where location_city = '".$pub['town']."'");
            $location_id = tep_db_fetch_array($location_id_query);
        }

        // echo $pub['city'] . ' - ' . $pub['town'] . ' - id: ' . $location_id['location_id'] . '<br>';

        $sql_data_array = array(
            'pub_name' => tep_db_prepare_input($pub['name']),
            'pub_description' => '',
            'pub_address' => tep_db_prepare_input($pub['address']),
            'pub_phone' => tep_db_prepare_input($pub['phone']),
            'pub_fax' => tep_db_prepare_input($pub['fax']),
            'pub_website' => '',
            'pub_pictures' => '',
            'likes' => 0,
            'location_id' => $location_id['location_id'],
            'pub_city' => tep_db_prepare_input($pub['city']),
            'longitude' => tep_db_prepare_input($pub['longitude']),
            'latitude' => tep_db_prepare_input($pub['latitude']),
            'pub_url' => strip($pub['name']),
            'title_tag' => '',
            'description_tag' => '',
            'keywords_tag' => '',
            'status' => '1',
            'got_from' => '',
            'date_added' => 'now()'
        );

        tep_db_perform(TABLE_PUBS, $sql_data_array);
    }
}
unset($list);
echo 'Added ' . $count . ' rows';
?>

<?php
	require(DIR_WS_INCLUDES . 'template_bottom.php');
  	require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
