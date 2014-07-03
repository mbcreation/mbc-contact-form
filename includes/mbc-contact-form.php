<?php

/* version 0.1 */

if( !class_exists( 'MbcContactForm' ) )
{
	class MbcContactForm
	{

		protected $plugin_path;
		
		protected $shortcode;
		protected $filter_prefix;
		public $attachments;
		
		public $form_error = array();
		public $form_message;
		public $form_success_message;
		public $display_form;
		public $fields = array();
		public $button_label;
		public $title;
		public $content_type;
		public $use_selectbox;
		public $use_labels;
		public $use_placeholders;
		public $has_file;
		public $send_files_as_attachments;
		public $store_files_as_attachments;

		function __construct($shortcode)
		{

			$this->plugin_path = dirname(__FILE__);
			$this->shortcode = $shortcode;
			$this->filter_prefix = 'mbccf_'.$this->shortcode.'_';
			$this->attachments = array();
			
			add_action('plugins_loaded', array($this, 'hooks' ) );
			add_action('plugins_loaded', array($this, 'set_default_options' ) );
		}


		public function set_default_options()
		{
			$this->display_form = true;
			$this->form_message = '';
			
			$this->form_success_message = apply_filters($this->filter_prefix.'form_success_message', __('Votre message a bien ete envoye.', 'mbccf'));

			$this->button_label = apply_filters($this->filter_prefix.'button_label', __('Envoyer', 'mbccf'));
			$this->title = apply_filters($this->filter_prefix.'title', '<h3>'.__('Contactez-nous', 'mbccf').'</h3>');
			$this->content_type = apply_filters($this->filter_prefix.'content_type', 'text/html');
			$this->use_selectbox = apply_filters($this->filter_prefix.'use_selectbox', false);
			$this->use_labels = apply_filters($this->filter_prefix.'use_labels', true);
			$this->use_placeholders = apply_filters($this->filter_prefix.'use_placeholders', false);
			
			$this->send_files_as_attachments = apply_filters($this->filter_prefix.'send_files_as_attachments', true);
			$this->store_files_as_attachments = apply_filters($this->filter_prefix.'store_files_as_attachments', false);
			
		}
		
		public function hooks()
		{
			add_action('wp_loaded', array( $this, 'set_parameters') );
        	add_action('wp', array( $this, 'form_treatment') );

			add_shortcode($this->shortcode, array( $this, 'form_shortcode' ) );

			add_action( 'wp_enqueue_scripts', array($this, 'enqueue_scripts'), 999 );
			
			load_plugin_textdomain('mbccf', false, dirname(plugin_basename(__FILE__)).'/languages/');
			
			add_filter( 'body_class', array($this, 'add_body_class') );
			
		}
		
		
		public function set_parameters()
		{
			$this->fields = $this->getFields();
			$this->hasFile();
		}


		public function enqueue_scripts()
		{
			//custom selectbox
			
			global $post;

			if( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, $this->shortcode) )
			{
				if($this->use_selectbox)
				{
					wp_enqueue_script( 'selectbox', plugins_url( '/../js/jquery.selectbox-0.2.min.js', plugin_basename( __FILE__ ) ), array( 'jquery' ), '', true );
			
					wp_enqueue_style( 'selectbox', plugins_url( '/../css/jquery.selectbox.css', plugin_basename( __FILE__ ) ));
				
				}
				
				if(file_exists( dirname(__FILE__) .'/../css/mbc-contact-form.css' ))
					wp_enqueue_style( 'mbc-contact-form', plugins_url( '/../css/mbc-contact-form.css', plugin_basename( __FILE__ ) ));
			}
			
		}

		protected function hasFile()
		{
			$this->has_file = false;
			foreach($this->fields as $key=>$field)
			{
				if($field['type'] == 'file')
				{
					$this->has_file = true;
				}
			}
			
			$this->has_file = apply_filters($this->filter_prefix.'has_file', $this->has_file, $this);
		}
    
    
    	protected function globalValidation()
		{
			if(apply_filters($this->filter_prefix.'do_password_match_check', true, $this))
			{
				$password = false;
				$password2 = false;
				foreach($this->fields as $f => $field)
				{
					if($field['type'] == 'password')
						$password = $f;
					if($field['type'] == 'password_confirm')
						$password2 = $f;
				}

				if($password and $password2)
				{
					if($this->fields[$password]['val'] != $this->fields[$password2]['val'])
					{
						$this->form_error[] = apply_filters($this->filter_prefix.'password_error', sprintf(__('Le champ %s et sa confirmation doivent etre identiques', 'mbccf'), $this->fields[$password]['label']));
					}
				}
			}
			
			if(apply_filters($this->filter_prefix.'do_antispam_check', true, $this))
			{
				/* anti-spam */
				$spam = false;
				foreach($this->fields as $f => $field)
				{
					if($field['type'] == 'textarea' and $field['val'] != strip_tags($field['val']))
						$spam = true;
				}
				if($spam)
					$this->form_error[] = apply_filters($this->filter_prefix.'antispam_error', __('Les tags html sont interdits', 'mbccf'));
			
			}
			
			do_action($this->filter_prefix.'additional_global_validation', $this);

		}
		
		
		protected function placeholder($field)
		{
			if(!$this->use_placeholders)
				return '';
				
			$star = apply_filters($this->filter_prefix.'placeholder_required', '*');

			return apply_filters($this->filter_prefix.'placeholder', ($field['label'].($field['mandatory']?$star:'')), $field);
		}

		protected function label($field, $key)
		{
			if(!$this->use_labels)
				return '';

			$str = ' <label class="label_'.$field['type'].'"';

			if($field['type'] != 'radio')
				$str.=' for="'.$this->shortcode.'-'.$key.'"';
				
			$star = apply_filters($this->filter_prefix.'label_required', '*');

			$str .= '>'.$field['label'].($field['mandatory']?$star:'').'</label> ';

			return apply_filters($this->filter_prefix.'label', $str, $field, $key);
		}
		
		
		protected function html_field($field)
		{

			$str = apply_filters($this->filter_prefix.'html_field_wrapper_begining', '<p class="form-row form-row-'.$field.' form-row-'.$field.'-'.$this->shortcode.' form-row-'.$this->shortcode.'" id="form-row-'.$this->shortcode.'-'.$field.'">', $field);
			
			switch($this->fields[$field]['type'])
			{
				case 'text':
					$str.= $this->label($this->fields[$field], $field);
					$str.= '<input value="'.$this->fields[$field]['val'].'" type="text" class="input-text"
						name="'.$this->shortcode.'['.$field.']" id="'.$this->shortcode.'-'.$field.'" placeholder="'.$this->placeholder($this->fields[$field]).'">';
					break;

				case 'password':
				case 'password_confirm':
					$str.= $this->label($this->fields[$field], $field);
					$str.= '<input value="'.$this->fields[$field]['val'].'" type="password" class="input-text"
						name="'.$this->shortcode.'['.$field.']" id="'.$this->shortcode.'-'.$field.'" placeholder="'.$this->placeholder($this->fields[$field]).'">';
					break;

				case 'email':
					$str.= $this->label($this->fields[$field], $field);
					$str.= '<input value="'.$this->fields[$field]['val'].'" type="email" class="input-text"
						name="'.$this->shortcode.'['.$field.']" id="'.$this->shortcode.'-'.$field.'" placeholder="'.$this->placeholder($this->fields[$field]).'">';
					break;

				case 'textarea':
					$str.= $this->label($this->fields[$field], $field);
					$str.= '<textarea name="'.$this->shortcode.'['.$field.']" id="'.$this->shortcode.'-'.$field.'"
						placeholder="'.$this->placeholder($this->fields[$field]).'">'.$this->fields[$field]['val'].'</textarea>';
					break;

				case 'radio':
					$str.= $this->label($this->fields[$field], $field);
					foreach($this->fields[$field]['data'] as $val=>$name)
					{
						$str.='<input type="radio" class="input-radio" name="'.$this->shortcode.'['.$field.']" id="'.$this->shortcode.'-'.$field.'-'.$val.'" value="'.$val.'"';
						if($this->fields[$field]['val'] == $val)
							$str.=' checked="checked"';
						$str.=' /><label class="label_radio_data" for="'.$this->shortcode.'-'.$field.'-'.$val.'">'.$name.'</label>';
					}
					break;

				case 'checkbox':
					$str.= $this->label($this->fields[$field], $field);

					foreach($this->fields[$field]['data'] as $val=>$name)
					{
						$str.='<input class="input-checkbox" type="checkbox" name="'.$this->shortcode.'['.$field.'][]" id="'.$this->shortcode.'-'.$field.'-'.$val.'" value="'.$val.'"';
						if(is_array($this->fields[$field]['val']) and in_array($val, $this->fields[$field]['val']))
							$str.=' checked="checked"';
						$str.=' /><label class="label_checkbox_data" for="'.$this->shortcode.'-'.$field.'-'.$val.'">'.$name.'</label>';
					}

					break;

				case 'checkbox_unique':
					$str.= '<input value="1" class="input-checkbox-unique" type="checkbox"
						name="'.$this->shortcode.'['.$field.']" id="'.$this->shortcode.'-'.$field.'" ';

					if($this->fields[$field]['val'] == 1)
							$str.=' checked="checked"';

					$str .= '/>';
					$str.= $this->label($this->fields[$field], $field);
					break;


				case 'file':
					$str.= $this->label($this->fields[$field], $field);
					$str.= '<input type="file" class="input-file"
						name="'.$field.'" id="'.$this->shortcode.'-'.$field.'">';
					break;


				case 'select':
					$star = apply_filters($this->filter_prefix.'select_required', '*');
					
					$default_select_value = $this->fields[$field]['label'].($this->fields[$field]['mandatory']?$star:'');
					
					$default_select_value = apply_filters($this->filter_prefix.'default_select_value', $default_select_value, $this->fields[$field]);
					
					$str.= $this->label($this->fields[$field], $field);
					$str.= '<select name="'.$this->shortcode.'['.$field.']" id="'.$this->shortcode.'-'.$field.'">
						<option value="">'.$default_select_value.'</option>';

					foreach($this->fields[$field]['data'] as $val=>$name)
					{
						$str.='<option value="'.$val.'"';
						if($this->fields[$field]['val'] == $val)
							$str.=' selected="selected"';
						$str.='>'.$name.'</option>';
					}

					$str.='</select>';
					if($this->use_selectbox):
						$str.='<script type="text/javascript">
						(function($){
							$(document).ready(function(){
								var responsive_viewport = $(window).width();

								if (responsive_viewport > 1030) {
									$("#'.$this->shortcode.'-'.$field.'").selectbox();
								}
							});
						})(jQuery)
						</script>';
					endif;

					break;

				default:
					$str.= apply_filters($this->filter_prefix.'html_field_'.$this->fields[$field]['type'], $this->fields[$field]['type'].__(' non defini !', 'mbccf'), $field);
			}
			$str.= apply_filters($this->filter_prefix.'html_field_wrapper_end', '</p>');

			return apply_filters($this->filter_prefix.'html_field', $str, $field);
		}
	
		/*
		 * Field validator.
		 * Check empty mendatory fields and email type
		 * #TODO : support more fields type and constraints (min length…)
		 */
		protected function validateField($field, $val)
		{
			$error = '';
			if($field['mandatory'] and ($field['type']!='file'))
			{
				if(empty($val))
				{
					$error = apply_filters($this->filter_prefix.'validate_field_empty_error', sprintf(__('Le champ %s est obligatoire', 'mbccf'), $field['label']), $field, $val);
				}
				else
				{
					if($field['type']=='email')
					{
						if(!is_email($val))
							$error = apply_filters($this->filter_prefix.'validate_field_email_error', sprintf(__('Le champ %s nest pas un email valide', 'mbccf'), $field['label']), $field, $val);
					}
				}
			}

			if($field['type']=='file')
			{
				if($field['mandatory'])
				{
					if(empty($val['name']))
					{
						$error = apply_filters($this->filter_prefix.'validate_field_empty_error', sprintf(__('Le champ %s est obligatoire', 'mbccf'), $field['label']), $field, $val);
					}
				}

				if(!$error)
				{

					if(isset($field['validation']['mime_type']))
					{
						if($val['type'] and !in_array($val['type'], $field['validation']['mime_type']))
						{
							$error = apply_filters($this->filter_prefix.'validate_field_mime_error', sprintf(__('Le format du fichier %s est incorrect', 'mbccf'), $field['label']), $field, $val);
						}
					}
				}

				if($error and !empty($val['file']))
				{
					unlink($val['file']);
				}
				
				if($error and $this->store_files_as_attachments)
				{
					wp_delete_attachment($val['attachment_id']);
				}
			}

			if(!$error)
				$error = apply_filters($this->filter_prefix.'validate_field', $error, $field, $val, $this);

			if($error)
			{
				$this->form_error[] = $error;
				return false;
			}
			else
				return true;
		}
		
		/*
		 * Handle form submission
		 */
		public function form_treatment()
		{

			if($_SERVER['REQUEST_METHOD']!=='POST')
				return;

			if(!isset($_POST[$this->shortcode]))
				return;

			if(!wp_verify_nonce( $_REQUEST['_wpnonce'], $this->shortcode ))
				return;

			$contact = $_POST[$this->shortcode];

			$files = array();

			foreach($this->fields as $key=>$field)
			{
				if(isset($_FILES[$key]))
					$files[$key] = $_FILES[$key];
				else
					$files[$key] = '';
			}

			foreach($this->fields as $key=>$field)
			{
				if($field['type'] != 'file')
				{
					$this->validateField($field, isset($contact[$key])?$contact[$key]:null);

					if($field['type'] == 'textarea')
					{
						$this->fields[$key]['val'] = isset($_POST[$this->shortcode][$key])?stripslashes(($_POST[$this->shortcode][$key])):$this->fields[$key]['val'];
					}
					elseif($field['type'] == 'checkbox')
					{
						$this->fields[$key]['val'] = isset($_POST[$this->shortcode][$key])?($_POST[$this->shortcode][$key]):(is_array($this->fields[$key]['val'])?$this->fields[$key]['val']:array());
					}
					else
						$this->fields[$key]['val'] = isset($_POST[$this->shortcode][$key])?stripslashes(sanitize_text_field($_POST[$this->shortcode][$key])):$this->fields[$key]['val'];
					
					apply_filters($this->filter_prefix.'sanitize_field', $this->fields[$key]['val'], $field, $key);

				}
				else
				{
					$upload_dir = wp_upload_dir();
					$pos = strrpos($_FILES[$key]['name'],'.');
					$nom_ss_ext = substr($_FILES[$key]['name'], 0, $pos);
					$ext =  substr($_FILES[$key]['name'], $pos);
					
					$nom = date('Ymdhis_').sanitize_title($nom_ss_ext).$ext;
					move_uploaded_file($_FILES[$key]['tmp_name'], $upload_dir['basedir'].'/'.$nom);
					
					
						
					if($this->store_files_as_attachments)
					{
						$filetype = wp_check_filetype( basename( $nom ), null );
						$id = wp_insert_attachment( 
							array(
								'post_title' => $nom_ss_ext,
								'post_content' => '',
								'post_status'=> 'publish', 
								'post_mime_type' => $filetype['type']
							),
							$upload_dir['basedir'].'/'.$nom, 0 
						);
						
						if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) require_once( ABSPATH . 'wp-admin/includes/image.php' );
						 
            			wp_update_attachment_metadata($id, wp_generate_attachment_metadata($id, $upload_dir['basedir'].'/'.$nom));
            			
						
						$infos_upload = $_FILES[$key];
						$files[$key]['attachment_id'] = $id;
						
					}
					else
					{
						$infos_upload = $_FILES[$key];
					}	
					
					
					
					if($this->send_files_as_attachments)
					{
						$infos_upload['url'] = $nom;
						$this->attachments[] = $upload_dir['basedir'].'/'.$nom;
					}
					else
					{
						$infos_upload['url'] = $upload_dir['baseurl'].'/'.$nom;
					}
					$this->fields[$key]['val'] = $infos_upload;
					
					$this->validateField($field, $files[$key]);
				}
			}

			$this->globalValidation();

			if(empty($this->form_error)) {
				if(!$this->send_form())
				{
					$this->form_message = '<div class="alert-error"><ul>
						<li class="error-notice"> '.apply_filters($this->filter_prefix.'sending_error',__('Erreur lors de lenvoi.', 'mbccf')).' </li>
					</ul></div>';
				}
			}
			else {

				$this->form_message = '<div class="alert-error"><ul>';

				foreach ($this->form_error as $error ) {
					$this->form_message.= '<li class="error-notice">'.$error.'</li>';
				}

				$this->form_message .= '</ul></div>';

			}
		}
		
		
		/*
		*   Replace the shortcode by the form
		*/
		public function form_shortcode()
		{

			foreach($this->fields as $key=>$field)
			{
				if($field['type'] != 'checkbox')
					$this->fields[$key]['val'] = (empty($_POST[$this->shortcode][$key])) ? $this->fields[$key]['val'] : esc_attr( stripslashes($_POST[$this->shortcode][$key]) );
				else
				{
					$this->fields[$key]['val'] = (isset($_POST[$this->shortcode][$key])?$_POST[$this->shortcode][$key]: (is_array($this->fields[$key]['val'])?$this->fields[$key]['val']:array()));
				}
					
				$this->fields[$key]['val'] = apply_filters($this->filter_prefix.'sanitize_field_post', $this->fields[$key]['val'], $field, $key, $_POST);
			}
			
			$form = $this->getForm();

			if($this->display_form)
				return apply_filters($this->filter_prefix.'form_with_errors', $this->form_message.$form, $this->form_message, $form);

			return $this->form_message;
		} // form_shortcode
		
		/*
		 * Handle form submission
		 */
		protected function send_form()
		{

			$this->form_message = '<div class="alert-success">'. $this->form_success_message.'</div>';
			$this->display_form = apply_filters($this->filter_prefix.'show_form_after_success', false);
			$retour = true;
	
			do_action($this->filter_prefix.'before_sending_mail', $this);
	
			if(apply_filters($this->filter_prefix.'do_send_email', true))
			{

				$to = implode(', ', $this->getTo());
				$headers = '';

				if(count($cc = $this->getCc()))
				{
					 $headers .= 'Cc: '.implode(', ', $cc)."\n";
				}

				if(count($bcc = $this->getBcc()))
				{
					 $headers .= 'Bcc: '.implode(', ', $bcc)."\n";
				}
				$headers .='Content-Type: '.$this->content_type.'; charset="utf-8"'."\n";

				$retour = wp_mail($to, $this->getSubject(), $this->getMessage(), $headers, $this->attachments);
				
				if($this->send_files_as_attachments and !$this->store_files_as_attachments)
				{
					foreach($this->attachments as $a)
					{
						unlink($a);
					}
				}

			}
	
			do_action($this->filter_prefix.'after_sending_mail', $this);

			return $retour;

		}
		
		
		/*
		 *  Return the fields of the form
		 */
		public function getFields()
		{
			//default standard fields for a contact form
			$array = array(
				'nom' => array(
					'type'=> 'text',
					'val' => '',
					'label'=> __('Nom', 'mbccf'),
					'mandatory' => true,
				),
				'prenom' => array(
					'type'=> 'text',
					'val' => '',
					'label'=> __('Prenom', 'mbccf'),
					'mandatory' => true,
				),
				'email' => array(
					'type'=> 'email',
					'val' => '',
					'label'=> __('Email', 'mbccf'),
					'mandatory' => true,
				),
				'message' => array(
					'type'=> 'textarea',
					'val' => '',
					'label'=> __('Votre message', 'mbccf'),
					'mandatory' => true,
					),
			);
			
			return apply_filters($this->filter_prefix.'fields', $array);
		}
		
		
		/*
		 * Return the Html of the form
		 */
		protected function getForm()
		{
			$classform = apply_filters($this->filter_prefix.'classform', $this->shortcode.' clearfix');
			
			$formbegin = '<form method="post" id="mbc_contact_form_'.$this->shortcode.'" class="'.$classform.'" action="'.$_SERVER['REQUEST_URI'].'" '.($this->has_file?'enctype="multipart/form-data"':'').'>';

			$formtitle = '';
			if($this->title)
				$formtitle.=  $this->title;
			
			$form = '';
			foreach($this->fields as $key=>$field)
			{
				$form .= $this->html_field($key);
			}

			$formsubmit = '<input type="submit" class="button" value="'.$this->button_label.'" name="'.$this->shortcode.'[submit]">';

			$nonce= wp_nonce_field( $this->shortcode , '_wpnonce', true, false );
			
			$formend ='</form>';
			
			$beforeform = apply_filters($this->filter_prefix.'beforeform', '', $this);
			$formbegin = apply_filters($this->filter_prefix.'formbegin', $formbegin, $this);
			$formtitle = apply_filters($this->filter_prefix.'formtitle', $formtitle, $this);
			$form = apply_filters($this->filter_prefix.'form', $form, $this);
			$formend = apply_filters($this->filter_prefix.'formend', $formend, $this);
			$formsubmit = apply_filters($this->filter_prefix.'formsubmit', $formsubmit, $this);
			$afterform = apply_filters($this->filter_prefix.'afterform', '', $this);

			$totalform = $beforeform.$formbegin.$formtitle.$form.$formsubmit.$nonce.$formend.$afterform;

			return apply_filters($this->filter_prefix.'totalform', $totalform, $this);
		}
		
		
		
		/*
		 * Return the recipients of email. Default : Site administrator
		 */
		protected function getTo()
		{
			return apply_filters($this->filter_prefix.'to', array(get_option('admin_email')), $this);
		}

		/*
		 * Return the BCC recipients of email. Default: none.
		 */
		protected function getBcc()
		{
			return apply_filters($this->filter_prefix.'bcc', array(), $this);
		}

		/*
		 * Return the CC recipients of email. Default: none.
		 */
		protected function getCc()
		{
			return apply_filters($this->filter_prefix.'cc', array(), $this);
		}


		/*
		 * Return the email subject.
		 */
		protected function getSubject()
		{
			return apply_filters($this->filter_prefix.'subject', __('Formulaire de contact', 'mbccf'), $this);
		}
		
		
		protected function getMessage()
		{
			$str = '';

			if( $this->content_type == 'text/plain')
			{
				$begin_tag = apply_filters($this->filter_prefix.'begin_text_email_line', '');
				$end_tag = apply_filters($this->filter_prefix.'end_html_text_email_line', "\n");
			}
			else
			{
				$begin_tag = apply_filters($this->filter_prefix.'begin_html_email_line', '<p>');
				$end_tag = apply_filters($this->filter_prefix.'end_html_email_line', '</p>');
			}

			foreach($this->fields as $key=>$field)
			{
				$str .= $begin_tag.$field['label'].' : ';

				if($field['type'] == 'file')
				{
					if(isset($field['val']['url']))
						$str .= $field['val']['url'];
				}
				elseif($field['type'] == 'checkbox')
				{
					$str .= implode(', ', $field['val']);
				}
				elseif($field['type'] == 'textarea' and $this->content_type == 'text/html')
					$str .= nl2br($field['val']);
				else
					$str .= $field['val'];

				$str .= $end_tag;
			}

			return apply_filters($this->filter_prefix.'message', $str, $this);
		}
		
		public function add_body_class($classes)
		{
			$classes[] = 'has_mbc_contact_form';
			$classes = array_unique($classes);
   			return $classes;
		}
		
	}
	
}
