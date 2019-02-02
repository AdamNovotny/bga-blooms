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
 * material.inc.php
 *
 * blooms game material description
 *
 * Here, you can describe the material of your game with PHP variables.
 *   
 * This file is loaded in your game logic class constructor, ie these variables
 * are available everywhere in your game logic code.
 *
 */



$this->hex_row_counts = array(
    4 => array(4,5,6,7,6,5,4),
    5 => array(5,6,7,8,9,8,7,6,5),
    6 => array(6,7,8,9,10,11,10,9,8,7,6),
    7 => array(7,8,9,10,11,12,13,12,11,10,9,8,7)
);

$this->hex_size = array(
  4 => 70, 
  5 => 70,
  6 => 52,
  7 => 50,
);

$this->space_between = array(
  4 => 3, 
  5 => 2,
  6 => 2,
  7 => 2,
);

$this->row_percentage = array(
  4 => 4.45, 
  5 => 3.45,
  6 => 2.85,
  7 => 1.5,
);

$this->holes = array(
  4 => array(), 
  5 => array( array(2,3),array(6,3)  ),
  6 => array( array(3,2),array(3,6),array(7,2),array(7,6)   ),
  7 => array(), 
);





