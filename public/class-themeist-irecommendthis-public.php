<?php
/**
 * Public-facing functionality for the I Recommend This plugin.
 *
 * @package IRecommendThis
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Main public class that coordinates all public-facing functionality.
 */
class Themeist_IRecommendThis_Public {

	/**
	 * Path to the main plugin file.
	 *
	 * @var string
	 */
	private $plugin_file;

	/**
	 * Assets component instance.
	 *
	 * @var Themeist_IRecommendThis_Public_Assets
	 */
	private $assets_component;

	/**
	 * Display component instance.
	 *
	 * @var Themeist_IRecommendThis_Public_Display
	 */
	private $display_component;

	/**
	 * Processor component instance.
	 *
	 * @var Themeist_IRecommendThis_Public_Processor
	 */
	private $processor_component;

	/**
	 * Constructor.
	 *
	 * @param string $plugin_file Path to the main plugin file.
	 */
	public function __construct( $plugin_file ) {
		$this->plugin_file = $plugin_file;

		// Include component classes.
		$this->include_components();

		// Initialize components.
		$this->initialize_components();
	}

	/**
	 * Include component classes.
	 */
	private function include_components() {
		require_once __DIR__ . '/class-themeist-irecommendthis-public-assets.php';
		require_once __DIR__ . '/class-themeist-irecommendthis-public-display.php';
		require_once __DIR__ . '/class-themeist-irecommendthis-public-processor.php';
	}

	/**
	 * Initialize components.
	 */
	private function initialize_components() {
		$this->assets_component    = new Themeist_IRecommendThis_Public_Assets( $this->plugin_file );
		$this->display_component   = new Themeist_IRecommendThis_Public_Display();
		$this->processor_component = new Themeist_IRecommendThis_Public_Processor();
	}

	/**
	 * Add public-facing hooks.
	 */
	public function add_public_hooks() {
		$this->assets_component->initialize();
		$this->display_component->initialize();
	}

	/**
	 * Static method to access the processor component for recommendation processing.
	 *
	 * @param int    $post_id           Post ID.
	 * @param string $text_zero_suffix  Text for zero suffix.
	 * @param string $text_one_suffix   Text for one suffix.
	 * @param string $text_more_suffix  Text for more suffix.
	 * @param string $action            Action to perform: 'get' or 'update'.
	 * @return string HTML output for the recommendation count.
	 */
	public static function process_recommendation( $post_id, $text_zero_suffix = false, $text_one_suffix = false, $text_more_suffix = false, $action = 'get' ) {
		$processor = new Themeist_IRecommendThis_Public_Processor();
		return $processor->process_recommendation( $post_id, $text_zero_suffix, $text_one_suffix, $text_more_suffix, $action );
	}
}
