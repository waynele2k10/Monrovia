<? require_once('inc/header.php'); ?>
<? require_once(get_template_directory().'/includes/designers.php'); ?>



<style>
#table_designer_profiles {
	margin-top:8px;
	width:982px;
}
#table_designer_profiles td {
	font-size:9pt;
}
#table_designer_profiles {
	border-collapse:collapse;
}
#table_designer_profiles td {
	padding:4px 8px 4px 8px;
}

.field_label {
	float:right;
	whitespace:no-wrap;
}


#table_designer_profiles thead a {
	color:#FFF;
}

#form_search {
	font-size:12px;
}

#form_search fieldset {
	display:inline;
	height:35px;
}
</style>


<?
	// FILTER QUERY
	session_save_path('/nas/wp/www/cluster-1975/monrovia/monrovia_admin'); 
	session_start();
	$search_fields = array('firm_name', 'email', 'is_active', 'approval_status');
	$filter_query = "(is_submitted_for_approval='1')";

	if ( isset($_REQUEST['submitted'])&&$_REQUEST['submitted'] == 'search' )
	{
		foreach ( $search_fields as $search_field )
		{
			if ( isset($_REQUEST[$search_field]) && (($_REQUEST[$search_field] != '') || is_array($_REQUEST[$search_field])) )
			{
				$_SESSION['admin_tables']['designer_profiles']['search'][$search_field] = $_REQUEST[$search_field];
			}
			else
				unset($_SESSION['admin_tables']['designer_profiles']['search'][$search_field]);
		}
	}
	
	if ( isset($_SESSION['admin_tables']['designer_profiles']['search'])&&is_array($_SESSION['admin_tables']['designer_profiles']['search']) )
	{
		foreach ( $_SESSION['admin_tables']['designer_profiles']['search'] as $key=>$value )
		{
			if ( is_array($value) )
				$filter_query .= " AND (".$key." IN (".sql_sanitize(join(',', $value))."))";
			else
				$filter_query .= " AND (".$key." LIKE '%".sql_sanitize($value)."%')";
		}
	}

	// LIST
	$sort_fields_available = array('date_created', 'approval_status');
	$sort_field = (isset($_REQUEST['sort_field']) && in_array($_REQUEST['sort_field'], $sort_fields_available) ) ? $_REQUEST['sort_field'] : 'date_created';
	$sort_direction = (isset($_REQUEST['sort_direction']) && ($_REQUEST['sort_direction'] == 'ASC')) ? 'ASC' : 'DESC';
	$sort_direction_link = ($sort_direction == 'ASC') ? 'DESC' : 'ASC';

?>

<h2>Designer Profiles</h2>

