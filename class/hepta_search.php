<?php
/*class hepta_search*/
class hepta_search{
/*Début fonctions de manipulation de tableaux pour éditer le ficher de configuaration*//*pour convertir un array en json sans se soucier des quotes*/function hepta_search_array_php_2_js($tab)	{	return json_encode($tab,true).';';	}/* fonction permettant d'ajuster les quotes et caractères à échaper*/function hepta_search_quotes($text) 	{	return str_replace(array('\\', '\'', "\0"), array('\\\\', '\\\'', '\\0'), $text);	}/*Fin des fonctions de manipulation de tableaux*//*Début fonctions d'installation / désinstallation *//* Pour désinstaller le plugin */function hepta_search_uninstall()	{	global $wpdb;	$table_name = $wpdb -> prefix. 'log_rech';	$sql = "DROP TABLE IF EXISTS '$table_name'";	$wpdb->query( $sql );		}	/* Pour installer le plugin*/function hepta_search_install() 	{	global $wpdb;	$charset_collate = $wpdb->get_charset_collate();	//1 Création de la table de log de résultats 	$table_name = $wpdb->prefix . 'log_rech';	$sql = "CREATE TABLE $table_name (		id int(11) NOT NULL AUTO_INCREMENT,		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,		mts text NOT NULL,		ip text NOT NULL,		res_s int(11) NOT NULL,		tables_m text NOT NULL,		id_aut int(11) NOT NULL,		statut int(11) NOT NULL,		code_canal text NOT NULL,		mts_filtre text NOT NULL,		PRIMARY KEY  (id)	) $charset_collate;";	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );	dbDelta( $sql );	//2 Création de la table de blacklist	$table_name = $wpdb->prefix . 'moteur_r_bl';	$sql = "CREATE TABLE $table_name (		id int(11) NOT NULL AUTO_INCREMENT,		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,		exp text NOT NULL,		PRIMARY KEY  (id)	) $charset_collate;";	dbDelta( $sql );		$this-> hepta_search_option_add();	}	/*les options par défaut */	function hepta_search_options_defaut()	{	return array(		'expire' => 0,		'blist' => 1,		'sanctu' => 1,		);	}	/* pour supprimer les options */function hepta_search_option_remove()	{	$hepta_place_pr = 'hepta_search_';	$liste = $this-> hepta_search_options_defaut();	if(!empty($liste))		{		foreach($liste as $k=>$v)			{			delete_option(sanitize_text_field($hepta_place_pr.$k));			}		}	}/* Modifier les options */function hepta_search_option_edit()	{	$hepta_place_pr = 'hepta_search_';	$liste = $this-> hepta_search_options_defaut();	if(!empty($liste))		{		foreach($liste as $k=>$v)			{			if(isset($_POST[$k]))				{				$test = update_option(sanitize_text_field($hepta_place_pr.$k), absint($_POST[$k]));				}			}		}	}			/* Récupérer les options */function hepta_search_option_get()	{	$hepta_place_pr = 'hepta_search_';	$liste = $this-> hepta_search_options_defaut();	if(!empty($liste))		{		foreach($liste as $k=>$v)			{			$test = get_option(sanitize_text_field($hepta_place_pr.$k));			if($test !== false)				{				$liste[$k] = $test;				}			}		}	return $liste;	}		/*pour ajouter les options lors de l'installation */	function hepta_search_option_add()	{	$hepta_place_pr = 'hepta_search_';	$liste = $this-> hepta_search_options_defaut();	if(!empty($liste))		{		foreach($liste as $k=>$v)			{			add_option( sanitize_text_field($hepta_place_pr.$k), absint($v), '', 'yes' );				}		}	}/*Chargement de la blacklist par défaut*/function hepta_search_blacklist_defaut($path)	{	$url = $path.'blacklist_defaut_fr.txt';	$tab = array();	if(file_exists($url))		{		$handle = fopen($url,"r");		if ($handle)			{			while (!feof($handle))				{				$buffer = trim(fgets($handle));				if($buffer != "")					{					$tab[] = $buffer;					}				}			fclose($handle);			}		}	return $tab;	}	/*Fin des fonctions d'installation / désinstallation*//* fonction permetant d'avoir l'ip d'une personne qui se connecte */
function hepta_search_getIp()  
	{
    if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
    else
		{
        $ip = $_SERVER['REMOTE_ADDR'];
		}
    return $ip;
	}

function gen_tab_imp($tab)
	{
	$of = array();
	foreach($tab as $k=>$v)
		{
		$of[] = "'".$v."'";
		}
	return $of ;
	}	
function dec_json_url($str)
	{
	return json_decode(urldecode($str),true);
	}	
function enc_json_url($str)
	{
	return urlencode(json_encode($str));
	}	

function hepta_search_capte_info($wp_query)
	{
	$res = array();
	if ( $wp_query->have_posts() ) 
		{
		while ( $wp_query->have_posts() ) {
		$wp_query->the_post();
			$res[] =$wp_query->post->ID;
			}
		}
	return $res;
	}
function hepta_search_decoupe_pour_index($string)
	{
	return preg_split('/(?:\s+)|(http:\/\/[^\s]+|www\.[^\s]+|\-+|\w+\'|\W+)/u',$string,-1,PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
	}
	
/*filtre balck liste */
function hepta_search_rech_filtre_black_liste($tab)
	{
	global $wpdb;
	$table_name = $wpdb->prefix . 'moteur_r_bl';
	$of = array();
	if(!empty($tab))
		{
		$of = array_values(array_diff($tab,$wpdb->get_col("SELECT exp FROM $table_name WHERE exp IN (".implode(',',$this->gen_tab_imp($tab)).")")
		));
		}
	return $of; 
	}		
function hepta_search_filtres($str,$ops)
	{
	$ret = '';
	$encodage = get_bloginfo('charset');
	$exp = mb_strtolower($str,$encodage);
	$mts = $this-> hepta_search_decoupe_pour_index($exp);
	if($ops['sanctu'] == 0 || ($ops['sanctu'] == 1 && sizeof($mts) > 1))
		{
		$mts = $this->hepta_search_rech_filtre_black_liste($mts);
		}
	if(!empty($mts))
		{
		$ret = implode(' ',$mts);	
		}
	return $ret;
	}
function log_rech($tab)
	{
	$of = array(
	'time' => current_time('mysql' ),
	'mts'=> sanitize_text_field($tab[0]) ,
	'ip'=> $this->hepta_search_getIp() ,
	'res_s'=> $tab[1],
	'tables_m'=> $this->enc_json_url($tab[2]),
	'id_aut'=> get_current_user_id(),
	'statut' => 0,
	'code_canal' => 'moteur',
	'mts_filtre'=> sanitize_text_field($tab[3]),
	);
	global $wpdb;
	$table = $wpdb->prefix . 'log_rech';		
	$wpdb->insert($table, $of);
	}
}