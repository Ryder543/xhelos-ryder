<?php


/* ----------------------------------------------------------
  DEFINE archivos sistema
  ---------------------------------------------------------- */
  define("_X_FILE_ARMIES", "main.php");
  define("_X_FILE_REGION", "region.php");
  define("_X_FILE_PLANET", "planet.php");
  define("_X_FILE_STAR", "star.php");
  define("_X_FILE_GALAXY", "galaxy.php");
  define("_X_FILE_COLONY", "colony.php");
  
  define("_X_URL_ERROR", "error404");
  define("_X_URL_ADMIN", "admin.php");
  
  
  
  //////////////////////VARIABLES EN GENERAL/////////////////////////////
  define("_X_VAR_COLONY_TYPE","colony_type");
  define("_X_VAR_COLONY_ID","colony_id");
  define("_X_VAR_REGION_ID","region_id");
  define("_X_VAR_OWNER_ID","owner_id");
  define("_X_VAR_PLAYER_ID","player_id");
  define("_X_VAR_PLANET_ID","planet_id");
  define("_X_VAR_ORIGIN_REGION_ID","origin_region_id");//movearmytoregionwork
  define("_X_VAR_TARGET_REGION_ID","target_region_id");//movearmytoregionwork
  define("_X_VAR_TEAM_ID","team_id");//movearmytoregionwork
  
  
  /////////////////////VARIABLES Y SUFIJOS /////////////////////////////
  define("_X_MAN_SUFFIX", "man");
  define("_X_MAN_COLONY", "colony");
  define("_X_MAN_PLAYER", "player");
  define("_X_MAN_REGION", "player");
  define("_X_MAN_ARMY", "army");
  
  //////////////////////VARIABLES DE ORIGEN DE WORK/////////////////////////////
  define("_X_WORK_ORIGIN","work_origin");
  define("_X_WORK_ORIGIN_CREATEPRIMARYCOLONY",1);
  define("_X_WORK_ORIGIN_CREATESECONDARYCOLONY",2);
  define("_X_WORK_ORIGIN_TEAMMOVEMENT",4);
  define("_X_WORK_ORIGIN_UNDEFINED","undefined");
  
  //////////////////////VARIABLES DEMO/////////////////////////////
  
  define("_X_DEMO_USER_ID", 1);
  define("_X_DEBUG_MODE",true);
  
  //////////////////////DEFINE - Times///////////////////////////
  define("_TICK", "5"); // Cada cuanto tiempo se calcula los valores en segundos
  //define("_X_TRAVEL_TICK", '00:00:10'); // Cuanto se demora una unidad de ir de region a region
  // Define the travel tick as an interval in seconds for MySQL
  define("_X_TRAVEL_TICK", "INTERVAL 10 SECOND");
  
  //define("_X_TACTIC_TIME", '23:55:00'); //Tiempo que uno pasa en la zona de tactica
  // Define it like this:
  define("_X_TACTIC_TIME", "INTERVAL 23 HOUR + INTERVAL 55 MINUTE + INTERVAL 0 SECOND");
  
  //define("_X_BATTLE_TIME", '10:00:00'); //Tiempo que uno pasa en la zona de Batalla
  define("_X_BATTLE_TIME", "INTERVAL 10 HOUR");
  //define("_X_ERROR_TIME", '00:00:40'); //Tiempo que uno pasa cuando hay un error
  define("_X_ERROR_TIME", "INTERVAL 40 SECOND");
  define("_X_MAX_BUCLE FOR_TURNS", 10); //Tiempo que uno pasa en la zona de tactica
  define("_X_MOVEMENT_LIMIT_VIEW", 10); //Numero de movimientos a obtener de la Bd [TODO] Añadirle ITF
  define("_X_INITIAL_PLAYER_MOVEMENT_ID", 0); //El id con que inicia el campo last_movement en la tabla battle_player
  define("_X_SECONDS_IN_DAY", "86400"); // Cada cuanto tiempo se calcula los valores en segundos
  
  define("_X_HIDDEN_TIME", 5000); //Tiempo en que se actualiza nuestro hidden
  //////////////////////DEFINE - Variables de Interface///////////////////////////
  define("_X_ITF_PLANET_WIDTH", 600); //ITF means interface
  define("_X_ITF_REGION_LIMIT_TURNS", 15);  //La cantidad de turnos que se veran en la batalla
  define("_X_ITF_EMERGENCY_BREAK", 10); //Freno de una iteracion a
  //los 10 bucles, en regionview.class
  //////////////////////VARIABLES PAGINA WEB/////////////////////////////
  define("_VERSION", "0.7.1");
  define("_X_GAMENAME", 'Xhelos');
  define("_TITLE", "Xhelos");
  define("_SUBTITLE", " - Organismos de Batalla");
  define("_LAST_UPDATE", "06 de Octubre del 2024");
  
  ///////////////////VARIABLES GENERALES DEL JUEGO////////////////////
  /* Estando On, los jugadores artificiales se cargan automaticamente
   * en la fase Battle [B] al inicial una batalla */
  define("_X_AUTOBATTLE_ARTIFICIAL_PLAYER", true);
  define ("_X_TOTAL_RESOURCES",8);//[TODO esto deberia de cambiar,defrente de la BD]
  
  define("_X_ACTIVE", 'A');
  define("_X_INACTIVE", 'I');
  
  
  //////////////////////DEFINE - InitialRegion  ///////////////////////////
  define("_X_REGION_PORTAL_INITIAL_TIME_DURATION", 300);//5 minutos en llegar a algun nuevo planeta
  define("_X_REGION_INITIAL_HOME_X_SIZE", 20);//Tamaño Inicial
  define("_X_REGION_INITIAL_HOME_Y_SIZE", 20);//Tamaño Inicial
  define("_X_REGION_BIOME_TIER_1", 1);
  define("_X_REGION_BIOME_TIER_2", 2);
  define("_X_REGION_BIOME_TIER_3", 3);
  
  define("_X_REGION_BUILDING_RESOURCE_SECUNDARY_COLONY_COST_ID", 1);
  
  define("_X_REGION_ALIEN_PLAYER_ID_FOR_COLONIES", 10);//IA Baruch
  
  //////////////////////DEFINE - RegionConstruction  ///////////////////////////
  define("_X_REGION_CONSTRUCTION_NO_CONSTRUCTIONS", 0);
  define("_X_REGION_CONSTRUCTION_FOUNDATIONS", 1);//Cimientos
  define("_X_REGION_CONSTRUCTION_GREAT_DOMINION", 2);//Cimientos
  define("_X_REGION_CONSTRUCTION_ARTIFICIAL_PLANETARY_PORTAL", 3);//Portal Planetario Artificial
  
  define("_X_REGION_CONSTRUCTION_STATUS_ACTIVE", 'A');//Portal Planetario Artificial
  define("_X_REGION_CONSTRUCTION_STATUS_INACTIVE", 'I');//Portal Planetario Artificial
  
  define("_X_REGION_BUILD_STATE_BUILDING", 'B'); //Building
  define("_X_REGION_BUILD_STATE_DONE", 'D'); // Done
  
  define("_X_REGION_PORTAL_INITIAL_X_POSITION", 10);
  define("_X_REGION_PORTAL_INITIAL_Y_POSITION", 10);
  define("_X_REGION_RESOURCE_INITIAL_X_POSITION",12);
  define("_X_REGION_RESOURCE_INITIAL_Y_POSITION",12);
  
  //prado =1 ; mountain = 2; desert = 3 sea = 4 ; snow = 5
  define("_X_REGION_MAP_TYPE_PLAIN", 0); //Pradera
  define("_X_REGION_MAP_TYPE_DESERT", 1); // Desierto
  define("_X_REGION_MAP_TYPE_MOUNTAIN", 2); //Montaña
  define("_X_REGION_MAP_TYPE_LAGOS", 3); //Lagos
  define("_X_REGION_MAP_TYPE_NIEVE", 4); //Nieve y Rocas
  define("_X_REGION_MAP_TYPE_INICIAL", 5); //Tipo de Mapa Inicial
  
  define("_X_COLONY_AI_INITIAL_X_POSITION", 14); 
  define("_X_COLONY_AI_INITIAL_Y_POSITION", 14); 
  define("_X_COLONY_INITIAL_X_POSITION", 14); 
  define("_X_COLONY_INITIAL_Y_POSITION", 14);
  define("_X_COLONY_MINIMUN_RANGE_TO_BORDER", 4);
  
  define("_X_REGION_CONSTRUCTION_INITIAL_X_POSITION", 5);//Portal Planetario Artificial
  define("_X_REGION_CONSTRUCTION_INITIAL_Y_POSITION", 5);//Portal Planetario Artificial
  
  //////////////////////DEFINE - Resources  ///////////////////////////
  define("_X_RESOURCE_PREFIX", "res");
  define("_X_RESOURCE_INITIAL_QUALITY", 1);
  define("_X_RESOURCE_TYPE_STRATEGIC", 'S');
  define("_X_RESOURCE_TYPE_DINAMIC", 'D');
  
  define("_X_RESOURCE_1", 'res1');//Biomateria
  define("_X_RESOURCE_2", 'res2');//Agua
  define("_X_RESOURCE_3", 'res3');//Energia
  define("_X_RESOURCE_4", 'res4');
  define("_X_RESOURCE_5", 'res5');
  define("_X_RESOURCE_6", 'res6');
  define("_X_RESOURCE_7", 'res7');
  define("_X_RESOURCE_8", 'res8');
  
  define("_X_RESOURCE_1_ID",1);
  define("_X_RESOURCE_2_ID",2);
  define("_X_RESOURCE_3_ID",3);
  define("_X_RESOURCE_4_ID",4);
  define("_X_RESOURCE_5_ID",5);
  define("_X_RESOURCE_6_ID",6);
  define("_X_RESOURCE_7_ID",7);
  define("_X_RESOURCE_8_ID",8);
  
  
  define("_X_RESOURCE_8_INITIAL_VALUE", 100);
  define("_X_RESOURCE_7_INITIAL_VALUE", 100);
  define("_X_RESOURCE_6_INITIAL_VALUE",100);
  define("_X_RESOURCE_5_INITIAL_VALUE", 100);
  define("_X_RESOURCE_4_INITIAL_VALUE", 100);
  define("_X_RESOURCE_3_INITIAL_VALUE", 100);
  define("_X_RESOURCE_2_INITIAL_VALUE", 100);
  define("_X_RESOURCE_1_INITIAL_VALUE", 100);
  
  define("_X_RESOURCE_1_MAX_INITIAL_VALUE", 500);
  define("_X_RESOURCE_2_MAX_INITIAL_VALUE", 500);
  define("_X_RESOURCE_3_MAX_INITIAL_VALUE", 500);
  define("_X_RESOURCE_4_MAX_INITIAL_VALUE", 500);
  define("_X_RESOURCE_5_MAX_INITIAL_VALUE", 500);
  define("_X_RESOURCE_6_MAX_INITIAL_VALUE", 500);
  define("_X_RESOURCE_7_MAX_INITIAL_VALUE", 500);
  define("_X_RESOURCE_8_MAX_INITIAL_VALUE", 500);
  
  define("_X_RESOURCE_1_INITIAL_RATE", 0);
  define("_X_RESOURCE_2_INITIAL_RATE", 0);
  define("_X_RESOURCE_3_INITIAL_RATE", 0);
  define("_X_RESOURCE_4_INITIAL_RATE", 0);
  define("_X_RESOURCE_5_INITIAL_RATE", 0);
  define("_X_RESOURCE_6_INITIAL_RATE", 0);
  define("_X_RESOURCE_7_INITIAL_RATE", 0);
  define("_X_RESOURCE_8_INITIAL_RATE", 0);
  
  
  //////////////////////DEFINE - Colony  ///////////////////////////
  define("_X_COLONY_INITIAL_BUILDING_LEVEL", 0);
  define("_X_COLONY_INITIAL_CONSTRUCTED_LEVEL", 1);
  define("_X_COLONY_BUILD_STATE_BUILDING", 'B'); //Building
  define("_X_COLONY_BUILD_STATE_DONE", 'D'); // Done
  
  define("_X_COLONY_STATE_ACTIVE", 'A'); // Done
  
  define("_X_COLONY_INITIAL_LEVEL", 1);  //Nivel inicial de la colonia, ya se vera como crece
  define("_X_COLONY_INITIAL_ALIEN_LEVEL", 1);  //Nivel inicial de la colonia controlada por AI
  define("_X_COLONY_INITIAL_X_SIZE", 20); 
  define("_X_COLONY_INITIAL_Y_SIZE", 20); 
  
  define("_X_COLONY_MAX_NUMBER_PER_PLANET", 8);//Total de Colonias que pueden existir por planeta
  define("_X_COLONY_MAX_NUMBER_PER_REGION", 1);//Numero Maximo de Colonias por Region
  define("_X_COLONY_FIRST_POSITION", 12);
  
  define ("_X_COLONY_INITIAL_MAP","{{1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,2},{1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,2,2},{1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,2,2,1,1,1,1,1,1,1,2,2,2},{1,1,1,1,1,1,2,1,2,2,1,1,1,1,1,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2},{2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2},{2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2},{2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2},{2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2},{2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2},{2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2},{2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2},{2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2},{2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2},{2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2},{2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2},{2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,3,3,3,3,3,3,2,2,2,2,2,3,3,3},{2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,3,3,3,2,2,2,3,3,3,3,3,3,3,3},{3,3,3,3,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,3,3,3,3,3,3,2,2,2},{3,3,3,2,2,2,2,2,2,2,2,2,2,2,2,3,3,3,3,2,2,3,3,3,2,2,2,3,3,3},{2,2,2,3,3,3,2,2,2,2,2,2,2,2,3,3,2,2,2,2,3,3,3,3,2,2,2,2,2,3}}");
  
  define ("_X_COLONY_INITIAL_SECONDARY_MAP","{{1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,2},{1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,2,2},{1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,2,2,1,1,1,1,1,1,1,2,2,2},{1,1,1,1,1,1,2,1,2,2,1,1,1,1,1,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2},{2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2},{2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2},{2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2},{2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2},{2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2},{2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2},{2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2},{2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2},{2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2},{2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2},{2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2},{2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,3,3,3,3,3,3,2,2,2,2,2,3,3,3},{2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,3,3,3,2,2,2,3,3,3,3,3,3,3,3},{3,3,3,3,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,3,3,3,3,3,3,2,2,2},{3,3,3,2,2,2,2,2,2,2,2,2,2,2,2,3,3,3,3,2,2,3,3,3,2,2,2,3,3,3},{2,2,2,3,3,3,2,2,2,2,2,2,2,2,3,3,2,2,2,2,3,3,3,3,2,2,2,2,2,3}}");
  define("_X_COLONY_INITIAL_X_SECONDARY_SIZE", 20); 
  define("_X_COLONY_INITIAL_Y_SECONDARY_SIZE", 10); 
  
  define("_X_COLONY_TYPE_PRIMARY", "P"); 
  define("_X_COLONY_TYPE_SECONDARY", "S"); 
  define("_X_COLONY_TYPE_PRIMARY_RANGE", 1); 
  define("_X_COLONY_TYPE_SECONDARY_RANGE", 1);
  
  //////////////////////DEFINE - Buildings  ///////////////////////////
  define("_X_BUILDING_CORE", 1); 
  define("_X_BUILDING_DEFAULT_CORE_POSITION_X", 14); 
  define("_X_BUILDING_DEFAULT_CORE_POSITION_Y", 15); 
  define("_X_BUILDING_RESPIRADERO", 2); 
  define("_X_BUILDING_DEFAULT_RESPIRADERO_POSITION_X", 14); 
  define("_X_BUILDING_DEFAULT_RESPIRADERO_POSITION_Y", 3); 
  
  //////////////////////DEFINE - Starlanes  ///////////////////////////
  define("_X_STARLANE_STATUS_ACTIVE", 'A');
  define("_X_STARLANE_STATUS_INACTIVE", 'I');
  
  //////////////////////DEFINE - Regions  ///////////////////////////
  define("_X_REGION_STATUS_ACTIVE", 'A');
  define("_X_REGION_STATUS_INACTIVE", 'I');
  
  //////////////////////DEFINE - Planets  ///////////////////////////
  define("_X_PLANET_STATUS_ACTIVE", 'A');
  define("_X_PLANET_STATUS_INACTIVE", 'I');
  define("_X_PLANET_SIZE_TINY", 'A');
  define("_X_PLANET_SIZE_MEDIUM", 'B');
  define("_X_PLANET_SIZE_BIG", 'C');
  define("_X_PLANET_SIZE_GIGANTIC", 'D');
  //INITIAL PLANET
  define("_X_PLANET_TYPE_PRIMARY", 'P');
  
  //////////////////////DEFINE - Galaxy  ///////////////////////////
  define("_X_GALAXY_NAME", 'Lemuria');
  define("_X_GALAXY_SIZE_X", '10');
  define("_X_GALAXY_SIZE_Y", '10');
  
  //////////////////////DEFINE - Team   ///////////////////////////
  define("_X_TEAM_TRAVEL_REGION", -1);
  
  //////////////////////DEFINE - Players  ///////////////////////////
  define("_X_PLAYER_STATUS_ACTIVE", 'A');
  define("_X_PLAYER_STATUS_INACTIVE", 'I');
  
  define("_X_PLAYER_TYPE_HUMAN", 'H');
  define("_X_PLAYER_TYPE_ARTIFICIAL", 'A');
  
  define("_X_PLAYER_MAX_TEAM_PER_REGION", 3);
  
  define("_X_PLAYER_NO_PLAYER_ID", 0);
  
  define("_X_PLAYER_MIN_PASS_SIZE", 6); // Tamaño minimo del password
  define("_X_PLAYER_INITIAL_MAX_ENERGY", 200); // Pozo de energia inicial
  define("_X_PLAYER_INITIAL_ENERGY", 100); // Pozo de energia inicial
  define("_X_PLAYER_INITIAL_ENERGY_RATE", 6); // Cantidad de Mana que se regenera inicialmente por tick
  define("_X_ALLIANCE_NOALLIANCE_ID", 0); // Alianza Zero
  
  define("_X_PLAYER_INITIAL_TUTORIAL", 'true'); // Pozo de energia inicial
  define("_X_PLAYER_INITIAL_TUTORIAL_STEP",1); // Pozo de energia inicial
  define("_X_PLAYER_BATTLE_TUTORIAL_STEP",7);
  define("_X_PLAYER_POSTACTION_TUTORIAL_STEP",11);
  
  define("_X_PLAYER_MAX_TUTORIAL_STEP",13); // Pozo de energia inicial
  //////////////////////DEFINE - Star  ///////////////////////////
  define("_X_STAR_STATUS_ACTIVE", 'A');
  define("_X_STAR_STATUS_INACTIVE", 'I');
  //////////////////////DEFINE - Movements///////////////////////////
  define("_X_MOV_NOMOV", -1);
  define("_X_MOV_STATE_NEW", 'N');
  
  //////////////////////DEFINE - Actions///////////////////////////
  define("_X_ACTION_ERROR", 0);
  define("_X_ACTION_DEFEND", 5);
  define("_X_ACTION_TRAVEL", 10);
  
  define("_X_ACTION_STATE_ACTIVE", 'A');
  define("_X_ACTION_STATE_INACTIVE", 'I');
  
  define("_X_ACTION_PATH_RANGE",'RANGE');
  define("_X_ACTION_PATH_ATTACK",'ATTACK');
  define("_X_ACTION_PATH_CROSS",'CROSS');
  define("_X_ACTION_PATH_SQUARE",'SQUARE');
  define("_X_ACTION_PATH_MOV",'MOV');
  
  
  define("_X_ACTION_SCOPE_SINGLE",'S'); //Solo hace daño al target indicado
  define("_X_ACTION_SCOPE_MULTIPLE",'M'); //HAce daño a toda la linea de ataque
  
  
  //////////////////////DEFINE - AFFECTS///////////////////////////
  define("_X_ATTACK", 0); //[TODO ELIMINAR ESTO]
  define("_X_ATTRITION", 1);
  define("_X_ARMOR", 2);
  define("_X_LIFE", 3);
  define("_X_UNIT", 4);
  define("_X_SIZE", 4);
  define("_X_MOVEMENT", 5);
  
  define("_X_AFFECTS_ATTACK", 0);
  define("_X_AFFECTS_ATTRITION", 1);
  define("_X_AFFECTS_ARMOR", 2);
  define("_X_AFFECTS_LIFE", 3);
  define("_X_AFFECTS_UNIT", 4);
  define("_X_AFFETCS_SIZE", 4);
  define("_X_AFFECTS_MOVEMENT", 5);
  define("_X_AFFECTS_DAMAGE", 6);
  define("_X_AFFECTS_SHIELD", 7);
  
  define("_X_AFFECTS_CAUSE", 'cause');
  define("_X_AFFECTS_EFFECT", 'effect');
  
  //Para movement.class
  define("_X_TYPE_ARMY", 'army');
  define("_X_TYPE_TERRAIN", 'terrain');
  define("_X_TYPE_BUILDING", 'building');
  
  
  //////////////////////DEFINE - Si, No///////////////////////////
  define("_X_SI", "S");
  define("_X_NO", "N");
  //////////////////////DEFINE - Posicion///////////////////////////
  define("_X_POSITION_ORBIT", "O");
  define("_X_POSITION_ATERRIZAR", "A");
  //define("_X_POSITION_SALIENDO", "S"); Ya no se usa
  define("_X_POSITION_SUPERFICIE", "P");
  define("_X_POSITION_ESPACIO", "E");
  define("_X_POSITION_COORD_NULL", -1);
  //define("_X_POSITION_OUTSIDE", "U"); //Listo para ingresar a la region
  //define("_X_POSITION_INSIDE", "I"); //Ingreso a la region, toca posicionarse
  
  define("_X_REGION_STATE_INSIDE", "I"); //Ingreso a la region, toca posicionarse
  define("_X_REGION_STATE_OUTSIDE", "O"); //Ingreso a la region, toca posicionarse
  
  define("_X_ORBITAR", "O");
  define("_X_ATERRIZAR", "A");
  define("_X_EN_REGIONA", "P");
  define("_X_SALIENDO", "S");
  //////////////////////DEFINE - targets///////////////////////////
  define("_X_TARGET_TERRAIN", 2);
  define("_X_TARGET_ARMY", 1);
  define("_X_TARGET_ARMYANDTERRAIN", 3);
  define("_X_TARGET_ENEMY", 4);
  define("_X_TARGET_ENEMYANDARMY", 5);
  define("_X_TARGET_ENEMYANDARMYANDTERRAIN", 6);
  define("_X_TARGET_NONE", 0);
  //////////////////////DEFINE - Axis///////////////////////////
  define("_X_AXIS_X", 'x');
  define("_X_AXIS_Y", 'y');
  define("_X_AXIS_Z", 'z');
  
  define("_PLAYER", "1");
  define("_REGION", "1");
  define("_REGION_DEFAULT", "1");
  define("_MAXMOV", "3");
  
  
  //////////////////////DEFINE - Estado de la Unidad///////////////////////////
  define("_X_ARMY_STATE_DEAD", "D");
  define("_X_ARMY_STATE_ALIVE", "A");
  define("_X_ARMY_STATE_INACTIVE", "I");
  
  //////////////////////DEFINE - Iniciales de Armies///////////////////////////
  define("_X_ARMY_INITIAL_LEVEL", 1);
  define("_X_ARMY_INITIAL_XP", 0);
  define("_X_ARMY_TURN_INITIAL", 0);
  
  
  
  //////////////////////DEFINE - Armies Action///////////////////////////
  define("_X_ARMY_ACTION_STATE_ACTIVE", 'A');
  define("_X_ARMY_ACTION_STATE_INACTIVE", 'I');
  
  //???? [WHY] Se usa para la tabla battlestae
  define("_X_ARMY_BATTLESTATE_TACTIC", "T");
  define("_X_ARMY_BATTLESTATE_BATTLE", "B");
  define("_X_ARMY_BATTLESTATE_END", "E");
  
  
  //////////////////////DEFINE - Armies SIDE [TODO]Eliminarlos///////////////////////////
  define("_X_ARMY_SIDE_TOP", "T");
  define("_X_ARMY_SIDE_BOTTOM", "B");
  define("_X_ARMY_SIDE_LEFT", "L");
  define("_X_ARMY_SIDE_RIGHT", "R");
  define("_X_ARMY_SIDE_NONE", "N");
  define("_X_ARMY_SIDE_CENTER", "C");
  
  
  //////////////////////DEFINE - Army Type///////////////////////////
  define("_X_UNIT_TYPE_HERO", 'H'); //[TODO] Eliminar	 y sustituir
  define("_X_UNIT_TYPE_NORMAL", 'N'); //[TODO] Eliminar y sustituir
  define("_X_ARMY_TYPE_HERO", 'H');
  define("_X_ARMY_TYPE_NORMAL", 'N');
  
  //////////////////////DEFINE - Tipos de Criatura///////////////////////////
  define("_X_CREATURE_TYPE_CANON", 1);
  define("_X_CREATURE_TYPE_EASYMEAT", 1);
  define("_X_CREATURE_TYPE_ACIDDISPENSER", 1);
  define("_X_CREATURE_TYPE_SPIDER", 1);
  define("_X_CREATURE_TYPE_ZEALOT", 1);
  define("_X_CREATURE_TYPE_FIGHTER", 1);
  define("_X_CREATURE_TYPE_ARCHER", 2);
  define("_X_CREATURE_TYPE_ARTILLERY", 3);
  
  //////////////////////DEFINE - Creature Actions///////////////////////////
  define("_X_CREATURE_ACTION_STATE_ACTIVE", "A");
  //////////////////////DEFINE - Varios///////////////////////////
  define("_X_DEATH_ARMY", '0');
  define("_X_TEMP_INITIAL_REGION", '4');
  
  //////////////////////DEFINE - Battles///////////////////////////
  define("_X_BATTLE_INITIAL_TURN", '1'); //Lo usa battle.class al iniciar
  //la fase tactica, lo usa otro sitio?
  
  define("_X_BATTLE_PHASE_TACTIC", 'T');
  define("_X_BATTLE_PHASE_BATTLE", 'B');
  define("_X_BATTLE_PHASE_END", 'E');
  
  //////////////////////DEFINE - Battles PLayer/////////////////////
  define("_X_BATTLE_PLAYER_PHASE_TACTIC", 'T');
  define("_X_BATTLE_PLAYER_PHASE_READY", 'R');
  define("_X_BATTLE_PLAYER_PHASE_BATTLE", 'B');
  define("_X_BATTLE_PLAYER_PHASE_END", 'E');
  
  define("_X_BATTLE_PLAYER_STATE_ALIVE", 'A');
  define("_X_BATTLE_PLAYER_STATE_DEAD", 'D');
  
  //////////////////////DEFINE - Travel///////////////////////////
  define("_X_TRAVEL_STATE_ACTIVE", 'A');//El viaje se esta realizando
  define("_X_TRAVEL_STATE_ARRIVED", 'R');//Acaba de llegar la unidad
  define("_X_TRAVEL_STATE_INACTIVE", 'I');//Termino de viajar
  
  //////////////////////DEFINE - maputil///////////////////////////
  define("_X_MAPUTIL_NORMALMAP", 'normalmap'); //Tiempo que uno pasa en la zona de tactica
  define("_X_MAPUTIL_TOPMAP", 'topmap'); //Tiempo que uno pasa en la zona de tactica
  define("_X_MAPUTIL_BOTTOMMAP", 'bottommap'); //Tiempo que uno pasa en la zona de tactica
  define("_X_MAPUTIL_LEFTMAP", 'leftmap'); //Tiempo que uno pasa en la zona de tactica
  define("_X_MAPUTIL_RIGHTMAP", 'rightmap'); //Tiempo que uno pasa en la zona de tactica
  define("_X_MAPUTIL_CENTERMAP", 'centermap'); //Tiempo que uno pasa en la zona de tactica
  define("_X_MAPUTIL_OBSTACLEMAP", 'obstaclemap'); //Mapa de Obstaculos de la zona
  
  //////////////////////DEFINE - maputil - SIMPLIFICADOS usar estos en lugar de los armyside///////////////////////////
  
  define("_X_TOP", 'T'); //Tiempo que uno pasa en la zona de tactica
  define("_X_BOTTOM", 'B'); //Tiempo que uno pasa en la zona de tactica
  define("_X_LEFT", 'L'); //Tiempo que uno pasa en la zona de tactica
  define("_X_RIGHT", 'R'); //Tiempo que uno pasa en la zona de tactica
  define("_X_CENTER", 'C'); //Tiempo que uno pasa en la zona de tactica
  define("_X_NONE", 'C'); //Tiempo que uno pasa en la zona de tactica
  
  
  ///////////////////// DEFINE - 
  define("_X_TERRAIN_MAX",5); // Numero total de terrenos en el juego
  
  
  ///////////////////// DEFINE - Sufijos de Clases 
  define("_X_CLASS_VIEW_SUFFIX","view"); // Para las clases de tipo state
  define("_X_VIEW_TYPE_REGION","region"); // Para las clases de tipo state
  define("_X_VIEW_TYPE_PLANET","planet"); // Para las clases de tipo state
  define("_X_VIEW_TYPE_COLONY","colony"); // Para las clases de tipo state
  define("_X_VIEW_TYPE_STAR","star"); // Para las clases de tipo state
  define("_X_VIEW_TYPE_GALAXY","galaxy"); // Para las clases de tipo state
  
  
  /* ----------------------------------------------------------
    DEFINE ERRORES -
    ---------------------------------------------------------- */
  define("_X_ERROR_PLAYER_DONT_EXIST", 'Parece que su jugador no existe!. Consulte con al administrador sobre este error');
  define("_X_ERROR_FALSE_ERROR", 'Parece que la pagina a consultar no existe. Consulte con el administrador');
  define("_X_ERROR_XFILEROOT_DONT_EXIST", 'Parece que la variable XFILEROOT no ha sido definida');
  
  //////////////////////DEFINE - GameState///////////////////////////
  define("_X_GAMESTATE_PLANET", "planet");
  define("_X_GAMESTATE_REGION", "region");
  

?>