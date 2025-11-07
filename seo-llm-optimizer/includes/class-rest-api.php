<?php
/**
 * REST API
 *
 * Provides RESTful API endpoints for programmatic access to the SEO & LLM
 * Optimizer plugin functionality. Handles markdown conversion, chunking,
 * batch processing, and cache management.
 *
 * @package SEO_LLM_Optimizer
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Handles REST API endpoints for content processing
 *
 * Provides secure, rate-limited access to:
 * - Markdown conversion for posts
 * - Chunk generation with multiple strategies
 * - Batch processing for multiple posts
 * - Cache statistics and management
 * - Health check endpoint
 *
 * @since 1.0.0
 */
class SLO_REST_API {

	/**
	 * Singleton instance
	 *
	 * @var SLO_REST_API
	 */
	private static $instance = null;

	/**
	 * API namespace
	 *
	 * @var string
	 */
	const NAMESPACE = 'slo/v1';

	/**
	 * Get singleton instance
	 *
	 * @return SLO_REST_API
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize WordPress hooks
	 */
	private function init_hooks() {
		add_action('rest_api_init', array($this, 'register_routes'));
	}

	/**
	 * Register REST API routes
	 *
	 * Registers all plugin REST API endpoints if enabled in settings.
	 */
	public function register_routes() {
		// Check if REST API is enabled in settings
		if (!$this->is_api_enabled()) {
			return;
		}

		// GET /slo/v1/posts/{id}/markdown - Get markdown for a post
		register_rest_route(self::NAMESPACE, '/posts/(?P<id>\d+)/markdown', array(
			'methods'             => 'GET',
			'callback'            => array($this, 'get_markdown'),
			'permission_callback' => array($this, 'check_read_permission'),
			'args'                => array(
				'id'               => array(
					'validate_callback' => function($param) {
						return is_numeric($param);
					},
					'sanitize_callback' => 'absint',
					'required'          => true,
				),
				'include_metadata' => array(
					'default'           => true,
					'sanitize_callback' => 'rest_sanitize_boolean',
				),
				'include_images'   => array(
					'default'           => true,
					'sanitize_callback' => 'rest_sanitize_boolean',
				),
			),
		));

		// GET /slo/v1/posts/{id}/chunks - Get chunks for a post
		register_rest_route(self::NAMESPACE, '/posts/(?P<id>\d+)/chunks', array(
			'methods'             => 'GET',
			'callback'            => array($this, 'get_chunks'),
			'permission_callback' => array($this, 'check_read_permission'),
			'args'                => array(
				'id'         => array(
					'validate_callback' => function($param) {
						return is_numeric($param);
					},
					'sanitize_callback' => 'absint',
					'required'          => true,
				),
				'strategy'   => array(
					'default'           => 'hierarchical',
					'enum'              => array('hierarchical', 'fixed', 'semantic'),
					'sanitize_callback' => 'sanitize_text_field',
				),
				'format'     => array(
					'default'           => 'universal',
					'enum'              => array('universal', 'langchain', 'llamaindex'),
					'sanitize_callback' => 'sanitize_text_field',
				),
				'chunk_size' => array(
					'default'           => 512,
					'sanitize_callback' => 'absint',
				),
				'overlap'    => array(
					'default'           => 128,
					'sanitize_callback' => 'absint',
				),
			),
		));

		// POST /slo/v1/batch/markdown - Batch markdown conversion
		register_rest_route(self::NAMESPACE, '/batch/markdown', array(
			'methods'             => 'POST',
			'callback'            => array($this, 'batch_markdown'),
			'permission_callback' => array($this, 'check_read_permission'),
			'args'                => array(
				'post_ids'         => array(
					'required'          => true,
					'validate_callback' => function($param) {
						return is_array($param) && !empty($param);
					},
					'sanitize_callback' => function($param) {
						return array_map('absint', $param);
					},
				),
				'include_metadata' => array(
					'default'           => true,
					'sanitize_callback' => 'rest_sanitize_boolean',
				),
			),
		));

		// POST /slo/v1/batch/chunks - Batch chunk generation
		register_rest_route(self::NAMESPACE, '/batch/chunks', array(
			'methods'             => 'POST',
			'callback'            => array($this, 'batch_chunks'),
			'permission_callback' => array($this, 'check_read_permission'),
			'args'                => array(
				'post_ids' => array(
					'required'          => true,
					'validate_callback' => function($param) {
						return is_array($param) && !empty($param);
					},
					'sanitize_callback' => function($param) {
						return array_map('absint', $param);
					},
				),
				'strategy' => array(
					'default'           => 'hierarchical',
					'enum'              => array('hierarchical', 'fixed', 'semantic'),
					'sanitize_callback' => 'sanitize_text_field',
				),
				'format'   => array(
					'default'           => 'universal',
					'enum'              => array('universal', 'langchain', 'llamaindex'),
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		));

		// GET /slo/v1/cache/stats - Get cache statistics
		register_rest_route(self::NAMESPACE, '/cache/stats', array(
			'methods'             => 'GET',
			'callback'            => array($this, 'get_cache_stats'),
			'permission_callback' => array($this, 'check_admin_permission'),
		));

		// DELETE /slo/v1/cache/{post_id} - Clear cache for a post
		register_rest_route(self::NAMESPACE, '/cache/(?P<post_id>\d+)', array(
			'methods'             => 'DELETE',
			'callback'            => array($this, 'clear_post_cache'),
			'permission_callback' => array($this, 'check_admin_permission'),
			'args'                => array(
				'post_id' => array(
					'validate_callback' => function($param) {
						return is_numeric($param);
					},
					'sanitize_callback' => 'absint',
					'required'          => true,
				),
			),
		));

		// DELETE /slo/v1/cache - Clear all caches
		register_rest_route(self::NAMESPACE, '/cache', array(
			'methods'             => 'DELETE',
			'callback'            => array($this, 'clear_all_cache'),
			'permission_callback' => array($this, 'check_admin_permission'),
		));

		// GET /slo/v1/health - Health check endpoint
		register_rest_route(self::NAMESPACE, '/health', array(
			'methods'             => 'GET',
			'callback'            => array($this, 'health_check'),
			'permission_callback' => '__return_true',  // Public endpoint
		));
	}

	/**
	 * Check read permission
	 *
	 * Validates that the user has permission to read content via the API.
	 * Enforces rate limiting and API enabled status.
	 *
	 * @param WP_REST_Request $request Request object
	 * @return bool|WP_Error True if allowed, error otherwise
	 */
	private function check_read_permission($request) {
		// Check if REST API is enabled
		if (!$this->is_api_enabled()) {
			return new WP_Error(
				'rest_disabled',
				__('REST API is disabled in settings', 'seo-llm-optimizer'),
				array('status' => 403)
			);
		}

		// Apply rate limiting
		if (!$this->apply_rate_limit($request)) {
			return new WP_Error(
				'rate_limit_exceeded',
				__('Rate limit exceeded. Please try again later.', 'seo-llm-optimizer'),
				array('status' => 429)
			);
		}

		// Allow logged-in users with read permission
		if (current_user_can('read')) {
			return true;
		}

		// For public access, check if posts being accessed are published
		// This is handled in the endpoint callback
		return new WP_Error(
			'rest_forbidden',
			__('You must be logged in to access this endpoint', 'seo-llm-optimizer'),
			array('status' => 401)
		);
	}

	/**
	 * Check write permission
	 *
	 * Validates that the user has permission to modify content via the API.
	 *
	 * @param WP_REST_Request $request Request object
	 * @return bool|WP_Error True if allowed, error otherwise
	 */
	private function check_write_permission($request) {
		if (!current_user_can('edit_posts')) {
			return new WP_Error(
				'rest_forbidden',
				__('You do not have permission to modify content', 'seo-llm-optimizer'),
				array('status' => 403)
			);
		}
		return true;
	}

	/**
	 * Check admin permission
	 *
	 * Validates that the user has administrator permission.
	 *
	 * @param WP_REST_Request $request Request object
	 * @return bool|WP_Error True if allowed, error otherwise
	 */
	private function check_admin_permission($request) {
		if (!current_user_can('manage_options')) {
			return new WP_Error(
				'rest_forbidden',
				__('You do not have permission to access this endpoint', 'seo-llm-optimizer'),
				array('status' => 403)
			);
		}
		return true;
	}

	/**
	 * Get markdown for a post
	 *
	 * Endpoint: GET /slo/v1/posts/{id}/markdown
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error Response or error
	 */
	public function get_markdown($request) {
		$post_id          = $request->get_param('id');
		$include_metadata = $request->get_param('include_metadata');
		$include_images   = $request->get_param('include_images');

		// Check if post exists
		$post = get_post($post_id);
		if (!$post) {
			return $this->prepare_error('Post not found', 'post_not_found', 404);
		}

		// Check if post is published or user can edit
		if ('publish' !== $post->post_status && !current_user_can('edit_post', $post_id)) {
			return $this->prepare_error('Post not accessible', 'post_not_accessible', 403);
		}

		// Get content processor
		$processor = SLO_Content_Processor::get_instance();

		// Convert to markdown
		$markdown = $processor->convert_to_markdown($post_id, array(
			'include_metadata' => $include_metadata,
			'include_images'   => $include_images,
		));

		if (is_wp_error($markdown)) {
			return $this->prepare_error($markdown->get_error_message(), 'conversion_failed', 500);
		}

		return $this->prepare_response(array(
			'post_id'    => $post_id,
			'markdown'   => $markdown,
			'post_title' => get_the_title($post_id),
			'post_url'   => get_permalink($post_id),
			'post_type'  => get_post_type($post_id),
		));
	}

	/**
	 * Get chunks for a post
	 *
	 * Endpoint: GET /slo/v1/posts/{id}/chunks
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error Response or error
	 */
	public function get_chunks($request) {
		$post_id    = $request->get_param('id');
		$strategy   = $request->get_param('strategy');
		$format     = $request->get_param('format');
		$chunk_size = $request->get_param('chunk_size');
		$overlap    = $request->get_param('overlap');

		// Check if post exists
		$post = get_post($post_id);
		if (!$post) {
			return $this->prepare_error('Post not found', 'post_not_found', 404);
		}

		// Check if post is published or user can edit
		if ('publish' !== $post->post_status && !current_user_can('edit_post', $post_id)) {
			return $this->prepare_error('Post not accessible', 'post_not_accessible', 403);
		}

		// Get chunking engine
		$engine = SLO_Chunking_Engine::get_instance();

		// Create chunks
		$chunks = $engine->get_cached_chunks($post_id, $strategy, array(
			'chunk_size' => $chunk_size,
			'overlap'    => $overlap,
		));

		if (is_wp_error($chunks)) {
			return $this->prepare_error($chunks->get_error_message(), 'chunking_failed', 500);
		}

		// Format based on requested format
		switch ($format) {
			case 'langchain':
				$output = $engine->format_for_langchain($chunks);
				break;
			case 'llamaindex':
				$output = $engine->format_for_llamaindex($chunks);
				break;
			case 'universal':
			default:
				$output = $engine->format_universal($chunks, $post_id);
				break;
		}

		return $this->prepare_response($output);
	}

	/**
	 * Batch markdown conversion
	 *
	 * Endpoint: POST /slo/v1/batch/markdown
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error Response or error
	 */
	public function batch_markdown($request) {
		$post_ids         = $request->get_param('post_ids');
		$include_metadata = $request->get_param('include_metadata');

		// Limit batch size
		if (count($post_ids) > 50) {
			return $this->prepare_error('Batch size exceeds maximum of 50 posts', 'batch_too_large', 400);
		}

		$processor = SLO_Content_Processor::get_instance();
		$results   = array();
		$errors    = array();

		foreach ($post_ids as $post_id) {
			$post = get_post($post_id);
			if (!$post) {
				$errors[] = array(
					'post_id' => $post_id,
					'error'   => 'Post not found',
				);
				continue;
			}

			// Check post accessibility
			if ('publish' !== $post->post_status && !current_user_can('edit_post', $post_id)) {
				$errors[] = array(
					'post_id' => $post_id,
					'error'   => 'Post not accessible',
				);
				continue;
			}

			$markdown = $processor->convert_to_markdown($post_id, array(
				'include_metadata' => $include_metadata,
			));

			if (is_wp_error($markdown)) {
				$errors[] = array(
					'post_id' => $post_id,
					'error'   => $markdown->get_error_message(),
				);
			} else {
				$results[] = array(
					'post_id'    => $post_id,
					'markdown'   => $markdown,
					'post_title' => get_the_title($post_id),
					'post_url'   => get_permalink($post_id),
				);
			}
		}

		return $this->prepare_response(array(
			'success_count' => count($results),
			'error_count'   => count($errors),
			'results'       => $results,
			'errors'        => $errors,
		));
	}

	/**
	 * Batch chunk generation
	 *
	 * Endpoint: POST /slo/v1/batch/chunks
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error Response or error
	 */
	public function batch_chunks($request) {
		$post_ids = $request->get_param('post_ids');
		$strategy = $request->get_param('strategy');
		$format   = $request->get_param('format');

		// Limit batch size
		if (count($post_ids) > 20) {
			return $this->prepare_error('Batch size exceeds maximum of 20 posts', 'batch_too_large', 400);
		}

		$engine  = SLO_Chunking_Engine::get_instance();
		$results = array();
		$errors  = array();

		foreach ($post_ids as $post_id) {
			$post = get_post($post_id);
			if (!$post) {
				$errors[] = array(
					'post_id' => $post_id,
					'error'   => 'Post not found',
				);
				continue;
			}

			// Check post accessibility
			if ('publish' !== $post->post_status && !current_user_can('edit_post', $post_id)) {
				$errors[] = array(
					'post_id' => $post_id,
					'error'   => 'Post not accessible',
				);
				continue;
			}

			$chunks = $engine->get_cached_chunks($post_id, $strategy);

			if (is_wp_error($chunks)) {
				$errors[] = array(
					'post_id' => $post_id,
					'error'   => $chunks->get_error_message(),
				);
			} else {
				// Format based on requested format
				switch ($format) {
					case 'langchain':
						$output = $engine->format_for_langchain($chunks);
						break;
					case 'llamaindex':
						$output = $engine->format_for_llamaindex($chunks);
						break;
					case 'universal':
					default:
						$output = $engine->format_universal($chunks, $post_id);
						break;
				}

				$results[] = $output;
			}
		}

		return $this->prepare_response(array(
			'success_count' => count($results),
			'error_count'   => count($errors),
			'results'       => $results,
			'errors'        => $errors,
		));
	}

	/**
	 * Get cache statistics
	 *
	 * Endpoint: GET /slo/v1/cache/stats
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error Response or error
	 */
	public function get_cache_stats($request) {
		global $wpdb;

		// Count cache-related transients
		$cache_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options}
				WHERE option_name LIKE %s
				OR option_name LIKE %s",
				$wpdb->esc_like('_transient_slo_') . '%',
				$wpdb->esc_like('_transient_timeout_slo_') . '%'
			)
		);

