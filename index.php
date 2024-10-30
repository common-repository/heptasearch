<?php
/*
Plugin Name: Hepta Search
Plugin URI: http://heptaplace.com/moteur-recherche-heptasearch_30.html
Description: Ce plugin permet de visualiser les recherches effectuées sur votre site web
Version: 0.5
Author: Heptadeca
Author URI: http://www.heptadeca.com
License: GPL2
*/

// Affichage de la page par défaut
function hepta_search_menu_callback()
	{ 
	if(isset($_GET['vu_log']))
		{
		hepta_search_visu_log_rech();
		}
	elseif(isset($_GET['vu_blacklist']))
		{
		hepta_search_blacklist_callback();	
		}
	else 
		{
		echo '<div class="wrap"><h1>Historique des recherches :</h1><p>Bienvenue sur HeptaSearch. Sur cette page, vous pouvez voir tout ce que les internautes ont recherché à travers votre moteur de recherche. Vous pouvez cliquer sur le nombre de résultats pour <strong>faire apparaître les résultats qui ont été apportés</strong>. Pour plus d\'informations ou demandes d\'évolution merci de nous contacter à hello@heptadeca.com</p></div>'; 
		echo hepta_search_res();
		}
	} 

// fonction permetant de générer un <select> par array_envoyé 
function hepta_search_aff_list_select_comb_cle($param,$tab,$sel=false) 
	{
	$pro_selext = '';
	if(!empty($param)) // si il y a des paramètres pour le select (du genre id,style,onclick ...)
		{
		foreach($param as $k=> $v) // on les insere dans la balise select à créer
			{
			$pro_selext.=$k.'="'.$v.'" ';
			}
		}
	$str= '<select '.$pro_selext.'>';
	foreach($tab as $k => $v) // pour tout l'array tab envoyé (les <option> à créer 
		{
		if($sel !== false && $k == $sel) // si il y a une valeur prédéfinie -> on la calle en selected (utile pour les formulaires de selections de produit || scénario --> comme ça l'utilisateur garde le select sur ça précédent sélection)
			{
			$pref = 'selected="selected"';
			}
		else
			{
			$pref ='';
			}
		$str.='<option value="'.$k.'" '.$pref.'>'.$v.'</option>';		
		}
	$str.='</select>';
	return $str;
	}	



/* Le formulaire de réglage*/
function hepta_search_form_reglage($dej = array())
	{
	$non_oui = array('Non','Oui');
	$str= '<form  method="post" action="admin.php?page=hepta_search_reglages">';	
	$str.='<table class="form-table">';
	// durée des logs 
	$str.='<tr><th scope="row"><label for="expire">Supprimer les logs</label></th>
	<td>'.hepta_search_aff_list_select_comb_cle(
	array('name'=>'expire'),
	array(
		0=> 'jamais',
		1=> 'Au bout d\'une heure',
		2=> 'Au bout d\'un jour',
		3=> 'Au bout d\'une semaine',
		4=> 'Au bout d\'un mois',
		5=> 'Au bout d\'un an',
		),
	hepta_search_si_renv_val($dej,'expire',0)
	).'</td>
	</tr>';
	// utilisér blackliste
	$str.='<tr><th scope="row"><label for="expire">Utiliser la blacklist</label></th>
	<td>'.hepta_search_aff_list_select_comb_cle(
	array('name'=>'blist'),
	$non_oui,
	hepta_search_si_renv_val($dej,'blist',0)
	).'</td>
	</tr>';
	// Sanctuarisé la première expression 
	$str.='<tr><th scope="row"><label for="expire">Ignorer la blacklist si seulement une expression</label></th>
	<td>'.hepta_search_aff_list_select_comb_cle(
	array('name'=>'sanctu'),
	$non_oui,
	hepta_search_si_renv_val($dej,'sanctu',1)
	).'</td>
	</tr>';
	$str.='</table>';
	$str.='<p class="submit"><input type="submit" name="reglage_modif" class="button button-primary" value="Enregistrer les modifications"/></p>';
	$str.='</form>';
	return $str;
	}


