<?php

/**
* redirected include from /resources/plugins/cndp
*/   
    
    print_heading(get_string('cndpsearch', 'cndp', '', $CFG->dirroot.'/resources/plugins/cndp/lang/'));
    print_container_start(true, 'emptyleftspace');

?>

<script language="JavaScript">
<!--

function afficheId(baliseId){
    if (document.getElementById && document.getElementById(baliseId) != null){
        document.getElementById(baliseId).style.visibility = 'visible';
        // document.getElementById(baliseId).style.display = 'block';
    }
}

function cacheId(baliseId){
    if (document.getElementById && document.getElementById(baliseId) != null){
        document.getElementById(baliseId).style.visibility = 'hidden';
        // document.getElementById(baliseId).style.display='none';
    }
} 

function init(){
	window.location.href='RechAvanc.asp';
}

this.name = "Origin";

TabEN       =  new Array();
TabEN[0]    =  new Array('', '-------- Niveaux d\'enseignement et de formation --------' );
TabEN[1]    = new Array('EP', 'école primaire' );
TabEN[2]    = new Array('EPEM', ' école maternelle' );
TabEN[3]    = new Array('EPEMC1', '  cycle 1' );
TabEN[4]    = new Array('EPEMC1PS', '   petite section' );
TabEN[5]    = new Array('EPEMC1MS', '   moyenne section' );
TabEN[6]    = new Array('EPEMC2', '  cycle 2 - grande section' );
TabEN[7]    = new Array('EPEE', ' école élémentaire' );
TabEN[8]    = new Array('EPEEC2', '  cycle 2' );
TabEN[9]    = new Array('EPEEC2CP', '   CP cours préparatoire' );
TabEN[10]   = new Array('EPEEC2E1', '   CE1 cours élémentaire 1re année' );
TabEN[11]   = new Array('EPEEC3', '  cycle 3' );
TabEN[12]   = new Array('EPEEC3E2', '   CE2 cours élémentaire 2e année' );
TabEN[13]   = new Array('EPEEC3M1', '   CM1 cours moyen 1re année' );
TabEN[14]   = new Array('EPEEC3M2', '   CM2 cours moyen 2e année' );

TabGN       =  new Array();
TabGN[0]    =  new Array('', '-------- Niveaux d\'enseignement et de formation --------' );
TabGN[1]    = new Array('GT', 'enseignement secondaire - voies générale et technologique' );
TabGN[2]    = new Array('GTCO', ' collège' );
TabGN[3]    = new Array('GTCO6E', '  6e' );
TabGN[4]    = new Array('GTCO5E', '  5e' );
TabGN[5]    = new Array('GTCO4E', '  4e' );
TabGN[6]    = new Array('GTCO3E', '  3e' );
TabGN[7]    = new Array('GTLY', ' lycée' );
TabGN[8]    = new Array('GTLY2N', '  2de' );
TabGN[9]    = new Array('GTLY1R', '  1re' );
TabGN[10]   = new Array('GTLYTR', '  terminale' );
TabGN[11]   = new Array('GTLYBT', '  BT brevet de technicien' );

TabPN       = new Array();
TabPN[0]    =  new Array('', '-------- Niveaux d\'enseignement et de formation --------' );
TabPN[1]    = new Array('VP', 'enseignement secondaire - voie professionnelle' );
TabPN[2]    = new Array('VPCO', ' collège' );
TabPN[3]    = new Array('VPCO4P', '  4e professionnelle' );
TabPN[4]    = new Array('VPCO3P', '  3e professionnelle' );
TabPN[5]    = new Array('VPLY', ' lycée' );
TabPN[6]    = new Array('VPLY2P', '  2de professionnelle' );
TabPN[7]    = new Array('VPLYTB', '  terminale BEP' );
TabPN[8]    = new Array('VPLY1P', '  1re bac pro' );
TabPN[9]    = new Array('VPLYTP', '  terminale bac pro' );
TabPN[10]   = new Array('VPLYCA', '  CAP certificat d\'aptitude professionnelle' );
TabPN[11]   = new Array('VPLYBE', '  BEP brevet d\'études professionnelles' );
TabPN[12]   = new Array('VPLYBM', '  BMA brevet des métiers d\'art' );
TabPN[13]   = new Array('VPLYBP', '  BP brevet professionnel' );
TabPN[14]   = new Array('VPLYBT', '  BTM brevet technique des métiers' );
TabPN[15]   = new Array('VPLYFC', '  FCIL formation complémentaire d\'initiative' );
TabPN[16]   = new Array('VPLYMC', '  MC mention complémentaire' );

TabAN       = new Array();
TabAN[0]    = new Array('', '-------- Niveaux d\'enseignement et de formation --------' );
TabAN[1]    = new Array('EAGT', ' enseignement agricole secondaire - voies générale et technologique' );
TabAN[2]    = new Array('EAGTCO', '  collège' );
TabAN[3]    = new Array('EAGTCO4E', '   4e enseignement agricole' );
TabAN[4]    = new Array('EAGTCO3E', '   3e enseignement agricole' );
TabAN[5]    = new Array('EAGTCOCL', '   CLIPA classe d\'initiation préprofessionnelle en alternance' );
TabAN[6]    = new Array('EAGTCOA1', '   CPA classe préparatoire à l\'apprentissage - 1re année' );
TabAN[7]    = new Array('EAGTCOA2', '   CPA classe préparatoire à l\'apprentissage - 2e année' );
TabAN[8]    = new Array('EAGTLY', '  lycée' );
TabAN[9]    = new Array('EAGTLY2N', '   2de' );
TabAN[10]   = new Array('EAGTLY1R', '   1re' );
TabAN[11]   = new Array('EAGTLYTR', '   terminale' );
TabAN[12]   = new Array('EAGTLYT1', '   BTA brevet de technicien agricole - 1re année' );
TabAN[13]   = new Array('EAGTLYTT', '   BTA brevet de technicien agricole - terminale' );
TabAN[14]   = new Array('EAVP', ' enseignement agricole secondaire - voie professionnelle' );
TabAN[15]   = new Array('EAVPC1', '  CAPA certificat d\'aptitude professionnelle agricole - 1re année' );
TabAN[16]   = new Array('EAVPC2', '  CAPA certificat d\'aptitude professionnelle agricole - 2e année' );
TabAN[17]   = new Array('EAVPB1', '  BEPA brevet d\'études professionnelles agricoles - 1re année' );
TabAN[18]   = new Array('EAVPB2', '  BEPA brevet d\'études professionnelles agricoles - 2e année' );
TabAN[19]   = new Array('EAVP1B', '  1re bac pro' );
TabAN[20]   = new Array('EAVPTB', '  terminale bac pro' );

TabSN       = new Array();
TabSN[0]    = new Array('', '-------- Niveaux d\'enseignement et de formation --------' );
TabSN[1]    = new Array('ES', 'enseignement supérieur' );
TabSN[2]    = new Array('ESCP', ' CPGE classe préparatoire aux grandes écoles' );
TabSN[3]    = new Array('ESBT', ' BTS brevet de technicien supérieur' );
TabSN[4]    = new Array('ESDA', ' diplômes assimilés aux BTS' );
TabSN[5]    = new Array('ESAD', ' autres diplômes de niveau 3' );
TabSN[6]    = new Array('ESDG', ' DEUG diplôme d\'études universitaires générales' );
TabSN[7]    = new Array('ESDT', ' DEUST diplôme d\'études universitaires scientifiques et techniques' );
TabSN[8]    = new Array('ESDM', ' DMA diplôme des métiers d\'art' );
TabSN[9]    = new Array('ESAA', ' DSAA diplôme supérieur d\'arts appliqués' );
TabSN[10]   = new Array('ESDU', ' DUT diplôme universitaire de technologie' );
TabSN[11]   = new Array('ESLI', ' licence' );
TabSN[12]   = new Array('ESLP', ' licence professionnelle' );
TabSN[13]   = new Array('ESMA', ' maîtrise' );
TabSN[14]   = new Array('ESMS', ' master' );
TabSN[15]   = new Array('ESDE', ' DEA diplôme d\'études approfondies' );
TabSN[16]   = new Array('ESDS', ' DESS diplôme d\'études supérieures spécialisées' );
TabSN[17]   = new Array('ESDO', ' doctorat' );
TabSN[18]   = new Array('EAS1', ' enseignement agricole supérieur - 1er cycle' );
TabSN[19]   = new Array('EAS1CP', '  CPGE classe préparatoire aux grandes écoles' );
TabSN[20]   = new Array('EAS1CPBI', '   CP BCPST classe préparatoire biologie, chimie, physique et sciences de la Terre' );
TabSN[21]   = new Array('EAS1CPDI', '   CP post BTSA/BTS/DUT classe préparatoire postérieure au BTS agricole, au BTS, au DUT' );
TabSN[22]   = new Array('EAS1B1', '  BTSA brevet de technicien supérieur agricole - 1re année' );
TabSN[23]   = new Array('EAS1B2', '  BTSA brevet de technicien supérieur agricole - 2e année' );
TabSN[24]   = new Array('EAS1LI', '  classe de pré-licence' );
TabSN[25]   = new Array('EAS2', ' enseignement agricole supérieur - 2e cycle' );
TabSN[26]   = new Array('EAS2AA', '  DAA diplôme d\'agronomie approfondie' );
TabSN[27]   = new Array('EAS2AG', '  DAG diplôme d\'agronomie générale' );
TabSN[28]   = new Array('EAS2AR', '  DEFA diplôme d\'études fondamentales en architecture' );
TabSN[29]   = new Array('EAS2IA', '  DEGIA diplôme d\'études générales en industries agricoles et alimentaires' );
TabSN[30]   = new Array('EAS2EV', '  DESV diplôme d\'études spécialisées vétérinaires' );
TabSN[31]   = new Array('EAS2RT', '  DRT diplôme de recherche technologique' );
TabSN[32]   = new Array('EAS2HA', '  DSHA diplôme de sciences horticoles approfondies' );
TabSN[33]   = new Array('EAS2TA', '  DTAA diplôme de technologie agricole approfondie' );
TabSN[34]   = new Array('EAS2LP', '  licence professionnelle' );

