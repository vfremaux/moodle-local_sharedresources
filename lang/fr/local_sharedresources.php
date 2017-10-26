<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

$string['adminrepository'] = 'Administrer la librairie';
$string['backtocourse'] = 'Revenir au cours';
$string['backtoindex'] = 'Revenir à l\'index';
$string['clinonexistingpath'] = 'Erreur : Chemin inexistant';
$string['clinonexistingcontext'] = 'Erreur : Contexte inexistant';
$string['configdefaulttaxonomypurposeonimport'] = 'L\'objet de la taxonomie définit pour quoi cette taxonomie est utilisée. Certains schémas de métadonnées autorisent la classification des ressources selon plusieurs taxonomies avec des objectifs différents.';
$string['confirm'] = 'Confirmer';
$string['confignotfound'] = 'Fichier de configuration introuvable';
$string['deducetaxonomyfrompath'] = 'Déduire la taxonomie du chemin';
$string['defaulttaxonomypurposeonimport'] = 'Objet de la taxonomie par défaut';
$string['doresetvolume'] = 'Réinitialiser';
$string['errorinvalidresource'] = 'Resource invalide';
$string['errorinvalidresourceid'] = 'Identifiant de ressource inconnu';
$string['rpcsharedresourceerror'] = 'Erreur RPC mod/sharedresource/get_list:<br/>{$a}';
$string['errormnetpeer'] = 'Erreur d\'initialisation du client MNET';
$string['errornotadir'] = 'Le répertoire d\'import n\'existe pas ou n\'est pas accessible';
$string['exclusionpattern'] = 'Motif d\'exclusion';
$string['filestoimport'] = 'Fichiers à importer de : {$a}';
$string['forcedelete'] = 'Forcer la suppression (même si utilisé)';
$string['importpath'] = 'Chemin à importer';
$string['importvolume'] = 'Importer des documents';
$string['installltitool'] = 'Installer comme outil externe';
$string['keywords'] = 'Mots-clefs : ';
$string['library'] = 'Librairie';
$string['liked'] = 'Appréciée : {$a}';
$string['markliked'] = 'J\'aime ça !';
$string['massimport'] = 'Importer massivement';
$string['newresource'] = 'Ajouter une ressource';
$string['noresources'] = '<p>Aucune ressource locale dans cette librairie</p>';
$string['pluginname'] = 'Librairie de ressources';
$string['private_catalog'] = 'Catalogue privé';
$string['config_private_catalog'] = 'Si coché, toute la librairie mutualisée est totalement privée et les ressources ne peuvent être accédées de l\'extérieur. Les index ne sont alors pas moissonables à travers le service OAI';
$string['reinitialized'] = '{$a} fichiers rétablis';
$string['resetvolume'] = 'Réinitialiser le volume pour l\'import';
$string['resourceimport'] = 'Importation de ressources';
$string['resources'] = 'Ressources';
$string['resourcesadministration'] = 'Administration des ressources';
$string['resourcespushout'] = 'Exporter vers un fournisseur de ressources';
$string['sharedresources_library'] = 'Librairie partagée';
$string['textsearch'] = 'Recherche texte';
$string['topkeywords'] = 'Mots-clefs principaux';
$string['updateresourcespageoff'] = 'Mode normal';
$string['updateresourcespageon'] = 'Mode édition';
$string['used'] = 'Utilisée : {$a}';
$string['viewed'] = 'Vue : {$a}';

$string['resetvolume_help'] = 'Les fichiers traités sont marqués par le préfixe "__" pour permettre une
reprise partielle du traitement en cas de limite mémoire ou physique.
Vous pouvez complétement réinitialiser le volume de fichiers à importer avec cette option.';

$string['importpath_help'] = '
Le chemin peut indiquer n\'importe quel répertoire dans le système de fichier local du serveur.
Les fichiers devront avoir été préalablement téléchargés par un administrateur.
L\'ensemble des répertoires sera parcouru et tous les fichiers qui s\'y trouvent seront indexés. Si un fichier "metadata.csv"
est présent, alors les métadonnées de base seront alimentées à partir de ce fichier, pour chaque entrée correspondant à un
fichier physique. Ce fichier ne sera évidemment pas indexé.
';

$string['exclusionpattern_help'] = 'Les noms de fichier correpondant à ce motif seront ignorés. Le motif admet des jokers simples
(ex. "*.jpg" ignorera les fichiers JPEG)';

$string['deducetaxonomyfrompath_help'] = 'Si coché, le chemin relatif de la ressoure importée servira de base à l\'alimentation
de la taxonomie. L^\'organisation hiérarchique de la taxonomie produite sera le reflet de l\'organisation physique du
répertoire d\'import.';
