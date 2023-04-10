<?php
require('includes/application_top.php');

include 'simple_html_dom.php';

$query = tep_db_query("SELECT * FROM locations WHERE location_postcode = ''");

$result = array();

$count = 1;
while ($row = tep_db_fetch_array($query)) {
    $result[$count]['id'] = $row['location_id'];
    $result[$count]['url'] = $row['location_url'];
    $count++;
}
// echo '<pre>';
// print_r($result); die();
// echo count($result);
// die();

for ($i = 0; $i <= count($result); $i++) {
    $data = file_get_html('http://new-zealand.postcode.info/' . $result[$i]['url']);

    foreach ($data->find('h2') as $el) {
        $code = $el->next_sibling()->first_child()->plaintext;

        if ($code !== 'click here') {
            // echo $code . '<br>';
            $sql_data_array = array(
                'location_postcode' => $code
            );

            tep_db_perform(TABLE_LOCATIONS, $sql_data_array, 'update', "location_id = '".$result[$i]['id']."'");
        }
    }

    unset($data);
    // if ($i >= 100) break;
}
