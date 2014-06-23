<?php

class acf_field_select2 extends acf_field {
	
	// vars
	var $settings, // will hold info such as dir / path
		$defaults; // will hold default field options
		
		
	/*
	*  __construct
	*
	*  Set name / label needed for actions / filters
	*
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function __construct()
	{
		// vars
		$this->name = 'select2';
		$this->label = __('Select2');
		$this->category = __("Basic",'acf'); // Basic, Content, Choice, etc
		$this->defaults = array(
			//'preview_size' => 'thumbnail'
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
	*  create_options()
	*
	*  Create extra options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like below) to save extra data to the $field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field	- an array holding all the field's data
	*/
	
	function create_options( $field )
	{

		
		// key is needed in the field names to correctly save the data
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

        do_action('acf/create_field', array(
            'type'	=>	'textarea',
            'name'	=>	'fields['.$key.'][values]',
            'value'	=>	$field['values'],
        ));

		?>
	</td>
</tr>
		<?php
		
	}
	
	
	/*
	*  create_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field - an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function create_field( $field )


        /*
        *  input_admin_enqueue_scripts()
        *
        *  This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
        *  Use this action to add CSS + JavaScript to assist your create_field() action.
        *
        *  $info	http://codex.wordpress.org/Plugin_API/Action_Reference/admin_enqueue_scripts
        *  @type	action
        *  @since	3.6
        *  @date	23/01/13
        */
    {
        // value must be array
        if( !is_array($field['values']) )
        {
            // perhaps this is a default value with new lines in it?
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

        ?>
        <input type='hidden' value='<?php echo implode(',', $field["value"]) ?>' name='<?php echo $field["name"] ?>' id='js-select2-<?php echo $field["id"] ?>' style="width: 200px"/>

        <div class="hidden">
            <select id="<?php echo $field["id"] ?>" class="js-select2" multiple>
                <?php
                foreach($values as $key => $val) {
                    $params = null;
                    if(in_array($key, $field['value'])) {
                        $params = 'selected="selected"';
                    }

                    $row = sprintf('<option value="%s" %s>%s</option>', $key, $params, $val);
                    echo $row;
                }
            ?>
        </div>

    <?php
    }

    function input_admin_enqueue_scripts()
	{
		// Note: This function can be removed if not used
		
		
		// register ACF scripts
        wp_register_script( 'acf-input-select2-select2', $this->settings['dir'] . 'js/select2.min.js', array('acf-input'), $this->settings['version'] );
        wp_register_script( 'acf-input-select2-field', $this->settings['dir'] . 'js/field.js', array('acf-input'), $this->settings['version'], true );

		wp_register_style( 'acf-input-select2', $this->settings['dir'] . 'css/select2.css', array('acf-input'), $this->settings['version'] );
		
		
		// scripts
		wp_enqueue_script(array(
			'acf-input-select2-field',
			'acf-input-select2-select2',
		));

		// styles
		wp_enqueue_style(array(
			'acf-input-select2',
		));
		
		
	}

    function format_value( $value, $post_id, $field )
    {
        $return = explode(",", $value);
        array_map('trim', $value);

        return $return;
    }

}


// create field
new acf_field_select2();

?>
