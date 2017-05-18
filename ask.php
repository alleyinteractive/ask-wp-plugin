<?php
/**
 * Plugin Name: Ask
 * Plugin URI: https://coralproject.net
 * Description: A plugin to easily embed Ask Forms and Ask Galleries in WordPress
 * Version: 1.0.1
 * Author: The Coral Project
 * Author URI: https://coralproject.net
 * License: Apache 2.0
 *
 * @package Ask_Plugin
 */

/**
 * Class Ask_Plugin
 */
class Ask_Plugin {

	/**
	 * Ask_Plugin constructor.
	 */
	public function __construct() {
		add_shortcode( 'ask-form', array( $this, 'render_form_shortcode' ) );
		add_shortcode( 'ask-gallery', array( $this, 'render_gallery_shortcode' ) );
		add_action( 'admin_menu', array( $this, 'create_settings_page' ) );
		add_action( 'admin_init', array( $this, 'setup_sections' ) );
		add_action( 'admin_init', array( $this, 'setup_fields' ) );
	}

	/**
	 * Registers the functions to create the settings page.
	 *
	 * @since 1.0.0
	 */
	public function create_settings_page() {
		add_menu_page( __( 'Ask Forms', 'coral-project-ask' ), __( 'Ask Forms', 'coral-project-ask' ), 'manage_options', 'ask-forms', array(
			$this,
			'render_settings_page',
		), 'dashicons-list-view', 100 );

		add_submenu_page( 'ask-forms', __( 'Ask Settings', 'coral-project-ask' ), __( 'Ask Settings', 'coral-project-ask' ), 'manage_options', 'ask-forms', array(
			$this,
			'render_settings_page',
		), 'dashicons-admin-plugins', 100 );

		if ( get_option( 'coral_ask_admin_url' ) ) {
			add_submenu_page( 'ask-forms', __( 'Ask Admin', 'coral-project-ask' ), __( 'Ask Admin', 'coral-project-ask' ), 'manage_options', 'ask-admin', array(
				$this,
				'render_admin_page',
			), 'dashicons-admin-plugins', 100 );
		}
	}

	/**
	 * Registers the settings sections.
	 *
	 * @since 1.0.0
	 */
	public function setup_sections() {
		add_settings_section( 'integration', __( 'About Ask', 'coral-project-ask' ), array( $this, 'section_callback' ), 'ask-settings' );
	}

	/**
	 * Register the settings fields.
	 *
	 * @since 1.0.0
	 */
	public function setup_fields() {
		$fields = array(
			array(
				'uid'         => 'coral_ask_base_url',
				'label'       => __( 'Server Base URL', 'coral-project-ask' ),
				'section'     => 'integration',
				'type'        => 'url',
				'options'     => false,
				'placeholder' => 'https://s3-us-west-2.amazonaws.com/my-s3-bucket/',
				'default'     => '',
				'callback'    => 'base_url_callback',
			),
			array(
				'uid'         => 'coral_ask_admin_url',
				'label'       => __( 'Admin Base URL', 'coral-project-ask' ),
				'section'     => 'integration',
				'type'        => 'url',
				'options'     => false,
				'placeholder' => 'https://ask.mydomain.com',
				'default'     => '',
				'callback'    => 'admin_url_callback',
			),
		);

		foreach ( $fields as $field ) {
			add_settings_field( $field['uid'], $field['label'], array(
				$this,
				$field['callback'],
			), 'ask-settings', $field['section'], $field );
			register_setting( 'ask-settings', $field['uid'] );
		}
	}

	/**
	 * Creates the markup for the settings page.
	 *
	 * @internal
	 * @since 1.0.0
	 *
	 * @param array $arguments The data sent from {@see add_settings_section()}.
	 */
	public function section_callback( $arguments ) {
		?>
		<p><?php esc_html_e( 'Ask is a form tool, built specifically for journalists.', 'coral-project-ask' ); ?></p>
		<p><?php esc_html_e( 'Ask lets you easily create embeddable forms, manage submissions, and display galleries of the best responses. It’s fast, flexible, and you control the design and the data.', 'coral-project-ask' ); ?></p>
		<p>
			<?php printf(
				esc_html__( 'You can find out how to install and manage Ask %shere%s.', 'coral-project-ask' ),
				'<a href="https://docs.coralproject.net/products/ask/">',
				'</a>'
			); ?>
		</p>
		<p>
			<?php printf(
				esc_html__( 'Ask is an open source product brought to you by The Coral Project. Find out more about Coral and the tools we build %shere%s.', 'coral-project-ask' ),
				'<a href="https://coralproject.net">',
				'</a>'
			); ?>
		</p>

		<h2><?php esc_html_e( 'Instructions', 'coral-project-ask' ); ?></h2>
		<p><?php esc_html_e( 'Use your Ask shortcode in any post or page where you want to embed an Ask form:', 'coral-project-ask' ); ?></p>
		<p><code>[ask-form id="1234567890abcdefghij"]</code></p>
		<p><?php esc_html_e( 'You can find your Ask form ID in the URL of your form, ex:', 'coral-project-ask' ); ?> <strong>https://ask.yourdomain.com/forms/1234567890abcdefghij</strong></p>

		<h2><?php esc_html_e( 'Ask Settings', 'coral-project-ask' ); ?></h2>
		<p>
			<?php printf(
				esc_html__( 'Questions/feedback? Reach out to us on %sTwitter%s or join our %sCommunity%s.', 'coral-project-ask' ),
				'<a href="https://twitter.com/coralproject">',
				'</a>',
				'<a href="https://community.coralproject.net/">',
				'</a>'
			); ?>
		</p>
		<p>
			<?php printf(
				esc_html__( 'You are using the version %s of the Ask WordPress Plugin. View the code, documentation, and latest releases %shere%s.', 'coral-project-ask' ),
				esc_html( get_plugin_data( __FILE__ )['Version'] ),
				'<a href="https://github.com/coralproject/ask-wp-plugin">',
				'</a>'
			); ?>
		</p>
		<?php
	}