// Affichage des sous liens 1 et 2 du menu précédent
function hepta_search_reglages(){ 
    echo '<div class="wrap"><h1>Réglages Hepta-Search :</h1><p>Sur cette page vous pouvez faire tous les réglages concernant votre moteur de recherche. Notamment si vous désirez <strong>activer ou pas votre blacklist</strong>, et gérer la <strong>suppression automatique des logs de recherche</strong>. Nous allons rajouter de nouvelles fonctionnalités très prochainement. Pour plus d\'informations ou demandes d\'évolution merci de nous contacter à hello@heptadeca.com</p></div>'; 
	require_once('class/hepta_search.php');
	$rech = new hepta_search();
	$base = $rech -> hepta_search_option_get();
	if(isset($_POST['reglage_modif']))
		{
		$rech -> hepta_search_option_edit();
		echo '<p>Configuration actualisée</p>';
		echo hepta_search_form_reglage($_POST);	
		}
	else 
		{
		echo hepta_search_form_reglage($base);
		}
} 


function hepta_search_form_ajout_blacklist()
	{
	$str= '<form  method="post" action="admin.php?page=blacklist&ajout_fait=1">';	
	$str.='<p>Vous pouvez ajouter plusieurs expressions simultanément en les séparant par des virgules</p>';
	$str.='<textarea name="exp" class="large-text code"></textarea>';
	$str.='<p class="submit"><input type="submit" name="screen-options-apply" class="button button-primary" value="Mémoriser les expressions" /></p>';
	$str.='</form>';
	return $str;
	}

function hepta_search_blacklist_callback()
	{ 
	if(isset($_GET['ajout_fait']) && isset($_POST['exp']) )
		{
		$liste = preg_split('/,/',sanitize_text_field($_POST['exp']),-1,PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE); 
		$cb = hepta_search_aj_data_blist($liste);
		echo '<div class="wrap"><h1>Gestionnaire de Blacklist 
		<a class="page-title-action" href="admin.php?page=blacklist&ajout=1">Ajouter expressions</a>
		</h1></div>'; 
		echo '<p>'.$cb.' expression(s) ajoutée(s)</p>';
		echo hepta_search_visu_blacklist();
		}
	elseif(isset($_GET['ajout']))
		{
		echo '<div class="wrap"><h1>Ajouter des expressions dans la blacklist : </h1></div>'; 	
		echo hepta_search_form_ajout_blacklist();
		}
	else 
		{
		echo '<div class="wrap"><h1>Gestionnaire de Blacklist 
			<a class="page-title-action" href="admin.php?page=blacklist&ajout=1">Ajouter expressions</a>
			</h1><p>Sur cette page vous pouvez gérer votre blacklist. La blacklist est la <strong>liste des mots qui seront retirés de la requête</strong> de l\'utilisateur. Elle permet notamment d\'éliminer les mots vides de sens ou les insultes. Pour plus d\'informations ou demandes d\'évolution merci de nous contacter à hello@heptadeca.com</p></div>'; 
		echo hepta_search_visu_blacklist();
		}
	} 

function hepta_search_visu_log_rech()
	{
	global $wpdb;
	$table_name = $wpdb->prefix . 'log_rech';
	$log = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE id=%d",array(hepta_search_assure_nombre($_GET,'vu_log'))));	
	if(!empty($log))
		{
		echo '<div class="wrap"><h1>Résultats : '.$log[0]->mts.' ('.$log[0]->res_s.')</h1></div>'; 
		require_once('class/hepta_search.php');
		$rech = new hepta_search();
		$post_m = $rech -> dec_json_url($log[0]->tables_m);
		if(!empty($post_m))
			{
			$liste = implode(',',$post_m);
			$table_post = $wpdb->prefix . 'posts';
			$sort = array('FIELD(ID,'.$liste.')');
			if(isset($_GET['orderby']) && isset($_GET['order']) )
				{
				$sort = array(
				sanitize_text_field(hepta_search_si_renv_val($_GET,'orderby','time')),
				hepta_search_si_dans_tab(array('asc','desc'),mb_strtolower(hepta_search_si_renv_val($_GET,'order','desc'),'UTF-8'),'desc')
					);	
				}
			$req = $wpdb->get_results("SELECT * FROM $table_post WHERE ID IN(".$liste.") ORDER BY ".implode(' ',$sort));	
			echo hepta_search_table_de_visu_res($req,$post_m);
			}
		}
	else 
		{
		echo '<div class="wrap"><h1>Erreur, la requète que vous cherchez n\'existe pas ou plus </h1></div>'; 		
		}
	}

