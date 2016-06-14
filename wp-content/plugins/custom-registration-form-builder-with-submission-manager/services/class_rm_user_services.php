<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Class responsible for User and Roles related operations
 *
 * @author CMSHelplive
 */
class RM_User_Services extends RM_Services
{

    private $default_user_roles = array('administrator', 'editor', 'author', 'contributor', 'subscriber');

    public function get_user_roles()
    {
        $roles = get_editable_roles();
        //echo '<pre>';var_dump($roles);die;
        $role_names = array();
        foreach ($roles as $key => $role)
        {
            $role_names[$key] = $role['name'];
        }

        return $role_names;
    }

    // This function creates a copy of the role with a different name
    public function create_role($role_name, $display_name, $capability)
    {
        $role = get_role($capability);
        if (add_role($role_name, $display_name, $role->capabilities) !== null)
            return true;
        else
            return false;
    }

    public function get_roles_by_status()
    {
        $roles_data = new stdClass();
        $roles = $this->get_user_roles();
        $custom = array();
        $default = array();
        foreach ($roles as $key => $role)
        {
            if (in_array($key, $this->default_user_roles))
            {
                $default[$key] = $role;
            } else
            {
                $custom[$key] = $role;
            }
        }
        $roles_data->default = $default;
        $roles_data->custom = $custom;

        return $roles_data;
    }

    public function delete($users)
    {
        if (is_array($users) && !empty($users))
        {
            $curr_user = wp_get_current_user();
            if (isset($curr_user->ID))
                $curr_user_id = $curr_user->ID;
            else
                $curr_user_id = null;
            foreach ($users as $id)
            {
                if ($curr_user_id != $id)
                    wp_delete_user($id);
            }
        }
    }

    public function activate($users)
    {
        if (is_array($users) && !empty($users))
        {
            foreach ($users as $id)
            {
                update_user_meta($id, 'rm_user_status', '0');
            }
        }
    }

    public function deactivate_user_by_id($user_id)
    {
        $curr_user = wp_get_current_user();
        if (isset($curr_user->ID))
            $curr_user_id = $curr_user->ID;
        else
            $curr_user_id = null;
        if ($curr_user_id != $user_id)
            update_user_meta($user_id, 'rm_user_status', '1');
    }

    public function activate_user_by_id($user_id)
    {
        return update_user_meta($user_id, 'rm_user_status', '0');
    }

    public function deactivate($users)
    {
        if (is_array($users) && !empty($users))
        {
            $curr_user = wp_get_current_user();
            if (isset($curr_user->ID))
                $curr_user_id = $curr_user->ID;
            else
                $curr_user_id = null;
            foreach ($users as $id)
            {
                if ($curr_user_id != $id)
                    update_user_meta($id, 'rm_user_status', '1');
            }
        }
    }

    public function delete_roles($roles)
    {
        if (is_array($roles) && !empty($roles))
        {
            foreach ($roles as $name)
            {
                $users = $this->get_users_by_role($name);
                foreach ($users as $user)
                {
                    $user->add_role('subscriber');
                }

                remove_role($name);
            }
        }
    }

    public function get_users_by_role($role_name)
    {
        $args = array('role' => $role_name);
        $users = get_users($args);
        return $users;
    }

    public function get_user_count()
    {
        $result = count_users();
        $total_users = $result['total_users'];
        return $total_users;
    }

    public function get_users($offset = '', $number = '', $search_str = '', $user_status = 'all', $interval = 'all', $user_ids = array())
    {
        $args = array('number' => $number, 'offset' => $offset, 'include' => $user_ids, 'search' => '*' . $search_str . '*');
        //$args = array();

        switch ($user_status)
        {
            case 'active':
                $args['meta_query'] = array('relation' => 'OR',
                    array(
                        'key' => 'rm_user_status',
                        'value' => '1',
                        'compare' => '!='
                    ),
                    array(
                        'key' => 'rm_user_status',
                        'value' => '1',
                        'compare' => 'NOT EXISTS'
                ));
                break;

            case 'pending':
                $args['meta_query'] = array(array(
                        'key' => 'rm_user_status',
                        'value' => '1',
                        'compare' => '='
                ));
                break;
        }

        switch ($interval)
        {
            case 'today':
                $args['date_query'] = array(array('after' => 'today', 'inclusive' => true));
                break;

            case 'week':
                $args['date_query'] = array(array('after' => 'this week', 'inclusive' => true));
                break;

            case 'month':
                $args['date_query'] = array(array('after' => 'first day of this month', 'inclusive' => true));
                break;

            case 'year':
                $args['date_query'] = array(array('year' => date('Y'), 'inclusive' => true));
                break;
        }
        //echo "Args:<pre>", var_dump($args), "</pre>";

        $users = get_users($args);

        return $users;
    }

