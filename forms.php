<?php

/*
Plugin Name:  Contact Form
Author: MB Création
Description:  Formulaires de contact
Version:      0.1
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
			$contact_form = new MbcContactForm('form-shortcode');
		}
		
		public function hooks()
		{
			// ici déclarer tous les filters & actions
			//ex : add_filter('mbccf_contact-form_fields', array($this, 'contact_form_fields' )); 
			add_filter('mbccf_contact-form_fields', array($this, 'contact_form_fields' ));
			//add_filter('mbccf_contact-form_use_selectbox', '__return_true'); 

		}
		
		
		/* ci-dessous : écrires les fonctions appelées via les filters */
		
		
		public function contact_form_fields($array)
		{ 
	
			$array = array(
				'text' => array(
					'type'=> 'text',
					'val' => '',
					'label'=> __('Nom', 'mbccf'),
					'mandatory' => true,
				),
				
				'password' => array(
					'type'=> 'password',
					'val' => '',
					'label'=> __('Nom', 'mbccf'),
					'mandatory' => true,
				),
				
				'password_confirm' => array(
					'type'=> 'password_confirm',
					'val' => '',
					'label'=> __('Nom', 'mbccf'),
					'mandatory' => true,
				),
				
				'email' => array(
					'type'=> 'email',
					'val' => '',
					'label'=> __('Nom', 'mbccf'),
					'mandatory' => true,
				),
				
				'textarea' => array(
					'type'=> 'textarea',
					'val' => '',
					'label'=> __('Nom', 'mbccf'),
					'mandatory' => true,
				),
				
				'radio' => array(
					'type'=> 'radio',
					'val' => '',
					'label'=> __('Nom', 'mbccf'),
					'data' => array(1, 2),
					'mandatory' => true,
				),
				
				'checkbox' => array(
					'type'=> 'checkbox',
					'val' => '',
					'label'=> __('Nom', 'mbccf'),
					'data' => array(1, 2),
					'mandatory' => true,
				),
				
				'checkbox_unique' => array(
					'type'=> 'checkbox_unique',
					'val' => '',
					'label'=> __('Nom', 'mbccf'),
					'mandatory' => true,
				),
				
				'select' => array(
					'type'=> 'select',
					'val' => '',
					'label'=> __('Nom', 'mbccf'),
					'data' => array(1, 2),
					'mandatory' => true,
				),
				
				'file' => array(
					'type'=> 'file',
					'val' => '',
					'label'=> __('Nom', 'mbccf'),
					'mandatory' => true,
				),
			);
			
			return $array;
	
		}
	}
	
	$mbcContactForms = new MbcContactForms();
	
}
