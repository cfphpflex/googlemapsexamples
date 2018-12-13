<?php

    defined('BASEPATH') OR exit('No direct script access allowed');


    // control memory		
    ini_set('memory_limit', '-1');
    ini_set('output_buffering', 4092);
    set_include_path(dirname(dirname(__FILE__)));  //echo "<br>";

    /***************************************************************************
     * Function: Google Apps Controller
     * Description: main gmail groups controller
     * Parameters:  none
     *
     **************************************************************************/

    class gappscontroller extends CI_Controller
    {
        /***************************************************************************
         * Function: index
         * Description: default function in framework
         * Parameters:  none
         *
         **************************************************************************/
        function index()
        {  //USE to get Code & Access token for Google API
            $this->load->model('host');
            //$this->load->model('gappsapimodel');
            if (isset($_GET["code"])) {  //STEP 2 GET GOOGLE API  ACCESS TOKEN
                $getaccesstoken = $this->get_oauth2_token($_GET["code"]);  //var_dump($getaccesstoken);
                $this->load->model('host'); // display
                $this->load->view('navigation.php', array('_POST' => $_POST), array('_REQUEST' => $_REQUEST),
                    array('_GET' => $_GET), $this, $getaccesstoken); // display
                switch ($_GET["state"]) {
                    case 'groups':
                        $this->getgroupsview(array('_POST' => $_POST), array('_REQUEST' => $_REQUEST),
                            array('_GET' => $_GET), $this, $getaccesstoken);
                        break;
                    case 'users':
                        $this->getusersview(array('_POST' => $_POST), array('_REQUEST' => $_REQUEST),
                            array('_GET' => $_GET), $this, $getaccesstoken);
                        break;
                    case 'getgroupsview':
                        $this->getgroupsview(array('_POST' => $_POST), array('_REQUEST' => $_REQUEST),
                            array('_GET' => $_GET), $this, $getaccesstoken);
                        break;
                    case 'newgroupmember':
                        $this->getgroupsdata(array('_POST' => $_POST), array('_REQUEST' => $_REQUEST),
                            array('_GET' => $_GET), $this, $getaccesstoken);
                        break;
                    case 'getgroupsmemberdata':
                        $this->getgroupsmemberdata(array('_POST' => $_POST), array('_REQUEST' => $_REQUEST),
                            array('_GET' => $_GET), $this, $getaccesstoken);
                        break;
                    case 'getgroupsview':
                        $this->getgroupsview(array('_POST' => $_POST), array('_REQUEST' => $_REQUEST),
                            array('_GET' => $_GET), $this, $getaccesstoken);
                        break;
                    case 'getusersdata':
                        $this->getusersdata(array('_POST' => $_POST), array('_REQUEST' => $_REQUEST),
                            array('_GET' => $_GET), $this, $getaccesstoken);
                        break;
                    case 'createemailuser':
                        $this->createemailuser(array('_POST' => $_POST), array('_REQUEST' => $_REQUEST),
                            array('_GET' => $_GET), $this, $getaccesstoken);
                        break;
                    case 'newgroupmember':
                        $this->newgroupmember(array('_POST' => $_POST), array('_REQUEST' => $_REQUEST),
                            array('_GET' => $_GET), $this, $getaccesstoken);
                        break;
                    case 'newemailuser':
                        $this->newemailuser(array('_POST' => $_POST), array('_REQUEST' => $_REQUEST),
                            array('_GET' => $_GET), $this, $getaccesstoken);
                        break;
                    case 'restoreemails':
                        $this->restoreemails($getaccesstoken);
                        break;

                }

                var_dump($getaccesstoken);
            } ELSE { //STEP 1 GET GOOGLE API autho code
                $getaccesscode = $this->get_oauth2_code();     //			var_dump($getaccesstoken);
            }

        }
 
        
        /***************************************************************************
         * Function: get_oauth2_code
         * Description: auth
         * Parameters:  none
         *
         **************************************************************************/
        protected function get_oauth2_code()
        {


            ini_set('error_reporting', E_STRICT);
            if (!isset($res)) {
                $res = new stdClass();
            }

            $res->success = false;
            
            //DADMIN CREDS
            $clientid = '-key.apps.googleusercontent.com';
            $KEY = '-key.apps.googleusercontent.com';
            $SECRET = 's-somesecre';
            $CALLBACK_URL = 'http://localhost/hr-portal/newportal/index.php/gappscontroller';


            //SCOPE CONTROL
            $service = $_GET['service'];

            //Insert email project due to Vault deleting email
            if ($_GET['service'] == 'restoreemails') {
                $scope = "  https://mail.google.com/    https://www.googleapis.com/auth/gmail.modify   https://www.googleapis.com/auth/gmail.compose       ";
            }


            if ($_GET['service'] == 'groups' || $_GET['service'] == 'newgroup' || $_GET['service'] == 'newgroupmember') {
                $scope = "https://www.googleapis.com/auth/admin.directory.group";
            }

            //ADD NEW Group Member Scope
            if ($_GET['service'] == 'newgroupmember') {
                $scope = "  https://www.googleapis.com/auth/admin.directory.group  https://www.googleapis.com/auth/admin.directory.group.member";
            }

            //Scope to get users
            if ($_GET['service'] == 'users') {
                $scope = "https://www.googleapis.com/auth/admin.directory.user";
            }

            //Scope to get users
            if ($_GET['service'] == 'getallgroupmembers') {
                $scope = "  https://www.googleapis.com/auth/admin.directory.group  https://www.googleapis.com/auth/admin.directory.group.member";
            }

            //Scope to get users
            if ($_GET['service'] == 'newemailuser') {
                $memberKey = 'key';
                $scope = "https://www.googleapis.com/auth/admin.directory.user";
            }

            //don't touch
            $AUTHORIZATION_ENDPOINT = 'https://accounts.google.com/o/oauth2/auth';
            $ACCESS_TOKEN_ENDPOINT = 'https://accounts.google.com/o/oauth2/token/';


            $auth_url = $AUTHORIZATION_ENDPOINT
                . "?client_id=" . $clientid
                . "&response_type=code"
                . "&scope=" . $scope
                . "&redirect_uri=" . $CALLBACK_URL
                . "&login_hint=dadmin@hortonworks.com"
                . "&state=" . $service;
            header("Location: $auth_url");
        }


        /***************************************************************************
         * Function: get_oauth2_token  
         * Description: auth
         * Parameters: code (string) - code
         *
         **************************************************************************/
        protected  function get_oauth2_token($code)
        {
            $code = $_GET["code"];
            $clientid = 'key.apps.googleusercontent.com';
            $KEY = 'keu-key.apps.googleusercontent.com';
            $SECRET = 's-secret';
            $CALLBACK_URL = 'http://localhost/hr-portal/newportal/index.php/gappscontroller';

            //don't touch
            $AUTHORIZATION_ENDPOINT = 'https://accounts.google.com/o/oauth2/auth';
            $ACCESS_TOKEN_ENDPOINT = 'https://accounts.google.com/o/oauth2/token';

            //construct POST object for access token fetch request
            $postvals = array(
                'code' => $code,
                'client_id' => $KEY,
                'client_secret' => $SECRET,
                'redirect_uri' => $CALLBACK_URL,

                'grant_type' => 'authorization_code'
            );

            //get JSON access token object (with refresh_token parameter)
            $token = json_decode($this->run_curl($ACCESS_TOKEN_ENDPOINT, 'POST', $postvals));

            return $token;
        }


        /***************************************************************************
         * Function: Download page
         * Description: download page
         * Parameters: auth_url (string) - url to auuth
         *        
         **************************************************************************/
        protected function download_page($auth_url)
        {
            $curl_Response = run_curl($auth_url); //var_dump($curl_Response);
            $authObj = json_decode($curl_Response);
            //$accessToken = $authObj->access_token;
            return $authObj;

        }


        /***************************************************************************
         * Function: Run CURL
         * Description: Executes a CURL request
         * Parameters: url (string) - URL to make request to
         *             method (string) - HTTP transfer method
         *             headers - HTTP transfer headers
         *             postvals - post values
         **************************************************************************/
       private function run_curl($url, $method = 'GET', $postvals = null)
        {
            $ch = curl_init($url);

            //GET request: send headers and return data transfer
            if ($method == 'GET') {
                $options = array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => 1
                );
                curl_setopt_array($ch, $options);
                //POST / PUT request: send post object and return data transfer
            } else {
                $options = array(
                    CURLOPT_URL => $url,
                    CURLOPT_POST => 1,
                    CURLOPT_POSTFIELDS => $postvals,
                    CURLOPT_RETURNTRANSFER => 1
                );
                curl_setopt_array($ch, $options);
            }

            $response = curl_exec($ch);
            curl_close($ch);

            return $response;
        }

        /***************************************************************************
         * Function: Refresh Access Token
         * Description: Refreshes an expired access token
         * Parameters: key (string) - application consumer key
         *             secret (string) - application consumer secret
         *             refresh_token (string) - refresh_token parameter passed in
         *                to fetch access token request.
         **************************************************************************/
        private function refreshToken($refresh_token)
        {
            //construct POST object required for refresh token fetch
            $postvals = array(
                'grant_type' => 'refresh_token',
                'client_id' => KEY,
                'client_secret' => SECRET,
                'refresh_token' => $refresh_token,
            );

            //return JSON refreshed access token object
            return json_decode(run_curl(ACCESS_TOKEN_ENDPOINT, 'POST', $postvals));
        }

    }





 