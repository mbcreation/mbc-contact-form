Contact From – v.0.1
--------------------

Utilisation :
-------------

- Dans le fichier forms.php, instancier les formulaires avec le shortcode en paramètre,
dans la function create_forms() : 

Ex :	$contact_form = new MbcContactForm('contact-form');

- Toute le paramétrage se fait via des filters. Tous ont le shorcode dans le nom pour ne pas affecter tous les formualaires. Normalement tout est filtré, cf. le code.
Déclarer les filtres et les actions dans la fonctions hooks().

Ex :	add_filter('mbccf_contact-form_fields', array($this, 'contact_form_fields' )); 

- Ecrire les fonctions appelées par les filters comme fonction publique de la classe.


Exemples de filtres :
---------------------

- Changer les destinataires :

	add_filter('mbccf_contact-form_to', array($this, 'contact_form_to'));
	public function contact_form_to($array)
	{
		//changer / ajouter des destinataires
		return $array;
	}
	

- Changer les champs :

	add_filter('mbccf_contact-form_fields', array($this, 'contact_form_fields') );
	public function contact_form_fields($array)
	{
	
		$array = array(
			'nom' => array(
				'type'=> 'text',
				'val' => '',
				'label'=> __('Nom', 'mbccf'),
				'mandatory' => true,
			),
			'civilite' => array(
				'type'=> 'select',
				'val' => 'M',
				'label'=> 'Civilité',
				'data' => array('M'=> 'M', 'Mme'=>'Mme'),
				'mandatory' => true,
			),
			'fichier' => array(
				'type'=> 'file',
				'val' => '',
				'label'=> 'Fichier',
				'mandatory' => true,
				'validation' => array('mime_type' => array('application/pdf'))
			),
		);
			
		return $array;
	
	}

Les différentes clés pour chaque champ :
- type : text, password, password_confirm, email, textarea, radio, checkbox (choix multiple), checkbox_unique (acceptation CGV...), file, select
- mandatory : true ou false
- val : valeur par défault. string sauf pour checkbox : array
- label
- data : pour radio, checkbox et select : tableau associatif des choix
- validation : pour file. Tableau associatif des tests à effectuer. Pour l'instant, uniquement clé 'mime_type' avec un tableau des types mime acceptés.


- Changer le comportement de l'uplaod de fichier
	
	//ne pas envoyer les fichier en pj, mais juste le lien.
	add_filter('mbccf_contact-form_send_files_as_attachments', '__return_false');
	
	//stocker les fichiers comme des attachments WP
	add_filter('mbccf_contact-form_store_files_as_attachments', '__return_true');


- Utiliser selectbox

	add_filter('mbccf_contact-form_use_selectbox', '__return_true');
	
	
- Activer les placeholders

	add_filter('mbccf_contact-form_use_placeholders', '__return_true');
	
	
- Désactiver les labels

	add_filter('mbccf_contact-form_use_labels', '__return_false');
	
	
- Ajouter un action après l'envoi du mail (sauvegarde base, autre email...)

	add_action('mbccf_contact-form_after_sending_mail', array($this, 'contact_form_additional_action'));
	
	public function contact_form_additional_action($object)
	{
		print_r($object->fields);
		die;
	}

- Modifier le texte de l'email

	add_action('mbccf_contact-form_message', array($this, 'contact_form_message'), 10, 2);
	
	public function contact_form_message($str, $object)
	{
		
		return $str;
	}


-----------------
Changelog
-----------------
Version : 0.1
- Initial release


