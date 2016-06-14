<?php 
echo "Tester<br /><br />";

$string  = "Here is a trademark ™";

echo htmlentities($string, ENT_COMPAT, 'UTF-8');

echo "MB ".mb_convert_encoding($string , "UTF-8");

?>