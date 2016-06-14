<?php require( dirname(__FILE__) . '/wp-load.php' ); ?>
<?php include_once( dirname(__FILE__) . '/wp-config.php'); 

function plantShop($plantID = "", $inStockOnly = false) {
    $is_available = false;
    $result = array();
    
    if (false === $is_available) {
        try {
            //$proxy = new SoapClient('http://shop.monrovia.com/api/soap/?wsdl');                                                                                                          
            //$proxy = new SoapClient('http://local.monrovia.net/api/soap/?wsdl');            
            // FOR DEBUG ONLY
            //$proxy->__setCookie('XDEBUG_SESSION', 'netbeans-xdebug');

            // Connect
            $proxy = new SoapClient('http://shop.monrovia.com/api/soap/?wsdl');                                                                                                                                                                  
            // Login
            $sessionId = $proxy->login('brett', 'primitivespark');
            // Get stock info                                                                                                                                                                                                                       
            $result = $proxy->call($sessionId, 'product_stock.list', array($plantID, $inStockOnly));
            //Return true if product is in stock                                                                                                                                                                                                    
            if (isset($result[0]['is_in_stock']) && $result[0]['is_in_stock'] == 1) {
                $is_available = true;
            }
        } catch (Exception $e) {
            //echo $e->getmessage();
        }
    }
    return $result;
}
?>                                                          

    <?php
    // QUERY ALL PLANTS IN STOCK
    $plants = plantShop('*', true);
	
	// Truncate the Current Incarnation of the table
	mysql_query("TRUNCATE TABLE shop_plant_availibility");
	//Insert into the Database
	foreach($plants as $plant){

		//Convert SKU to item number if longer than 4 digits
		if(strlen($plant['sku'])>4){
			//Remove 1st number, then use the next 4
			$item = substr($plant['sku'], 1, 4);
		} else {
			$item = $plant['sku'];
		}
		
		$url = $plant['url_path'];
		$quantity = $plant['qty'];
		
		mysql_query("INSERT INTO shop_plant_availibility ( item_number, url, quantity ) VALUES ( '$item', '$url', '$quantity')");
	}

    // QUERY ALL PLANTS IN THE DATABASE
    //print_r(plantAvailibility('*', false));

    // QUERY SINGLE PLANT
    //print_r(plantAvailibility('1937'));
    ?> 