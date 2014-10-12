<?php
/*
Plugin Name: Contact Form 7 Israeli Phone numbers
Plugin URI: http://en.bainternet.info
Description: This plugin add a new form tag named [telil] which acts just like the tel tag but validate against Israeli mobile and land-line phone numbers ex:
052-5555555, 052-555-5555,0525555555,08-5555555,085555555.
known mobile pattern is 10 digital starting with: "050","052","053","054","055","056","057","058","059".
known land-lines patterns are 10 digital starting with:"072","073","074","076","077","078" and 9 digital starting with "02","03","04","08","09".
Version: 0.1
Author: Bainternet
Author Email: admin@bainternet.info
License:

  Copyright 2014 Bainternet (admin@bainternet.info)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
  
*/
class cpcf7_il_phone{
	/**
	 * plugin constructor
	 */
	function __construct(){
		add_action( 'wpcf7_init', array($this,'wpcf7_add_shortcode_telil' ));
		add_filter( 'wpcf7_validate_telil', array($this,'wpcf7_telil_validation_filter'), 10, 2 );
		add_filter( 'wpcf7_validate_telil*', array($this,'wpcf7_telil_validation_filter'), 10, 2 );
		/* Tag generator */
		add_action( 'admin_init', array($this,'reg_tag'), 15 );
	}

	/**
	 * Register cf7 telil shortcode
	 */
	function wpcf7_add_shortcode_telil(){
		if(function_exists('wpcf7_add_shortcode')){
			wpcf7_add_shortcode(
				array( 'telil','telil*' ),
				array($this,'wpcf7_teli_shortcode_handler'), true 
			);
		}
	}

	/**
	 * handle cf7 telil shortcode
	 */
	function wpcf7_teli_shortcode_handler($tag){
		$tag = new WPCF7_Shortcode( $tag );
		if ( empty( $tag->name ) )
			return '';

		$validation_error = wpcf7_get_validation_error( $tag->name );

		$class = wpcf7_form_controls_class( $tag->type, 'wpcf7-text' );

		if ( $validation_error )
			$class .= ' wpcf7-not-valid';

		$atts              = array();
		$atts['size']      = $tag->get_size_option( '40' );
		$atts['maxlength'] = $tag->get_maxlength_option();
		$atts['class']     = $tag->get_class_option( $class );
		$atts['id']        = $tag->get_id_option();
		$atts['tabindex']  = $tag->get_option( 'tabindex', 'int', true );

		if ( $tag->has_option( 'readonly' ) )
			$atts['readonly'] = 'readonly';

		if ( $tag->is_required() )
			$atts['aria-required'] = 'true';

		$atts['aria-invalid'] = $validation_error ? 'true' : 'false';

		$value = (string) reset( $tag->values );

		if ( $tag->has_option( 'placeholder' ) || $tag->has_option( 'watermark' ) ) {
			$atts['placeholder'] = $value;
			$value = '';
		} elseif ( '' === $value ) {
			$value = $tag->get_default_option();
		}

		$value = wpcf7_get_hangover( $tag->name, $value );

		$atts['value'] = $value;

		if ( wpcf7_support_html5() ) {
			$atts['type'] = 'tel';
		} else {
			$atts['type'] = 'text';
		}

		$atts['name'] = $tag->name;

		$atts = wpcf7_format_atts( $atts );

		$html = sprintf(
			'<span class="wpcf7-form-control-wrap %1$s"><input %2$s />%3$s</span>',
			sanitize_html_class( $tag->name ), $atts, $validation_error 
		);

		return $html;
	}

