<?php

$string['plugindist'] = 'Distribution du plugin';
$string['plugindist_desc'] = '
<p>Ce plugin est distribué dans la communauté Moodle pour l\'évaluation de ses fonctions centrales
correspondant à une utilisation courante du plugin. Une version "professionnelle" de ce plugin existe et est distribuée
sous certaines conditions, afin de soutenir l\'effort de développement, amélioration; documentation et suivi des versions.</p>
<p>Contactez un distributeur pour obtenir la version "Pro" et son support.</p>
<p><a href="http://www.mylearningfactory.com/index.php/documentation/Distributeurs?lang=fr_utf8">Distributeurs MyLF</a></p>';

require_once($CFG->dirroot.'/local/sharedresources/lib.php'); // to get xx_supports_feature();
if ('pro' == local_sharedresources_supports_feature('emulate/community')) {
    include($CFG->dirroot.'/local/sharedresources/pro/lang/fr/pro.php');
}