<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * blooms implementation : © Adam Novotny <adam.novotny.ck@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * gameoptions.inc.php
 *
 * blooms game options description
 * 
 * In this file, you can define your game options (= game variants).
 *   
 * Note: If your game has no variant, you don't have to modify this file.
 *
 * Note²: All options defined in this file should have a corresponding "game state labels"
 *        with the same ID (see "initGameStateLabels" in blooms.game.php)
 *
 * !! It is not a good idea to modify this file when a game is running !!
 *
 */

$game_options = array(

    100 => array(
                'name' => totranslate('Board size'),

                'values' => array(

                            5 => array( 'name' => totranslate('5 hexagons per edge'), 'tmdisplay' => totranslate('5 hexagons per edge'), 'nobeginner' => true ),

                            4 => array( 'name' => totranslate('4 hexagons per edge'), 'tmdisplay' => totranslate('4 hexagons per edge') ),

                            6  => array( 'name' => totranslate('6 hexagons per edge'), 'tmdisplay' => totranslate('6 hexagons per edge'), 'nobeginner' => true ),
                ),
            ),
         
    101 => array(
        'name' => totranslate('X value'),

        'values' => array(
                    15 => array( 'name' => '15', 'tmdisplay' => totranslate('X value = 15') ),
                    5 => array( 'name' => '5', 'tmdisplay' => totranslate('X value = 5') ),
                    10 => array( 'name' => '10', 'tmdisplay' => totranslate('X value = 10') ),
                    20 => array( 'name' => '20', 'tmdisplay' => totranslate('X value = 20') ),
        ),
        'displaycondition' => array(
            15 => array(  'type' => 'otheroption',  'id' => 100,  'value' => array(4)  ),  
            5 => array(  'type' => 'otheroption',  'id' => 100,  'value' => array(4)  ), 
            10 => array(  'type' => 'otheroption',  'id' => 100,  'value' => array(4)  ), 
            20 => array(  'type' => 'otheroption',  'id' => 100,  'value' => array(4)  ),               
        ),
    ),

    102 => array(
        'name' => totranslate('X value2'),

        'values' => array(
                    20 => array( 'name' => '20', 'tmdisplay' => totranslate('X value = 20')),
                    5 => array( 'name' => '5', 'tmdisplay' => totranslate('X value = 5') ),
                    10 => array( 'name' => '10', 'tmdisplay' => totranslate('X value = 10') ),
                    15 => array( 'name' => '15', 'tmdisplay' => totranslate('X value = 15') ),
                    25 => array( 'name' => '25', 'tmdisplay' => totranslate('X value = 25') ),
        ),

        'displaycondition' => array(
            20 => array(  'type' => 'otheroption',  'id' => 100,  'value' => array(5)  ),
            5 => array(  'type' => 'otheroption',  'id' => 100,  'value' => array(5)  ),
            10 => array(  'type' => 'otheroption',  'id' => 100,  'value' => array(5)  ),
            15 => array(  'type' => 'otheroption',  'id' => 100,  'value' => array(5)  ),
            25 => array(  'type' => 'otheroption',  'id' => 100,  'value' => array(5)  ),
        ),
    ),

    103 => array(
        'name' => totranslate('X value3'),

        'values' => array(
                    25  => array( 'name' => '25', 'tmdisplay' => totranslate('X value = 25')),
                    5 => array( 'name' => '5', 'tmdisplay' => totranslate('X value = 5') ),
                    10 => array( 'name' => '10', 'tmdisplay' => totranslate('X value = 10') ),
                    15 => array( 'name' => '15', 'tmdisplay' => totranslate('X value = 15') ),
                    20 => array( 'name' => '20', 'tmdisplay' => totranslate('X value = 20') ),
                    30 => array( 'name' => '30', 'tmdisplay' => totranslate('X value = 30') ),      
        ),
        'displaycondition' => array(
            25  => array(  'type' => 'otheroption',  'id' => 100,  'value' => array(6)  ),   
            5  => array(  'type' => 'otheroption',  'id' => 100,  'value' => array(6)  ),
            10  => array(  'type' => 'otheroption',  'id' => 100,  'value' => array(6)  ),
            15  => array(  'type' => 'otheroption',  'id' => 100,  'value' => array(6)  ),
            20  => array(  'type' => 'otheroption',  'id' => 100,  'value' => array(6)  ),
            30  => array(  'type' => 'otheroption',  'id' => 100,  'value' => array(6)  ),            
        ),
    ),    
);


$game_preferences = array(
    104 => array(
			'name' => totranslate('Display coordinates'),
			'needReload' => false,
			'values' => array(
					1 => array( 'name' => totranslate( 'Enabled' ), 'cssPref' => 'coord_on' ),
					2 => array( 'name' => totranslate( 'Disabled' ), 'cssPref' => 'coord_off' )
			)
    ),

    105 => array(
        'name' => totranslate('Display last move'),
        'needReload' => false,
        'values' => array(
                1 => array( 'name' => totranslate( 'Disabled' ), 'cssPref' => 'lastmove_off' ),
                2 => array( 'name' => totranslate( 'Enabled' ), 'cssPref' => 'lastmove_on' )
        )
    ),
);