function hepta_search_wp_req_blist()
	{
	$pal = 10; // nombre de logs_par pages
	$pagin = hepta_search_assure_nombre($_GET,'pagenum') ;
	$sort = array(
	sanitize_text_field(hepta_search_si_renv_val($_GET,'orderby','time')),
	hepta_search_si_dans_tab(array('asc','desc'),mb_strtolower(hepta_search_si_renv_val($_GET,'order','desc'),'UTF-8'),'desc')
	);
	$ret = array(	
		'datas'=>array(),
		'pages'=>0,
		'cb_res'=>0
		);
	global $wpdb;
	$table_name = $wpdb->prefix . 'moteur_r_bl';
	$ret['cb_res'] = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name");
	$ret['pages'] = ceil($ret['cb_res']/$pal);
	$ret['datas'] = $wpdb->get_results("SELECT * FROM $table_name ORDER BY ".implode(' ',$sort)." ".hepta_search_renv_limit($pal,$pagin));
	return $ret;
	}	

function hepta_search_visu_blacklist()
	{
	hepta_search_action_log('moteur_r_bl');
	$acces = 'blacklist';
	$resultats = hepta_search_wp_req_blist();
	$str.= '<form id="filter" method="get">';
	$str.= '<input type="hidden" name="page" value="'.$acces.'">';
	$str.='<div class="tablenav">';
	$str.= hepta_search_gen_action_groupee(array(
		array('title'=>'Supprimer','value'=>'trash'),
		)
	);
	$str.= hepta_search_gen_pagin($resultats,$acces);
	$str.='</div>';
	$str .= '<table class="wp-list-table widefat fixed striped pages">';
	$str .= '<thead>
		<td id="cb" class="manage-column column-cb check-column">
		<label class="screen-reader-text" for="cb-select-all-1">Tout sélectionner</label><input id="cb-select-all-1" type="checkbox" />
		</td>
		'.hepta_search_gen_colone_arg(array('id'=>'exp','title'=>'Expression','sort'=> 'desc'),$acces).'
		'.hepta_search_gen_colone_arg(array('id'=>'time','title'=>'Date','sort'=>'asc'),$acces).'
	</thead>';
	$str .='<tbody id="the-list">';
	foreach($resultats['datas'] as $k=>$v)
		{
		$str .= '<tr id="post-'.$v -> id.'" class="iedit author-self level-0 post-2 type-page status-publish hentry">
		<th scope="row" class="check-column">			<label class="screen-reader-text" for="cb-select-'.$v -> id.'">Sélectionner Page d&rsquo;exemple</label>
			<input id="cb-select-'.$v -> id.'" type="checkbox" name="post[]" value="'.$v -> id.'" />
			<div class="locked-indicator"></div>
		</th>
		<td>'.$v -> exp.'</td>
		<td>'.$v -> time .'</td>
		</tr>';
		}
	$str .='<tbody>';
	$str.='</table>';
	$str.='<div class="tablenav">';
	$str.= hepta_search_gen_action_groupee(array(
		array('title'=>'Supprimer','value'=>'trash')
		),2

	);
	$str.= hepta_search_gen_pagin($resultats,$acces);
	$str.='</div>';
	$str.='</form>';
	return $str;
	}	

function hepta_search_table_de_visu_res($tab,$pos)
	{
	$str = '<table class="wp-list-table widefat fixed striped pages">';
	$str .= '<thead>';
	$str .= '<td id="cb" class="manage-column column-cb check-column">Pos</td>';
	$str .= hepta_search_gen_colone_arg(array('id'=>'post_title','title'=>'Titre','sort'=> 'desc'),'hepta_search_menu&vu_log='.hepta_search_assure_nombre($_GET,'vu_log'));
	$str .= '</thead>';
	$str .='<tbody id="the-list">';
	$cb = 0;
	foreach($tab as $k=>$v)
		{
		$cle = array_keys($pos,$v -> ID);
		$cb = $cle[0]+1;
		$str .= '<tr id="post-'.$v -> ID.'" class="iedit author-self level-0 post-'.$v -> ID.' type-page status-publish hentry">
		<th scope="row" class="check-column">			
		'.$cb.'
		</th>
		<td><a href="'.get_permalink($v -> ID).'">'.$v -> post_title.'</a></td>';
		$str .='</tr>';
		}
	$str .='<tbody>';	
	$str .= '</table>';
	return $str;
	}

