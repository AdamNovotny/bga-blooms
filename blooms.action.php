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
 * blooms.action.php
 *
 * blooms main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *       
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/blooms/blooms/myAction.html", ...)
 *
 */
  
  
  class action_blooms extends APP_GameAction
  { 
    // Constructor: please do not modify
   	public function __default()
  	{
  	    if( self::isArg( 'notifwindow') )
  	    {
            $this->view = "common_notifwindow";
  	        $this->viewArgs['table'] = self::getArg( "table", AT_posint, true );
  	    }
  	    else
  	    {
            $this->view = "blooms_blooms";
            self::trace( "Complete reinitialization of board game" );
      }
  	} 

    public function placePawn() {
        self::setAjaxMode();     
        $x = self::getArg( "x", AT_posint, true );
        $y = self::getArg( "y", AT_posint, true );
        $player = self::getArg( "player", AT_posint, true );
        $color = self::getArg( "color", AT_posint, true );
        $this->game->placePawn( $x,$y, $player, $color );
        self::ajaxResponse();
    }

    public function pass() {
        self::setAjaxMode();     
        $this->game->passTurn();
        self::ajaxResponse();
    }

  }
  