TabXN       =  new Array();
TabXN[0]    = new Array('', '-------- Niveaux d\'enseignement et de formation --------' );
TabXN[1]    = new Array('AI', 'adaptation scolaire et scolarisation des élèves handicapés' );
TabXN[2]    = new Array('AI1D', ' 1er degré' );
TabXN[3]    = new Array('AI1DCS', '  CLIS classe d\'intégration scolaire' );
TabXN[4]    = new Array('AI1DCN', '  CLIN classe d\'initiation' );
TabXN[5]    = new Array('AI2D', ' 2d degré' );
TabXN[6]    = new Array('AI2D4A', '  4e aide et soutien' );
TabXN[7]    = new Array('AI2D3I', '  3e insertion' );
TabXN[8]    = new Array('AI2DSE', '  SEGPA section d\'enseignement général et professionnel adapté' );
TabXN[9]    = new Array('AI2DUP', '  UPI unité pédagogique d\'intégration' );
TabXN[10]   = new Array('AI2DCA', '  CLA classe d\'accueil' );
TabXN[11]   = new Array('AI2DER', '  EREA école régionale d\'enseignement adapté' );

TabED       = new Array();
TabED[0]    = new Array('PRI', 'domaines de l\'école primaire' );
TabED[1]    = new Array('PRIEPS', ' agir et s\'exprimer avec son corps - EPS' );
TabED[2]    = new Array('PRIB2I', ' B2i brevet informatique et internet (école)' );
TabED[3]    = new Array('PRIEHU', ' découvrir le monde - éducation humaine' );
TabED[4]    = new Array('PRIEHUGEO', '  géographie ' );
TabED[5]    = new Array('PRIEHUHIS', '  histoire ' );
TabED[6]    = new Array('PRISCI', ' découvrir le monde - éducation scientifique' );
TabED[7]    = new Array('PRISCIMAT', '  mathématiques' );
TabED[8]    = new Array('PRISCISET', '  sciences expérimentales et technologie' );
TabED[9]    = new Array('PRIART', ' la sensibilité, l\'imagination, la création - éducation artistique' );
TabED[10]   = new Array('PRIARTMUS', '  la voix et l\'écoute - éducation musicale' );
TabED[11]   = new Array('PRIARTVIS', '  le regard et le geste - arts visuels' );
TabED[12]   = new Array('PRILAN', ' langage et langue française - éducation littéraire' );
TabED[13]   = new Array('PRILANECR', '  écriture ' );
TabED[14]   = new Array('PRILANLEC', '  lecture ' );
TabED[15]   = new Array('PRILANORA', '  maîtrise du langage oral' );
TabED[16]   = new Array('PRILER', ' langue étrangère ou régionale' );
TabED[17]   = new Array('PRIVIV', ' vivre ensemble - éducation civique' );