/* renvoyer le palier de pagination */
function hepta_search_req_pagin_pal($op)
	{
	$user = get_current_user_id();
	$screen = get_current_screen();
	$screen_option = $screen->get_option($op, 'option');
	$per_page = get_user_meta($user, $screen_option, true);
	if ( empty ( $per_page) || $per_page < 1 ) 
		{
		$per_page = $screen->get_option( 'per_page', 'default' );
		}	
	return $per_page;
	}

function hepta_search_req_log_rech()
	{
	$pal = hepta_search_req_pagin_pal('per_page'); // nombre de logs_par pages
	$pagin = hepta_search_assure_nombre($_GET,'pagenum');
	$sort = array(
	sanitize_text_field(hepta_search_si_renv_val($_GET,'orderby','time')),
	hepta_search_si_dans_tab(array('asc','desc'),mb_strtolower(hepta_search_si_renv_val($_GET,'order','desc'),'UTF-8'),'desc')
	);
	$conds = '';
	$pos_cond = array();
	$filtre = hepta_search_assure_nombre($_GET,'filtres',-1);
	if($filtre > -1)
		{
		if($filtre == 0)
			{
			$pos_cond[] = ' res_s = 0 ';
			}
		if($filtre == 1)
			{
			$pos_cond[] = ' res_s > 0 ';
			}
		}
	if(!empty($pos_cond))	
		{
		$conds = ' WHERE '.implode(' AND ',$pos_cond).' ';
		}
	$ret = array(	
		'datas'=>array(),
		'pages'=>0,
		'cb_res'=>0
		);

	add_screen_option( 
      'per_page',
      array('label' => _x( 'Comments', 'comments per page (screen options)' )) );
	global $wpdb;
	$table_name = $wpdb->prefix . 'log_rech';
	$ret['cb_res'] = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name $conds");
	$ret['pages'] = ceil($ret['cb_res']/$pal);
	$ret['datas'] = $wpdb->get_results("SELECT * FROM $table_name $conds ORDER BY ".implode(' ',$sort)." ".hepta_search_renv_limit($pal,$pagin));
	return $ret;
	}

/*créer le Limit x , y*/

function hepta_search_renv_limit($pal,$pos_p = 0)
	{
	$ret = '';
	$ind = $pos_p * $pal;
	if($pos_p> 0)
		{
		$ind = ($pos_p -1) * $pal;
		}
	$ret = 'LIMIT '.$ind.', '.$pal;
	return $ret;	
	}	

/*creer une colone */	
function hepta_search_gen_colone_arg($nom = array('id'=>'test','title'=>'Titre','sort'=>'desc'),$page='hepta_search_menu')
	{
	$nom = hepta_search_adatpe_asc_desc($nom);
	$str = '<th scope="col" id="'.$nom['id'].'" class="manage-column column-'.$nom['id'].' sortable '.$nom['sort'].'"><a href="admin.php?page='.$page.'&orderby='.$nom['id'].'&order='.$nom['sort'].'"><span>'.$nom['title'].'</span><span class="sorting-indicator"></span></a></th>';
	return $str;
	}

/*renvoyer une valeur si celle si n'existe pas*/
function hepta_search_si_renv_val($tab,$cle,$val='')
	{
	if(isset($tab) && isset($tab[$cle]))
		{
		$val = 	$tab[$cle]; 
		}
	return $val;
	}

/* si valeur dans un tableau ok sinon valeur par défaut*/
function hepta_search_si_dans_tab($tab,$val,$df='')
	{
	if(!in_array($val,$tab))
		{
		$val = 	$df; 
		}
	return $val;
	}	
	
/*test si nombre */
function hepta_search_est_nombre($str)
	{
	$ret = false;
	if(preg_match('/^[0-9]+$/',$str))
		{
		$ret = true;
		}
	return $ret;
	}

/*assur en nombre */
function hepta_search_assure_nombre($tab,$cle,$val=0)
	{
	// 1 on s'assure que la donnée envoyée est bien envoyée 
	$ret = hepta_search_si_renv_val($tab,$cle,$val);
	
	// 2 on s'assure qu'il s'agit bien d'un nombre
	$test= hepta_search_est_nombre($ret);
	if($test != true) // si pas un nombre , on le remplace par une valeur par défaut 
		{
		$ret = $val;
		}
	return $ret;
	}	
	
