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
  * blooms.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );


class blooms extends Table
{
	function __construct( )
	{
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();
        
        self::initGameStateLabels( array( 
            "played_color" => 10,
            "first_turn" => 11,
            "board_size" => 100,
            "x_value4" => 101,
            "x_value5" => 102,
            "x_value6" => 103,

        ) );        
	}
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "blooms";
    }	

    /*
        setupNewGame:
        
        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame( $players, $options = array() )
    {    
        // Set the colors of the players with HTML color code
        // The default below is red/green/blue/orange/brown
        // The number of colors defined here must correspond to the maximum number of players allowed for the gams
        $gameinfos = self::getGameinfos();
        $default_colors = $gameinfos['player_colors'];
 
        // Create players
        // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ";
        $values = array();
        foreach( $players as $player_id => $player )
        {
            $color = array_shift( $default_colors );
            $values[] = "('".$player_id."','$color','".$player['player_canal']."','".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."')";
        }
        $sql .= implode( $values, ',' );
        self::DbQuery( $sql );
        self::reattributeColorsBasedOnPreferences( $players, $gameinfos['player_colors'] );
        self::reloadPlayersBasicInfos();
        
        /************ Start the game initialization *****/
        $variant = self::getGameStateValue( 'board_size');

        $sql = "INSERT INTO board (board_x,board_y ) VALUES ";
        $sql_values = array();

        for($x=0;$x<count($this->hex_row_counts[$variant]);$x++) {
            for($y=0;$y<$this->hex_row_counts[$variant][$x];$y++) {
                $sql_values[] = "('$x','$y')";
            }
        }

        $sql .= implode( $sql_values, ',' );
        self::DbQuery( $sql );

        // Initial setup
        foreach($this->holes[$variant] as $hole) {
            $sql = "UPDATE board SET board_player = 'hole' WHERE board_x = '$hole[0]' and board_y = '$hole[1]' ";
            self::DbQuery( $sql );
        }


        // Init global values with their initial values
        self::setGameStateInitialValue( 'played_color', 0 );
        self::setGameStateInitialValue( 'first_turn', 1 );
        
        // Init game statistics
        self::initStat( 'table', 'turns_number', 0 ); 
        self::initStat( 'player', 'turns_number', 0 );  
        self::initStat( 'player', 'player_captured_blooms', 0 ); 
        self::initStat( 'player', 'player_lost_blooms', 0 ); 
        self::initStat( 'player', 'player_average_captured_stones_per_bloom', 0 ); 
        self::initStat( 'player', 'player_average_lost_stones_per_bloom', 0 );        

        // Activate first player (which is in general a good idea :) )
        $this->activeNextPlayer();

        /************ End of the game initialization *****/
    }

    /*
        getAllDatas: 
        
        Gather all informations about current game situation (visible by the current player).
        
        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        $result = array();
    
        $current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!
    
        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score score, player_no FROM player ORDER BY player_no";
        $result['players'] = self::getCollectionFromDb( $sql );

  
        // TODO: Gather all information about current game situation (visible by player $current_player_id).

        $result['board'] = self::getObjectListFromDB( "SELECT board_x x, board_y y, board_player player, board_type color, board_lastmove lastmove FROM board WHERE board_player IS NOT NULL" );

        $result['variant'] = self::getGameStateValue( 'board_size');
  
        return $result;
    }

    /*
        getGameProgression:
        
        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).
    
        This method is called each time we are in a game state with the "updateGameProgression" property set to true 
        (see states.inc.php)
    */
    function getGameProgression()
    {
        // TODO: compute and return the game progression
        $string = "x_value".self::getgameStateValue('board_size');
        $victory_points = self::getgameStateValue($string);

        $sql = "SELECT COUNT(board_id) FROM board WHERE board_player IS NOT NULL";
        $stones_in_play = self::getUniqueValueFromDB( $sql );
        $number_of_hexes = array_sum( $this->hex_row_counts[self::getgameStateValue('board_size')]);

        $sql = "SELECT MAX(player_score) FROM player";
        $highest_score = self::getUniqueValueFromDB( $sql );

        if (($highest_score/$victory_points)*100 < 80) {
            $valueA = ($highest_score/$victory_points)*90;
            $valueB = ($stones_in_play/$number_of_hexes)*10;
        } else { 
            $valueA = ($highest_score/$victory_points)*100;
            $valueB = ($stones_in_play/$number_of_hexes)*10;
        }

        if ($valueA+$valueB > 100) {
            return 100;
        } else {
            return $valueA+$valueB;           
        }
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    

    /*
        In this space, you can put any utility methods useful for your game logic
    */

    function getBlooms($player_id) {
        $sql = "SELECT board_x x, board_y y, board_id id, board_player player, board_type color FROM board ";
        $board = self::getDoubleKeyCollectionFromDB($sql);

        $blooms = array();
        $board_size = self::getGameStateValue('board_size');

        for($row=0;$row<count($board);$row++) {
            for($index=0;$index<count($board[$row]);$index++) {

                if($board[$row][$index]['player'] != null && $board[$row][$index]['player'] == $player_id ) {
                    $player = $board[$row][$index]['player'];
                    $color = $board[$row][$index]['color'];
                    $new_bloom = true;
                    $s = 0;
                    $r = $row <(count($board)/2) ? -1 : 0;
                    $fenced = true;
                    
                    for($i=($row-1);$i<($row+1);$i++) {
                        for($j=($index+$r);$j<($index+$r+2-$s);$j++) {
                        
                            if( $i>-1 && $j>-1 && $j<$this->hex_row_counts[$board_size][$i] ) {
                                if ( $board[$i][$j]['player'] == $player && $board[$i][$j]['color'] == $color ) {

                                    if (!isset($bloom_id)) {
                                        $new_bloom = false;
                                        // add to existing bloom
                                        $bloom_id = $this->serchBloomIndex($blooms,$color, $player, array($i,$j) );
                                        // var_dump($bloom_id);
                                        $blooms[$bloom_id]['positions'][] = array($row,$index);
                                    }

                                    if ( isset($bloom_id)) {
                                        $new_bloom = false;
                                        $another_bloom_id = $this->serchBloomIndex($blooms,$color, $player, array($i,$j));
                                        if ($bloom_id == $another_bloom_id) {
                                            // same bloom
                                        } else               {
                                            // merge blooms
                                            $position_merged = array_merge( $blooms[$bloom_id]['positions'], $blooms[$another_bloom_id]['positions']);
                                            $fenced_merged = $blooms[$bloom_id]['fenced'] && $blooms[$another_bloom_id]['fenced'];
                                            $blooms[$bloom_id]['positions'] = $position_merged;
                                            $blooms[$bloom_id]['fenced'] = $fenced_merged;

                                            unset($blooms[$another_bloom_id]); // remove merged bloom
                                        }   
                                    }
                                }

                                if ( $board[$i][$j]['player'] == null ) {
                                    $fenced = false;
                                }
                            }
                        }
                        $s++;
                        $r = -1;
                    }

                    if( isset($bloom_id) ) {
                        if ( $blooms[$bloom_id]['fenced'] == true ) {
                            $blooms[$bloom_id]['fenced'] = $fenced;
                        }
                        $blooms = array_values($blooms); // reindex blooms
                        unset($bloom_id);
                    }

                    if ($new_bloom == true) {  // create new bloom
                        $blooms[] = array('positions' => array( array($row,$index) ) ,'color' => $color, 'player'=> $player, 'fenced'=>$fenced );
                    }
                }
            
            }
        }

        // check rest hexes for fencing
        $fenced_check = array_filter($blooms, function($v) {
            return( $v['fenced'] == true ); } 
        );

        foreach ($fenced_check as $key => $value ) {
            foreach ($value['positions'] as $element ) {
                    $x = $element[0];
                    $y = $element[1];
                    $r = 0;
                    $s = 1; $p= 1;

                    for($i=$x;$i<($x+2);$i++) {
                        for($j=($y+$s+$r);$j<($y+$s+$p+$r);$j++) {
                            // var_dump(''.$x.$y.':'.$i.$j.'');
                            if( $i<count($this->hex_row_counts[$board_size]) && $j<$this->hex_row_counts[$board_size][$i] && $i>-1 && $j>-1) {                          

                                if ( $board[$i][$j]['player'] == null ) {
                                    $blooms[$key ]['fenced'] = false;
                                    break 3;
                                }
                            }
                        }
                        $s--; $p++; 
                        $r = $x+1 <(count($board)/2) ? 0 : -1;
                    }
            }            
        }

        // var_dump($blooms);
        return $blooms;
    }

    function serchBloomIndex($blooms,$color, $player, $coords ) {
        $player_filter = array_filter($blooms, function($v) use( $player) {
            return( $v['player'] == $player ); } 
        );

        $color_filter = array_filter($player_filter, function($v) use( $color) {
            return( $v['color'] == $color ); } 
        );
 
        $bloom_index = null;
        foreach ($color_filter as $key => $element ) {
            if ( in_array($coords, $element['positions']) ) {
                $bloom_index = $key;
                break;
            }
        }

        return $bloom_index;
    }

    function captureBloom($bloom) {
        $number_of_hexes = count($bloom['positions']);
        $border_set = array();

        foreach($bloom['positions'] as $hex) {
            $sql = "UPDATE board SET board_player = null, board_type = null WHERE board_x = '$hex[0]' AND board_y ='$hex[1]' ";
            self::DbQuery( $sql );

            $border_set[] = $this->getFenceBorders($hex, $bloom['positions']);
        }

        self::DbQuery( "UPDATE player SET player_score=player_score+'$number_of_hexes' WHERE player_id='".self::getActivePlayerId()."'" );

        self::notifyAllPlayers('fenceBloom','', array ( 
            'borders' => $border_set,  'x' => $number_of_hexes
        ));

        if ($number_of_hexes == 1) {
            self::notifyAllPlayers('captureBloom',clienttranslate('${player_name} captures bloom (1 stone)'), array ( 
                'player_name' => self::getActivePlayerName(), 'x' => $number_of_hexes, 'hexes' => $bloom['positions'], 'player_id' =>  self::getActivePlayerId(), 
                'color' => $bloom['color'], 'captured_id' => $bloom['player']
            ));
        } else {
            self::notifyAllPlayers('captureBloom',clienttranslate('${player_name} captures bloom (${x} stones)'), array ( 
                'player_name' => self::getActivePlayerName(), 'x' => $number_of_hexes, 'hexes' => $bloom['positions'], 'player_id' =>  self::getActivePlayerId(), 
                'color' => $bloom['color'], 'captured_id' => $bloom['player']
            ));
        }

        self::notifyAllPlayers('destroyFence','', array (  ));
    }

    function getFenceBorders($hex, $bloom_coords) {
        if (($key = array_search($hex, $bloom_coords)) !== false) {
            unset($bloom_coords[$key]);
        }
        $borders = array();
        $borders['hex'] = $hex;
        $borders['borders'] = array();
        $board_size = self::getGameStateValue( 'board_size');

        $bottom_half_coef = $hex[0] < (count($this->hex_row_counts[$board_size])/2) ? 0 : 1;
        $top_half_coef = $hex[0]+1 < (count($this->hex_row_counts[$board_size])/2) ? 0 : -1;

        for ($i=0;$i<6;$i++) {

            if ($i<2) { $row_coef =  -1; }
            if ($i>1 && $i<4) { $row_coef =  0; }
            if ($i>3) { $row_coef =  1; }

            if ($i== 0 ) { $col_coef =  -1 + $bottom_half_coef; }
            if ($i== 1 ) { $col_coef =  0 + $bottom_half_coef; }
            if ($i== 2 ) { $col_coef =  -1; }
            if ($i== 3 ) { $col_coef =  1; }
            if ($i== 4 ) { $col_coef =  0 + $top_half_coef; }
            if ($i== 5 ) { $col_coef =  1 + $top_half_coef; }

            $x =  $hex[0]+ $row_coef;
            $y =  $hex[1]+ $col_coef;

            if ($x>-1 && $y>-1 && $x<count($this->hex_row_counts[$board_size]) && $y<$this->hex_row_counts[$board_size][$x]) {                       
                if(in_array( array($x,$y), $bloom_coords ) ) {
                        //no fence
                } else {
                    $borders['borders'][] = $i;
                }


            } else {
                $borders['borders'][] = $i;
            }
        }

        return $borders; 
    }

    function getNotation($x,$y) {
        $alphabet = "ABCDEFGHIJKLMNOP";
        $treshold = self::getGameStateValue( 'board_size') -1;

        if ($x> $treshold) {
            $y = $y + $x-$treshold;
        }

        $text = 'notation_'.$alphabet[$x].$y;
        return $text;
    }

    function showLastMove() {
        $sql = "SELECT board_player player, board_x x, board_y y FROM board WHERE board_lastmove = '1' ";
        $lastmoves = self::getObjectListFromDB( $sql );

        self::notifyAllPlayers('showLastMove','', array ( 'last_moves' => $lastmoves ) );
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
////////////

    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in blooms.action.php)
    */

    function placePawn( $x,$y, $player, $color ) {
        self::checkAction( 'placePawn' ); 

        $sql = "SELECT board_player player FROM board WHERE board_x = '$x' AND board_y ='$y' ";
        if (self::getUniqueValueFromDB( $sql ) != null ) {
            throw new BgaUserException( self::_("This tile is not free, hit F5 to update current situation") );
        }

        if ( $this->gamestate->state()['name'] == 'playerTurn2' && self::getGameStateValue('played_color') == $color ) {
            throw new BgaUserException( self::_("You cannot play same color twice, hit F5 to update current situation") );
        }

        if ($color == 0 ) {
            $type = 0;
            self::setGameStateValue( 'played_color', 0 );
        }

        if ($color == 1) {
            $type = 1;
            self::setGameStateValue( 'played_color', 1 );
        }

        $sql = "UPDATE board SET board_player = '$player', board_type = '$type' WHERE board_x = '$x' AND board_y ='$y' ";
        self::DbQuery( $sql );

        $meeple_log = 'meeple_'.$player.'_'.$color;

        self::notifyAllPlayers('pawnPlaced', clienttranslate('${player_name} places ${stone} to ${location}'), array ( 
            'player_name' => self::getActivePlayerName(), 'x' => $x, 'y' => $y, 'player_id' => $player, 'color' => $color, 'stone' => $meeple_log,
            'location' =>  $this->getNotation($x,$y)
        ));  
        
        
        $state=$this->gamestate->state(); 
        if( $state['name'] == 'playerTurn' ) {                  // reset last moves
            // $sql = "UPDATE board SET board_lastmove = '0' WHERE board_lastmove = '1' AND board_player ='$player' ";
            $sql = "UPDATE board SET board_lastmove = '0' WHERE board_lastmove = '1' ";
            self::DbQuery( $sql );
        }

        $sql = "UPDATE board SET board_lastmove = '1' WHERE board_x = '$x' AND board_y ='$y' ";
        self::DbQuery( $sql );

        if (self::getGameStateValue( 'first_turn' ) == 1 ) {
            self::setGameStateValue( 'first_turn',0 );
            $this->gamestate->nextState("placeFirstPawn");
        } else {
            $this->gamestate->nextState("placePawn");
        }
    }

    function passTurn() {
        self::checkAction( 'pass' ); 

        self::notifyAllPlayers('pass', clienttranslate('${player_name} passes'), array ( 
            'player_name' => self::getActivePlayerName()
        )); 

        $this->gamestate->nextState("pass");
    }

    
//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */

    
    function argPlayerTurn2() { 
        $this->showLastMove();

        $already_played = self::getgameStateValue('played_color');
        if ($already_played == 0) {
            $block = 'A';
        }
        if ($already_played == 1) {
            $block = 'B';
        }
        return array ( 'occupied' => $block );
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */
    
    function stNextPlayer() {
        $player = self::getActivePlayerId();

        self::incStat( 1,'turns_number'); 
        self::incStat( 1,'turns_number', $player); 

        $oponent_player = self::getPlayerAfter( $player );
        $oponent_blooms = $this->getBlooms( $oponent_player );
        foreach ($oponent_blooms as $bloom) {
            if($bloom['fenced'] == true) {
                $this->captureBloom($bloom);

                self::incStat( 1, 'player_captured_blooms', $player ); 
                self::incStat( 1, 'player_lost_blooms', $oponent_player ); 
            }
        }

        $this->showLastMove();

        //check victory condition
        $player_score = self::getUniqueValueFromDB( "SELECT player_score FROM player WHERE player_id='$player'" );

        $string = "x_value".self::getgameStateValue('board_size');
        if ($player_score > (self::getgameStateValue($string)-1) ) {

            $oponent_score = self::getUniqueValueFromDB( "SELECT player_score FROM player WHERE player_id='$oponent_player'" );
            
            if (self::getStat('player_captured_blooms' , $player ) == 0 ) {
                self::setStat( 0, 'player_average_captured_stones_per_bloom', $player ); 
                self::setStat( 0 , 'player_average_lost_stones_per_bloom', $oponent_player );  
            } else {
                self::setStat( $player_score/self::getStat('player_captured_blooms' , $player ), 'player_average_captured_stones_per_bloom', $player ); 
                self::setStat( $player_score/self::getStat('player_captured_blooms' , $player ) , 'player_average_lost_stones_per_bloom', $oponent_player );  
            }

            if (self::getStat('player_captured_blooms' , $oponent_player ) == 0) {
                self::setStat( 0, 'player_average_lost_stones_per_bloom', $player ); 
                self::setStat( 0, 'player_average_captured_stones_per_bloom', $oponent_player ); 
            } else {
                self::setStat( $oponent_score/self::getStat('player_captured_blooms' , $oponent_player ), 'player_average_lost_stones_per_bloom', $player ); 
                self::setStat( $oponent_score/self::getStat('player_captured_blooms' , $oponent_player ), 'player_average_captured_stones_per_bloom', $oponent_player ); 
            }

            $this->gamestate->nextState("endGame");
        } else {
            self::giveExtraTime( $player);
            $this->activeNextPlayer();
            $this->gamestate->nextState("nextPlayer");
        }
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////

    /*
        zombieTurn:
        
        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
    */

    function zombieTurn( $state, $active_player )
    {
    	$statename = $state['name'];
    	
        if ($state['type'] === "activeplayer") {
            switch ($statename) {
                default:
                    $this->gamestate->nextState( "zombiePass" );
                	break;
            }

            return;
        }

        if ($state['type'] === "multipleactiveplayer") {
            // Make sure player is in a non blocking status for role turn
            $this->gamestate->setPlayerNonMultiactive( $active_player, '' );
            
            return;
        }

        throw new feException( "Zombie mode not supported at this game state: ".$statename );
    }
    
///////////////////////////////////////////////////////////////////////////////////:
////////// DB upgrade
//////////

    /*
        upgradeTableDb:
        
        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.
    
    */
    
    function upgradeTableDb( $from_version )
    {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named "140430-1345",
        // $from_version is equal to 1404301345
        
        // Example:
//        if( $from_version <= 1404301345 )
//        {
//            $sql = "ALTER TABLE xxxxxxx ....";
//            self::DbQuery( $sql );
//        }
//        if( $from_version <= 1405061421 )
//        {
//            $sql = "CREATE TABLE xxxxxxx ....";
//            self::DbQuery( $sql );
//        }
//        // Please add your future database scheme changes here
//
//


    }    
}