TabGD       = new Array();
TabGD[0]    = new Array('SEC', 'disciplines générales et enseignements de détermination de l\'enseignement secondaire' );
TabGD[1]    = new Array('SECAAP', ' arts appliqués  ' );
TabGD[2]    = new Array('SECACI', ' arts du cirque ' );
TabGD[3]    = new Array('SECAPL', ' arts plastiques ' );
TabGD[4]    = new Array('SECBLP', ' BLP biologie de laboratoire et paramédicale' );
TabGD[5]    = new Array('SECCAV', ' cinéma et audiovisuel' );
TabGD[6]    = new Array('SECCRE', ' création-design ' );
TabGD[7]    = new Array('SECCUL', ' culture-design ' );
TabGD[8]    = new Array('SECDAN', ' danse  ' );
TabGD[9]    = new Array('SECECV', ' éducation civique  ' );
TabGD[10]   = new Array('SECEPS', ' EPS éducation physique et sportive' );
TabGD[11]   = new Array('SECFLE', ' FLE français langue étrangère' );
TabGD[12]   = new Array('SECFRA', ' français   ' );
TabGD[13]   = new Array('SECGEO', ' géographie ' );
TabGD[14]   = new Array('SECHIS', ' histoire  ' );
TabGD[15]   = new Array('SECHAR', ' histoire des arts ' );
TabGD[16]   = new Array('SECIGC', ' IGC informatique de gestion et de communication' );
TabGD[17]   = new Array('SECINF', ' informatique ' );
TabGD[18]   = new Array('SECISI', ' ISI initiation aux sciences de l\'ingénieur' );
TabGD[19]   = new Array('SECISP', ' ISP informatique et systèmes de production' );
TabGD[20]   = new Array('SECLAN', 'langue ancienne' );
TabGD[21]   = new Array('SECLANGRA', '    grec ancien ' );
TabGD[22]   = new Array('SECLANLAT', '    latin  ' );
TabGD[23]   = new Array('SECLVE', 'langue vivante étrangère' );
TabGD[24]   = new Array('SECLVEALL', '    allemand ' );
TabGD[25]   = new Array('SECLVEANG', '    anglais ' );
TabGD[26]   = new Array('SECLVEARA', '    arabe ' );
TabGD[27]   = new Array('SECLVECHI', '    chinois ' );
TabGD[28]   = new Array('SECLVECRO', '    croate ' );
TabGD[29]   = new Array('SECLVEDAN', '    danois ' );
TabGD[30]   = new Array('SECLVEESP', '    espagnol ' );
TabGD[31]   = new Array('SECLVEFIN', '    finnois ' );
TabGD[32]   = new Array('SECLVEGRM', '    grec moderne' );
TabGD[33]   = new Array('SECLVEHEB', '    hébreu moderne' );
TabGD[34]   = new Array('SECLVEHON', '    hongrois ' );
TabGD[35]   = new Array('SECLVEITA', '    italien ' );
TabGD[36]   = new Array('SECLVEJAP', '    japonais ' );
TabGD[37]   = new Array('SECLVENEE', '    néerlandais ' );
TabGD[38]   = new Array('SECLVENOR', '    norvégien ' );
TabGD[39]   = new Array('SECLVEPOL', '    polonais ' );
TabGD[40]   = new Array('SECLVEPOR', '    portugais ' );
TabGD[41]   = new Array('SECLVEROU', '    roumain ' );
TabGD[42]   = new Array('SECLVERUS', '    russe ' );
TabGD[43]   = new Array('SECLVESER', '    serbe ' );
TabGD[44]   = new Array('SECLVESLA', '    slovaque ' );
TabGD[45]   = new Array('SECLVESLO', '    slovène ' );
TabGD[46]   = new Array('SECLVESUE', '    suédois ' );
TabGD[47]   = new Array('SECLVETAM', '    tamoul ' );
TabGD[48]   = new Array('SECLVETCH', '    tchèque ' );
TabGD[49]   = new Array('SECLVETUR', '    turc  ' );
TabGD[50]   = new Array('SECLVR', 'langue vivante régionale' );
TabGD[51]   = new Array('SECLVRBAS', '    basque ' );
TabGD[52]   = new Array('SECLVRBRE', '    breton ' );
TabGD[53]   = new Array('SECLVRCAT', '    catalan ' );
TabGD[54]   = new Array('SECLVRCOR', '    corse ' );
TabGD[55]   = new Array('SECLVRCRE', '    créole ' );
TabGD[56]   = new Array('SECLVRLRA', '    langues régionales d\'Alsace' );
TabGD[57]   = new Array('SECLVROCC', '    occitan - langue d\'oc' );
TabGD[58]   = new Array('SECMAT', ' mathématiques ' );
TabGD[59]   = new Array('SECMPI', ' MPI mesures physiques et informatique' );
TabGD[60]   = new Array('SECMUS', ' musique  ' );
TabGD[61]   = new Array('SECPCL', ' PCL physique et chimie de laboratoire' );
TabGD[62]   = new Array('SECPHI', ' philosophie ' );
TabGD[63]   = new Array('SECPHY', ' physique-chimie  ' );
TabGD[64]   = new Array('SECSIN', ' sciences de l\'ingénieur' );
TabGD[65]   = new Array('SECSES', ' SES sciences économiques et sociales' );
TabGD[66]   = new Array('SECSMS', ' SMS sciences médico-sociales' );
TabGD[67]   = new Array('SECSVT', ' SVT sciences de la vie et de la Terre' );
TabGD[68]   = new Array('SECTEC', ' technologie ' );
TabGD[69]   = new Array('SECTHE', ' théâtre - expression dramatique' );
TabGD[70]   = new Array('TEC', 'domaines et spécialités de l\'enseignement technologique, professionnel, agricole' );
TabGD[71]   = new Array('TECAGR', ' agro-alimentaire - alimentation' );
TabGD[72]   = new Array('TECAGO', ' agronomie  ' );
TabGD[73]   = new Array('TECAME', ' aménagement de l\'espace' );
TabGD[74]   = new Array('TECART', ' artisanat d\'art - métiers d\'art - arts appliqués' );
TabGD[75]   = new Array('TECAUT', ' automatisme - automatique' );
TabGD[76]   = new Array('TECBAN', ' banque, assurance, immobilier' );
TabGD[77]   = new Array('TECBTP', ' bâtiment - travaux publics' );
TabGD[78]   = new Array('TECBLG', ' biologie   ' );
TabGD[79]   = new Array('TECBIO', ' biotechnologies - bio-industries - métiers de laboratoire' );
TabGD[80]   = new Array('TECBOI', ' bois et matériaux associés' );
TabGD[81]   = new Array('TECCOI', ' coiffure - esthétique' );
TabGD[82]   = new Array('TECCOV', ' commerce - vente' );
TabGD[83]   = new Array('TECCOM', ' comptabilité, finances' );
TabGD[84]   = new Array('TECCPI', ' conception et définition de produits industriels' );
TabGD[85]   = new Array('TECDRO', ' droit - économie ' );
TabGD[86]   = new Array('TECECG', ' économie générale' );
TabGD[87]   = new Array('TECESO', ' éducation socioculturelle' );
TabGD[88]   = new Array('TECEIR', ' électronique - informatique industrielle et réseaux' );
TabGD[89]   = new Array('TECEEN', ' électrotechnique et énergie' );
TabGD[90]   = new Array('TECGRH', ' gestion des ressources humaines' );
TabGD[91]   = new Array('TECGES', ' gestion des systèmes d\'information' );
TabGD[92]   = new Array('TECHRT', ' hôtellerie - restauration - tourisme' );
TabGD[93]   = new Array('TECHES', ' hygiène, environnement et sécurité' );
TabGD[94]   = new Array('TECIGI', ' industries graphiques - imprimerie' );
TabGD[95]   = new Array('TECMIM', ' maintenances industrielle et mécanique' );
TabGD[96]   = new Array('TECMAN', ' management  ' );
TabGD[97]   = new Array('TECMOD', ' matériaux souples - métiers de la mode' );
TabGD[98]   = new Array('TECMER', ' mercatique  ' );
TabGD[99]   = new Array('TECPRO', ' production agricole' );
TabGD[100]  = new Array('TECANI', ' production animale' );
TabGD[101]  = new Array('TECAQU', ' production aquacole' );
TabGD[102]  = new Array('TECFOR', ' production forestière' );
TabGD[103]  = new Array('TECVEG', ' production végétale' );
TabGD[104]  = new Array('TECPME', ' production mécanique' );
TabGD[105]  = new Array('TECSMS', ' santé - paramédical et social' );
TabGD[106]  = new Array('TECSEC', ' secrétariat  ' );
TabGD[107]  = new Array('TECSPO', ' sport - animation culturelle' );
TabGD[108]  = new Array('TECSME', ' structures métalliques' );
TabGD[109]  = new Array('TECTIS', ' techniques de l\'image et du son - arts du spectacle' );
TabGD[110]  = new Array('TECTRM', ' transformation et mise en forme des matériaux' );
TabGD[111]  = new Array('TECTMA', ' transport - magasinage' );
TabGD[112]  = new Array('TECVMO', ' véhicules motorisés' );
TabPD =  new Array();
TabPD[0] = new Array('SEC', 'disciplines générales et enseignements de détermination de l\'enseignement secondaire' );
TabPD[1] = new Array('SECAAP', ' arts appliqués  ' );
TabPD[2] = new Array('SECACI', ' arts du cirque ' );
TabPD[3] = new Array('SECAPL', ' arts plastiques ' );
TabPD[4] = new Array('SECBLP', ' BLP biologie de laboratoire et paramédicale' );
TabPD[5] = new Array('SECCAV', ' cinéma et audiovisuel' );
TabPD[6] = new Array('SECCRE', ' création-design ' );
TabPD[7] = new Array('SECCUL', ' culture-design ' );
TabPD[8] = new Array('SECDAN', ' danse  ' );
TabPD[9] = new Array('SECECV', ' éducation civique  ' );
TabPD[10] = new Array('SECEPS', ' EPS éducation physique et sportive' );
TabPD[11] = new Array('SECFLE', ' FLE français langue étrangère' );
TabPD[12] = new Array('SECFRA', ' français   ' );
TabPD[13] = new Array('SECGEO', ' géographie ' );
TabPD[14] = new Array('SECHIS', ' histoire  ' );
TabPD[15] = new Array('SECHAR', ' histoire des arts ' );
TabPD[16] = new Array('SECIGC', ' IGC informatique de gestion et de communication' );
TabPD[17] = new Array('SECINF', ' informatique ' );
TabPD[18] = new Array('SECISI', ' ISI initiation aux sciences de l\'ingénieur' );
TabPD[19] = new Array('SECISP', ' ISP informatique et systèmes de production' );
TabPD[20] = new Array('SECLAN', '   langue ancienne ' );
TabPD[21] = new Array('SECLANGRA', '    grec ancien ' );
TabPD[22] = new Array('SECLANLAT', '    latin  ' );
TabPD[23] = new Array('SECLVE', '   langue vivante étrangère' );
TabPD[24] = new Array('SECLVEALL', '    allemand ' );
TabPD[25] = new Array('SECLVEANG', '    anglais ' );
TabPD[26] = new Array('SECLVEARA', '    arabe ' );
TabPD[27] = new Array('SECLVECHI', '    chinois ' );
TabPD[28] = new Array('SECLVECRO', '    croate ' );
TabPD[29] = new Array('SECLVEDAN', '    danois ' );
TabPD[30] = new Array('SECLVEESP', '    espagnol ' );
TabPD[31] = new Array('SECLVEFIN', '    finnois ' );
TabPD[32] = new Array('SECLVEGRM', '    grec moderne' );
TabPD[33] = new Array('SECLVEHEB', '    hébreu moderne' );
TabPD[34] = new Array('SECLVEHON', '    hongrois ' );
TabPD[35] = new Array('SECLVEITA', '    italien ' );
TabPD[36] = new Array('SECLVEJAP', '    japonais ' );
TabPD[37] = new Array('SECLVENEE', '    néerlandais ' );
TabPD[38] = new Array('SECLVENOR', '    norvégien ' );
TabPD[39] = new Array('SECLVEPOL', '    polonais ' );
TabPD[40] = new Array('SECLVEPOR', '    portugais ' );
TabPD[41] = new Array('SECLVEROU', '    roumain ' );
TabPD[42] = new Array('SECLVERUS', '    russe ' );
TabPD[43] = new Array('SECLVESER', '    serbe ' );
TabPD[44] = new Array('SECLVESLA', '    slovaque ' );
TabPD[45] = new Array('SECLVESLO', '    slovène ' );
TabPD[46] = new Array('SECLVESUE', '    suédois ' );
TabPD[47] = new Array('SECLVETAM', '    tamoul ' );
TabPD[48] = new Array('SECLVETCH', '    tchèque ' );
TabPD[49] = new Array('SECLVETUR', '    turc  ' );
TabPD[50] = new Array('SECLVR', '   langue vivante régionale' );
TabPD[51] = new Array('SECLVRBAS', '    basque ' );
TabPD[52] = new Array('SECLVRBRE', '    breton ' );
TabPD[53] = new Array('SECLVRCAT', '    catalan ' );
TabPD[54] = new Array('SECLVRCOR', '    corse ' );
TabPD[55] = new Array('SECLVRCRE', '    créole ' );
TabPD[56] = new Array('SECLVRLRA', '    langues régionales d\'Alsace' );
TabPD[57] = new Array('SECLVROCC', '    occitan - langue d\'oc' );
TabPD[58] = new Array('SECMAT', ' mathématiques ' );
TabPD[59] = new Array('SECMPI', ' MPI mesures physiques et informatique' );
TabPD[60] = new Array('SECMUS', ' musique  ' );
TabPD[61] = new Array('SECPCL', ' PCL physique et chimie de laboratoire' );
TabPD[62] = new Array('SECPHI', ' philosophie ' );
TabPD[63] = new Array('SECPHY', ' physique-chimie  ' );
TabPD[64] = new Array('SECSIN', ' sciences de l\'ingénieur' );
TabPD[65] = new Array('SECSES', ' SES sciences économiques et sociales' );
TabPD[66] = new Array('SECSMS', ' SMS sciences médico-sociales' );
TabPD[67] = new Array('SECSVT', ' SVT sciences de la vie et de la Terre' );
TabPD[68] = new Array('SECTEC', ' technologie ' );
TabPD[69] = new Array('SECTHE', ' théâtre - expression dramatique' );
TabPD[70] = new Array('TEC', 'domaines et spécialités de l\'enseignement technologique, professionnel, agricole' );
TabPD[71] = new Array('TECAGR', ' agro-alimentaire - alimentation' );
TabPD[72] = new Array('TECAGO', ' agronomie  ' );
TabPD[73] = new Array('TECAME', ' aménagement de l\'espace' );
TabPD[74] = new Array('TECART', ' artisanat d\'art - métiers d\'art - arts appliqués' );
TabPD[75] = new Array('TECAUT', ' automatisme - automatique' );
TabPD[76] = new Array('TECBAN', ' banque, assurance, immobilier' );
TabPD[77] = new Array('TECBTP', ' bâtiment - travaux publics' );
TabPD[78] = new Array('TECBLG', ' biologie   ' );
TabPD[79] = new Array('TECBIO', ' biotechnologies - bio-industries - métiers de laboratoire' );
TabPD[80] = new Array('TECBOI', ' bois et matériaux associés' );
TabPD[81] = new Array('TECCOI', ' coiffure - esthétique' );
TabPD[82] = new Array('TECCOV', ' commerce - vente' );
TabPD[83] = new Array('TECCOM', ' comptabilité, finances' );
TabPD[84] = new Array('TECCPI', ' conception et définition de produits industriels' );
TabPD[85] = new Array('TECDRO', ' droit - économie ' );
TabPD[86] = new Array('TECECG', ' économie générale' );
TabPD[87] = new Array('TECESO', ' éducation socioculturelle' );
TabPD[88] = new Array('TECEIR', ' électronique - informatique industrielle et réseaux' );
TabPD[89] = new Array('TECEEN', ' électrotechnique et énergie' );
TabPD[90] = new Array('TECGRH', ' gestion des ressources humaines' );
TabPD[91] = new Array('TECGES', ' gestion des systèmes d\'information' );
TabPD[92] = new Array('TECHRT', ' hôtellerie - restauration - tourisme' );
TabPD[93] = new Array('TECHES', ' hygiène, environnement et sécurité' );
TabPD[94] = new Array('TECIGI', ' industries graphiques - imprimerie' );
TabPD[95] = new Array('TECMIM', ' maintenances industrielle et mécanique' );
TabPD[96] = new Array('TECMAN', ' management  ' );
TabPD[97] = new Array('TECMOD', ' matériaux souples - métiers de la mode' );
TabPD[98] = new Array('TECMER', ' mercatique  ' );
TabPD[99] = new Array('TECPRO', ' production agricole' );
TabPD[100] = new Array('TECANI', ' production animale' );
TabPD[101] = new Array('TECAQU', ' production aquacole' );
TabPD[102] = new Array('TECFOR', ' production forestière' );
TabPD[103] = new Array('TECVEG', ' production végétale' );
TabPD[104] = new Array('TECPME', ' production mécanique' );
TabPD[105] = new Array('TECSMS', ' santé - paramédical et social' );
TabPD[106] = new Array('TECSEC', ' secrétariat  ' );
TabPD[107] = new Array('TECSPO', ' sport - animation culturelle' );
TabPD[108] = new Array('TECSME', ' structures métalliques' );
TabPD[109] = new Array('TECTIS', ' techniques de l\'image et du son - arts du spectacle' );
TabPD[110] = new Array('TECTRM', ' transformation et mise en forme des matériaux' );
TabPD[111] = new Array('TECTMA', ' transport - magasinage' );
TabPD[112] = new Array('TECVMO', ' véhicules motorisés' );