		// Get cache duration setting
		$cache_duration = (int) get_option('slo_cache_duration', 3600);

		// Get some sample cache keys
		$cache_keys = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT option_name FROM {$wpdb->options}
				WHERE option_name LIKE %s
				AND option_name NOT LIKE %s
				LIMIT 10",
				$wpdb->esc_like('_transient_slo_') . '%',
				'%_timeout_%'
			)
		);

		// Remove _transient_ prefix from keys
		$cache_keys = array_map(function($key) {
			return str_replace('_transient_', '', $key);
		}, $cache_keys);

		return $this->prepare_response(array(
			'cache_enabled'  => true,
			'cache_count'    => (int) $cache_count / 2, // Divided by 2 because each transient has a timeout entry
			'cache_duration' => $cache_duration,
			'sample_keys'    => $cache_keys,
			'cache_group'    => SLO_Cache_Manager::CACHE_GROUP,
		));
	}

	/**
	 * Clear cache for a specific post
	 *
	 * Endpoint: DELETE /slo/v1/cache/{post_id}
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error Response or error
	 */
	public function clear_post_cache($request) {
		$post_id = $request->get_param('post_id');

		// Check if post exists
		$post = get_post($post_id);
		if (!$post) {
			return $this->prepare_error('Post not found', 'post_not_found', 404);
		}

		// Clear cache for this post
		$cache_manager = SLO_Cache_Manager::get_instance();
		$cache_manager->invalidate_post($post_id);

		// Also clear any chunk caches for this post
		global $wpdb;
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options}
				WHERE option_name LIKE %s
				OR option_name LIKE %s",
				$wpdb->esc_like('_transient_slo_chunks_' . $post_id) . '%',
				$wpdb->esc_like('_transient_timeout_slo_chunks_' . $post_id) . '%'
			)
		);

		return $this->prepare_response(array(
			'message' => sprintf('Cache cleared for post %d', $post_id),
			'post_id' => $post_id,
		));
	}

	/**
	 * Clear all caches
	 *
	 * Endpoint: DELETE /slo/v1/cache
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error Response or error
	 */
	public function clear_all_cache($request) {
		global $wpdb;

		// Clear all plugin transients
		$deleted = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options}
				WHERE option_name LIKE %s
				OR option_name LIKE %s",
				$wpdb->esc_like('_transient_slo_') . '%',
				$wpdb->esc_like('_transient_timeout_slo_') . '%'
			)
		);

		// Clear object cache
		wp_cache_flush();

		return $this->prepare_response(array(
			'message'       => 'All caches cleared successfully',
			'deleted_count' => (int) $deleted / 2, // Divided by 2 because each transient has a timeout entry
		));
	}

	/**
	 * Health check endpoint
	 *
	 * Endpoint: GET /slo/v1/health
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error Response or error
	 */
	public function health_check($request) {
		return $this->prepare_response(array(
			'status'            => 'ok',
			'version'           => SLO_VERSION,
			'wordpress_version' => get_bloginfo('version'),
			'api_enabled'       => $this->is_api_enabled(),
			'cache_enabled'     => class_exists('SLO_Cache_Manager'),
			'timestamp'         => current_time('c'),
			'endpoints'         => array(
				'markdown' => rest_url(self::NAMESPACE . '/posts/{id}/markdown'),
				'chunks'   => rest_url(self::NAMESPACE . '/posts/{id}/chunks'),
				'batch'    => rest_url(self::NAMESPACE . '/batch/markdown'),
			),
		));
	}

	/**
	 * Prepare successful response
	 *
	 * @param mixed $data    Response data
	 * @param int   $status  HTTP status code
	 * @return WP_REST_Response Response object
	 */
	private function prepare_response($data, $status = 200) {
		return new WP_REST_Response($data, $status);
	}

	/**
	 * Prepare error response
	 *
	 * @param string $message Error message
	 * @param string $code    Error code
	 * @param int    $status  HTTP status code
	 * @return WP_Error Error object
	 */
	private function prepare_error($message, $code = 'error', $status = 400) {
		return new WP_Error($code, $message, array('status' => $status));
	}

	/**
	 * Check if REST API is enabled in settings
	 *
	 * @return bool True if enabled, false otherwise
	 */
	private function is_api_enabled() {
		return (bool) get_option('slo_enable_rest_api', false);
	}

	/**
	 * Apply rate limiting to API requests
	 *
	 * Uses transients to track requests per client. Rate limits are based on
	 * IP address for non-authenticated users and user ID for authenticated users.
	 *
	 * @param WP_REST_Request $request Request object
	 * @return bool True if within limit, false if exceeded
	 */
	private function apply_rate_limit($request) {
		// Get client identifier
		$ip      = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
		$user_id = get_current_user_id();

		// Create unique identifier
		$identifier = $user_id ? 'user_' . $user_id : 'ip_' . md5($ip);

		$transient_key = 'slo_api_rate_limit_' . $identifier;
		$requests      = get_transient($transient_key);

		if (false === $requests) {
			$requests = 0;
		}

		// Get rate limit from settings (default: 60 per hour)
		$rate_limit = (int) get_option('slo_rate_limit', 60);

		if ($requests >= $rate_limit) {
			return false;
		}

		// Increment request count
		set_transient($transient_key, $requests + 1, HOUR_IN_SECONDS);

		return true;
	}
}