	/**
	 * Output the settings field for the form's base URL.
	 *
	 * @since 1.0.0
	 *
	 * @param array $arguments Data sent from {@see add_settings_field()}.
	 */
	public function base_url_callback( $arguments ) {
		?>
		<p><?php esc_html_e( 'To use Ask forms in WordPress, you will need to set a Form Base URL, which is where your forms are stored:', 'coral-project-ask' ); ?></p>
		<?php $this->render_settings_input_field( 'coral_ask_base_url', $arguments['placeholder'] ); ?>
		<?php
	}


	/**
	 * Output the settings field for the form's admin URL.
	 *
	 * @since 1.0.0
	 *
	 * @param array $arguments Data sent from {@see add_settings_field()}.
	 */
	public function admin_url_callback( $arguments ) {
		?>
		<p><?php esc_html_e( 'You can also optionally manage your forms in WordPress, by providing the URL where your Ask admin is located:', 'coral-project-ask' ); ?></p>
		<?php $this->render_settings_input_field( 'coral_ask_admin_url', $arguments['placeholder'] ); ?>
		<?php
	}

	/**
	 * Prints input field for settings.
	 *
	 * @since 1.0.1
	 *
	 * @param string $option_id    Field option id.
	 * @param string $placeholder  Placeholder text.
	 */
	public function render_settings_input_field( $option_id, $placeholder ) {
		?>
		<input
			style="width: 600px; height: 40px;"
			name="<?php echo esc_attr( $option_id ); ?>"
			placeholder="<?php echo esc_attr( $placeholder ); ?>"
			id="<?php echo esc_attr( $option_id ); ?>"
			type="url"
			value="<?php echo esc_url( get_option( $option_id ) ); ?>"
		/>
		<?php
	}

	/**
	 * Generates the markup for the settings page.
	 *
	 * @since 1.0.0
	 */
	public function render_settings_page() {
		?>
		<div class="wrap">
			<h2><?php esc_html_e( 'Ask Settings', 'coral-project-ask' ) ?></h2>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'ask-settings' );
				do_settings_sections( 'ask-settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Generates the output for the plugin's admin page.
	 *
	 * @since 1.0.0
	 */
	public function render_admin_page() {
		?>
		<div class="wrap">
			<h2><?php esc_html_e( 'Ask Admin', 'coral-project-ask' ) ?></h2>
			<iframe
				width="100%"
				height="600px"
				src="<?php echo esc_url( get_option( 'coral_ask_admin_url' ) ); ?>"
				frameborder="0"
				hspace="0"
				vspace="0"
				marginheight="0"
				marginwidth="0"
			></iframe>
		</div>
		<?php
	}

	/**
	 * Generates output for the shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type   The type of shortcode to generate. Accepts 'form' or 'gallery'.
	 * @param array	 $attrs  {
	 *		The options passed in the shortcode.
	 *
	 *		@type int|string  $height The unitless height of the container. Default '580'.
	 * 		@type int|string  $id     The form ID.
	 * 		@type bool|string $iframe Whether to use an iframe or div. Default 'true'.
	 * }
	 * @return string The HTML output.
	 */
	public function render_shortcode( $type, $attrs ) {
		// Base URL and ID must be set.
		if ( empty( get_option( 'coral_ask_base_url' ) ) || empty( $attrs['id'] ) ) {
			return '';
		}

		// Height defaults to 580.
		$height = ! empty( $attrs['height'] ) && is_numeric( $attrs['height'] ) ? $attrs['height'] : '580';

		// Set up item URL.
		$item_url_base = trailingslashit( get_option( 'coral_ask_base_url' ) ) . trim( $attrs['id'] );

		if ( isset( $attrs['iframe'] ) && 'true' == $attrs['iframe'] ) {
			return sprintf(
				'<iframe width="100%%" height="%d" src="%s" frameborder="0" hspace="0" vspace="0" marginheight="0" marginwidth="0"></iframe>',
				absint( $height ),
				esc_url( $item_url_base . '.html' )
			);
		} else {
			return sprintf(
				'<div id="ask-%s"></div><script async src="%s"></script>',
				esc_attr( $type ),
				esc_url( $item_url_base . '.js' )
			);
		}
	}

	/**
	 * Generate output for the 'ask-form' shortcode.
	 *
	 * @since 1.0.0
	 * @see Ask_Plugin::render_shortcode()
	 *
	 * @param array $attrs The options to pass to render_shortcode().
	 * @return string The HTML generated for the Ask form.
	 */
	public function render_form_shortcode( $attrs ) {
		return $this->render_shortcode( 'form', $attrs );
	}

	/**
	 * Generate output for the 'ask-gallery' shortcode.
	 *
	 * @since 1.0.0
	 * @see   Ask_Plugin::render_shortcode()
	 *
	 * @param array $attrs The options to pass to render_shortcode().
	 * @return string The HTML generated for the Ask gallery.
	 */
	public function render_gallery_shortcode( $attrs ) {
		return $this->render_shortcode( 'gallery', $attrs );
	}
}

new Ask_Plugin();