<div id="page_content">
	<div>
		<form id="form_search" method="post">
			<input type="hidden" name="submitted" value="search" />
            <fieldset>
                <legend>Status</legend>
                <input id="chk_active" type="checkbox" name="is_active[]" value="1" <? if ( isset($_SESSION['admin_tables'])&&isset($_SESSION['admin_tables']['designer_profiles']['search']['is_active'])&& is_array($_SESSION['admin_tables']['designer_profiles']['search']['is_active'])&& in_array('1', $_SESSION['admin_tables']['designer_profiles']['search']['is_active']) ) echo 'checked' ?> /> <label for="chk_active">Active</label>
                <input id="chk_inactive" type="checkbox" name="is_active[]" value="0" <? if ( isset($_SESSION['admin_tables'])&&isset($_SESSION['admin_tables']['designer_profiles']['search']['is_active'])&&is_array($_SESSION['admin_tables']['designer_profiles']['search']['is_active'])&&in_array('0', $_SESSION['admin_tables']['designer_profiles']['search']['is_active']) ) echo 'checked' ?> /> <label for="chk_inactive">Inactive</label>
            </fieldset>
            <fieldset>
                <legend>Approval Status</legend>
	            <input id="chk_approved" type="checkbox" name="approval_status[]" value="1" <? if ( isset($_SESSION['admin_tables'])&&isset($_SESSION['admin_tables']['designer_profiles']['search']['approval_status'])&&is_array($_SESSION['admin_tables']['designer_profiles']['search']['approval_status'])&&in_array('1', $_SESSION['admin_tables']['designer_profiles']['search']['approval_status']) ) echo 'checked' ?> /> <label for="chk_approved">Approved</label>
                <input id="chk_pending" type="checkbox" name="approval_status[]" value="0" <? if ( isset($_SESSION['admin_tables'])&&isset($_SESSION['admin_tables']['designer_profiles']['search']['approval_status'])&&is_array($_SESSION['admin_tables']['designer_profiles']['search']['approval_status'])&&in_array('0', $_SESSION['admin_tables']['designer_profiles']['search']['approval_status']) ) echo 'checked' ?> /> <label for="chk_pending">Pending Approval</label>
                <input id="chk_rejected" type="checkbox" name="approval_status[]" value="-1" <? if ( isset($_SESSION['admin_tables'])&&isset($_SESSION['admin_tables']['designer_profiles']['search']['approval_status'])&&is_array($_SESSION['admin_tables']['designer_profiles']['search']['approval_status'])&&in_array('-1', $_SESSION['admin_tables']['designer_profiles']['search']['approval_status']) ) echo 'checked' ?> /> <label for="chk_rejected">Rejected</label>
            </fieldset>
            <fieldset>
                <legend>Filter</legend>
                <label>Firm Name:</label>
                <input type="text" name="firm_name" value="<? if ( isset($_SESSION['admin_tables']['designer_profiles']['search']['firm_name']) ) echo $_SESSION['admin_tables']['designer_profiles']['search']['firm_name'] ?>" />
                <label>E-mail:</label>
                <input type="text" name="email" value="<? if ( isset($_SESSION['admin_tables']['designer_profiles']['search']['email']) ) echo $_SESSION['admin_tables']['designer_profiles']['search']['email'] ?>" />
            </fieldset>
			<input type="submit" value="Search" />
		</form>
	</div>
	<hr />
	<table id="table_designer_profiles">
		<thead>
			<tr style="background-color:#666;color:#fff;">
				<td><a href="?sort_field=approval_status&sort_direction=<?=$sort_direction_link?>">Approval Status</a></td>
				<td>Active</td>
				<td><a href="?sort_field=date_created&sort_direction=<?=$sort_direction_link?>">Date Submitted</a></td>
				<td>Firm Name</a></td>
				<td>First Name</a></td>
				<td>Last Name</a></td>
				<td>Email</a></td>
				<td>Membership Affilation</td>
				<td>Action</td>
			</tr>
		</thead>
		<tbody>
        <?
			$result = mysql_query("SELECT * FROM monrovia_profiles WHERE ".$filter_query." ORDER BY ".$sort_field." ".$sort_direction);
			$i=0;
			while ( $profile = mysql_fetch_array($result) )
			{
				$row_bg_class = ($i % 2 == 0) ? 'row_even' : '';
				switch ( $profile['approval_status'] )
				{
					case '-1':
						$profile['approval_status'] = 'Rejected';
						break;
					case '0':
						$profile['approval_status'] = '<b>Pending Approval</b>';
						break;
					case '1':
						$profile['approval_status'] = 'Approved';
						break;
				}

				$profile['is_active'] = ( $profile['is_active'] ) ? 'Yes' : 'No';

				// MEMBERSHIP AFFILIATION OTHER
				if($profile['membership_affiliation_other']!=''){
					if($profile['membership_affiliation']!='') $profile['membership_affiliation'] .= ', ';
					$profile['membership_affiliation'] .= $profile['membership_affiliation_other'];
				}

				echo '
				<tr class="'.$row_bg_class.'">
					<td>'.$profile['approval_status'].'</td>
					<td>'.$profile['is_active'].'</td>
					<td>'.$profile['date_created'].'</td>
					<td><a href="/landscape-architects/profiles/'.$profile['id'].'" target="_blank">'.$profile['firm_name'].'</a></td>
					<td>'.$profile['first_name'].'</td>
					<td>'.$profile['last_name'].'</td>
					<td>'.$profile['email'].'</td>
					<td>'.$profile['membership_affiliation'].'</td>
					<td><a href="/landscape-architects/profiles/'.$profile['id'].'" target="_blank">View</a></td>
				</tr>';

				$i++;
			}
		?>
        </tbody>
	</table>
</div>

<? include('inc/footer.php'); ?>