TabAD       = new Array();
TabAD[0]    = new Array('SEC', 'disciplines générales et enseignements de détermination de l\'enseignement secondaire' );
TabAD[1]    = new Array('SECAAP', ' arts appliqués  ' );
TabAD[2]    = new Array('SECACI', ' arts du cirque ' );
TabAD[3]    = new Array('SECAPL', ' arts plastiques ' );
TabAD[4]    = new Array('SECBLP', ' BLP biologie de laboratoire et paramédicale' );
TabAD[5]    = new Array('SECCAV', ' cinéma et audiovisuel' );
TabAD[6]    = new Array('SECCRE', ' création-design ' );
TabAD[7]    = new Array('SECCUL', ' culture-design ' );
TabAD[8]    = new Array('SECDAN', ' danse  ' );
TabAD[9]    = new Array('SECECV', ' éducation civique  ' );
TabAD[10]   = new Array('SECEPS', ' EPS éducation physique et sportive' );
TabAD[11]   = new Array('SECFLE', ' FLE français langue étrangère' );
TabAD[12]   = new Array('SECFRA', ' français   ' );
TabAD[13]   = new Array('SECGEO', ' géographie ' );
TabAD[14]   = new Array('SECHIS', ' histoire  ' );
TabAD[15]   = new Array('SECHAR', ' histoire des arts ' );
TabAD[16]   = new Array('SECIGC', ' IGC informatique de gestion et de communication' );
TabAD[17]   = new Array('SECINF', ' informatique ' );
TabAD[18]   = new Array('SECISI', ' ISI initiation aux sciences de l\'ingénieur' );
TabAD[19]   = new Array('SECISP', ' ISP informatique et systèmes de production' );
TabAD[20]   = new Array('SECLAN', '   langue ancienne ' );
TabAD[21]   = new Array('SECLANGRA', '    grec ancien ' );
TabAD[22]   = new Array('SECLANLAT', '    latin  ' );
TabAD[23]   = new Array('SECLVE', '   langue vivante étrangère' );
TabAD[24]   = new Array('SECLVEALL', '    allemand ' );
TabAD[25]   = new Array('SECLVEANG', '    anglais ' );
TabAD[26]   = new Array('SECLVEARA', '    arabe ' );
TabAD[27]   = new Array('SECLVECHI', '    chinois ' );
TabAD[28]   = new Array('SECLVECRO', '    croate ' );
TabAD[29]   = new Array('SECLVEDAN', '    danois ' );
TabAD[30]   = new Array('SECLVEESP', '    espagnol ' );
TabAD[31]   = new Array('SECLVEFIN', '    finnois ' );
TabAD[32]   = new Array('SECLVEGRM', '    grec moderne' );
TabAD[33]   = new Array('SECLVEHEB', '    hébreu moderne' );
TabAD[34]   = new Array('SECLVEHON', '    hongrois ' );
TabAD[35]   = new Array('SECLVEITA', '    italien ' );
TabAD[36]   = new Array('SECLVEJAP', '    japonais ' );
TabAD[37]   = new Array('SECLVENEE', '    néerlandais ' );
TabAD[38]   = new Array('SECLVENOR', '    norvégien ' );
TabAD[39]   = new Array('SECLVEPOL', '    polonais ' );
TabAD[40]   = new Array('SECLVEPOR', '    portugais ' );
TabAD[41]   = new Array('SECLVEROU', '    roumain ' );
TabAD[42]   = new Array('SECLVERUS', '    russe ' );
TabAD[43]   = new Array('SECLVESER', '    serbe ' );
TabAD[44]   = new Array('SECLVESLA', '    slovaque ' );
TabAD[45]   = new Array('SECLVESLO', '    slovène ' );
TabAD[46]   = new Array('SECLVESUE', '    suédois ' );
TabAD[47]   = new Array('SECLVETAM', '    tamoul ' );
TabAD[48]   = new Array('SECLVETCH', '    tchèque ' );
TabAD[49]   = new Array('SECLVETUR', '    turc  ' );
TabAD[50]   = new Array('SECLVR', '   langue vivante régionale' );
TabAD[51]   = new Array('SECLVRBAS', '    basque ' );
TabAD[52]   = new Array('SECLVRBRE', '    breton ' );
TabAD[53]   = new Array('SECLVRCAT', '    catalan ' );
TabAD[54]   = new Array('SECLVRCOR', '    corse ' );
TabAD[55]   = new Array('SECLVRCRE', '    créole ' );
TabAD[56]   = new Array('SECLVRLRA', '    langues régionales d\'Alsace' );
TabAD[57]   = new Array('SECLVROCC', '    occitan - langue d\'oc' );
TabAD[58]   = new Array('SECMAT', ' mathématiques ' );
TabAD[59]   = new Array('SECMPI', ' MPI mesures physiques et informatique' );
TabAD[60]   = new Array('SECMUS', ' musique  ' );
TabAD[61]   = new Array('SECPCL', ' PCL physique et chimie de laboratoire' );
TabAD[62]   = new Array('SECPHI', ' philosophie ' );
TabAD[63]   = new Array('SECPHY', ' physique-chimie  ' );
TabAD[64]   = new Array('SECSIN', ' sciences de l\'ingénieur' );
TabAD[65]   = new Array('SECSES', ' SES sciences économiques et sociales' );
TabAD[66]   = new Array('SECSMS', ' SMS sciences médico-sociales' );
TabAD[67]   = new Array('SECSVT', ' SVT sciences de la vie et de la Terre' );
TabAD[68]   = new Array('SECTEC', ' technologie ' );
TabAD[69]   = new Array('SECTHE', ' théâtre - expression dramatique' );
TabAD[70]   = new Array('TEC', 'domaines et spécialités de l\'enseignement technologique, professionnel, agricole' );
TabAD[71]   = new Array('TECAGR', ' agro-alimentaire - alimentation' );
TabAD[72]   = new Array('TECAGO', ' agronomie  ' );
TabAD[73]   = new Array('TECAME', ' aménagement de l\'espace' );
TabAD[74]   = new Array('TECART', ' artisanat d\'art - métiers d\'art - arts appliqués' );
TabAD[75]   = new Array('TECAUT', ' automatisme - automatique' );
TabAD[76]   = new Array('TECBAN', ' banque, assurance, immobilier' );
TabAD[77]   = new Array('TECBTP', ' bâtiment - travaux publics' );
TabAD[78]   = new Array('TECBLG', ' biologie   ' );
TabAD[79]   = new Array('TECBIO', ' biotechnologies - bio-industries - métiers de laboratoire' );
TabAD[80]   = new Array('TECBOI', ' bois et matériaux associés' );
TabAD[81]   = new Array('TECCOI', ' coiffure - esthétique' );
TabAD[82]   = new Array('TECCOV', ' commerce - vente' );
TabAD[83]   = new Array('TECCOM', ' comptabilité, finances' );
TabAD[84]   = new Array('TECCPI', ' conception et définition de produits industriels' );
TabAD[85]   = new Array('TECDRO', ' droit - économie ' );
TabAD[86]   = new Array('TECECG', ' économie générale' );
TabAD[87]   = new Array('TECESO', ' éducation socioculturelle' );
TabAD[88]   = new Array('TECEIR', ' électronique - informatique industrielle et réseaux' );
TabAD[89]   = new Array('TECEEN', ' électrotechnique et énergie' );
TabAD[90]   = new Array('TECGRH', ' gestion des ressources humaines' );
TabAD[91]   = new Array('TECGES', ' gestion des systèmes d\'information' );
TabAD[92]   = new Array('TECHRT', ' hôtellerie - restauration - tourisme' );
TabAD[93]   = new Array('TECHES', ' hygiène, environnement et sécurité' );
TabAD[94]   = new Array('TECIGI', ' industries graphiques - imprimerie' );
TabAD[95]   = new Array('TECMIM', ' maintenances industrielle et mécanique' );
TabAD[96]   = new Array('TECMAN', ' management  ' );
TabAD[97]   = new Array('TECMOD', ' matériaux souples - métiers de la mode' );
TabAD[98]   = new Array('TECMER', ' mercatique  ' );
TabAD[99]   = new Array('TECPRO', ' production agricole' );
TabAD[100]  = new Array('TECANI', ' production animale' );
TabAD[101]  = new Array('TECAQU', ' production aquacole' );
TabAD[102]  = new Array('TECFOR', ' production forestière' );
TabAD[103]  = new Array('TECVEG', ' production végétale' );
TabAD[104]  = new Array('TECPME', ' production mécanique' );
TabAD[105]  = new Array('TECSMS', ' santé - paramédical et social' );
TabAD[106]  = new Array('TECSEC', ' secrétariat  ' );
TabAD[107]  = new Array('TECSPO', ' sport - animation culturelle' );
TabAD[108]  = new Array('TECSME', ' structures métalliques' );
TabAD[109]  = new Array('TECTIS', ' techniques de l\'image et du son - arts du spectacle' );
TabAD[110]  = new Array('TECTRM', ' transformation et mise en forme des matériaux' );
TabAD[111]  = new Array('TECTMA', ' transport - magasinage' );
TabAD[112]  = new Array('TECVMO', ' véhicules motorisés' );

TabSD       = new Array();
TabSD[0]    = new Array('', '-------------------- pas de disciplines -----------------' );

TabXD       = new Array();
TabXD[0]    = new Array('', '-------------------- pas de disciplines -----------------' );

function affich(intitule) {
    if ( (intitule == "E") || (intitule == "G") || (intitule == "P") || (intitule == "A") || (intitule == "S") || (intitule == "X") ){
		u = eval("Tab"+intitule+"N.length");
		for(i = 0; i < u; i++){
			moncode = eval("Tab"+ intitule +"N[i][0]");
			mavaleur = eval("Tab"+ intitule +"N[i][1]");
			nomOption = new Option( mavaleur, moncode, [], [])
			eval("document.Formul.Niveau.options[i] = nomOption")
		}
		document.Formul.Niveau.selectedIndex = 0;
		document.Formul.Niveau.length = u;

		u = eval("Tab"+intitule+"D.length");
		for(i = 0; i < u; i++) {
			moncode = eval("Tab"+ intitule +"D[i][0]");
			mavaleur = eval("Tab"+ intitule +"D[i][1]");
			nomOption = new Option( mavaleur, moncode, [], [])
			eval("document.Formul.Discipline.options[i] = nomOption")
		}
		document.Formul.Discipline.selectedIndex = 0;
		document.Formul.Discipline.length = u;
		afficheId('Disc');
		afficheId('Niv');
	} else {
		document.Formul.Degre.selectedIndex = 0;
		document.Formul.Niveau.options[0].text =		"-------- Niveaux d\'enseignement et de formation --------";
		document.Formul.Niveau.selectedIndex = 0;
		document.Formul.Niveau.length = 1;		
		document.Formul.Discipline.options[0].text =	"-------- Domaines disciplinaires et transversaux --------";
		document.Formul.Discipline.selectedIndex = 0;
		document.Formul.Discipline.length = 1;
		cacheId('Disc');
		cacheId('Niv');
	}
}

