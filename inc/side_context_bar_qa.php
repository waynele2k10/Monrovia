<?
	$subcategories = get_subcategories(0);
	if(count($subcategories)){ ?>
		<div>
			<h3>main Q&amp;A categories</h3>
			<ul>
				<?
					for($i=0;$i<count($subcategories);$i++){
					?>
						<li><a href="<?=$subcategories[$i]->full_path?>"><?=$subcategories[$i]->info['name']?></a></li>
					<?
					}
				?>
			</ul>
		</div>
<? } ?>
<? if($monrovia_user->is_logged_in()){ ?>
	<div>
		<h3>my Q&amp;A snapshot</h3>
		<table style="width:145px;margin-top:12px;" class="module_wrapper yellow">
			<tbody>
				<tr>
					<td class="corner top_left"></td>
					<td class="top_center"></td>
					<td class="corner top_right"></td>
				</tr>
				<tr>
					<td class="side_left"></td>
					<td class="content">
						<div class="avatar" style="margin-right:8px;">
							 <a href="/<?=$GLOBALS['server_info']['qa_root']?>/profiles/<?=$monrovia_user->info['user_name']?>"><img src="/img/qa/<?=$monrovia_user->info['avatar']!=''?$monrovia_user->info['avatar']:'avatar-generic.png'?>" alt="" /></a>
						</div>
						<div style="float:left;">
							<a href="/<?=$GLOBALS['server_info']['qa_root']?>/profiles/<?=$monrovia_user->info['user_name']?>"><?=$monrovia_user->info['user_name']?></a>
							<!--
							<? if($monrovia_user->info['website_url']!=''&&$monrovia_user->info['website_name']!=''){ ?>
								<a href="<?=$monrovia_user->info['website_url']?>" target="_blank"><?=$monrovia_user->info['website_name']?></a>
							<? } ?>
							-->
						</div>
						<div style="clear:both;"></div>
					</td>
					<td class="side_right"></td>
				</tr>
				<tr>
					<td class="corner bottom_left"></td>
					<td class="bottom_center"></td>
					<td class="corner bottom_right"></td>
				</tr>
			</tbody>
		</table>
		<ul>
			<li id="lnk_sidebar_qa_userpage"><a href="/<?=$GLOBALS['server_info']['qa_root']?>/profiles/<?=$monrovia_user->info['user_name']?>">view my profile</a></li>
			<li id="lnk_sidebar_qa_subscriptions"><a href="/<?=$GLOBALS['server_info']['qa_root']?>/questions-subscriptions.php">my questions and subscriptions</a></li>
			<li id="lnk_sidebar_qa_scrapbook"><a href="/<?=$GLOBALS['server_info']['qa_root']?>/scrapbook.php">my scrapbook</a></li>
			<li id="lnk_sidebar_qa_settings"><a href="/<?=$GLOBALS['server_info']['qa_root']?>/update-profile.php">update my profile and settings</a></li>
		</ul>
	</div>
<? } ?>