    public function get_total_user_per_pagination()
    {
        $total = $this->get_user_count();
        return (int) ($total / 2) + (($total % 2) == 0 ? 0 : 1);
    }

    public function get_all_user_data($page = '1', $number = '20', $search_str = '', $user_status = 'all', $interval = 'all', $user_ids = array())
    {
        $offset = ($page * $number) - $number;
        $all_user_info = $this->get_users($offset, $number, $search_str, $user_status, $interval, $user_ids);
        $all_user_data = array();

        foreach ($all_user_info as $user)
        {

            $tmpuser = new stdClass();
            $user_info = get_userdata($user->ID);
            $is_disabled = (int) get_user_meta($user->ID, 'rm_user_status', true);
            $tmpuser->ID = $user->ID;

            // echo'<pre>';var_dump($user_info);die;

            if (empty($user_info->display_name))
                $tmpuser->first_name = $user_info->first_name;
            else
                $tmpuser->first_name = $user_info->display_name;

            if (isset($user_info->user_email))
                $tmpuser->user_email = $user_info->user_email;
            else
                $tmpuser->user_email = '';

            if ($is_disabled == 1)
                $tmpuser->user_status = RM_UI_Strings::get('LABEL_DEACTIVATED');
            else
                $tmpuser->user_status = RM_UI_Strings::get('LABEL_ACTIVATED');

            $tmpuser->date = $user_info->user_registered;

            $all_user_data[] = $tmpuser;
        }

        return $all_user_data;
    }

    public function get_user_by($field, $value)
    {
        $user = get_user_by($field, $value);
        return $user;
    }

    public function login($request)
    {

        global $user;
        $credentials = array();
        $credentials['user_login'] = $request->req['username'];
        $credentials['user_password'] = $request->req['password'];
        if (isset($request->req['remember']))
            $credentials['remember'] = true;
        else
            $credentials['remember'] = false;

        require_once(ABSPATH . 'wp-load.php');
        require_once(ABSPATH . 'wp-includes/pluggable.php');
        $user = wp_signon($credentials, false);
        return $user;
    }

    public function facebook_login_html()
    {
        global $rm_fb_sdk_req;
        $gopts = new RM_Options;
        $current_uri = RM_Utilities::get_current_url();
        //var_dump($current_uri);
        //var_dump($_GET['fbcb']);
        $sign = strpos($current_uri, '?') === FALSE ? '?' : '&';
        //var_dump($current_uri.$sign.'rm_target=fbcb');
        //die;
        if ($gopts->get_value_of('enable_facebook') == 'yes')
        {
            $fb_app_id = $gopts->get_value_of('facebook_app_id');
            $fb_app_secret = $gopts->get_value_of('facebook_app_secret');

            if (!$fb_app_id || !$fb_app_secret)
                return;

            if ($rm_fb_sdk_req === RM_FB_SDK_REQ_OK)
            {
                $fb = new Facebook\Facebook(array(
                    'app_id' => $fb_app_id,
                    'app_secret' => $fb_app_secret,
                    'default_graph_version' => 'v2.2',
                ));

                $helper = $fb->getRedirectLoginHelper();

                $permissions = array('email'); // Optional permissions
                $loginUrl = $helper->getLoginUrl($current_uri . $sign . 'rm_target=fbcb', $permissions);
                return '<div class="facebook_login"><a href="' . htmlspecialchars($loginUrl) . '">Log in with Facebook!</a></div>';
            } else
            {
                $fb = new Facebook(array(
                    'appId' => $fb_app_id,
                    'secret' => $fb_app_secret
                ));

                $loginUrl = $fb->getLoginUrl(array('redirect_uri' => $current_uri . $sign . 'rm_target=fbcb'));
                return '<div class="facebook_login"><a href="' . htmlspecialchars($loginUrl) . '">Log in with Facebook!</a></div>';
            }
        }
    }

