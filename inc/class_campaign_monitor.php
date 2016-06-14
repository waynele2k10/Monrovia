<?
	require_once('class_sql.php');
	require('campaign_monitor/CMBase.php');
	
	class campaign_monitor_wrapper {
		function campaign_monitor_wrapper($api_key,$client_id,$campaign_id,$list_id){
			$this->api_key = $api_key;
			$this->client_id = $client_id;
			$this->list_id = $list_id;
			$this->campaign_id = $campaign_id;
			$this->cm = new CampaignMonitor($api_key,$client_id,$campaign_id,$list_id);
		}
		function upsert($email_address,$first_name,$last_name,$zip_code){
			// USE THIS METHOD TO SUBSRIBE AN EMAIL ADDRESS, OR TO UPDATE AN EXISTING USER

			$result = $this->cm->subscriberAddAndResubscribeWithCustomFields($email_address,$first_name,array('First Name'=>$first_name,'Last Name'=>$last_name,'Zip Code'=>$zip_code));

			// CAMPAIGN MONITOR DOESN'T SEEM TO SET NAME CORRECTLY IF PREVIOUSLY UNSUBSCRIBED, SO WE CALL IT AGAIN
			$result = $this->cm->subscriberAddAndResubscribeWithCustomFields($email_address,$first_name,array('First Name'=>$first_name,'Last Name'=>$last_name,'Zip Code'=>$zip_code));

			return ($result['Code']=='0');
		}
		function unsubscribe($email_address){
			// BLIND UNSUBSCRIBE
			$this->cm->subscriberUnsubscribe($email_address);
		}
		
		function get_subscription_status($email_address){
			$result = $this->cm->subscribersGetIsSubscribed($email_address);
			return ($result['anyType'] == "True")?"1":"0";			
		}
		
		function handle_actions(){
			global $monrovia_user;
			if(isset($_POST['action'])){
				if($_POST['action']=='set_interests'){
					$newsletter_interests = @str_replace('interest','',implode('|',$_POST['interestItems']));
					$newsletter_versions = @implode('|',$_POST['newsletterVersions']);
					// SAVE ONLY IF LOGGED IN (NOT DURING SIGNUP PROCESS)
					if(isset($monrovia_user)&&$monrovia_user->info['id']!=''){
						$monrovia_user->info['newsletter'] = '1';
						$monrovia_user->info['newsletter_interests'] = $newsletter_interests;
						$monrovia_user->info['newsletter_versions'] = $newsletter_versions;
						$monrovia_user->save();
						$url_redirect = '/community/update-profile.php?notice=changes_saved&action=newsletter_subscribe';

						header('location:'.$url_redirect.'&success='.($this->upsert($monrovia_user->info['email_address'],$monrovia_user->info['first_name'],$monrovia_user->info['last_name'],$monrovia_user->info['zip'])?'true':'false'));
						exit;

					}else if(isset($_SESSION['new_registration'])){
						// DESERIALIZE AND UPDATE
						$new_registration = from_json($_SESSION['new_registration']);
						$new_registration->newsletter_interests = $newsletter_interests;
						$new_registration->newsletter_versions = $newsletter_versions;

						// RESERIALIZE AND SAVE
						$_SESSION['new_registration'] = to_json($new_registration);

						header('location:/community/register-user-agreement.php?action=newsletter_subscribe&success='.($this->upsert($new_registration->email_address,$new_registration->first_name,$new_registration->last_name,$new_registration->zip)?'true':'false'));
						exit;

					}else{
						// THIS SHOULD NEVER HIT
						//@header('location:/');
						//exit;
					}

					// PREFERENCES UPDATED.
					//exit;
				}else if($_POST['action']=='unsubscribe'){
						$unsubscribe_email = strtolower($_POST['subscriber']);

						// SQL INJECTION-SAFE
						if(is_suspicious($unsubscribe_email)) exit;

						$subscribed = (intval(@mysql_result(sql_query("SELECT COUNT(*) as total FROM monrovia_users WHERE LOWER(email_address)='".$unsubscribe_email."' AND newsletter='1'"),0,"total"))>0);
						if(!$subscribed){
							// THIS SHOULD NEVER HIT
							@header('location:/');
						}else{
							$this->unsubscribe($unsubscribe_email);
							header('location:/community/newsletter-unsubscribe.php?action=newsletter_unsubscribe&subscriber='.base64_encode($unsubscribe_email));
						}
					exit;
				}
			}
		}
		
	}

	$cm = new campaign_monitor_wrapper($campaign_monitor_api_key,$campaign_monitor_client_id,'',$campaign_monitor_list_id);
	$cm->handle_actions();
?>