/*pour adapeter les haut bas */
function hepta_search_adatpe_asc_desc($tab)
	{
	if(isset($_GET['orderby']) && $_GET['orderby'] == $tab['id'])
		{
		if(isset($_GET['order']))
			{
			switch ($_GET['order'])
				{
				case 'asc' :
				$tab['sort'] = 'desc';
				break;
				case 'desc' :
				$tab['sort'] = 'asc';
				break;
				}
			}	
		}
	return $tab;
	}

/*Générateur de pagination */
function hepta_search_gen_pagin($resultats,$base = 'hepta_search_menu')
	{
	$str = '';
	$str.='<div class="tablenav-pages">';
	$str.='<span class="displaying-num">'.$resultats['cb_res'].' éléments</span>';
	$str.= paginate_links( array(
    'base' => 'admin.php?page='.$base.'%_%',
    'format' => '&pagenum=%#%',
    'prev_text' => __( '&laquo;', 'text-domain' ),
    'next_text' => __( '&raquo;', 'text-domain' ),
    'total' => $resultats['pages'],
    'current' => hepta_search_assure_nombre($_GET,'pagenum',1)
) );
	$str.='</div>';
	return $str;
	}

/*générateur action groupée*/	
function hepta_search_gen_action_groupee($actions,$pr='')
	{
	$str = '
	<div class="alignleft actions bulkactions">
			<label for="bulk-action-selector-top" class="screen-reader-text">Sélectionnez l&rsquo;action groupée</label><select name="action'.$pr.'" id="bulk-action-selector-top">
	<option value="-1">Actions groupées</option>';
	foreach($actions as $v)
		{
		$str .='<option value="'.$v['value'].'" class="hide-if-no-js">'.$v['title'].'</option>';
		}
	$str .= '</select>
	<input type="submit" id="doaction" class="button action'.$pr.'" value="Appliquer" onclick="return confirm(\'Êtes vous sur de vouloir effectuer cette action?\')" />
	</div>';
	return $str;
	}

/*Générateur de filtres*/
function hepta_search_gen_fitres($params)
	{
	$str = '<div class="alignleft actions">';
	$str.= '<label class="screen-reader-text" for="'.$params[0]['name'].'"></label>';
	$str.= hepta_search_aff_list_select_comb_cle($params[0],$params[1],$params[2]);
	$str.= '<input type="submit" id="post-query-submit" class="button" value="'.$params[3].'"  />		
	</div>';
	return $str;
	}

function hepta_search_req_action($pos)
	{
	$action = '';
	foreach($pos as $v)
		{
		if(isset($_GET[$v]) &&	$_GET[$v] != -1)
			{
			$action = $_GET[$v];
			break;
			}
		}
	return $action;
	}

/*pour extraire une liste de nombre */
function hepta_search_secure_liste_nbr($ids)
	{
	$of = array();
	foreach($ids as $k=>$v)
		{
		if(hepta_search_est_nombre($v) === true)
			{
			$of[] = $v;
			}
		}
	return $of;
	}
	
/* Pour supprimer via id */
function hepta_search_action_log($table)
	{
	if(isset($_GET['post']) && !empty($_GET['post']))
		{
		$action = hepta_search_req_action(array('action','action2'));
		$possible = hepta_search_secure_liste_nbr($_GET['post']);
		if(!empty($possible))
			{
			$ids = implode(',',$possible);
			global $wpdb;
			$table_name = $wpdb->prefix .$table;
			switch($action)
				{
				case 'trash' :
				$wpdb->query(
				'DELETE  FROM '.$table_name.'
				WHERE id IN ('.$ids.')'
				);
				break;
				}
			}
		}	
	}

