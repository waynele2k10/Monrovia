<?php
include 'wp-config.php';
//$hostWp =  '127.0.0.1';
//$usernameWp = 'monrovia2';
//$passwordWp = 'RZodLxEaSrD4Osq0YJE9';
//$databaseWp ='wp_monrovia2';
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$itemNumber = $_GET['itemnumber'];
//$mysqli = new mysqli("localhost", "bluecal_db", "1qaz@WSX", "bluecal_wp");
/* check connection */
if ($mysqli->connect_errno) {
	printf("Connect failed: %s\n", $mysqli->connect_error);
	exit();
}

$result = $mysqli->query("
	SELECT plants.id,item_number, wp_users.user_email,wish_lists.id as wishlist_id, monrovia_zipcodes.state, wp_usermeta.meta_value AS 'first_name' 
	FROM plants
	INNER JOIN wish_list_items
	ON wish_list_items.plant_id = plants.id
	INNER JOIN wish_lists
	ON wish_lists.id = wish_list_items.wish_list_id
	INNER JOIN wp_users
	ON wp_users.id = wish_lists.user_id 
        INNER JOIN wp_usermeta 
        ON wp_usermeta.user_id = wp_users.id AND wp_usermeta.meta_key = 'first_name' 
        INNER JOIN wp_cimy_uef_data  as zip_doce
        ON zip_doce.USER_ID = wp_users.id and zip_doce.FIELD_ID = 2
        INNER JOIN monrovia_zipcodes 
        ON monrovia_zipcodes.zip_code = zip_doce.VALUE AND monrovia_zipcodes.country = 'US'
        where item_number='".addslashes($itemNumber)."' and wish_list_items.enable_send_mail = 1;
 ");
 
$plantArray = array();
if ($result->num_rows) {
	  while($row = $result->fetch_array(MYSQL_ASSOC)) {
            $plantArray[] = $row;
    }
}
 echo json_encode($plantArray);