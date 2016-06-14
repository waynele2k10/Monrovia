<?php
set_time_limit(600);
include_once( '../wp-config.php');
$_max_file = 3;
function updateExportFlag() {
	update_option('monrovia_export_flag', '1');
}

function checkingExportFlag() {
	$_export_flag = get_option('monrovia_export_flag', '0');
	$_res['flag'] = $_export_flag;
	echo json_encode($_res);
}

function getExistingExportFilesName() {
	try {
		$_max_file = 3;
		$_flag = get_option('monrovia_export_flag', '0');
		if ($_flag == '0') {
			$exp_files = glob('export_auto/*.xls');
			if (count($exp_files) <= 0) {
				return "Click 'Export' button to create first export.";
			}
			arsort($exp_files);
			$_i = 0;
			$return = '<div class="rs-export-list"><p>Last export files:</p>';
			foreach ($exp_files as $file) {
				$_i ++;
				if ($_i > $_max_file) {
					if (is_file($file)) {
						unlink($file);
					}
				} else {
					$return .= '<a href="http://'.$_SERVER['HTTP_HOST'].'/monrovia_admin/'.$file.'" target="_blank">'.basename($file).'</a><br>';
				}
			}
			$return .= '</div>';
			return $return;
		} else {
			return 'generating export file...';
		}
	} catch (Exception $exc) {
		return "Error";
	}
}

$exp = $_GET['exp'];

try {
	if ($exp == 'update') {
		return updateExportFlag();
	}
	if ($exp == 'check') {
		return checkingExportFlag();
	}
	if ($exp == 'get') {
		echo getExistingExportFilesName();
	}
} catch (Exception $exc) {
	echo $exc->getMessage();
	exit();
}
