<?php
/**
 * Plugin Class
 */
class Post_And_Page_Asides {
	/**
	 * Load Variables
	 */
	protected $plugin_name;
	protected $plugin_prefix;
	protected $screens;
	protected $fields;
	
	/**
	 * Constructor
	 */
	public function __construct() {
		/**
		* Name and prefix for plugin
		*/
		$this->plugin_name = 'post_and_page_asides';
		$this->plugin_prefix = 'ppa';
		
		/**
		 * Post Types in which this should be available
		 */
		$this->screens = array(
			'post',
			'page',
		);
		
		/**
		 * Meta Fields
		 */
		$this->fields = array(
			array(
				'label' => __( 'Aside Title', $this->plugin_name ),
				'desc'  => __( '', $this->plugin_name ),
				'name'  => $this->plugin_prefix.'_aside_title',
				'id'    => $this->plugin_prefix.'_aside_title',
				'type'  => 'text',
			),
			array(
				'label' => __( 'Aside Content', $this->plugin_name ),
				'desc'  => __( '', $this->plugin_name ),
				'name'  => $this->plugin_prefix.'_aside_text',
				'id'    => $this->plugin_prefix.'_aside_text',
				'type'  => 'editor',
			),
			array(
				'label' => __( 'Aside Type', $this->plugin_name ),
				'desc'  => __( 'Sets a color scheme for the aside.', $this->plugin_name ),
				'name'  => $this->plugin_prefix.'_aside_type',
				'id'    => $this->plugin_prefix.'_aside_type',
				'type'  => 'dropdown',
				'options' => array(
					'default' => 'Default (Gray)',
					'primary' => 'Primary (Dark Blue)',
					'success' => 'Success (Green)',
					'info'    => 'Info (Light Blue)',
					'warning' => 'Warning (Yellow)',
					'danger'  => 'Danger (Red)',
				),
			),
		);
		
		/**
		 * Add actions to hook in to editor
		 */
		add_action( 'add_meta_boxes', array( $this, 'add_custom_meta_box'), 10, 2 );
		add_action( 'save_post', array( $this, 'save_meta' ) );
	}
	/**
	 * Add Meta Box
	 */
	public function add_custom_meta_box() {
		foreach ( $this->screens as $screen ) {
			add_meta_box(
				$this->plugin_prefix.'_custom_meta_box', // $id
				__( 'Aside', $this->plugin_name ), // $title
				array( $this, 'show_custom_meta_box' ), // $callback
				$screen, // $page
				'normal', // $context
				'high' // $priority
			);
		}
	}
	
	/**
	 * Meta Box Callback Function
	 *
	 * Build interface within the WordPress dashboard
	 */
	public function show_custom_meta_box() {
		global $post; // load wordpress post variable
		
		// Use nonce for verification
		echo '<input type="hidden" name="custom_meta_box_nonce" value="'.wp_create_nonce( basename( __FILE__ ) ).'" />';
		
		// Begin the field table and loop
		foreach ( $this->fields as $field ) {
			
			// get value of this field if it exists for this post
			$meta = get_post_meta( $post->ID, $field['id'], true );
			
			// Output field label
			echo '<h3><label for="'.$field['id'].'">'.$field['label'].'</label></h3>';
			
			// Output fields
			switch( $field['type'] ) {
				// text
				case 'text':
					echo '<input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" size="50" style="width:100%" />
						<br /><span class="description">'.$field['desc'].'</span>';
					break;
				// editor
				case 'editor':
					wp_editor( htmlspecialchars_decode( $meta ), $field['id'], $settings = array( 'textarea_name'=>$field['id'] ) );
					break;
				case 'dropdown':
					echo '<select name="'.$field['id'].'" id="'.$field['id'].'">';
					foreach( $field['options'] as $option => $option_title ) {
						if ( $option == $meta ) {
							echo '<option value="'.$option.'" selected>'.$option_title.'</option>';
						} else {
							echo '<option value="'.$option.'">'.$option_title.'</option>';
						}
					}
					echo '</select>';
					break;
			} //end switch
		} // end foreach
	}
	
	/**
	 * Save Meta Content
	 */
	public function save_meta( $post_id ) {
		
		// verify nonce
		if ( isset( $_POST['custom_meta_box_nonce'] ) ) {
			if ( ! wp_verify_nonce( $_POST['custom_meta_box_nonce'], basename( __FILE__ ) ) )
				return $post_id;
		}
		
		// check autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}
		
		// check permissions
		if ( isset( $_POST['post_type'] ) ) {
			if ( 'page' == $_POST['post_type'] ) {
				if ( !current_user_can( 'edit_page', $post_id ) )
					return $post_id;
			} elseif ( !current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}
		}
		
		// loop through fields and save the data
		foreach ( $this->fields as $field ) {
			if ( isset( $field['id'] ) ) {
				$old = get_post_meta( $post_id, $field['id'], true );
				if ( isset( $_POST[$field['id']] ) ) {
					$new = $_POST[$field['id']];
					if ( $new && $new != $old ) {
						update_post_meta( $post_id, $field['id'], $new );
					} elseif ( '' == $new && $old ) {
						delete_post_meta( $post_id, $field['id'], $old );
					}
				}
			}
		} // end foreach
	}
}
