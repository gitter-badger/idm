<?php

/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2009                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/

if (!defined("_ECRIRE_INC_VERSION")) return;

// http://doc.spip.org/@action_tourner_dist
function action_tourner_dist() {
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$arg = $securiser_action();

	if (!preg_match(",^\W*(\d+)\W?(-?\d+)$,", $arg, $r)) {
		spip_log("action_tourner_dist $arg pas compris");
	} else  action_tourner_post($r[1],$r[2]);
}

// http://doc.spip.org/@action_tourner_post
function action_tourner_post($id_document,$angle)
{
	$row = sql_fetsel("fichier,extension", "spip_documents", "id_document=".intval($id_document));

	if (!$row) return;

	include_spip('inc/charsets');	# pour le nom de fichier
	include_spip('inc/documents'); 
	// Fichier destination : on essaie toujours de repartir de l'original
	$var_rot = $angle;

	include_spip('inc/distant'); # pour copie_locale
	$src = _DIR_RACINE . copie_locale(get_spip_doc($row['fichier']));
	if (preg_match(',^(.*)-r(90|180|270)\.([^.]+)$,', $src, $match)) {
		$effacer = $src;
		$src = $match[1].'.'.$match[3];
		$var_rot += intval($match[2]);
	}
	$var_rot = ((360 + $var_rot) % 360); // 0, 90, 180 ou 270

	if ($var_rot > 0) {
		$dest = preg_replace(',\.[^.]+$,', '-r'.$var_rot.'$0', $src);
		spip_log("rotation $var_rot $src : $dest");

		include_spip('inc/filtres');
		$res = filtrer('image_rotation',$src,$var_rot);
		$res = filtrer('image_format',$res,$row['extension']);

		list($hauteur,$largeur) = taille_image($res);
		$res = extraire_attribut($res,'src');

		include_spip('inc/getdocument');
		deplacer_fichier_upload($res,$dest);
	}
	else {
		$dest = $src;
		$size_image = @getimagesize($dest);
		$largeur = $size_image[0];
		$hauteur = $size_image[1];
	}

	// succes !
	if ($largeur>0 AND $hauteur>0) {
		$set = array('fichier' => set_spip_doc($dest), 'largeur'=>$largeur, 'hauteur'=>$hauteur);
		if ($taille = @filesize($dest))
			$set['taille'] = $taille;
		sql_updateq('spip_documents', $set, "id_document=".intval($id_document));
		if ($effacer) {
			spip_log("rotation : j'efface $effacer");
			spip_unlink($effacer);
		}
	}

}

?>