function hepta_search_res()
	{
	hepta_search_action_log('log_rech');
	$filtre = hepta_search_assure_nombre($_GET,'filtres',-1);
	$base_p = 'hepta_search_menu&filtres='.$filtre;
	$resultats = hepta_search_req_log_rech();
	$str.= '<form id="filter" method="get">';
	$str.= '<input type="hidden" name="page" value="hepta_search_menu">';
	$str.='<div class="tablenav">';
	$str.= hepta_search_gen_action_groupee(array(
		array('title'=>'Supprimer','value'=>'trash'),
		)

	);
	$str.= hepta_search_gen_fitres(array(array('name' =>'filtres'),array(-1=> 'Filtrer',0=>'Sans résultats',1=> 'Avec résultats'),$filtre,'Filtrer')); 
	$str.= hepta_search_gen_pagin($resultats);
	$str.='</div>';
	$str .= '<table class="wp-list-table widefat fixed striped pages">';
	$str .= '<thead>
		<td id="cb" class="manage-column column-cb check-column">
		<label class="screen-reader-text" for="cb-select-all-1">Tout sélectionner</label><input id="cb-select-all-1" type="checkbox" />
		</td>
		'.hepta_search_gen_colone_arg(array('id'=>'mts','title'=>'Requêtes','sort'=> 'desc'),$base_p).'
		'.hepta_search_gen_colone_arg(array('id'=>'res_s','title'=>'Résultats','sort'=>'desc'),$base_p).'
		'.hepta_search_gen_colone_arg(array('id'=>'id_aut','title'=>'Auteur','sort'=>'desc'),$base_p).'
		'.hepta_search_gen_colone_arg(array('id'=>'ip','title'=>'Ip','sort'=>'desc'),$base_p).'
		'.hepta_search_gen_colone_arg(array('id'=>'time','title'=>'Date','sort'=>'asc'),$base_p).'	
	</thead>';
	$str .='<tbody id="the-list">';
	foreach($resultats['datas'] as $k=>$v)
		{
		$str .= '<tr id="post-'.$v -> id.'" class="iedit author-self level-0 post-2 type-page status-publish hentry">
		<th scope="row" class="check-column">			<label class="screen-reader-text" for="cb-select-'.$v -> id.'">Sélectionner Page d&rsquo;exemple</label>
			<input id="cb-select-'.$v -> id.'" type="checkbox" name="post[]" value="'.$v -> id.'" />
			<div class="locked-indicator"></div>
		</th>
		<td>'.$v -> mts.' ('.$v -> mts_filtre.') </td>';
		if($v -> res_s>0)
			{
			$str .='<td><a href="admin.php?page=hepta_search_menu&vu_log='.$v -> id.'">'.$v -> res_s.'</a></td>';
			}
		else 
			{
			$str .='<td>'.$v -> res_s.'</td>';
			}
		$str .='<td>'.get_the_author_meta( 'user_login',$v -> id_aut ).'</td>
		<td>'.$v -> ip.'</td>
		<td>'.$v -> time .'</td>	
		</tr>';
		}
	$str .='<tbody>';
	$str.='</table>';
	$str.='<div class="tablenav">';
	$str.= hepta_search_gen_action_groupee(array(
		array('title'=>'Supprimer','value'=>'trash')
		),2
	);
	$str.= hepta_search_gen_pagin($resultats);
	$str.='</div>';
	$str.='</form>';
	return $str;
	}

function hepta_search_gen_tab_imp($tab)
	{
	$of = array();
	foreach($tab as $k=>$v)
		{
		$of[] = "'".$v."'";
		}
	return $of ;
	}

/*ajout de datas dans la blacklist*/	
function hepta_search_aj_data_blist($tab)
	{
	global $wpdb;
	$cb = 0;
	$aj = array();
	if(!empty($tab))
		{
		$tab = array_map('trim', array_values(array_unique($tab)));
		$table_name = $wpdb->prefix . 'moteur_r_bl';
		$dej = $wpdb->get_col("SELECT exp FROM $table_name WHERE exp IN (".implode(',',hepta_search_gen_tab_imp($tab)).")");	
		$aj = array_values(array_diff($tab,$dej));
		if(!empty($aj))
			{
			$of = array();
			$tps = current_time('mysql');
			foreach($aj as $k=>$v)
				{
				$of[] = '(\'\',\''.$tps.'\',\''.sanitize_text_field($v).'\')';
				}
			if(!empty($of))
				{
				$cb = sizeof($of);
				$wpdb->query("INSERT INTO $table_name (id, time, exp) VALUES ".implode(',',$of)) ;
				//echo $wpdb->last_error;
				}
			}
		}
	return $cb;
	}

// fonction d'installation	
function hepta_search_install()
	{
	require_once('class/hepta_search.php');
	$rech = new hepta_search();
	$rech -> hepta_search_install();	
	hepta_search_aj_data_blist($rech -> hepta_search_blacklist_defaut(plugin_dir_path( __FILE__ )));
	}

