		</div>

		<div id="field_error_message">
			<div id="tip"></div>
			<div id="message"></div>
		</div>

<!-- <?

 /*  $mtime = explode(' ',microtime());
   $mtime = $mtime[1] + $mtime[0];
   $page_end_time = $mtime;
   $totaltime = ($page_end_time - $page_begin_time);
   echo "This page was created in ".$totaltime." seconds"; */

//   var_dump($GLOBALS['sql_queries']);
   //var_dump($GLOBALS['monrovia_user']);

?> -->
		<div id="modal_container">
			<div class="modal_dialog" id="modal_lightview">
				<table class="modal_dialog_backing">
					<tr>
						<td class="corner corner_topleft"></td><td class="corner corner_top"><td class="corner corner_topright"></td>
					</tr>
					<tr>
						<td class="corner corner_left"></td>
						<td class="corner corner_middle">
							<div id="image_container"></div>
							<div id="lightview_info">
								<div id="lightview_title"></div>
								<div id="lightview_description"></div>
							</div>
						</td>
						<td class="corner corner_right"></td>
					</tr>
					<tr>
						<td class="corner corner_bottomleft"></td><td class="corner corner_bottom"><td class="corner corner_bottomright"><td>
					</tr>
				</table>
			</div>
			<? if(function_exists('output_modals')) output_modals(); ?>
		</div>
	</body>
</html>
<? //sql_disconnect(); ?>