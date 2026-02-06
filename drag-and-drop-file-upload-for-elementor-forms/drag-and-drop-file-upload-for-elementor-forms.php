<?php
/*
 * Plugin Name: Drag and Drop File Upload for Elementor Forms
 * Plugin URI: https://wordpress.org/plugins/drag-and-drop-file-upload-for-elementor-forms/
 * Description: Which allows the user to upload multiple files using the drag-and-drop feature or the common browse-file of your webform.
 * Requires Plugins: elementor
 * Author: add-ons.org
 * Author URI: https://add-ons.org/
 * Version: 1.5.5
 * Requires PHP: 5.2
 * Elementor tested up to: 3.28
 * Elementor pro tested up to: 3.28
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if (!defined('SUPERADDONS_FILE_UPLOAD_PLUGIN_PATH')) {
    define( 'SUPERADDONS_FILE_UPLOAD_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
    define( 'SUPERADDONS_FILE_UPLOAD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
    add_action( 'elementor_pro/forms/fields/register', 'superaddons_elementor_pro_add_upload_field' );
    function superaddons_elementor_pro_add_upload_field($form_fields_registrar){
        require_once( SUPERADDONS_FILE_UPLOAD_PLUGIN_PATH."fields/file_upload.php" );
        require_once( SUPERADDONS_FILE_UPLOAD_PLUGIN_PATH."fields/dropbox.php" );
        $form_fields_registrar->register( new \Superaddons_EL_File_Uploads() );
    }
    include SUPERADDONS_FILE_UPLOAD_PLUGIN_PATH."yeekit/document.php";
} 