function selectInt(typ, Deg, intitule){
    if ((typ=="N" || typ=="D") && (Deg=="E" || Deg == "G" || Deg == "P" || Deg == "A" || Deg == "S"  || Deg == "X" ) && (intitule != "")){
        nameTab = "Tab"+ Deg + typ
        for(temp in eval(nameTab)){
            if (Trim(eval(nameTab)[temp][0]) == intitule){
                if (typ == "D"){
			        u = eval("Tab"+Deg+"D.length");
			        for(i = 0; i < u; i++) {
			            moncode = eval("Tab"+ Deg +"D[i][0]");
						mavaleur = eval("Tab"+ Deg +"D[i][1]");
			            nomOption = new Option( mavaleur, moncode, [], [])
						eval("document.Formul.Discipline.options[i] = nomOption")
					}
			        document.Formul.Discipline.selectedIndex = temp;
			        document.Formul.Discipline.length = u;
                } else {
					u = eval("Tab"+Deg+"N.length");
					for(i = 0; i < u; i++) {
			            moncode = eval("Tab"+ Deg +"N[i][0]");
			            mavaleur = eval("Tab"+ Deg +"N[i][1]");
			            nomOption = new Option( mavaleur, moncode, [], [])
						eval("document.Formul.Niveau.options[i] = nomOption")
					}
			        document.Formul.Niveau.selectedIndex = temp;
			        document.Formul.Niveau.length = u;
                }
            }
        }
    }
}

function Trim(string) { 
	return string.replace(/(^\s*)|(\s*$)/g,''); 
}

function Conserve(){
    degre = "";
    niveau = "";
    discipline = "";
    if (degre == ""){
    	affich('R');
    } else {
	    if (niveau == ""){
    		affich(degre);
		} else {
    		selectInt ('N', degre, niveau);
		}
	    if (discipline == ""){
			u = eval("Tab"+degre+"D.length");
			for(i = 0; i < u; i++) {
			    moncode = eval("Tab"+ degre +"D[i][0]");
				mavaleur = eval("Tab"+ degre +"D[i][1]");
				nomOption = new Option( mavaleur, moncode, [], [])
				eval("document.Formul.Discipline.options[i] = nomOption")
			}
			document.Formul.Discipline.selectedIndex = 0;
			document.Formul.Discipline.length = u;
		} else {
			selectInt ('D', degre, discipline);
		}
	}
}
//-->
</script>

