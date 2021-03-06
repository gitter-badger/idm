<?php

/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2014                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/actions');
include_spip('inc/editer');

function formulaires_editer_groupe_mot_charger_dist($id_groupe='new',$retour='', $config_fonc='groupes_mots_edit_config', $row=array(), $hidden=''){
	$valeurs = formulaires_editer_objet_charger('groupe_mots',$id_groupe,0,'',$retour,$config_fonc,$row,$hidden);

	$valeurs['tables_liees'] = explode(',',$valeurs['tables_liees']);

	// par defaut a la creation de groupe
	if (!intval($id_groupe)) {
		$valeurs['tables_liees'] = array('articles');
		$valeurs['minirezo'] = 'oui';
		$valeurs['comite'] = 'oui';
	}

	return $valeurs;
}

/**
 * Identifier le formulaire en faisant abstraction des parametres qui
 * ne representent pas l'objet edite
 */
function formulaires_editer_groupe_mot_identifier_dist($id_groupe='new',$retour='', $config_fonc='groupes_mots_edit_config', $row=array(), $hidden=''){
	return serialize(array(intval($id_groupe)));
}

// Choix par defaut des options de presentation
// http://code.spip.net/@articles_edit_config
function groupes_mots_edit_config($row)
{
	global $spip_ecran, $spip_lang;

	$config = $GLOBALS['meta'];
	$config['lignes'] = ($spip_ecran == "large")? 8 : 5;
	$config['langue'] = $spip_lang;
	return $config;
}

function formulaires_editer_groupe_mot_verifier_dist($id_groupe='new',$retour='', $config_fonc='groupes_mots_edit_config', $row=array(), $hidden=''){

	$erreurs = formulaires_editer_objet_verifier('groupe_mots',0,array('titre'));
	return $erreurs;
}

// http://code.spip.net/@inc_editer_groupe_mot_dist
function formulaires_editer_groupe_mot_traiter_dist($id_groupe='new',$retour='', $config_fonc='groupes_mots_edit_config', $row=array(), $hidden=''){
	set_request('redirect','');
	// cas des checkbox : injecter la valeur non si rien de coche
	foreach (array(
		'obligatoire', 'unseul',
		'comite', 'forum', 'minirezo'
	) as $champ)
		if (!_request($champ))
			set_request($champ,'non');

	$res = formulaires_editer_objet_traiter('groupe_mots',$id_groupe,0,0,$retour,$config_fonc,$row,$hidden);
	return $res;
}


?>
