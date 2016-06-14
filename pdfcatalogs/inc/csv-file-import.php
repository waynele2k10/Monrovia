<?php

    require_once 'csv-decode.php';
    $output_dir = "../csv-tmp/";
     
    if(isset($_FILES["csv-file"])){
        //Filter the file types , if you want.
        if ($_FILES["csv-file"]["error"] > 0){
          echo "Error: " . $_FILES["file"]["error"] . "<br>";
        }else{
            //move the uploaded file to uploads folder;
            move_uploaded_file($_FILES["csv-file"]["tmp_name"],$output_dir. $_FILES["csv-file"]["name"]);         

            $csvObject = csvLoadFile($_FILES["csv-file"]["name"]);
            if ( isset( $csvObject) ){
                echo $csvObject;
            }
        }
     
    }
?>