<form method="get" action="<?php echo $CFG->wwwroot.'/resources/results.php' ?>" name="Formul">
<input type="hidden" name="repo" value="<?php p($repo) ?>" />
<input type="hidden" name="id" value="<?php p($courseid) ?>" />
<table width="100%" cellpadding="5">
    <tr valign="top">
        <td align="right" size="30%"><b>Recherche :</b></td>
        <td align="left" size="70%">
            <input type="text" name="search" value="" size="40" />
        </td>
    </tr>
    <input type="hidden" name="type" value="1"  /> <!-- tout Educasources -->
    <input type="hidden" name="tri" value="SCORE DESC" />
    <input type="hidden" name="start" value="0" />
    <!-- tr valign="top">
        <td align="right"><b>Tri :</b></td>
        <td align="left">
            <input type="text" name="tri" value="SCORE DESC" />
        </td>
    </tr>
    <tr valign="top">
        <td align="right"><b>Start :</b></td>
        <td align="left">
            <input type="text" name="start" value="" />
        </td>
    </tr -->
    <tr>
        <td colspan="2" align="center">Sélectionner : 1. le degré </td>
    </tr>
    <tr>
        <td align="center" colspan="2">
            <select name="Degre" class="NivDisc" onChange="affich(this.value);">
                <option value="" selected="selected">------------------------- degrés ------------------------</option>
                <option value="E" title="Enseignement primaire" >primaire</option>
                <option value="G" title="Enseignement primaire" >secondaire - voie générale et technologique</option>
        
        		<option value="P" title="Enseignement primaire" >secondaire - voie professionnelle</option>
                <option value="A" title="Enseignement secondaire" >secondaire - Enseignement agricole</option>
                <option value="S" title="Enseignement supérieur" >supérieur</option>
                <option value="X" title="Enseignement primaire" >spécialisé</option>
            </select>
        </td>
    </tr>
    <tr>
        <td colspan="2" align="center">Sélectionner : 2. le niveau </td>
    </tr>
        <td align="center" colspan="2">
            <select name="Niveau" class="NivDisc" id="Niv">
                <option value="" selected="selected">-------- Niveaux d'enseignement et de formation --------</option>

                <option value="EP">école&nbsp;primaire</option>
                <option value="EPEM">&nbsp;école&nbsp;maternelle</option>
                <option value="EPEMC1">&nbsp;&nbsp;cycle&nbsp;1</option>
                <option value="EPEMC1PS">&nbsp;&nbsp;&nbsp;petite&nbsp;section</option>
                <option value="EPEMC1MS">&nbsp;&nbsp;&nbsp;moyenne&nbsp;section</option>
                <option value="EPEMC2">&nbsp;&nbsp;cycle&nbsp;2&nbsp;-&nbsp;grande&nbsp;section</option>
                <option value="EPEE">&nbsp;école&nbsp;élémentaire</option>
                <option value="EPEEC2">&nbsp;&nbsp;cycle&nbsp;2</option>
                <option value="EPEEC2CP">&nbsp;&nbsp;&nbsp;CP&nbsp;cours&nbsp;préparatoire</option>
                <option value="EPEEC2E1">&nbsp;&nbsp;&nbsp;CE1&nbsp;cours&nbsp;élémentaire&nbsp;1re&nbsp;année</option>
                <option value="EPEEC3">&nbsp;&nbsp;cycle&nbsp;3</option>
                <option value="EPEEC3E2">&nbsp;&nbsp;&nbsp;CE2&nbsp;cours&nbsp;élémentaire&nbsp;2e&nbsp;année</option>
                <option value="EPEEC3M1">&nbsp;&nbsp;&nbsp;CM1&nbsp;cours&nbsp;moyen&nbsp;1re&nbsp;année</option>
                <option value="EPEEC3M2">&nbsp;&nbsp;&nbsp;CM2&nbsp;cours&nbsp;moyen&nbsp;2e&nbsp;année</option>

                <option value="GT">enseignement&nbsp;secondaire&nbsp;-&nbsp;voies&nbsp;générale&nbsp;et&nbsp;technologique</option>
                <option value="GTCO">&nbsp;collège</option>
                <option value="GTCO6E">&nbsp;&nbsp;6e</option>
                <option value="GTCO5E">&nbsp;&nbsp;5e</option>
                <option value="GTCO4E">&nbsp;&nbsp;4e</option>
                <option value="GTCO3E">&nbsp;&nbsp;3e</option>
                <option value="GTLY">&nbsp;lycée</option>
                <option value="GTLY2N">&nbsp;&nbsp;2de</option>
                <option value="GTLY1R">&nbsp;&nbsp;1re</option>
                <option value="GTLYTR">&nbsp;&nbsp;terminale</option>
                <option value="GTLYBT">&nbsp;&nbsp;BT&nbsp;brevet&nbsp;de&nbsp;technicien</option>

                <option value="VP">enseignement&nbsp;secondaire&nbsp;-&nbsp;voie&nbsp;professionnelle</option>
                <option value="VPCO">&nbsp;collège</option>
                <option value="VPCO4P">&nbsp;&nbsp;4e&nbsp;professionnelle</option>
                <option value="VPCO3P">&nbsp;&nbsp;3e&nbsp;professionnelle</option>
                <option value="VPLY">&nbsp;lycée</option>
                <option value="VPLY2P">&nbsp;&nbsp;2de&nbsp;professionnelle</option>
                <option value="VPLYTB">&nbsp;&nbsp;terminale&nbsp;BEP</option>
                <option value="VPLY1P">&nbsp;&nbsp;1re&nbsp;bac&nbsp;pro</option>
                <option value="VPLYTP">&nbsp;&nbsp;terminale&nbsp;bac&nbsp;pro</option>
                <option value="VPLYCA">&nbsp;&nbsp;CAP&nbsp;certificat&nbsp;d'aptitude&nbsp;professionnelle</option>

                <option value="PP">&nbsp;&nbsp;BAC&nbsp;PRO</option>

                <option value="VPLYBE">&nbsp;&nbsp;BEP&nbsp;brevet&nbsp;d'études&nbsp;professionnelles</option>
                <option value="VPLYBM">&nbsp;&nbsp;BMA&nbsp;brevet&nbsp;des&nbsp;métiers&nbsp;d'art</option>
                <option value="VPLYBP">&nbsp;&nbsp;BP&nbsp;brevet&nbsp;professionnel</option>
                <option value="VPLYBT">&nbsp;&nbsp;BTM&nbsp;brevet&nbsp;technique&nbsp;des&nbsp;métiers</option>
                <option value="VPLYFC">&nbsp;&nbsp;FCIL&nbsp;formation&nbsp;complémentaire&nbsp;d'initiative</option>
                <option value="VPLYMC">&nbsp;&nbsp;MC&nbsp;mention&nbsp;complémentaire</option>

                <option value="ES">enseignement&nbsp;supérieur</option>
                <option value="ESCP">&nbsp;CPGE&nbsp;classe&nbsp;préparatoire&nbsp;aux&nbsp;grandes&nbsp;écoles</option>
                <option value="ESBT">&nbsp;BTS&nbsp;brevet&nbsp;de&nbsp;technicien&nbsp;supérieur</option>
                <option value="ESDA">&nbsp;diplômes&nbsp;assimilés&nbsp;aux&nbsp;BTS</option>
                <option value="ESAD">&nbsp;autres&nbsp;diplômes&nbsp;de&nbsp;niveau&nbsp;3</option>

                <option value="ESDG">&nbsp;DEUG&nbsp;diplôme&nbsp;d'études&nbsp;universitaires&nbsp;générales</option>
                <option value="ESDT">&nbsp;DEUST&nbsp;diplôme&nbsp;d'études&nbsp;universitaires&nbsp;scientifiques&nbsp;et&nbsp;techniques</option>
                <option value="ESDM">&nbsp;DMA&nbsp;diplôme&nbsp;des&nbsp;métiers&nbsp;d'art</option>
                <option value="ESAA">&nbsp;DSAA&nbsp;diplôme&nbsp;supérieur&nbsp;d'arts&nbsp;appliqués</option>
                <option value="ESDU">&nbsp;DUT&nbsp;diplôme&nbsp;universitaire&nbsp;de&nbsp;technologie</option>
                <option value="ESLI">&nbsp;licence</option>
                <option value="ESLP">&nbsp;licence&nbsp;professionnelle</option>

                <option value="ESMA">&nbsp;maîtrise</option>
                <option value="ESMS">&nbsp;master</option>
                <option value="ESDE">&nbsp;DEA&nbsp;diplôme&nbsp;d'études&nbsp;approfondies</option>
                <option value="ESDS">&nbsp;DESS&nbsp;diplôme&nbsp;d'études&nbsp;supérieures&nbsp;spécialisées</option>
                <option value="ESDO">&nbsp;doctorat</option>

                <option value="EA">enseignement&nbsp;agricole</option>
                <option value="EAGT">&nbsp;enseignement&nbsp;agricole&nbsp;secondaire&nbsp;-&nbsp;voies&nbsp;générale&nbsp;et&nbsp;technologique</option>
                <option value="EAGTCO">&nbsp;&nbsp;collège</option>
                <option value="EAGTCO4E">&nbsp;&nbsp;&nbsp;4e&nbsp;enseignement&nbsp;agricole</option>
                <option value="EAGTCO3E">&nbsp;&nbsp;&nbsp;3e&nbsp;enseignement&nbsp;agricole</option>
                <option value="EAGTCOCL">&nbsp;&nbsp;&nbsp;CLIPA&nbsp;classe&nbsp;d'initiation&nbsp;préprofessionnelle&nbsp;en&nbsp;alternance</option>
                <option value="EAGTCOA1">&nbsp;&nbsp;&nbsp;CPA&nbsp;classe&nbsp;préparatoire&nbsp;à&nbsp;l'apprentissage&nbsp;-&nbsp;1re&nbsp;année</option>
                <option value="EAGTCOA2">&nbsp;&nbsp;&nbsp;CPA&nbsp;classe&nbsp;préparatoire&nbsp;à&nbsp;l'apprentissage&nbsp;-&nbsp;2e&nbsp;année</option>

                <option value="EAGTLY">&nbsp;&nbsp;lycée</option>
                <option value="EAGTLY2N">&nbsp;&nbsp;&nbsp;2de</option>
                <option value="EAGTLY1R">&nbsp;&nbsp;&nbsp;1re</option>
                <option value="EAGTLYTR">&nbsp;&nbsp;&nbsp;terminale</option>

                <option value="EAGTLYT1">&nbsp;&nbsp;&nbsp;BTA&nbsp;brevet&nbsp;de&nbsp;technicien&nbsp;agricole&nbsp;-&nbsp;1re&nbsp;année</option>
                <option value="EAGTLYTT">&nbsp;&nbsp;&nbsp;BTA&nbsp;brevet&nbsp;de&nbsp;technicien&nbsp;agricole&nbsp;-&nbsp;terminale</option>

                <option value="EAVP">&nbsp;enseignement&nbsp;agricole&nbsp;secondaire&nbsp;-&nbsp;voie&nbsp;professionnelle</option>
                <option value="EAVPC1">&nbsp;&nbsp;CAPA&nbsp;certificat&nbsp;d'aptitude&nbsp;professionnelle&nbsp;agricole&nbsp;-&nbsp;1re&nbsp;année</option>
                <option value="EAVPC2">&nbsp;&nbsp;CAPA&nbsp;certificat&nbsp;d'aptitude&nbsp;professionnelle&nbsp;agricole&nbsp;-&nbsp;2e&nbsp;année</option>

                <option value="EAVPB1">&nbsp;&nbsp;BEPA&nbsp;brevet&nbsp;d'études&nbsp;professionnelles&nbsp;agricoles&nbsp;-&nbsp;1re&nbsp;année</option>
                <option value="EAVPB2">&nbsp;&nbsp;BEPA&nbsp;brevet&nbsp;d'études&nbsp;professionnelles&nbsp;agricoles&nbsp;-&nbsp;2e&nbsp;année</option>

                <option value="EAVP1B">&nbsp;&nbsp;1re&nbsp;bac&nbsp;pro</option>
                <option value="EAVPTB">&nbsp;&nbsp;terminale&nbsp;bac&nbsp;pro</option>
                <option value="EAS1">&nbsp;enseignement&nbsp;agricole&nbsp;supérieur&nbsp;-&nbsp;1er&nbsp;cycle</option>
                <option value="EAS1CP">&nbsp;&nbsp;CPGE&nbsp;classe&nbsp;préparatoire&nbsp;aux&nbsp;grandes&nbsp;écoles</option>

                <option value="EAS1CPBI">&nbsp;&nbsp;&nbsp;CP&nbsp;BCPST&nbsp;classe&nbsp;préparatoire&nbsp;biologie,&nbsp;chimie,&nbsp;physique&nbsp;et&nbsp;sciences&nbsp;de&nbsp;la&nbsp;Terre</option>
                <option value="EAS1CPDI">&nbsp;&nbsp;&nbsp;CP&nbsp;post&nbsp;BTSA/BTS/DUT&nbsp;classe&nbsp;préparatoire&nbsp;postérieure&nbsp;au&nbsp;BTS&nbsp;agricole,&nbsp;au&nbsp;BTS,&nbsp;au&nbsp;DUT</option>

                <option value="EAS1B1">&nbsp;&nbsp;BTSA&nbsp;brevet&nbsp;de&nbsp;technicien&nbsp;supérieur&nbsp;agricole&nbsp;-&nbsp;1re&nbsp;année</option>
                <option value="EAS1B2">&nbsp;&nbsp;BTSA&nbsp;brevet&nbsp;de&nbsp;technicien&nbsp;supérieur&nbsp;agricole&nbsp;-&nbsp;2e&nbsp;année</option>

                <option value="EAS1LI">&nbsp;&nbsp;classe&nbsp;de&nbsp;pré-licence</option>
                <option value="EAS2">&nbsp;enseignement&nbsp;agricole&nbsp;supérieur&nbsp;-&nbsp;2e&nbsp;cycle</option>
                <option value="EAS2AA">&nbsp;&nbsp;DAA&nbsp;diplôme&nbsp;d'agronomie&nbsp;approfondie</option>
                <option value="EAS2AG">&nbsp;&nbsp;DAG&nbsp;diplôme&nbsp;d'agronomie&nbsp;générale</option>

                <option value="EAS2AR">&nbsp;&nbsp;DEFA&nbsp;diplôme&nbsp;d'études&nbsp;fondamentales&nbsp;en&nbsp;architecture</option>
                <option value="EAS2IA">&nbsp;&nbsp;DEGIA&nbsp;diplôme&nbsp;d'études&nbsp;générales&nbsp;en&nbsp;industries&nbsp;agricoles&nbsp;et&nbsp;alimentaires</option>

                <option value="EAS2EV">&nbsp;&nbsp;DESV&nbsp;diplôme&nbsp;d'études&nbsp;spécialisées&nbsp;vétérinaires</option>
                <option value="EAS2RT">&nbsp;&nbsp;DRT&nbsp;diplôme&nbsp;de&nbsp;recherche&nbsp;technologique</option>
                <option value="EAS2HA">&nbsp;&nbsp;DSHA&nbsp;diplôme&nbsp;de&nbsp;sciences&nbsp;horticoles&nbsp;approfondies</option>

                <option value="EAS2TA">&nbsp;&nbsp;DTAA&nbsp;diplôme&nbsp;de&nbsp;technologie&nbsp;agricole&nbsp;approfondie</option>
                <option value="EAS2LP">&nbsp;&nbsp;licence&nbsp;professionnelle</option>
                <option value="AI">adaptation&nbsp;scolaire&nbsp;et&nbsp;scolarisation&nbsp;des&nbsp;élèves&nbsp;handicapés</option>

                <option value="AI1D">&nbsp;1er&nbsp;degré</option>
                <option value="AI1DCS">&nbsp;&nbsp;CLIS&nbsp;classe&nbsp;d'intégration&nbsp;scolaire</option>
                <option value="AI1DCN">&nbsp;&nbsp;CLIN&nbsp;classe&nbsp;d'initiation</option>
                <option value="AI2D">&nbsp;2d&nbsp;degré</option>
                <option value="AI2D4A">&nbsp;&nbsp;4e&nbsp;aide&nbsp;et&nbsp;soutien</option>
                
                <option value="AI2D3I">&nbsp;&nbsp;3e&nbsp;insertion</option>
                <option value="AI2DSE">&nbsp;&nbsp;SEGPA&nbsp;section&nbsp;d'enseignement&nbsp;général&nbsp;et&nbsp;professionnel&nbsp;adapté</option>
                <option value="AI2DUP">&nbsp;&nbsp;UPI&nbsp;unité&nbsp;pédagogique&nbsp;d'intégration</option>
                <option value="AI2DCA">&nbsp;&nbsp;CLA&nbsp;classe&nbsp;d'accueil</option>
                
                <option value="AI2DER">&nbsp;&nbsp;EREA&nbsp;école&nbsp;régionale&nbsp;d'enseignement&nbsp;adapté</option>
                <option value="PE">formation&nbsp;des&nbsp;personnels&nbsp;de&nbsp;l'éducation&nbsp;nationale</option>
                <option value="PEAP">&nbsp;formation&nbsp;des&nbsp;autres&nbsp;personnels</option>
                
                <option value="PEDO">&nbsp;formation&nbsp;des&nbsp;documentalistes</option>
                <option value="PEPA">&nbsp;formation&nbsp;des&nbsp;personnels&nbsp;administratifs</option>
                <option value="PEEO">&nbsp;formation&nbsp;des&nbsp;personnels&nbsp;d'éducation&nbsp;et&nbsp;d'orientation</option>
                <option value="PEDI">&nbsp;formation&nbsp;des&nbsp;personnels&nbsp;de&nbsp;direction&nbsp;et&nbsp;d'inspection</option>                
                <option value="PEPE">&nbsp;formation&nbsp;des&nbsp;professeurs&nbsp;des&nbsp;écoles</option>
                <option value="PEPL">&nbsp;formation&nbsp;des&nbsp;professeurs&nbsp;des&nbsp;lycées&nbsp;et&nbsp;collèges</option>
                <option value="PA">formation&nbsp;professionnelle&nbsp;des&nbsp;adultes</option>
                <option value="FP">préparation&nbsp;aux&nbsp;concours&nbsp;de&nbsp;la&nbsp;fonction&nbsp;publique</option>
                <option value="FPCC">&nbsp;catégorie&nbsp;C</option>
                <option value="FPCB">&nbsp;catégorie&nbsp;B</option>
                <option value="FPCA">&nbsp;catégorie&nbsp;A</option>

            </select>
        </td>
    <tr>
        <td colspan="2" align="center">Sélectionner : 3. la discipline</td>
    </tr>
        <td align="center" colspan="2">
            <select name="Discipline" class="NivDisc" id="Disc">
                <option value="" selected="selected">-------- Domaines disciplinaires et transversaux --------</option>
                <option value="PRI">domaines&nbsp;de&nbsp;l'école&nbsp;primaire</option>

                <option value="PRIEPS">&nbsp;agir&nbsp;et&nbsp;s'exprimer&nbsp;avec&nbsp;son&nbsp;corps&nbsp;-&nbsp;EPS</option>
                <option value="PRIB2I">&nbsp;B2i&nbsp;brevet&nbsp;informatique&nbsp;et&nbsp;internet&nbsp;(école)</option>
                <option value="PRIEHU">&nbsp;découvrir&nbsp;le&nbsp;monde&nbsp;-&nbsp;éducation&nbsp;humaine</option>

                <option value="PRIEHUGEO">&nbsp;&nbsp;géographie </option>
                <option value="PRIEHUHIS">&nbsp;&nbsp;histoire </option>
                <option value="PRISCI">&nbsp;découvrir&nbsp;le&nbsp;monde&nbsp;-&nbsp;éducation&nbsp;scientifique</option>
                <option value="PRISCIMAT">&nbsp;&nbsp;mathématiques</option>
                <option value="PRISCISET">&nbsp;&nbsp;sciences&nbsp;expérimentales&nbsp;et&nbsp;technologie</option>

                <option value="PRIART">&nbsp;la&nbsp;sensibilité,&nbsp;l'imagination,&nbsp;la&nbsp;création&nbsp;-&nbsp;éducation&nbsp;artistique</option>
                <option value="PRIARTMUS">&nbsp;&nbsp;la&nbsp;voix&nbsp;et&nbsp;l'écoute&nbsp;-&nbsp;éducation&nbsp;musicale</option>
                <option value="PRIARTVIS">&nbsp;&nbsp;le&nbsp;regard&nbsp;et&nbsp;le&nbsp;geste&nbsp;-&nbsp;arts&nbsp;visuels</option>

                <option value="PRILAN"> langage&nbsp;et&nbsp;langue&nbsp;française&nbsp;-&nbsp;éducation&nbsp;littéraire</option>                
                <option value="PRILANECR">&nbsp&nbsp;écriture </option>
                <option value="PRILANLEC">&nbsp&nbsp;lecture </option>
                <option value="PRILANORA">&nbsp&nbsp;maîtrise&nbsp;du&nbsp;langage&nbsp;oral</option>

                <option value="PRILER">&nbsp;langue&nbsp;étrangère&nbsp;ou&nbsp;régionale</option>
                <option value="PRIVIV">&nbsp;vivre&nbsp;ensemble&nbsp;-&nbsp;éducation&nbsp;civique</option>
                
                <option value="SEC">disciplines&nbsp;générales&nbsp;et&nbsp;enseignements&nbsp;de&nbsp;détermination&nbsp;de&nbsp;l'enseignement&nbsp;secondaire</option>
                <option value="SECAAP">&nbsp;arts&nbsp;appliqués&nbsp; </option>
                <option value="SECACI">&nbsp;arts&nbsp;du&nbsp;cirque </option>
                <option value="SECAPL">&nbsp;arts&nbsp;plastiques </option>
                <option value="SECBLP">&nbsp;BLP&nbsp;biologie&nbsp;de&nbsp;laboratoire&nbsp;et&nbsp;paramédicale</option>
                <option value="SECCAV">&nbsp;cinéma&nbsp;et&nbsp;audiovisuel</option>
                <option value="SECCRE">&nbsp;création-design </option>
                <option value="SECCUL">&nbsp;culture-design </option>                
                <option value="SECDAN">&nbsp;danse  </option>
                <option value="SECECV">&nbsp;éducation&nbsp;civique&nbsp; </option>
                <option value="SECEPS">&nbsp;EPS&nbsp;éducation&nbsp;physique&nbsp;et&nbsp;sportive</option>
                <option value="SECFLE">&nbsp;FLE&nbsp;français&nbsp;langue&nbsp;étrangère</option>                
                <option value="SECFRA">&nbsp;français&nbsp;  </option>
                <option value="SECGEO">&nbsp;géographie </option>
                <option value="SECHIS">&nbsp;histoire  </option>
                <option value="SECHAR">&nbsp;histoire&nbsp;des&nbsp;arts </option>
                <option value="SECIGC">&nbsp;IGC&nbsp;informatique&nbsp;de&nbsp;gestion&nbsp;et&nbsp;de&nbsp;communication</option>                
                <option value="SECINF">&nbsp;informatique </option>
                <option value="SECISI">&nbsp;ISI&nbsp;initiation&nbsp;aux&nbsp;sciences&nbsp;de&nbsp;l'ingénieur</option>
                <option value="SECISP">&nbsp;ISP&nbsp;informatique&nbsp;et&nbsp;systèmes&nbsp;de&nbsp;production</option>

                <option value="SECLAN">&nbsp;langue&nbsp;ancienne </option>                
                <option value="SECLANGRA">&nbsp;&nbsp;grec&nbsp;ancien </option>
                <option value="SECLANLAT">&nbsp;&nbsp;latin  </option>

                <option value="SECLVE">&nbsp;langue&nbsp;vivante&nbsp;étrangère</option>
                <option value="SECLVEALB">&nbsp;&nbsp;albanais </option>
                <option value="SECLVEALL">&nbsp;&nbsp;allemand </option>
                <option value="SECLVEAMH">&nbsp;&nbsp;amharique </option>
                <option value="SECLVEANG">&nbsp;&nbsp;anglais </option>
                
                <option value="SECLVEARA">&nbsp;&nbsp;arabe </option>
                <option value="SECLVEARM">&nbsp;&nbsp;arménien </option>
                <option value="SECLVEBAM">&nbsp;&nbsp;bambara </option>
                <option value="SECLVEBER">&nbsp;&nbsp;berbère </option>
                <option value="SECLVEBUL">&nbsp;&nbsp;bulgare </option>
                <option value="SECLVECAM">&nbsp;&nbsp;cambodgien</option>
                <option value="SECLVECHI">&nbsp;&nbsp;chinois </option>
                <option value="SECLVECOR">&nbsp;&nbsp;coréen </option>
                <option value="SECLVECRO">&nbsp;&nbsp;croate </option>                
                <option value="SECLVEDAN">&nbsp;&nbsp;danois </option>
                <option value="SECLVEESP">&nbsp;&nbsp;espagnol </option>
                <option value="SECLVEFIN">&nbsp;&nbsp;finnois </option>
                <option value="SECLVEGRM">&nbsp;&nbsp;grec&nbsp;moderne</option>
                <option value="SECLVEHAO">&nbsp;&nbsp;haoussa </option>
                <option value="SECLVEHEB">&nbsp;&nbsp;hébreu&nbsp;moderne</option>
                <option value="SECLVEHIN">&nbsp;&nbsp;hindi  </option>
                <option value="SECLVEHON">&nbsp;&nbsp;hongrois </option>
                <option value="SECLVEIND">&nbsp;&nbsp;indonésien </option>
                <option value="SECLVEISL">&nbsp;&nbsp;islandais </option>
                <option value="SECLVEITA">&nbsp;&nbsp;italien </option>
                <option value="SECLVEJAP">&nbsp;&nbsp;japonais </option>
                <option value="SECLVELAO">&nbsp;&nbsp;laotien </option>
                <option value="SECLVEMAC">&nbsp;&nbsp;macédonien</option>
                <option value="SECLVEMAL">&nbsp;&nbsp;malaisien </option>
                <option value="SECLVEMAD">&nbsp;&nbsp;malgache </option>
                <option value="SECLVENEE">&nbsp;&nbsp;néerlandais </option>                
                <option value="SECLVENOR">&nbsp;&nbsp;norvégien </option>
                <option value="SECLVEPER">&nbsp;&nbsp;persan </option>
                <option value="SECLVEPEU">&nbsp;&nbsp;peul  </option>
                <option value="SECLVEPOL">&nbsp;&nbsp;polonais </option>
                <option value="SECLVEPOR">&nbsp;&nbsp;portugais </option>
                <option value="SECLVEROU">&nbsp;&nbsp;roumain </option>
                <option value="SECLVERUS">&nbsp;&nbsp;russe </option>
                <option value="SECLVESER">&nbsp;&nbsp;serbe </option>
                <option value="SECLVESLA">&nbsp;&nbsp;slovaque </option>                
                <option value="SECLVESLO">&nbsp;&nbsp;slovène </option>
                <option value="SECLVESUE">&nbsp;&nbsp;suédois </option>
                <option value="SECLVESWA">&nbsp;&nbsp;swahili </option>
                <option value="SECLVETAM">&nbsp;&nbsp;tamoul </option>
                <option value="SECLVETCH">&nbsp;&nbsp;tchèque </option>
                <option value="SECLVETUR">&nbsp;&nbsp;turc  </option>
                <option value="SECLVEVIE">&nbsp;&nbsp;vietnamien </option>

                <option value="SECLVR"> langue&nbsp;vivante&nbsp;régionale</option>                
                <option value="SECLVRBAS">&nbsp;&nbsp;basque </option>
                <option value="SECLVRBRE">&nbsp;&nbsp;breton </option>
                <option value="SECLVRCAT">&nbsp;&nbsp;catalan </option>
                <option value="SECLVRCOR">&nbsp;&nbsp;corse </option>
                <option value="SECLVRCRE">&nbsp;&nbsp;créole </option>
                <option value="SECLVRGAL">&nbsp;&nbsp;gallo </option>
                <option value="SECLVRLME">&nbsp;&nbsp;langues&nbsp;mélanésiennes</option>
                <option value="SECLVRLRA">&nbsp;&nbsp;langues&nbsp;régionales&nbsp;d'Alsace</option>
                <option value="SECLVRLRM">&nbsp;&nbsp;langues&nbsp;régionales&nbsp;des&nbsp;pays&nbsp;mosellans</option>
                <option value="SECLVROCC">&nbsp;&nbsp;occitan&nbsp;-&nbsp;langue&nbsp;d'oc</option>
                <option value="SECLVRTAH">&nbsp;&nbsp;tahitien </option>

                <option value="SECMAT">&nbsp;mathématiques </option>
                <option value="SECMPI">&nbsp;MPI&nbsp;mesures&nbsp;physiques&nbsp;et&nbsp;informatique</option>                
                <option value="SECMUS">&nbsp;musique  </option>
                <option value="SECPCL">&nbsp;PCL&nbsp;physique&nbsp;et&nbsp;chimie&nbsp;de&nbsp;laboratoire</option>
                <option value="SECPHI">&nbsp;philosophie </option>
                <option value="SECPHY">&nbsp;physique-chimie&nbsp; </option>
                <option value="SECSIN">&nbsp;sciences&nbsp;de&nbsp;l'ingénieur</option>
                <option value="SECSES">&nbsp;SES&nbsp;sciences&nbsp;économiques&nbsp;et&nbsp;sociales</option>
                <option value="SECSMS">&nbsp;SMS&nbsp;sciences&nbsp;médico-sociales</option>
                <option value="SECSVT">&nbsp;SVT&nbsp;sciences&nbsp;de&nbsp;la&nbsp;vie&nbsp;et&nbsp;de&nbsp;la&nbsp;Terre</option>
                
                <option value="SECTEC">&nbsp;technologie </option>
                <option value="SECTHE">&nbsp;théâtre&nbsp;-&nbsp;expression&nbsp;dramatique</option>

                <option value="TEC">domaines&nbsp;et&nbsp;spécialités&nbsp;de&nbsp;l'enseignement&nbsp;technologique,&nbsp;professionnel,&nbsp;agricole</option>
                <option value="TECAGR">&nbsp;agro-alimentaire&nbsp;-&nbsp;alimentation</option>                
                <option value="TECAGE">&nbsp;agroéquipement&nbsp; </option>
                <option value="TECAGO">&nbsp;agronomie&nbsp; </option>
                <option value="TECAME">&nbsp;aménagement&nbsp;de&nbsp;l'espace</option>
                <option value="TECART">&nbsp;artisanat&nbsp;d'art&nbsp;-&nbsp;métiers&nbsp;d'art&nbsp;-&nbsp;arts&nbsp;appliqués</option>                
                <option value="TECAUT">&nbsp;automatisme&nbsp;-&nbsp;automatique</option>
                <option value="TECBAN">&nbsp;banque,&nbsp;assurance,&nbsp;immobilier</option>
                <option value="TECBTP">&nbsp;bâtiment&nbsp;-&nbsp;travaux&nbsp;publics</option>
                <option value="TECBLG">&nbsp;biologie&nbsp;  </option>
                <option value="TECBIO">&nbsp;biotechnologies&nbsp;-&nbsp;bio-industries&nbsp;-&nbsp;métiers&nbsp;de&nbsp;laboratoire</option>                
                <option value="TECBOI">&nbsp;bois&nbsp;et&nbsp;matériaux&nbsp;associés</option>
                <option value="TECCOI">&nbsp;coiffure&nbsp;-&nbsp;esthétique</option>
                <option value="TECCOV">&nbsp;commerce&nbsp;-&nbsp;vente</option>
                <option value="TECCOM">&nbsp;comptabilité,&nbsp;finances</option>
                <option value="TECCPI">&nbsp;conception&nbsp;et&nbsp;définition&nbsp;de&nbsp;produits&nbsp;industriels</option>                
                <option value="TECDRO">&nbsp;droit&nbsp;-&nbsp;économie </option>
                <option value="TECECG">&nbsp;économie&nbsp;générale</option>
                <option value="TECESO">&nbsp;éducation&nbsp;socioculturelle</option>
                <option value="TECEIR">&nbsp;électronique&nbsp;-&nbsp;informatique&nbsp;industrielle&nbsp;et&nbsp;réseaux</option>
                <option value="TECEEN">&nbsp;électrotechnique&nbsp;et&nbsp;énergie</option>
                <option value="TECGRH">&nbsp;gestion&nbsp;des&nbsp;ressources&nbsp;humaines</option>
                <option value="TECGES">&nbsp;gestion&nbsp;des&nbsp;systèmes&nbsp;d'information</option>
                <option value="TECEQU">&nbsp;hippologie&nbsp;-&nbsp;équitation</option>                
                <option value="TECHRT">&nbsp;hôtellerie&nbsp;-&nbsp;restauration&nbsp;-&nbsp;tourisme</option>
                <option value="TECHES">&nbsp;hygiène,&nbsp;environnement&nbsp;et&nbsp;sécurité</option>
                <option value="TECIGI">&nbsp;industries&nbsp;graphiques&nbsp;-&nbsp;imprimerie</option>
                <option value="TECMIM">&nbsp;maintenances&nbsp;industrielle&nbsp;et&nbsp;mécanique</option>
                <option value="TECMAN">&nbsp;management&nbsp; </option>
                <option value="TECMOD">&nbsp;matériaux&nbsp;souples&nbsp;-&nbsp;métiers&nbsp;de&nbsp;la&nbsp;mode</option>
                <option value="TECMER">&nbsp;mercatique&nbsp; </option>
                <option value="TECPRO">&nbsp;production&nbsp;agricole</option>                
                <option value="TECANI">&nbsp;production&nbsp;animale</option>
                <option value="TECAQU">&nbsp;production&nbsp;aquacole</option>
                <option value="TECFOR">&nbsp;production&nbsp;forestière</option>
                <option value="TECVEG">&nbsp;production&nbsp;végétale</option>
                <option value="TECPME">&nbsp;production&nbsp;mécanique</option>
                <option value="TECSMS">&nbsp;santé&nbsp;-&nbsp;paramédical&nbsp;et&nbsp;social</option>                
                <option value="TECSEC">&nbsp;secrétariat  </option>
                <option value="TECSPO">&nbsp;sport&nbsp;-&nbsp;animation&nbsp;culturelle</option>
                <option value="TECSME">&nbsp;structures&nbsp;métalliques</option>
                <option value="TECTIS">&nbsp;techniques&nbsp;de&nbsp;l'image&nbsp;et&nbsp;du&nbsp;son&nbsp;-&nbsp;arts&nbsp;du&nbsp;spectacle</option>                
                <option value="TECTRM">&nbsp;transformation&nbsp;et&nbsp;mise&nbsp;en&nbsp;forme&nbsp;des&nbsp;matériaux</option>
                <option value="TECTMA">&nbsp;transport&nbsp;-&nbsp;magasinage</option>
                <option value="TECVMO">&nbsp;véhicules&nbsp;motorisés</option>

                <option value="DIS">actions&nbsp;éducatives&nbsp;et&nbsp;dispositifs&nbsp;pédagogiques</option>
                
                <option value="DISAPA">&nbsp;accueil&nbsp;des&nbsp;primo-arrivants</option>
                <option value="DISINT">&nbsp;actions&nbsp;internationales</option>
                <option value="DISTPR">&nbsp;aide&nbsp;au&nbsp;travail&nbsp;personnel&nbsp;et&nbsp;à&nbsp;la&nbsp;réussite</option>
                <option value="DISDIS">&nbsp;dispositifs&nbsp;relais&nbsp; </option>                
                <option value="DISECO">&nbsp;école&nbsp;ouverte </option>
                <option value="DISERL">&nbsp;enseignement&nbsp;renforcé&nbsp;des&nbsp;langues</option>

                <option value="DISTRA">&nbsp;formations&nbsp;transversales</option>
                <option value="DISTRACST">&nbsp;&nbsp;culture&nbsp;scientifique&nbsp;et&nbsp;technique</option>
                <option value="DISTRAEED">&nbsp;&nbsp;EEDD&nbsp;éducation&nbsp;à&nbsp;l'environnement&nbsp;pour&nbsp;un&nbsp;développement&nbsp;durable</option>                
                <option value="DISTRAEAC">&nbsp;&nbsp;éducation&nbsp;artistique&nbsp;et&nbsp;culturelle</option>
                <option value="DISTRAIMA">&nbsp;&nbsp;éducation&nbsp;à&nbsp;l'image</option>
                <option value="DISTRAORI">&nbsp;&nbsp;éducation&nbsp;à&nbsp;l'orientation&nbsp;et&nbsp;au&nbsp;choix</option>
                <option value="DISTRACIT">&nbsp;&nbsp;éducation&nbsp;à&nbsp;la&nbsp;citoyenneté</option>                
                <option value="DISTRASAN">&nbsp;&nbsp;éducation&nbsp;à&nbsp;la&nbsp;santé</option>
                <option value="DISTRASEC">&nbsp;&nbsp;éducation&nbsp;à&nbsp;la&nbsp;sécurité</option>
                <option value="DISTRAMED">&nbsp;&nbsp;éducation&nbsp;aux&nbsp;médias</option>
                <option value="DISTRAFMI">&nbsp;&nbsp;formation&nbsp;à&nbsp;la&nbsp;maîtrise&nbsp;de&nbsp;l'information</option>
                
                <option value="DISLIC">&nbsp;liaison&nbsp;inter-cycles&nbsp;et&nbsp;inter-degrés</option>
                <option value="DISPET">&nbsp;projets&nbsp;d'élèves&nbsp;transversaux</option>
                <option value="DISREE">&nbsp;relation&nbsp;école-entreprise</option>
                <option value="DISSEI">&nbsp;section&nbsp;européenne&nbsp;ou&nbsp;internationale</option>                
                <option value="DISSCD">&nbsp;séjour&nbsp;et&nbsp;classe&nbsp;de&nbsp;découvertes</option>

                <option value="DISTIC">&nbsp;TIC&nbsp;technologies&nbsp;de&nbsp;l'information&nbsp;et&nbsp;de&nbsp;la&nbsp;communication</option>
                <option value="DISTICB2I">&nbsp;&nbsp;B2i&nbsp;brevet&nbsp;informatique&nbsp;et&nbsp;internet</option>                
                <option value="DISTICC2I">&nbsp;&nbsp;C2i&nbsp;certificat&nbsp;informatique&nbsp;et&nbsp;internet</option>

                <option value="INF">sciences&nbsp;de&nbsp;l'information</option>
                <option value="SED">sciences&nbsp;de&nbsp;l'éducation&nbsp; </option>
                <option value="SEDHDE">&nbsp;histoire&nbsp;de&nbsp;l'éducation</option>
                
                <option value="SEDPED">&nbsp;pédagogie&nbsp;générale,&nbsp;courants&nbsp;pédagogiques</option>
                <option value="SEDPHI">&nbsp;philosophie&nbsp;de&nbsp;l'éducation</option>
                <option value="SEDPSY">&nbsp;psychologie&nbsp;de&nbsp;l'éducation</option>
                <option value="SEDSOC">&nbsp;sociologie&nbsp;de&nbsp;l'éducation</option>
                
                <option value="EVS">organisation&nbsp;de&nbsp;l'enseignement&nbsp;et&nbsp;vie&nbsp;scolaire</option>
                <option value="EVSSYS">&nbsp;pilotage&nbsp;et&nbsp;gestion&nbsp;du&nbsp;système&nbsp;éducatif</option>
                <option value="EVSORG">&nbsp;organisation&nbsp;pédagogique&nbsp;des&nbsp;établissements&nbsp;d'enseignement</option>                
                <option value="EVSVSU">&nbsp;vie&nbsp;scolaire&nbsp; </option>
                <option value="EVSGFE">&nbsp;gestion&nbsp;financière&nbsp;et&nbsp;matérielle&nbsp;des&nbsp;établissements&nbsp;d'enseignement</option>
                <option value="EVSREP">&nbsp;relations&nbsp;extérieures&nbsp;et&nbsp;partenariats</option>
            </select>
        </td>
    </tr>
    <tr>
        <td align="center" colspan="2">
	        <input type="submit" value="ok">
        </td>
    </tr>
</table>
</div>
</form>

<?php
print_container_end();
?>