<?php
// Increase script execution time
set_time_limit(0);

require('includes/application_top.php');
include 'simple_html_dom.php';

$html = file_get_html('https://en.wikipedia.org/wiki/List_of_towns_in_New_Zealand');

$links = array();
$list = array();

foreach($html->find('.multicol ul li a') as $key => $link) {
    $links[$key]['city'] = $link->plaintext;
    $links[$key]['url'] = 'https://en.wikipedia.org' . $link->getAttribute('href');
}
unset($html);

foreach ($links as $key => $value) {
    if (preg_match('/\/w\/index\.php\?/', $value['url'])) {
        unset($links[$key]);
    } elseif ($value['url'] === '' OR empty($value['url']) OR $value['url'] === NULL) {
        unset($links[$key]);
    }
}

$data = file_get_html('https://en.wikipedia.org/wiki/Tairua');


// echo '<pre>';
// print_r($links);
// die();

echo count($links);

for ($key = 486; $key <= count($links); $key++) {
    // if ($links[$key]['url'] === '' OR empty($links[$key]['url']) OR $links[$key]['url'] === NULL) {
        // echo $key . '<br>';
    // }

    // fetch data from url
    $data = file_get_html($links[$key]['url']);

    $list[$key]['location_city'] = strtoupper($links[$key]['city']);
    $list[$key]['location_url'] = preg_replace('/\s+/', '-', strtolower($links[$key]['city']));

    if (is_object($data)) {
        foreach ($data->find('.infobox th') as $el) {
            if ($el->plaintext === 'Region') {
                $list[$key]['region'] = preg_replace('/\'/', '`', $el->next_sibling()->plaintext);
            }
            // elseif ($el->plaintext === 'Postcode' OR $el->plaintext === 'Postcode(s)') {
            //     $list[$key]['location_postcode'] = substr($el->next_sibling()->plaintext, 0, 4);
            // }
        }
    }


    unset($data);
    // if ($key >= 485) break;
}


foreach ($list as $key => $value) {
    $location_id_query = tep_db_query("SELECT location_zone_id FROM ".TABLE_LOCATIONS_ZONES." WHERE region = '".$value['region']."' OR region LIKE '%".implode("%' OR region LIKE '%", explode(' ', $value['region']))."%'");
    $location_zone_id = tep_db_fetch_array($location_id_query);

    $sql_data_array = array(
        'location_id' => '',
        'location_zone_id' => ($location_zone_id['location_zone_id'] !== '') ? $location_zone_id['location_zone_id'] : '',
        'location_postcode' => '',
        'location_city' => tep_db_prepare_input($value['location_city']),
        'location_url' => tep_db_prepare_input($value['location_url']),
    );

    tep_db_perform(TABLE_LOCATIONS, $sql_data_array);
}
unset($list);
