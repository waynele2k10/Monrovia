<?
	$minify_js = true;
	$minify_css = true;

	$content_type = '';
	if(is_numeric(strpos(strtolower($_GET['path']),'.js'))) $content_type = 'text/javascript';
	if(is_numeric(strpos(strtolower($_GET['path']),'.css'))) $content_type = 'text/css';

	$file_names = explode(',',$_GET['path']);
	$contents = '';
	foreach($file_names as $path){
		$contents .= "\n";
		$path = $_SERVER['DOCUMENT_ROOT'] . $path;
		if(file_exists($path)){
			$contents .= "/* ************ BEGIN ".basename($path)." ************* */\n";
			$contents .= @file_get_contents($path);
			$contents .= "\n/* ************ END ".basename($path)." ************* */\n";
		}else{
			$contents .= "/* ".basename($path)." is missing */";
		}
	}
	$etag_contents = md5($contents);

	if($etag_contents==(isset($_SERVER['HTTP_IF_NONE_MATCH'])?trim($_SERVER['HTTP_IF_NONE_MATCH']):false)){
		header('HTTP/1.1 304 Not Modified');
		header('Expires: ');
		header('Content-Type: '.$content_type);
		header('ETag: "'.$etag_contents.'"');
	}else{
		if(is_numeric(strpos(strtolower($path),'.js'))){
			if($minify_js){
				require('class_jsmin.php');
				$packer = new JSMin($contents);
				$contents = $packer->minify($contents);
			}
		}
		if(is_numeric(strpos(strtolower($path),'.css'))){
			if($minify_css){
				require('class_cssmin.php');
				$contents = CssMin::minify($contents,array(
					"remove-empty-blocks"           => true,
					"remove-empty-rulesets"         => true,
					"remove-last-semicolons"        => true,
					"convert-css3-properties"       => true,
					"convert-font-weight-values"    => true, // new in v2.0.2
					"convert-named-color-values"    => true, // new in v2.0.2
					"convert-hsl-color-values"      => true, // new in v2.0.2
					"convert-rgb-color-values"      => true, // new in v2.0.2; was "convert-color-values" in v2.0.1
					"compress-color-values"         => true,
					"compress-unit-values"          => true,
					"emulate-css3-variables"        => true
				));
			}
		}
		
		$expires = 60*60*24*14;
		header("ETag: $etag_contents");
		header("Last-Modified: ".gmdate("D, d M Y H:i:s",time())." GMT");
		header("Pragma: public");
		header("Cache-Control: maxage=".$expires);
		header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
		header('Content-type:'.$content_type);
		echo($contents);
	}
?>