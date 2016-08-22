<?php

class acf_field_select2 extends acf_field {

	var $settings, // will hold info such as dir / path
			$defaults; // will hold default field options


	function __construct()
	{
		$this->name = 'select2';
		$this->label = __('Select2');
		$this->category = __("Basic",'acf'); // Basic, Content, Choice, etc
		$this->defaults = array(
				'field_type' 		=> 'select',
				'allow_null' 		=> 0,
				'load_save_terms' 	=> 1,
				'multiple'			=> 1,
				'return_format'		=> 'id'
		);

		// do not delete!
		parent::__construct();

		// settings
		$this->settings = array(
				'path' => apply_filters('acf/helpers/get_path', __FILE__),
				'dir' => apply_filters('acf/helpers/get_dir', __FILE__),
				'version' => '1.0.0'
		);

		add_filter('acf/update_field/type=select2', array($this, 'update_field'), 5, 2);
	}

	/*
    *  This filter is applied to the $value after it is loaded from the db
    *
    *  @param	$value - the value found in the database
    *  @param	$post_id - the $post_id from which the value was loaded from
    *  @param	$field - the field array holding all the field options
    *
    *  @return	$value - the value to be saved in te database
    */

	function load_value( $value, $post_id, $field )
	{
		if( $field['load_save_terms'] )
		{
			$value = array();

			$terms = get_the_terms( $post_id, $field['taxonomy'] );

			if( is_array($terms) ){ foreach( $terms as $term ){
				$value[] = $term->term_id;
			}}
		}

		return $value;
	}

	/**
	 *  This filter is appied to the $value before it is updated in the db
	 *
	 *  @param	$value - the value which will be saved in the database
	 *  @param	$field - the field array holding all the field options
	 *  @param	$post_id - the $post_id of which the value will be saved
	 *
	 *  @return	$value - the modified value
	 */

	function update_value( $value, $post_id, $field )
	{
		if( is_array($value) )
		{
			$value = array_filter($value);
		} else {
			$value = explode(',', $value);
		}

		if( $field['load_save_terms'] )
		{
			$value = acf_parse_types( $value );
			wp_set_object_terms( $post_id, $value, $field['taxonomy'], false );
		}

		return $value;
	}

	/**
	 * Send request to platform and get back ID of term in Wordpress
	 *
	 * @param $taxonomy
	 * @param $term
	 * @param $slug
	 * @return array|mixed
	 */

	function addTermToDatabase($taxonomy, $term, $slug) {
		$ch = curl_init();

		$uri = home_url("api/taxonomy/add");
		curl_setopt($ch, CURLOPT_URL, $uri);
		curl_setopt($ch, CURLOPT_POST, 1);

		curl_setopt($ch, CURLOPT_POSTFIELDS,
				http_build_query(array('dimension' => $taxonomy, 'term' => $term, 'slug' => $slug)));

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$server_output = curl_exec ($ch);

		return json_decode($server_output);
	}

	/**
	 *  This filter is appied to the $value after it is loaded from the db and before it is passed back to the api functions such as the_field
	 *
	 *  @param	$value	- the value which was loaded from the database
	 *  @param	$post_id - the $post_id from which the value was loaded
	 *  @param	$field	- the field array holding all the field options
	 *
	 *  @return	$value	- the modified value
	 */

	function format_value_for_api( $value, $post_id, $field )
	{
		if( !$value )
		{
			return $value;
		}

		$is_array = true;

		if( !is_array($value) )
		{
			$is_array = false;
			$value = array( $value );
		}

		// format
		if( $field['return_format'] == 'object' )
		{
			foreach( $value as $k => $v )
			{
				$value[ $k ] = get_term( $v, $field['taxonomy'] );
			}
		}


		// de-convert from array
		if( !$is_array && isset($value[0]) )
		{
			$value = $value[0];
		}

		// Note: This function can be removed if not used
		return $value;
	}

	/**
	 *  Create extra options for your field. This is rendered when editing a field.
	 *  The value of $field['name'] can be used (like below) to save extra data to the $field
	 *
	 *  @param	$field	- an array holding all the field's data
	 */

	function create_options( $field )
	{
		$key = $field['name'];


		// Create Field Options HTML
		?>
		<tr class="field_option field_option_<?php echo $this->name; ?>">
			<td class="label">
				<label>Options</label>
				<p class="description">Options for select2 field</p>
			</td>
			<td>
				<?php

				do_action('acf/render_field', array(
						'type'	=>	'textarea',
						'name'	=>	'fields['.$key.'][values]',
						'value'	=>	$field['values'],
				));

				?>
			</td>
		</tr>
		<?php

	}


	/**
	 *  Create the HTML interface for your field
	 *
	 *  @param	$field - an array holding all the field's data
	 */

	function render_field( $field )
	{
		$field['values'] = isset($field['values']) ? $field['values'] : [];
		// value must be array
		if( !is_array($field['values']) )
		{
			if( strpos($field['values'], "\n") !== false )
			{
				// found multiple lines, explode it
				$field['values'] = explode("\n", $field['values']);
			}
			else
			{
				$field['values'] = array( $field['values'] );
			}
			foreach($field["values"] as $val) {
				$choice = explode(":", $val);
				$values[$choice[0]] = $choice[1];
			}

		} else if(is_array($field["values"])) {
			$values = $field["values"];
		}

		// trim value
		$field['values'] = array_map('trim', $field['values']);

		$taxonomies= get_terms($field['taxonomy'], ['hide_empty' => false]);
		foreach($taxonomies as $val) {
			$values[$val->term_id] = $val->name;
		}


		$style = "<style> #acf-".$field["taxonomy"]."{ display: none; }</style>";
		?>
		<?php if ($field['hidden'] == 1): ?>
		<?php echo $style ?>
	<?php endif ?>
		<input class="text" type='hidden' value='<?php echo implode(',', $field["value"]) ?>' name='<?php echo $field["name"] ?>' id='js-select2-<?php echo $field["id"] ?>' />

		<div class="hidden">
			<select id="<?php echo $field["id"] ?>" class="js-select2" data-required="<?php echo $field["required"] ?>" data-static="<?php echo $field["static"] ?>">
				<?php
				foreach($values as $key => $val) {
					$params = null;
					if(in_array($key, $field['value'])) {
						$params = 'selected="selected"';
					}

					if(!empty($key) || !empty($val)) {
						$row = sprintf('<option value="%s" %s>%s</option>', $key, $params, $val);
						echo $row;
					}
				}
				?>
			</select>
		</div>
		<?php
	}

	function input_admin_enqueue_scripts()
	{
		// register ACF scripts
		wp_register_script( 'acf-input-select2-field', get_template_directory_uri() . '/js/acf-select2-field.js', array('acf-input'), $this->settings['version'], true );

		wp_register_style( 'acf-input-select2', get_template_directory_uri() . '/select2.css', array('acf-input'), $this->settings['version'] );


		// scripts
		wp_enqueue_script(array(
				'acf-input-select2-field',
		));

		// styles
		wp_enqueue_style(array(
				'acf-input-select2',
		));
	}

	function format_value( $value, $post_id, $field )
	{
		if(!is_array($value)) {
			$value = explode(",", $value);
			array_map('trim', $value);
		}
		return $value;
	}

}

// create field
new acf_field_select2();

?>
