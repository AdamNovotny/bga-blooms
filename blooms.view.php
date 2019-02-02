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
 * blooms.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in blooms_blooms.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */
  
  require_once( APP_BASE_PATH."view/common/game.view.php" );
  
  class view_blooms_blooms extends game_view
  {
    function getGameName() {
        return "blooms";
    }    
  	function build_page( $viewArgs )
  	{		
  	    // Get players & players number
        $players = $this->game->loadPlayersBasicInfos();
        $players_nbr = count( $players );

        /*********** Place your code below:  ************/

        $variant = $this->game->getGameStateValue( 'board_size');
        $this->tpl['bg_size'] = $variant;

        $this->page->begin_block( "blooms_blooms", "hexTiles" );
        $this->page->begin_block( "blooms_blooms", "hexRows" );

        for($i=0;$i<count($this->game->hex_row_counts[$variant]);$i++) {

            $this->page->reset_subblocks( 'hexTiles' ); 
            for($j=0;$j<$this->game->hex_row_counts[$variant][$i];$j++) {
                $this->page->insert_block( "hexTiles", array( 
                    "x" => $i,
                    "y" => $j,
                    "size" => 'hexagon'.$variant,
                    "schema" => 'schema1'
                    ) );
            }

            $this->page->insert_block( "hexRows", array( 
                "x" => $i,
                 "width" => $this->game->hex_row_counts[$variant][$i]*$this->game->hex_size[$variant]+
                            $this->game->hex_row_counts[$variant][$i]*$this->game->space_between[$variant]+
                            $this->game->space_between[$variant],
                "percent" => $this->game->row_percentage[$variant], 
                ) );
        }

        /*********** Do not change anything below this line  ************/
  	}
  }
  