// fonction de désinstaller
function hepta_search_uninstall()
	{
	require_once('class/hepta_search.php');
	$rech = new hepta_search();
	$rech -> hepta_search_uninstall();
	$rech -> hepta_search_option_remove();
	}

// fonction desactiver	
function hepta_search_unactive()
	{
	require_once('class/hepta_search.php');
	$rech = new hepta_search();
	$rech -> hepta_search_uninstall();			
	}

function hepta_search_log($q)
	{
	if(isset($_GET['s']))
		{
		global $wp_query;
		require_once('class/hepta_search.php');
		$rech = new hepta_search();
		if($wp_query->query_vars['s'] == '')
			{
			$wp_query->post_count = 0;	
			}
		$rech -> log_rech(array($_GET['s'],$wp_query->post_count,$rech->hepta_search_capte_info($wp_query),$wp_query->query_vars['s']));
		$wp_query->query_vars['s'] = $_GET['s'];
		$wp_query->query['s'] = $_GET['s'];
		}
	}

/*pour supprimer les logs au bout de x temps*/
function hepta_search_clean()
	{
	require_once('class/hepta_search.php');
	$rech = new hepta_search();
	$base = $rech -> hepta_search_option_get();
	$ref = array(
	1 => '1 HOUR',
	2 => '1 DAY',
	3 => '1 WEEK',
	4 => '1 MONTH',
	5 => '1 YEAR',
	);
	if($base['expire'] > 0) // si la blacklist est activée
		{
		if(isset($ref[$base['expire']]))
			{
			global $wpdb;
			$table_name = $wpdb->prefix . 'log_rech';
			$tps = current_time('mysql');
			 $wpdb->query(
              "DELETE FROM $table_name 
			  WHERE DATE_SUB('$tps',INTERVAL ".$ref[$base['expire']].") > time");	
			}
		}
	}

/* action de la blacklist*/
function hepta_search_ex_blacklist()
	{
	require_once('class/hepta_search.php');
	$rech = new hepta_search();
	$base = $rech -> hepta_search_option_get();	
	if($base['blist'] == 1) // si la blacklist est activée
		{
		if(isset($_GET['s']))
			{	
			global $wp_query;
			$mts = $rech-> hepta_search_filtres($wp_query->query_vars['s'],$base);
			$_GET['hepta_old'] = $_GET['s'];
			$wp_query->query_vars['s'] = $mts;
			$wp_query->query['s'] = $mts;
			}
		}
	}
	

// pour agire avant la recherche (pre_get_posts)
add_action('pre_get_posts', 'hepta_search_ex_blacklist');

// pour agire après la recherche (pre_get_posts)
add_action('wp_enqueue_scripts', 'hepta_search_log');

// pour installer le plugin
register_activation_hook( __FILE__, 'hepta_search_install');

// pour désinstaller le plugin 
register_uninstall_hook( __FILE__, 'hepta_search_uninstall');

// pour désactiver
register_deactivation_hook(__FILE__, 'hepta_search_unactive');


add_action('init', 'hepta_search_clean');
add_action( 'admin_menu', 'hepta_search_add_menu' );

function hepta_search_display_screen_options() {
	global $hepta_search_logs;
	$screen = get_current_screen();
	if(!is_object($screen) || $screen->id != $hepta_search_logs)

		return;

 

	$args = array(

		'label' => __('logs par page', 'p_page'),

		'default' => 10,

		'option' => 'p_page'

	);

	add_screen_option( 'per_page', $args );

}



function hepta_set_screen_option($status, $option, $value) {

	if ( 'p_page' == $option ) return $value;

}

add_filter('set-screen-option', 'hepta_set_screen_option', 10, 3);


/*

*/
function hepta_search_add_menu(){
	global $hepta_search_logs;
    $hepta_search_logs = add_menu_page('Hepta-Search', 'Hepta-Search', 'manage_options', 'hepta_search_menu', 'hepta_search_menu_callback',plugin_dir_url(__FILE__ ).'/images/loupe.png', 20);
	add_action("load-$hepta_search_logs", "hepta_search_display_screen_options");

	// Les pages du sous menu

	add_submenu_page( 'hepta_search_menu', 'Blacklist', 'Blacklist', 'manage_options', 'blacklist', 'hepta_search_blacklist_callback' );
	add_submenu_page( 'hepta_search_menu', 'Réglages', 'Réglages', 'manage_options', 'hepta_search_reglages', 'hepta_search_reglages' );
}

?>