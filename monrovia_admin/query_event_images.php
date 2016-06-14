<?
	require_once('../inc/class_monrovia_event.php');
	require_once('../inc/class_monrovia_event_image.php');
	$event_id = $_POST['id'];
    if(isset($_POST['image_id'])) $image_id = $_POST['image_id'];
    if(isset($_POST['direction'])) $direction = $_POST['direction'];
    if(isset($_POST['ordinal'])) $ordinal = $_POST['ordinal'];
    
    if (isset($image_id) && isset($direction) && isset($ordinal)){
        switch ($direction) {
            case 'up':
                    $new_ordinal = $ordinal - 1;   
                                 
                    //find image that is closest ordinal to the one we want to update
                    $result = sql_query('SELECT id,ordinal FROM monrovia_event_images WHERE monrovia_event_id="'.$event_id .'" AND ordinal < "'.$ordinal.'" ORDER BY ordinal DESC');
                    break;
            case 'down':
                    $new_ordinal = $ordinal + 1;
                    
                    //find image that is closest ordinal to the one we want to update
                    $result = sql_query('SELECT id,ordinal FROM monrovia_event_images WHERE monrovia_event_id="'.$event_id .'" AND ordinal > "'.$ordinal.'" ORDER BY ordinal ASC');
                    break;
        }

        if ($result) {
            $results = array();
            while ($image = mysql_fetch_object($result)){
                array_push($results, $image);
             }
            
            if (count($results)){
                $result = sql_query('UPDATE monrovia_event_images SET ordinal = "'.$ordinal.'" WHERE id ="'.$results[0]->id.'" LIMIT 1'); 
                
                //update our original image
                $result2 = sql_query('UPDATE monrovia_event_images SET ordinal = "'.$new_ordinal.'" WHERE id = "'.$image_id.'" LIMIT 1');   
            }
        }
        
        
        
    }
    
	$event = new monrovia_event($event_id);
	$event->output_cms_image_segments_html();
	$event->clear_cache();
	sql_disconnect();
?>