    public function facebook_login_callback()
    {
        global $rm_fb_sdk_req;
        $gopts = new RM_Options;

        $fb_app_id = $gopts->get_value_of('facebook_app_id');
        $fb_app_secret = $gopts->get_value_of('facebook_app_secret');

        if (!$fb_app_id || !$fb_app_secret)
            return;

        if ($rm_fb_sdk_req === RM_FB_SDK_REQ_OK)
        {
            $fb = new Facebook\Facebook(array(
                'app_id' => $fb_app_id,
                'app_secret' => $fb_app_secret,
                'default_graph_version' => 'v2.2',
            ));

            $helper = $fb->getRedirectLoginHelper();

            try
            {
                $accessToken = $helper->getAccessToken();
            } catch (Facebook\Exceptions\FacebookResponseException $e)
            {
                // When Graph returns an error
                echo 'Graph returned an error: ' . $e->getMessage();
                exit;
            } catch (Facebook\Exceptions\FacebookSDKException $e)
            {
                // When validation fails or other local issues
                echo 'Facebook SDK returned an error: ' . $e->getMessage();
                exit;
            }

            if (!isset($accessToken))
            {
                if ($helper->getError())
                {
                    header('HTTP/1.0 401 Unauthorized');
                    echo "Error: " . $helper->getError() . "\n";
                    echo "Error Code: " . $helper->getErrorCode() . "\n";
                    echo "Error Reason: " . $helper->getErrorReason() . "\n";
                    echo "Error Description: " . $helper->getErrorDescription() . "\n";
                } else
                {
                    header('HTTP/1.0 400 Bad Request');
                    echo 'Bad request';
                }
                exit;
            }

            // Logged in
            // echo '<h3>Access Token</h3>';
            //var_dump($accessToken->getValue());
            // The OAuth 2.0 client handler helps us manage access tokens
            $oAuth2Client = $fb->getOAuth2Client();

            // Get the access token metadata from /debug_token
            $tokenMetadata = $oAuth2Client->debugToken($accessToken);

            //echo '<h3>Metadata</h3>';
            //var_dump($tokenMetadata);
            // Validation (these will throw FacebookSDKException's when they fail)

            $tokenMetadata->validateAppId($fb_app_id); // Replace {app-id} with your app id
            // If you know the user ID this access token belongs to, you can validate it here
            //$tokenMetadata->validateUserId('123');
            $tokenMetadata->validateExpiration();

            if (!$accessToken->isLongLived())
            {
                // Exchanges a short-lived access token for a long-lived one
                try
                {
                    $accessToken2 = $oAuth2Client->getLongLivedAccessToken($accessToken);
                } catch (Facebook\Exceptions\FacebookSDKException $e)
                {
                    echo "<p>Error getting long-lived access token: " . $helper->getMessage() . "</p>\n\n";
                    exit;
                }

                //echo '<h3>Long-lived</h3>';
                //var_dump($accessToken2->getValue());
            }



            //$_SESSION['fb_access_token'] = (string) $accessToken;



            try
            {
                // Returns a `Facebook\FacebookResponse` object
                $response = $fb->get('/me?fields=id,name,email,first_name,last_name', (string) $accessToken);
            } catch (Facebook\Exceptions\FacebookResponseException $e)
            {
                echo 'Graph returned an error: ' . $e->getMessage();
                exit;
            } catch (Facebook\Exceptions\FacebookSDKException $e)
            {
                echo 'Facebook SDK returned an error: ' . $e->getMessage();
                exit;
            }

            $user = $response->getGraphUser();

            //var_dump($user->getFirstName());
            $user_name = $user->getName();
            $user_email = $user->getEmail();
            $user_fname = $user->getFirstName();
            $user_lname = $user->getLastName();
            $redirection_post = $gopts->get_value_of('post_submission_redirection_url');

            if (email_exists($user_email))
            { // user is a member
                $user = get_user_by('email', $user_email);

                $user_id = $user->ID;

                wp_set_auth_cookie($user_id, true);
            } else
            { // this user is a guest
                $random_password = wp_generate_password(10, false);

                $user_id = wp_create_user($user_email, $random_password, $user_email);

                update_user_meta($user_id, 'avatar_image', 'https://graph.facebook.com/' . $user->getId() . '/picture?type=large');

                wp_update_user(array(
                    'ID' => $user_id,
                    'display_name' => $user_name,
                    'first_name' => $user_fname,
                    'last_name' => $user_lname
                ));


                wp_set_auth_cookie($user_id, true);
            }
        } else
        {
            $fb = new Facebook(array(
                'appId' => $fb_app_id,
                'secret' => $fb_app_secret
            ));

            $user = $fb->getUser();

            if ($user)
            {
                $user_profile = $fb->api('/me?fields=id,name,email,first_name,last_name');
                $user_email = $user_profile['email'];

                $redirection_post = $gopts->get_value_of('post_submission_redirection_url');

                if (email_exists($user_email))
                { // user is a member
                    $user = get_user_by('email', $user_email);
                    $user_id = $user->ID;
                    wp_set_auth_cookie($user_id, true);
                } else
                { // this user is a guest
                    $random_password = wp_generate_password(10, false);

                    $user_id = wp_create_user($user_email, $random_password, $user_email);

                    update_user_meta($user_id, 'avatar_image', 'https://graph.facebook.com/' . $user_profile['id'] . '/picture?type=large');

                    wp_update_user(array(
                        'ID' => $user_id,
                        'display_name' => $user_profile['name'],
                        'first_name' => $user_profile['first_name'],
                        'last_name' => $user_profile['last_name']
                    ));


                    wp_set_auth_cookie($user_id, true);
                }
            }
        }

        if ($redirection_post > 0)
        {
            $after_login_url = get_permalink($redirection_post);
        } else
        {
            $after_login_url = home_url();
        }
        RM_Utilities::redirect($after_login_url);
    }

