<?php

/*
Plugin Name:  Contact Forms by MB Création
Author: MB Création
Description:  Formulaires de contact
Version:      1.0
*/

if( !class_exists( 'MbcContactForms' ) )
{
	include dirname(__FILE__).'/includes/mbc-contact-form.php';
	
	class MbcContactForms
	{

		protected $plugin_path;
		
		function __construct()
		{
			$this->plugin_path = dirname(__FILE__);
			
			$this->create_forms();

			add_action('plugins_loaded', array($this, 'hooks' ), 6 );
		}

		public function create_forms()
		{
			//ici déclarer tous les formulaires
			// ex : $contact_form = new MbcContactForm('contact-form');
			$contact_form = new MbcContactForm('contact-form');
		}
		
		public function hooks()
		{
			// ici déclarer tous les filters & actions. cf readme.md
			//ex : add_filter('mbccf_contact-form_fields', array($this, 'contact_form_fields' )); 
		}

		
		/* ci-dessous : écrires les fonctions appelées via les filters */
		
		
		/* exemple
		public function contact_form_fields($array)
		{ 
	
			$array = array(
				'text' => array(
					'type'=> 'text',
					'val' => 'text',
					'label'=> 'text',
					'mandatory' => false,
				),
				
				'password' => array(
					'type'=> 'password',
					'val' => 'password',
					'label'=> 'password',
					'mandatory' => false,
				),
				
				'password_confirm' => array(
					'type'=> 'password_confirm',
					'val' => 'password',
					'label'=> 'password_confirm',
					'mandatory' => false,
				),
				
				'email' => array(
					'type'=> 'email',
					'val' => 'email',
					'label'=> 'email',
					'mandatory' => false,
				),
				
				'textarea' => array(
					'type'=> 'textarea',
					'val' => 'textarea',
					'label'=> 'textarea',
					'mandatory' => false,
				),
				
				'radio' => array(
					'type'=> 'radio',
					'val' => 'radio1',
					'label'=> 'radio',
					'data' => array('radio1'=>'Radio 1' ,'radio2'=>'Radio 2'),
					'mandatory' => false,
				),
				
				'checkbox' => array(
					'type'=> 'checkbox',
					'val' => 'checkbox2',
					'label'=> 'checkbox',
					'data' => array('checkbox1'=>'Checkbox 1' ,'checkbox2'=>'Checkbox 2'),
					'mandatory' => false,
				),
				
				'checkbox_unique' => array(
					'type'=> 'checkbox_unique',
					'val' => '1',
					'label'=> 'checkbox_unique',
					'mandatory' => false,
				),
				
				'select' => array(
					'type'=> 'select',
					'val' => 'select2',
					'label'=> 'select',
					'data' => array('select1'=>'Select 1' ,'select2'=>'Select 2'),
					'mandatory' => false,
				),
				
				'file' => array(
					'type'=> 'file',
					'val' => '',
					'label'=> 'file',
					'mandatory' => false,
				),
			);
			
			return $array;
	
		}*/
	}
	
	$mbcContactForms = new MbcContactForms();
	
}