	/**
	 * Filter and validate cf7 telil field
	 */
	function wpcf7_telil_validation_filter($result, $tag ){
		$type = $tag['type'];
		$name = $tag['name'];
		$tag = new WPCF7_Shortcode( $tag );
		$value = $_POST[$name];
		if ( $tag->is_required() && '' == $value ) {
			$result['valid'] = false;
			$result['reason'][$name] = wpcf7_get_message( 'invalid_required' );
		}else{
			if (!class_exists('validate_phone_il')){
				include_once('validate_phone_il.php');
			}
			$valid_phone = new validate_phone_il($value);
			if ( !$valid_phone->valid) {
				$result['valid'] = false;
				$result['reason'][$name] = wpcf7_get_message( 'invalid_tel' );
			}
		}
		return $result;
	}

	/**
	 * register telil in tag gennerator
	 */
	function reg_tag(){
		if(function_exists('wpcf7_add_tag_generator')){
			wpcf7_add_tag_generator( 
				'telil', 
				__( 'IL Telephone number', 'wpcf7' ),
				'wpcf7-tg-pane-telil', 
				array($this,'wpcf7_tg_pane_telil')
			);
		}
	}

	/**
	 * call print tag gen
	 */
	function wpcf7_tg_pane_telil( $contact_form ) {
		$this->tag_gen('telil'); 
	}

	/**
	 * prints tag gnerator
	 */
	function tag_gen($type = 'telil'){
		?>
		<div id="wpcf7-tg-pane-<?php echo $type; ?>" class="hidden">
			<form action="">
				<table>
					<tr>
						<td>
							<input type="checkbox" name="required" />&nbsp;<?php echo esc_html( __( 'Required field?', 'contact-form-7' ) ); ?>
						</td>
					</tr>
					<tr>
						<td>
						<?php echo esc_html( __( 'Name', 'contact-form-7' ) ); ?><br /><input type="text" name="name" class="tg-name oneline" />
						</td>
						<td></td>
					</tr>
				</table>

				<table>
					<tr>
						<td>
							<code>id</code> (<?php echo esc_html( __( 'optional', 'contact-form-7' ) ); ?>)<br />
							<input type="text" name="id" class="idvalue oneline option" />
						</td>

						<td>
							<code>class</code> (<?php echo esc_html( __( 'optional', 'contact-form-7' ) ); ?>)<br />
							<input type="text" name="class" class="classvalue oneline option" />
						</td>
					</tr>

					<tr>
						<td>
							<code>size</code> (<?php echo esc_html( __( 'optional', 'contact-form-7' ) ); ?>)<br />
							<input type="number" name="size" class="numeric oneline option" min="1" />
						</td>
						<td>
							<code>maxlength</code> (<?php echo esc_html( __( 'optional', 'contact-form-7' ) ); ?>)<br />
							<input type="number" name="maxlength" class="numeric oneline option" min="1" />
						</td>
					</tr>

					<tr>
						<td>
							<?php echo esc_html( __( 'Default value', 'contact-form-7' ) ); ?> (<?php echo esc_html( __( 'optional', 'contact-form-7' ) ); ?>)<br />
							<input type="text" name="values" class="oneline" />
						</td>

						<td>
							<br /><input type="checkbox" name="placeholder" class="option" />&nbsp;<?php echo esc_html( __( 'Use this text as placeholder?', 'contact-form-7' ) ); ?>
						</td>
					</tr>
				</table>

				<div class="tg-tag"><?php echo esc_html( __( "Copy this code and paste it into the form left.", 'contact-form-7' ) ); ?><br /><input type="text" name="<?php echo $type; ?>" class="tag wp-ui-text-highlight code" readonly="readonly" onfocus="this.select()" /></div>

				<div class="tg-mail-tag"><?php echo esc_html( __( "And, put this code into the Mail fields below.", 'contact-form-7' ) ); ?><br /><input type="text" class="mail-tag wp-ui-text-highlight code" readonly="readonly" onfocus="this.select()" /></div>
			</form>
		</div>
		<?php
	}
}//end class
add_action('plugins_loaded','wpcf7_telil_init');
function wpcf7_telil_init(){
	global $cpcf7_il_phone;
	$cpcf7_il_phone = new cpcf7_il_phone();
}