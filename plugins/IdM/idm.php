<?php

global $tables_principales;
global $tables_auxiliaires;
global $table_des_tables;

$tables_principales['spip_auteurs']['field']['role']              = "ENUM ('visiteur','candidat','relecteur') NOT NULL DEFAULT 'visiteur'";
$tables_principales['spip_auteurs']['field']['math']              = "TINYTEXT";
$tables_principales['spip_auteurs']['field']['relecteur_combien'] = "INT NOT NULL DEFAULT 0";
$tables_principales['spip_auteurs']['field']['relecteur_quand']   = "TIMESTAMP";

$tables_principales['spip_idm_projets'] = array (
  'field' => array (
    'id_projet'   => 'BIGINT(21) NOT NULL',
    'id_editeur'  => 'BIGINT(21) NOT NULL',
    'id_auteur'   => 'BIGINT(21) NOT NULL default 0',
    'id_article'  => 'BIGINT(21) NOT NULL default 0',
    'id_rubrique' => 'BIGINT(21) NOT NULL',
    'auteur'      => 'TINYTEXT NOT NULL',
    'sujet'       => 'TINYTEXT NOT NULL',
    'modif'       => 'TIMESTAMP NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP',
    'comment'     => 'TEXT NOT NULL',
    'statut'      => "ENUM ('contact', 'redaction', 'relecture', 'publie', 'refus') NOT NULL DEFAULT 'contact'"),
  'key' => array (
    'PRIMARY KEY'   => 'id_projet'));

$tables_auxiliaires['spip_relecteurs_articles'] = array (
  'field' => array (
    'id_article'  => 'BIGINT(21) NOT NULL',
    'id_auteur'   => 'BIGINT(21) NOT NULL',
    'date_change' => 'TIMESTAMP',
    'status'      => "ENUM('pas_vu','vu','non','moyen','oui') NOT NULL DEFAULT 'pas_vu'",
    'avis'        => "TINYTEXT"),
  'key' => array());

$table_des_tables['relecteurs_articles'] = 'relecteurs_articles';
$table_des_tables['idm_projets']         = 'idm_projets';

function idm_install ($action) {
  switch ($action) {
  case 'test':
    $desc = sql_showtable ('spip_idm_projets');
    if ($desc AND $desc['field']['comment']) return true; else return false;
    break;

  case 'install':
    include_spip ('base/create');
    creer_base();
    maj_tables(array('spip_idm_projets','spip_relecteurs_articles'));
    break;

  case 'uninstall':
    // bad idea to drop the table ...
    break;
  }
}

function idm_boite_infos (&$flux) {
  if ($flux['args']['type'] == 'article'
    AND $id_article = intval($flux['args']['id'])
    AND $statut = $flux['args']['row']['statut']
    AND $statut == 'prop') {

      $message = 'G&eacute;rer la relecture';
      $url     = generer_url_public ("propose", array("id_article" => $id_article));
      $previsu = icone_horizontale ($message, $url, find_in_path("img/relecteurs.gif"), "rien.gif", false);

      if ($p = strpos ($flux['data'], '</ul>')) {
        while ($q = strpos ($flux['data'],'</ul>',$p+5))
          $p=$q;
        $flux['data'] = substr($flux['data'],0,$p+5) . $previsu . substr($flux['data'],$p+5);
      }
      else $flux['data'] .= $previsu;
    }

  return $flux;
}

function idm_autoriser() {}

function autoriser_article_relire_dist ($faire, $type, $id, $qui, $opt) {
  if ($qui['id_auteur'] == 0) return false;
  if ($qui['statut'] == '0minirezo') return true;

  $id_auteur = $qui['id_auteur'];
  if (sql_countsel('spip_auteurs_articles', "id_article = $id AND id_auteur = $id_auteur")) return true;
  if (sql_countsel('spip_relecteurs_articles', "id_article = $id AND id_auteur = $id_auteur")) return true;

  return false;
}
?>