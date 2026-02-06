<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Elementor\Controls_Manager;
use ElementorPro\Modules\Forms\Classes\Ajax_Handler;
use ElementorPro\Modules\Forms\Classes\Integration_Base;
use Elementor\Settings;
class Yeeaddons_EL_Dropbox_API {
    public static function get_token($clientId,$clientSecret,$authorizationCode){
        $url = "https://api.dropbox.com/oauth2/token";
        //$authorizationCode = "BJ8qO0zpOjAAAAAAAAAyYfC1TjEznVFRrWsE3DSARjI";
        $data = [
            "code" => $authorizationCode,
            "grant_type" => "authorization_code",
            "client_id" => $clientId,
            "client_secret" => $clientSecret
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $response = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($response, true);
        if (isset($response["access_token"])) {
            update_option( "_yeeaddons_dropbox_api_token", $response);
            update_option( "_yeeaddons_dropbox_api_token_refresh_token", $response["refresh_token"]);
            return "ok";
        }else{
            if(isset($response["error_description"])){
                return $response["error_description"];
            }else{
                return "error";
            }
        }
    }
    public static function uppload_files($fileTmpPath) {
        $data_dropbox = get_option("_yeeaddons_dropbox_api_token");
        $refresh_token = get_option("_yeeaddons_dropbox_api_token_refresh_token");
        if(isset($data_dropbox["access_token"])) {
            $clientId = get_option("elementor_yeeaddons_drobox_client_id");
            $clientSecret = get_option("elementor_yeeaddons_dropbox_client_secret");
            $accessToken = $data_dropbox["access_token"];
            $accessToken_ok = self::checkAccessToken($accessToken,$refresh_token,$clientId,$clientSecret);
            $filename = basename($fileTmpPath);
            $dropboxPath = '/' . $filename;
            $file = fopen($fileTmpPath, 'rb');
            $fileSize = filesize($fileTmpPath);
            $ch = curl_init('https://content.dropboxapi.com/2/files/upload');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $accessToken_ok,
                'Content-Type: application/octet-stream',
                'Dropbox-API-Arg: ' . json_encode([
                    "path" => $dropboxPath,
                    "mode" => "add",
                    "autorename" => true,
                    "mute" => false
                ])
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, fread($file, $fileSize));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
            fclose($file);
        }
    }
    public static function checkAccessToken($access_token,$refresh_token,$clientId,$clientSecret) {
       $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.dropboxapi.com/2/users/get_current_account',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer '.$access_token
          ),
        ));
        $response = curl_exec($curl);
        $result = json_decode($response, true);
        if(!isset($result["account_id"])) {
            return self::getNewAccessToken($refresh_token, $clientId, $clientSecret,$access_token);
        }else{
            return $access_token;
        }
    }
    public static function getNewAccessToken($refresh_token, $clientId, $clientSecret,$access_token) {
        $url = "https://api.dropbox.com/oauth2/token";
        $data = [
            "refresh_token" => $refresh_token,
            "grant_type" => "refresh_token",
            "client_id" => $clientId,
            "client_secret" => $clientSecret
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/x-www-form-urlencoded"]);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response, true);
        if (isset($result['access_token'])) {
            update_option( "_yeeaddons_dropbox_api_token", $result);
            return $result['access_token'];
        }
        return $access_token;
    }
}
class Yeeaddons_EL_Dropbox_API_Setttings{
    const OPTION_NAME_API_KEY = 'yeeaddons_drobox_client_id';
    function __construct(){
        if ( is_admin() ) {
            add_action( 'elementor/admin/after_create_settings/' . Settings::PAGE_ID, [ $this, 'register_admin_fields' ], 14 );
        }
        add_action( 'wp_ajax_' . self::OPTION_NAME_API_KEY . '_validate', [ $this, 'ajax_validate_api_token' ] );
    }
    public function ajax_validate_api_token() {
        check_ajax_referer( self::OPTION_NAME_API_KEY, '_nonce' );
        $clientId = sanitize_text_field($_POST['clientId']);
        $clientSecret = sanitize_text_field($_POST['clientSecret']);
        $authorizationCode = sanitize_text_field($_POST['authorizationCode']);
        if ( ! isset( $_POST['clientId'] ) ) {
            wp_send_json_error();
        }
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Permission denied' );
        }
        try {
           $datas = Yeeaddons_EL_Dropbox_API::get_token($clientId,$clientSecret,$authorizationCode);
           if($datas == "ok"){
                wp_send_json_success($datas);
           }else{
                wp_send_json_error($datas);
           }
        } catch ( \Exception $exception ) {
            wp_send_json_error();
        }
        wp_send_json_success();
    }
    public function register_admin_fields( Settings $settings ) {
        $clientId = get_option("elementor_yeeaddons_drobox_client_id");
        if($clientId == ""){
            $clientId = "s7rcm7zm4d1as2e";
        }
        $settings->add_section( Settings::TAB_INTEGRATIONS, 'yeeaddons_dropbox_api', [
            'callback' => function() {
                echo '<hr><h2>' . esc_html__( 'Dropbox API Drag and Drop File Upload', 'elementor-pro' ) . '</h2>';
            },
            'fields' => [
                self::OPTION_NAME_API_KEY => [
                    'label' => esc_html__( 'Client id', 'elementor-pro' ),
                    'field_args' => [
                        'type' => 'text',
                        'desc' => sprintf(
                            /* translators: 1: Link opening tag, 2: Link closing tag. */
                            esc_html__( 'Document %1$ssee more%2$s.', 'elementor-pro' ),
                            '<a href="https://add-ons.org/creating-a-new-application-on-dropbox-for-upload-files/" target="_blank">',
                            '</a>'
                        ),
                    ],
                ],
                "yeeaddons_dropbox_client_secret" => [
                    'label' => esc_html__( 'Client secret', 'elementor-pro' ),
                    'field_args' => [
                        'type' => 'text',
                    ],
                ],
                "yeeaddons_dropbox_access_code" => [
                    'label' => esc_html__( 'Access Code', 'elementor-pro' ),
                    'field_args' => [
                        'type' => 'text',
                        'desc' => sprintf( '<a id="yeeaddons_dropbox_api_getcode" href="https://www.dropbox.com/oauth2/authorize?client_id=%s&response_type=code&token_access_type=offline" target="_blank">%s</a>',$clientId, esc_html__( 'Get Access Code', 'elementor-pro' ) ),
                    ],
                ],
                'validate_api_data' => [
                    'field_args' => [
                        'type' => 'raw_html',
                        'html' => sprintf( '<button data-action="%s" data-nonce="%s" class="button elementor-button-spinner" id="elementor_pro_yeeaddons_dropbox_api_key_button">%s</button>', self::OPTION_NAME_API_KEY . '_validate', wp_create_nonce( self::OPTION_NAME_API_KEY ), esc_html__( 'Validate API Key', 'elementor-pro' ) ),
                    ],
                ],
            ],
        ] );
    }
}
new Yeeaddons_EL_Dropbox_API_Setttings;