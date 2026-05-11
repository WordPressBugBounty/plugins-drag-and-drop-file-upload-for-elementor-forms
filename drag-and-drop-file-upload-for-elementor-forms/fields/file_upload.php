<?php
if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

use Elementor\Widget_Base;
use ElementorPro\Modules\Forms\Classes;
use Elementor\Controls_Manager;
use ElementorPro\Modules\Forms\Widgets\Form;
use ElementorPro\Plugin;
use ElementorPro\Core\Utils;
use ElementorPro\Core\Utils\Collection;
use ElementorPro\Modules\Forms\Fields\Upload;
use Elementor\Settings;

class Superaddons_EL_File_Uploads extends \ElementorPro\Modules\Forms\Fields\Field_Base
{
	public $fixed_files_indices = false;
	public $field_uploads = array();
	public $attachments_array = array();
	public function get_type()
	{
		return 'file_upload';
	}
	public function get_name()
	{
		return esc_html__('Drag and Drop Upload', 'drag-and-drop-file-upload-for-elementor-forms');
	}
	/**
	 * @param Widget_Base $widget
	 */
	public function update_controls($widget)
	{
		$elementor = Plugin::elementor();
		$control_data = $elementor->controls_manager->get_control_from_stack($widget->get_unique_name(), 'form_fields');
		if (is_wp_error($control_data)) {
			return;
		}
		$field_controls = [
			'file_upload_attachment_type' => [
				'name' => 'file_upload_attachment_type',
				'label' => esc_html__('Attachments in emails', 'drag-and-drop-file-upload-for-elementor-forms'),
				'type' => Controls_Manager::SWITCHER,
				'condition' => [
					'field_type' => $this->get_type(),
				],
				'description' => esc_html__("Attachments will be included in the email", 'drag-and-drop-file-upload-for-elementor-forms'),
				'tab' => 'content',
				'inner_tab' => 'form_fields_content_tab',
				'tabs_wrapper' => 'form_fields_tabs',
			],
			'file_upload_preview_img' => [
				'name' => 'file_upload_preview_img',
				'label' => esc_html__('Preview Images uploads', 'drag-and-drop-file-upload-for-elementor-forms'),
				'type' => Controls_Manager::RAW_HTML,
				'content_classes' => 'pro_disable elementor-panel-alert elementor-panel-alert-info',
				'raw' => esc_html__('Show Thumbnail for images ( Upgrade to pro to add it )', 'drag-and-drop-file-upload-for-elementor-forms'),
				'condition' => [
					'field_type' => $this->get_type(),
				],
				'description' => esc_html__("Show Thumbnail for images", 'drag-and-drop-file-upload-for-elementor-forms'),
				'tab' => 'content',
				'inner_tab' => 'form_fields_content_tab',
				'tabs_wrapper' => 'form_fields_tabs',
			],
			'file_upload_save_media' => [
				'name' => 'file_upload_save_media',
				'label' => esc_html__('Save files in WordPress Media Library.', 'drag-and-drop-file-upload-for-elementor-forms'),
				'type' => Controls_Manager::RAW_HTML,
				'content_classes' => 'pro_disable elementor-panel-alert elementor-panel-alert-info',
				'raw' => esc_html__('Save files in WordPress Media Library. ( Upgrade to pro to add it )', 'drag-and-drop-file-upload-for-elementor-forms'),
				'condition' => [
					'field_type' => $this->get_type(),
				],
				'description' => esc_html__("Show Thumbnail for images", 'drag-and-drop-file-upload-for-elementor-forms'),
				'tab' => 'content',
				'inner_tab' => 'form_fields_content_tab',
				'tabs_wrapper' => 'form_fields_tabs',
			],
			'file_upload_dropbox' => [
				'name' => 'file_upload_dropbox',
				'label' => esc_html__('Save files to Dropbox', 'drag-and-drop-file-upload-for-elementor-forms'),
				'type' => Controls_Manager::RAW_HTML,
				'content_classes' => 'pro_disable elementor-panel-alert elementor-panel-alert-info',
				'raw' => esc_html__('Save files to https://www.dropbox.com. ( Upgrade to pro to add it )', 'drag-and-drop-file-upload-for-elementor-forms'),
				'condition' => [
					'field_type' => $this->get_type(),
				],
				'description' => esc_html__("Show Thumbnail for images", 'drag-and-drop-file-upload-for-elementor-forms'),
				'tab' => 'content',
				'inner_tab' => 'form_fields_content_tab',
				'tabs_wrapper' => 'form_fields_tabs',
			],
			'yeepdf_not_save' => [
				'name' => 'yeepdf_not_save',
				'label' => esc_html__('The files are not saved on the server', 'drag-and-drop-file-upload-for-elementor-forms'),
				'type' => Controls_Manager::SWITCHER,
				'description' => esc_html__('Uploaded files are not kept on the server', 'drag-and-drop-file-upload-for-elementor-forms'),
				'condition' => [
					'field_type' => $this->get_type(),
				],
				'tab' => 'content',
				'inner_tab' => 'form_fields_content_tab',
				'tabs_wrapper' => 'form_fields_tabs',
			],
			'file_upload_file_sizes' => [
				'name' => 'file_upload_file_sizes',
				'label' => esc_html__('Max. File Size', 'drag-and-drop-file-upload-for-elementor-forms'),
				'type' => Controls_Manager::SELECT,
				'condition' => [
					'field_type' => $this->get_type(),
				],
				'options' => $this->get_upload_file_size_options(),
				'description' => esc_html__('If you need to increase max upload size please contact your hosting.', 'drag-and-drop-file-upload-for-elementor-forms'),
				'tab' => 'content',
				'inner_tab' => 'form_fields_content_tab',
				'tabs_wrapper' => 'form_fields_tabs',
			],
			'file_upload_file_types' => [
				'name' => 'file_upload_file_types',
				'label' => esc_html__('Allowed File Types', 'drag-and-drop-file-upload-for-elementor-forms'),
				'type' => Controls_Manager::TEXT,
				'ai' => [
					'active' => false,
				],
				'condition' => [
					'field_type' => $this->get_type(),
				],
				'description' => esc_html__('Enter the allowed file types, separated by a comma (jpg, gif, pdf, etc).', 'drag-and-drop-file-upload-for-elementor-forms'),
				'tab' => 'content',
				'inner_tab' => 'form_fields_content_tab',
				'tabs_wrapper' => 'form_fields_tabs',
			],
			'file_upload_max_files' => [
				'name' => 'file_upload_max_files',
				'label' => esc_html__('Max. Files', 'drag-and-drop-file-upload-for-elementor-forms'),
				'type' => Controls_Manager::NUMBER,
				'condition' => [
					'field_type' => $this->get_type(),
				],
				'tab' => 'content',
				'inner_tab' => 'form_fields_content_tab',
				'tabs_wrapper' => 'form_fields_tabs',
			],
			'file_upload_text1' => [
				'name' => 'file_upload_text1',
				'label' => esc_html__('Translate text 1', 'drag-and-drop-file-upload-for-elementor-forms'),
				'type' => Controls_Manager::TEXT,
				'default' => 'Drag & Drop Files Here',
				'ai' => [
					'active' => false,
				],
				'condition' => [
					'field_type' => $this->get_type(),
				],
				'description' => esc_html__('Text: Drag & Drop Files Here', 'drag-and-drop-file-upload-for-elementor-forms'),
				'tab' => 'content',
				'inner_tab' => 'form_fields_advanced_tab',
				'tabs_wrapper' => 'form_fields_tabs',
			],
			'file_upload_text2' => [
				'name' => 'file_upload_text2',
				'label' => esc_html__('Translate text 2', 'drag-and-drop-file-upload-for-elementor-forms'),
				'type' => Controls_Manager::TEXT,
				'default' => 'or',
				'ai' => [
					'active' => false,
				],
				'condition' => [
					'field_type' => $this->get_type(),
				],
				'description' => esc_html__('Text: or', 'drag-and-drop-file-upload-for-elementor-forms'),
				'tab' => 'content',
				'inner_tab' => 'form_fields_advanced_tab',
				'tabs_wrapper' => 'form_fields_tabs',
			],
			'file_upload_text3' => [
				'name' => 'file_upload_text3',
				'label' => esc_html__('Translate text 3', 'drag-and-drop-file-upload-for-elementor-forms'),
				'type' => Controls_Manager::TEXT,
				'default' => 'Browse Files',
				'ai' => [
					'active' => false,
				],
				'condition' => [
					'field_type' => $this->get_type(),
				],
				'description' => esc_html__('Text: Browse Files', 'drag-and-drop-file-upload-for-elementor-forms'),
				'tab' => 'content',
				'inner_tab' => 'form_fields_advanced_tab',
				'tabs_wrapper' => 'form_fields_tabs',
			],
			'button_padding' => [
				'name' => 'button_padding',
				'label' => esc_html__('Button Padding', 'drag-and-drop-file-upload-for-elementor-forms'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em', 'rem', 'custom'],
				'default' => array("unit" => "px", "top" => 10, "right" => 24, "bottom" => 10, "left" => 24, 'isLinked' => false),
				'condition' => [
					'field_type' => $this->get_type(),
				],
				'tab' => 'advanced',
				'inner_tab' => 'form_fields_advanced_tab',
				'tabs_wrapper' => 'form_fields_tabs',
			],
			'button_background' => [
				'name' => 'button_background',
				'label' => esc_html__('Button Background Color', 'drag-and-drop-file-upload-for-elementor-forms'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#6381E6',
				'condition' => [
					'field_type' => $this->get_type(),
				],
				'tab' => 'advanced',
				'inner_tab' => 'form_fields_advanced_tab',
				'tabs_wrapper' => 'form_fields_tabs',
			],
			'button_color' => [
				'name' => 'button_color',
				'label' => esc_html__('Button Color', 'drag-and-drop-file-upload-for-elementor-forms'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#ffffff',
				'condition' => [
					'field_type' => $this->get_type(),
				],
				'tab' => 'advanced',
				'inner_tab' => 'form_fields_advanced_tab',
				'tabs_wrapper' => 'form_fields_tabs',
			],
			'button_border_width' => [
				'name' => 'button_border_width',
				'label' => esc_html__('Button Border Width', 'drag-and-drop-file-upload-for-elementor-forms'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em', 'rem', 'custom'],
				'default' => array("unit" => "px", "top" => 0, "right" => 0, "bottom" => 0, "left" => 0, 'isLinked' => false),
				'condition' => [
					'field_type' => $this->get_type(),
				],
				'tab' => 'advanced',
				'inner_tab' => 'form_fields_advanced_tab',
				'tabs_wrapper' => 'form_fields_tabs',
			],
			'button_border_color' => [
				'name' => 'button_border_color',
				'label' => esc_html__('Button Border Color', 'drag-and-drop-file-upload-for-elementor-forms'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#6381E6',
				'condition' => [
					'field_type' => $this->get_type(),
				],
				'tab' => 'advanced',
				'inner_tab' => 'form_fields_advanced_tab',
				'tabs_wrapper' => 'form_fields_tabs',
			],
			'button_border_radius' => [
				'name' => 'button_border_radius',
				'label' => esc_html__('Button Border Radius', 'drag-and-drop-file-upload-for-elementor-forms'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em', 'rem', 'custom'],
				'default' => array("unit" => "px", "top" => 5, "right" => 5, "bottom" => 5, "left" => 5, 'isLinked' => false),
				'condition' => [
					'field_type' => $this->get_type(),
				],
				'tab' => 'advanced',
				'inner_tab' => 'form_fields_advanced_tab',
				'tabs_wrapper' => 'form_fields_tabs',
			],
		];
		$control_data['fields'] = $this->inject_field_controls($control_data['fields'], $field_controls);
		$widget->update_control('form_fields', $control_data);
	}
	private function cover_css($css = "padding", $datas = array())
	{
		if (!is_array($datas)) {
			$datas = array("unit" => "px", "top" => 0, "right" => 0, "bottom" => 0, "left" => 0);
		}
		return $css . ":" . $datas["top"] . $datas["unit"] . " " . $datas["right"] . $datas["unit"] . " " . $datas["bottom"] . $datas["unit"] . " " . $datas["left"] . $datas["unit"] . " !important; ";
	}
	/**
	 * @param      $item
	 * @param      $item_index
	 * @param Form $form
	 */
	public function render($item, $item_index, $form)
	{
		$form->add_render_attribute('input' . $item_index, 'class', 'elementor-upload-field-drap_drop');
		$form->add_render_attribute('input' . $item_index, 'type', 'hidden', true);
		$allowed_size = $item['file_upload_file_types'];
		$max = $item['file_upload_max_files'];
		$size = $item['file_upload_file_sizes'];
		$text1 = $item['file_upload_text1'];
		$text2 = $item['file_upload_text2'];
		$text3 = $item['file_upload_text3'];
		$button_padding = (isset($item['button_padding']) ? $item['button_padding'] : array("unit" => "px", "top" => 10, "right" => 24, "bottom" => 10, "left" => 24));
		$button_background = (isset($item['button_background']) ? $item['button_background'] : "#6381E6");
		$button_color = (isset($item['button_color']) ? $item['button_color'] : "#ffffff");
		$button_border_width = (isset($item['button_border_width']) ? $item['button_border_width'] : array("unit" => "px", "top" => 0, "right" => 0, "bottom" => 0, "left" => 0));
		$button_border_color = (isset($item['button_border_color']) ? $item['button_border_color'] : "#6381E6");
		$button_border_radius = (isset($item['button_border_radius']) ? $item['button_border_radius'] : array("unit" => "px", "top" => 5, "right" => 5, "bottom" => 5, "left" => 5));
		$button_style = $this->cover_css("padding", $button_padding);
		$button_style .= $this->cover_css("border-width", $button_border_width);
		$button_style .= $this->cover_css("border-radius", $button_border_radius);
		$button_style .= 'color:' . $button_color . "!important; ";
		$button_style .= 'background:' . $button_background . "!important; ";
		$button_style .= 'border-color:' . $button_border_color . "!important; ";
		$button_style .= 'border-style:solid !important; ';
?>
		<div class="elementor-dragandrophandler-container">
			<div class="elementor-dragandrophandler" data-type="<?php echo esc_attr($allowed_size) ?>" data-size="<?php echo esc_attr($size) ?>" data-max="<?php echo esc_attr($max) ?>">
				<div class="elementor-dragandrophandler-inner">
					<div class="elementor-text-drop"><?php echo esc_html($text1) ?></div>
					<div class="elementor-text-or"><?php echo esc_html($text2) ?></div>
					<div class="elementor-text-browser"><a style="<?php echo wp_kses_post($button_style) ?>" href="#"><?php echo esc_html($text3) ?></a></div>
				</div>
				<input type="file" class="input-uploads hidden" multiple>
			</div>
		</div>
		<input <?php $form->print_render_attribute_string('input' . $item_index); ?>>
	<?php
	}
	private function get_blacklist_file_ext()
	{
		static $blacklist = false;
		if (! $blacklist) {
			$blacklist = [
				'php',
				'php3',
				'php4',
				'php5',
				'php6',
				'phps',
				'php7',
				'phtml',
				'shtml',
				'pht',
				'swf',
				'html',
				'asp',
				'aspx',
				'cmd',
				'csh',
				'bat',
				'htm',
				'hta',
				'jar',
				'exe',
				'com',
				'js',
				'lnk',
				'htaccess',
				'htpasswd',
				'phtml',
				'ps1',
				'ps2',
				'py',
				'rb',
				'tmp',
				'cgi',
				'svg',
				'php2',
				'phtm',
				'phar',
				'hphp',
				'phpt',
				'svgz',
			];
			/**
			 * Elementor forms blacklisted file extensions.
			 *
			 * Filters the list of file types that won't be uploaded using Elementor forms.
			 *
			 * By default Elementor forms doesn't upload some file types for security reasons.
			 * This hook allows developers to alter this list, either add more file types to
			 * strengthen the security or remove file types to increase flexibility.
			 *
			 * @since 1.0.0
			 *
			 * @param array $blacklist A blacklist of file extensions.
			 */
			$blacklist = apply_filters('elementor_pro/forms/filetypes/blacklist', $blacklist);
		}
		return $blacklist;
	}
	private function get_upload_dir()
	{
		$wp_upload_dir = wp_upload_dir();
		$path = $wp_upload_dir['basedir'] . '/elementor/forms/uploads/';
		/**
		 * Elementor forms upload file path.
		 *
		 * Filters the path to a file uploaded using Elementor forms.
		 *
		 * By default Elementor forms defines a path to uploaded file. This
		 * hook allows developers to alter this path.
		 *
		 * @since 1.0.0
		 *
		 * @param string $path Path to uploaded files.
		 */
		$path = apply_filters('elementor_pro/forms/uploads/upload_path', $path);
		return $path;
	}
	/**
	 * Gets the URL to uploaded file.
	 *
	 * @param $file_name
	 *
	 * @return string
	 */
	private function get_file_url($file_name)
	{
		$wp_upload_dir = wp_upload_dir();
		$url = $wp_upload_dir['baseurl'] . '/elementor/forms/uploads/' . $file_name;
		/**
		 * Elementor forms upload file URL.
		 *
		 * Filters the URL to a file uploaded using Elementor forms.
		 *
		 * By default Elementor forms defines a URL to uploaded file. This
		 * hook allows developers to alter this URL.
		 *
		 * @since 1.0.0
		 *
		 * @param string $url       Upload file URL.
		 * @param string $file_name Upload file name.
		 */
		$url = apply_filters('elementor_pro/forms/uploads/upload_url', $url, $file_name);
		return $url;
	}
	/**
	 * This function returns the uploads folder after making sure
	 * it is created and has protection files
	 * @return string
	 */
	private function get_ensure_upload_dir()
	{
		$path = $this->get_upload_dir();
		if (file_exists($path . '/index.php')) {
			return $path;
		}
		wp_mkdir_p($path);
		$files = [
			[
				'file' => 'index.php',
				'content' => [
					'<?php',
					'// Silence is golden.',
				],
			],
			[
				'file' => '.htaccess',
				'content' => [
					'Options -Indexes',
					'<ifModule mod_headers.c>',
					'	<Files *.*>',
					'       Header set Content-Disposition attachment',
					'	</Files>',
					'</IfModule>',
				],
			],
		];
		foreach ($files as $file) {
			if (! file_exists(trailingslashit($path) . $file['file'])) {
				$content = implode(PHP_EOL, $file['content']);
				@file_put_contents(trailingslashit($path) . $file['file'], $content);
			}
		}
		return $path;
	}
	/**
	 * creates array of upload sizes based on server limits
	 * to use in the file_sizes control
	 * @return array
	 */
	private function get_upload_file_size_options()
	{
		$max_file_size = wp_max_upload_size() / pow(1024, 2); //MB
		$sizes = [];
		$sizes["400"] = "400 KB";
		for ($file_size = 1; $file_size <= $max_file_size; $file_size++) {
			$sizes[$file_size] = $file_size . 'MB';
		}
		return $sizes;
	}
	/**
	 * process file and move it to uploads directory
	 *
	 * @param array                $field
	 * @param Classes\Form_Record  $record
	 * @param Classes\Ajax_Handler $ajax_handler
	 */
	public function process_field($field, $record, $ajax_handler)
	{
		$id = $field['id'];
		$settings = $record->get('form_settings');
		$save_media = false;
		$save_dropbox = false;
		foreach ($settings["form_fields"] as $f_field) {
			if ("field_" . $f_field["_id"] == $id || $f_field["custom_id"] == $id) {
				if (isset($f_field["file_upload_save_media"]) && $f_field["file_upload_save_media"] == "yes") {
					$save_media = true;
				}
				if (isset($f_field["file_upload_dropbox"]) && $f_field["file_upload_dropbox"] == "yes") {
					$save_dropbox = true;
				}
				if (isset($f_field["file_upload_attachment_type"]) && $f_field["file_upload_attachment_type"] == "yes") {
					$fields = $record->get("fields");
					$fields[$id]["attachment_type"] = "both";
					$record->set("fields", $fields);
				} else {
					$fields = $record->get("fields");
					$fields[$id]["attachment_type"] = "link";
					$record->set("fields", $fields);
				}
				break;
			}
		}
		if ($field["raw_value"] != "") {
			$dir_upload = $this->get_upload_dir();
			$files = explode("|", $field["raw_value"]);
			$index = 0;
			foreach ($files as $file) {
				$file_datas = explode("/", $file);
				$path = $dir_upload . end($file_datas);
				//upload to dopbox
				if ($save_dropbox) {
					Yeeaddons_EL_Dropbox_API::uppload_files($path);
				}
				if ($save_media) {
					$filetype = wp_check_filetype(basename($path), null);
					$attachment = array(
						'guid'           => $file,
						'post_mime_type' => $filetype['type'],
						'post_title'     => preg_replace('/\.[^.]+$/', '', basename($path)),
						'post_content'   => '',
						'post_status'    => 'inherit'
					);
					$attach_id = wp_insert_attachment($attachment, $path);
					//$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
					//wp_update_attachment_metadata( $attach_id, $attach_data );
				}
				$record->add_file(
					$id,
					$index,
					[
						'path' => $path,
						'url' => $file
					]
				);
				$index++;
			}
		}
	}
	public function remove_wp_mail_filter()
	{
		$this->attachments_array = [];
		remove_filter('wp_mail', [$this, 'wp_mail']);
	}
	public function wp_mail($args)
	{
		$old_attachments = $args['attachments'];
		$args['attachments'] = array_merge($this->attachments_array, $old_attachments);
		return $args;
	}
	function send_data($record, $ajax_handler)
	{
		$settings = $record->get('form_settings');
		$attachments_array = $this->get_file_by_attachment_type($settings['form_fields'], $record);
		$this->attachments_array = $attachments_array;
		add_filter('wp_mail', array($this, "wp_mail"));
	}
	function get_file_by_attachment_type($form_fields, $record, $type = "yes")
	{
		return Collection::make($form_fields)
			->filter(function ($field) use ($type) {
				return $type === $field['file_upload_attachment_type'];
			})
			->map(function ($field) use ($record) {
				$id = $field['custom_id'];
				return $record->get('files')[$id]['path'] ?? null;
			})
			->filter()
			->flatten()
			->values();
	}
	public function __construct()
	{
		parent::__construct();
		add_action('elementor/preview/init', array($this, 'editor_preview_footer'));
		add_action("wp_enqueue_scripts", array($this, "add_lib"));
		add_action("admin_enqueue_scripts", array($this, "add_lib_admin"));
		add_action('wp_ajax_elementor_file_upload', array($this, 'elementor_file_upload'));
		add_action('wp_ajax_nopriv_elementor_file_upload', array($this, 'elementor_file_upload'));
		add_action('wp_ajax_elementor_file_upload_remove', array($this, 'elementor_file_upload_remove'));
		add_action('wp_ajax_nopriv_elementor_file_upload_remove', array($this, 'elementor_file_upload_remove'));
		add_action('elementor_pro/forms/new_record', [$this, 'remove_wp_mail_filter'], 5);
		add_action('elementor_pro/forms/process', array($this, 'send_data'), 11, 2);
		add_action('elementor_pro/forms/mail_sent', array($this, "remove_files"), 10, 2);
	}
	function remove_files($settings, $record)
	{
		$attachments_mode_attach = $this->get_file_by_attachment_type_ok($settings['form_fields'], $record, "yes");
		foreach ($attachments_mode_attach as $file) {
			@unlink($file);
		}
	}
	private function get_file_by_attachment_type_ok($form_fields, $record, $type)
	{
		return Collection::make($form_fields)
			->filter(function ($field) use ($type) {
				return $type === $field['yeepdf_not_save'];
			})
			->map(function ($field) use ($record) {
				$id = $field['custom_id'];
				return $record->get('files')[$id]['path'] ?? null;
			})
			->filter()
			->flatten()
			->values();
	}
	public function elementor_file_upload_remove()
	{
		$nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
		if (!wp_verify_nonce($nonce, 'elementor_file_upload')) {
			wp_send_json_error(array("status" => "error", "message" => "Security check failed."));
		}
		$name = isset($_POST["name"]) ? sanitize_text_field(wp_unslash($_POST["name"])) : '';
		if (empty($name)) {
			wp_send_json_error(array("status" => "error", "message" => "No file name."));
		}
		$filename = basename(sanitize_file_name($name));
		$protected_files = array('.htaccess', 'index.php', 'index.html', 'php.ini', 'web.config');
		if (in_array(strtolower($filename), $protected_files)) {
			wp_send_json_error(array("status" => "error", "message" => "System files are protected."));
		}
		$upload_dir = $this->get_upload_dir();
		$file_path = trailingslashit($upload_dir) . $filename;
		$real_file_path = realpath($file_path);
		$real_upload_dir = realpath($upload_dir);
		if (
			$real_file_path !== false &&
			$real_upload_dir !== false &&
			is_file($real_file_path) &&
			strpos($real_file_path, $real_upload_dir) === 0
		) {
			if (@unlink($real_file_path)) {
				wp_send_json(array("status" => "ok"));
			} else {
				wp_send_json(array("status" => "error", "message" => "Delete failed."));
			}
		} else {
			wp_send_json(array("status" => "error", "message" => "Invalid file."));
		}
		wp_die();
	}
	private function is_file_type_valid($file_types, $file)
	{
		// If no file types are specified, use the default allowed list
		if (empty($file_types)) {
			$file_types = 'dcm,jpg,jpeg,png,gif,webp,pdf,doc,docx,ppt,pptx,odt,avi,ogg,m4a,mov,mp3,mp4,mpg,wav,wmv';
		}
		// Get the list of allowed MIME types from WordPress
		$allowed_mimes = get_allowed_mime_types();
		$custom_allowed_mimes = [];
		// Build a custom allowed MIME type list based on the provided $file_types
		$allowed_exts_input = array_map('trim', explode(',', strtolower($file_types)));
		foreach ($allowed_exts_input as $ext) {
			// Match each allowed extension with its MIME type from WordPress
			foreach ($allowed_mimes as $mime_ext => $mime_type) {
				if (strpos($mime_ext, $ext) !== false) {
					$custom_allowed_mimes[$mime_ext] = $mime_type;
				}
			}
		}
		// If no valid MIME types are found, reject the file for safety
		if (empty($custom_allowed_mimes)) {
			return false;
		}
		// Extra check: prevent double extension attacks (e.g., file.jpg.php)
		if (preg_match('/\.[a-z0-9]+\.([a-z0-9]+)$/i', $file['name'], $matches)) {
			if (! in_array(strtolower($matches[1]), $allowed_exts_input, true)) {
				return false;
			}
		}
		// Validate the uploaded file using WordPress function with our custom MIME list
		// This checks both the file extension and the actual file MIME type
		$checked = wp_check_filetype_and_ext($file['tmp_name'], $file['name'], $custom_allowed_mimes);
		// If either extension or MIME type is empty, the file is invalid or mismatched
		if (empty($checked['ext']) || empty($checked['type'])) {
			return false;
		}
		// Get the blacklist of disallowed file extensions for additional security (defense in depth)
		$blacklist = $this->get_blacklist_file_ext();
		// The file is valid if:
		// 1. It passed wp_check_filetype_and_ext (both extension and MIME are valid)
		// 2. The extension is not in the blacklist
		return ! in_array(strtolower($checked['ext']), $blacklist, true);
	}
	private function is_file_size_valid($file_sizes, $file)
	{
		// If $file_sizes is provided, use it; otherwise, use WordPress's max upload size (converted to MB)
		$allowed_size = ! empty($file_sizes) ? $file_sizes : wp_max_upload_size() / (1024 ** 2);
		// If $allowed_size is exactly 400, treat it as 400 KB instead of MB
		if ($allowed_size == 400) {
			$file_size_meta = 400 * 1024; // Convert 400 KB to bytes
		} else {
			$file_size_meta = $allowed_size * 1024 * 1024; // Convert MB to bytes
		}
		// Get actual uploaded file size (in bytes)
		$upload_file_size = $file['size'];
		// Return true if the uploaded file is smaller than the allowed size
		return $upload_file_size < $file_size_meta;
	}
	function elementor_file_upload()
	{
		if (isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'elementor_file_upload')) {
			$file = $_FILES["file"]; //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			if (isset($_REQUEST["size"])) {
				$size = sanitize_text_field(wp_unslash($_REQUEST["size"]));
			} else {
				$size = '';
			}
			if (isset($_REQUEST["type"])) {
				$type = sanitize_text_field(wp_unslash($_REQUEST["type"]));
			} else {
				$type = '';
			}
			$uploads_dir = $this->get_ensure_upload_dir();
			$name_of_file = sanitize_file_name($file["name"]);
			$filename = uniqid() . "-" . $name_of_file;
			$filename = wp_unique_filename($uploads_dir, $filename);
			$filename = sanitize_file_name($filename);
			$new_file = trailingslashit($uploads_dir) . $filename;
			// valid file type?
			if (!$this->is_file_type_valid($type, $file)) {
				wp_send_json(array("status" => "not", "text" => esc_html__('This file type is not allowed.', 'drag-and-drop-file-upload-for-elementor-forms')));
				die();
			}
			// allowed file size?
			if (! $this->is_file_size_valid($size, $file)) {
				wp_send_json(array("status" => "not", "text" => esc_html__('This file exceeds the maximum allowed size.', 'drag-and-drop-file-upload-for-elementor-forms')));
				die();
			}
			if (is_dir($uploads_dir) && is_writable($uploads_dir)) {
				if (function_exists('Plugin::instance()->php_api->move_uploaded_file')) {
					$move_new_file = Plugin::instance()->php_api->move_uploaded_file($file['tmp_name'], $new_file);
					if (false !== $move_new_file) {
						// Set correct file permissions.
						$perms = 0644;
						@chmod($new_file, $perms);
						wp_send_json(array("status" => "ok", "text" => $this->get_file_url($filename)));
					} else {
						wp_send_json(array("status" => "not", "text" => esc_html__('There was an error while trying to upload your file.', 'drag-and-drop-file-upload-for-elementor-forms')));
					}
				} else {
					if (! function_exists('wp_handle_upload')) {
						require_once(ABSPATH . 'wp-admin/includes/file.php');
					}
					$upload_overrides = array(
						'test_form' => false,
					);
					$yee_upload_dir_filter = function ($uploads) {
						$custom_dir = '/elementor/forms/uploads';
						$uploads['path']    = $uploads['basedir'] . $custom_dir;
						$uploads['url']     = $uploads['baseurl'] . $custom_dir;
						$uploads['subdir']  = $custom_dir;
						return $uploads;
					};
					add_filter('upload_dir', $yee_upload_dir_filter);
					$movefile = wp_handle_upload($file, $upload_overrides);
					remove_filter('upload_dir', $yee_upload_dir_filter);
					if ($movefile && ! isset($movefile['error'])) {
						wp_send_json(array(
							"status" => "ok",
							"text"   => $movefile['url']
						));
					} else {
						wp_send_json(array(
							"status" => "not",
							"text"   => $movefile['error']
						));
					}
				}
			} else {
				wp_send_json(array("status" => "not", "text" => esc_html__('Upload directory is not writable or does not exist.', 'drag-and-drop-file-upload-for-elementor-forms')));
			}
		}
	}
	public function editor_preview_footer()
	{
		add_action('wp_footer', array($this, "content_template_script"));
	}
	function content_template_script()
	{
	?>
		<script>
			jQuery(document).ready(() => {
				elementor.hooks.addFilter(
					'elementor_pro/forms/content_template/field/file_upload',
					function(inputField, item, i) {
						return '<input type="file" disabled />';
					}, 10, 3
				);
			});
		</script>
<?php
	}
	function add_lib()
	{
		wp_enqueue_script("elementor_file_upload", SUPERADDONS_FILE_UPLOAD_PLUGIN_URL . "assets/js/drap_drop_file_upload.js", array("jquery"), "1.5.8", true);
		wp_localize_script('elementor_file_upload', 'elementor_file_upload', array('nonce' => wp_create_nonce('elementor_file_upload'), "url_plugin" => SUPERADDONS_FILE_UPLOAD_PLUGIN_URL, 'ajax_url' => admin_url('admin-ajax.php'), "upload_url" => $this->get_file_url(""), "text_maximum" => __("You can upload maximum:", "drag-and-drop-file-upload-for-elementor-forms")));
		wp_enqueue_style("repeater_file_upload", SUPERADDONS_FILE_UPLOAD_PLUGIN_URL . "assets/css/drap_drop_file_upload.css", array(), "1.5.6");
	}
	function add_lib_admin()
	{
		wp_enqueue_script("elementor_file_upload", SUPERADDONS_FILE_UPLOAD_PLUGIN_URL . "assets/js/admin-upload.js", array("jquery"), "1.5.6", true);
	}
}