    public function set_user_role($user_id, $role)
    {
        $user = new WP_User($user_id);
        $user->set_role($role);
    }

    /*
      public function user_search($criterions, $type)
      {
      $user_ids = array();


      if ($type == "time")
      {
      $search_periods = array();
      foreach ($criterions as $period)
      {
      switch ($period)
      {
      case "today": $search_periods['today'] = array("start" => date('Y-m-d'), "end" => date('Y-m-d', strtotime("+1 day")));
      break;
      case "yesterday": $search_periods['yesterday'] = array("start" => date('Y-m-d', strtotime("-1 days")), "end" => date('Y-m-d'));
      break;
      case "this_week": $search_periods['this_week'] = array("start" => date('Y-m-d', strtotime("this week")), "end" => date('Y-m-d', strtotime("+1 day")));
      break;
      case "last_week": $search_periods['last_week'] = array("start" => date('Y-m-d', strtotime("last week")), "end" => date('Y-m-d', strtotime("+1 day")));
      break;
      case "this_month": $search_periods['this_month'] = array("start" => date("Y-m") . '-01', "end" => date('Y-m-d', strtotime("+1 day")));
      break;
      case "this_year": $search_periods['this_year'] = array("start" => date("Y") . '-01-01', "end" => date('Y-m-d', strtotime("+1 day")));
      break;
      }
      }
      $user_ids = RM_DBManager::sidebar_user_search($search_periods, $type);
      }

      echo 'TIme: ';
      print_r($user_ids);
      if ($type == "user_status")
      {
      $user_ids = RM_DBManager::sidebar_user_search($criterions, $type);
      echo 'Status: ';
      print_r($user_ids);
      }


      if ($type == "type")
      {
      foreach ($criterions as $el)
      {
      if ($type == "name")
      {
      $user_ids = RM_DBManager::sidebar_user_search($criterions, $type);
      echo 'name: ';
      print_r($user_ids);
      break;
      }


      if ($type == "email")
      {
      $user_ids = RM_DBManager::sidebar_user_search($criterions, $type);
      echo 'Email: ';
      print_r($user_ids);
      break;
      }
      }
      }


      die;

      return $user_ids;
      }
     */

    public function reset_user_password($pass, $conf, $user_id)
    {
        if ($pass && $conf && $user_id)
        {
            if ($pass === $conf)
            {
                wp_set_password($pass, $user_id);
            }
        } else
        {
            throw new InvalidArgumentException("Invalid Argument Supplied in " . __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function create_user_activation_link($user_id)
    {
        if ((int) $user_id)
        {
            $pass = wp_generate_password(10, false);
            $activation_code = md5($pass);

            if (!update_user_meta($user_id, 'rm_activation_code', $activation_code))
                return false;

            $user_data_obj = new stdClass();
            $user_data_obj->user_id = $user_id;
            $user_data_obj->activation_code = $activation_code;

            $user_data_json = json_encode($user_data_obj);

            $user_data_enc = urlencode(RM_Utilities::enc_str($user_data_json));

            $user_activation_link = admin_url('admin-ajax.php') . '?action=rm_activate_user&user=' . $user_data_enc;

            return $user_activation_link;
        }

        return false;
    }

}
