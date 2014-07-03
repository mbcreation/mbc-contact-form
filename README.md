<h1>Contact From – v.0.1</h1>

<h2>Utilisation :</h2>


<p>- Dans le fichier forms.php, instancier les formulaires avec le shortcode en paramètre,
dans la function <code>create_forms()</code> : </p>

<p>Ex :	<code>$contact_form = new MbcContactForm('contact-form');</code></p>

<p>- Toute le paramétrage se fait via des filters. Tous ont le shorcode dans le nom pour ne pas affecter tous les formualaires. Normalement tout est filtré, cf. le code.
Déclarer les filtres et les actions dans la fonctions <code>hooks()</code>.</p>

<p>Ex :	<code>add_filter('mbccf_contact-form_fields', array($this, 'contact_form_fields' ));</code> </p>

<p>- Ecrire les fonctions appelées par les filters comme fonction publique de la classe.</p>


<h2>Exemples de filtres<h2>

<h3>Changer les destinataires :</h3>
	<pre>
add_filter('mbccf_contact-form_to', array($this, 'contact_form_to'));
public function contact_form_to($array)
{
	//changer / ajouter des destinataires
	return $array;
}
</pre>

<h3>Changer les champs :</h3>
<pre>
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
</pre>

<h4>Les différentes clés pour chaque champ :</h4>
<ul><li>type : text, password, password_confirm, email, textarea, radio, checkbox (choix multiple), checkbox_unique (acceptation CGV...), file, select</li>
<li>mandatory : true ou false</li>
<li>val : valeur par défault. string sauf pour checkbox : array</li>
<li>label</li>
<li>data : pour radio, checkbox et select : tableau associatif des choix</li>
<li>validation : pour file. Tableau associatif des tests à effectuer. Pour l'instant, uniquement clé 'mime_type' avec un tableau des types mime acceptés.</li></ul>

<h3>Changer le comportement de l'uplaod de fichier</h3>
	
<pre>
//ne pas envoyer les fichier en pj, mais juste le lien.
add_filter('mbccf_contact-form_send_files_as_attachments', '__return_false');

//stocker les fichiers comme des attachments WP
add_filter('mbccf_contact-form_store_files_as_attachments', '__return_true');
</pre>

<h3>Utiliser selectbox</h3>

	<pre>add_filter('mbccf_contact-form_use_selectbox', '__return_true');</pre>
	
	
<h3>Activer les placeholders</h3>

	<pre>add_filter('mbccf_contact-form_use_placeholders', '__return_true');</pre>
	
	
<h3>Désactiver les labels</h3>

	<pre>add_filter('mbccf_contact-form_use_labels', '__return_false');</pre>
	
	
<h3>Ajouter un action après l'envoi du mail (sauvegarde base, autre email...)</h3>

<pre>
add_action('mbccf_contact-form_after_sending_mail', array($this, 'contact_form_additional_action'));

public function contact_form_additional_action($object)
{
	print_r($object->fields);
	die;
}
</pre>
<h3>Modifier le texte de l'email</h3>
<pre>
add_action('mbccf_contact-form_message', array($this, 'contact_form_message'), 10, 2);

public function contact_form_message($str, $object)
{
	
	return $str;
}
</pre>

<h2>Changelog</h2>

<h3>Version : 0.1</h3>
<p>- Initial release</p>


