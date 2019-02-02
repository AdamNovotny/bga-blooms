/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * blooms implementation : © © Adam Novotny <adam.novotny.ck@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * blooms.js
 *
 * blooms user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
    "dojo","dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter"
],
function (dojo, declare) {
    return declare("bgagame.blooms", ebg.core.gamegui, {
        constructor: function(){
            console.log('blooms constructor');
              
            this.handlers = [];
            this.stoneSchema = 1;
            this.blockedColor = null;
            this.holes = [];
            this.lastMovesIndicator = 0;

        },
        
        /*
            setup:
            
            This method must set up the game user interface according to current game situation specified
            in parameters.
            
            The method is called each time the game interface is displayed to a player, ie:
            _ when the game starts
            _ when a player refreshes the game page (F5)
            
            "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
        */
        
        setup: function( gamedatas )
        {
            console.log( "Starting game setup" );

            // Setting up player boards
            for( var player_id in gamedatas.players )
            {
                var player = gamedatas.players[player_id];
                         
                var player_board_div = $('player_board_'+player_id);
                dojo.place( this.format_block('jstpl_meeple_panel', {
                    color1: this.getPlayerColors(player_id)[0],
                    color2: this.getPlayerColors(player_id)[1] 
                } ), player_board_div ); 
            }

            if (this.prefs[105].value == 2) {
                this.lastMovesIndicator = 1;
            }

            for( var i in gamedatas.board ){
                var meeple = gamedatas.board[i];
                if( meeple.player !== null ) {
                    if ( meeple.player !== 'hole') {
                        this.addMeepleOnBoard( meeple.x, meeple.y, this.getPlayerColors(meeple.player)[meeple.color], meeple.player );

                        if(meeple.lastmove == 1 && meeple.player != this.player_id) {
                            this.addLastMoveOnBoard( new Array(meeple), false );
                        }

                    } else {
                        dojo.query('#hexagon_'+meeple.x+'_'+meeple.y).addClass('hole');

                        dojo.place( this.format_block( 'jstpl_holepatch', {
                            xxxx:  meeple.x,
                            yyyy: meeple.y,
                            v: 'size'+this.gamedatas.variant,
                        } ) , 'patchplace' );
        
                        this.placeOnObject('patch_'+meeple.x+'_'+meeple.y, 'hexagon_'+meeple.x+'_'+meeple.y);
                        if ( !this.holes[meeple.x] ) {
                            this.holes[meeple.x] = []; 
                        }

                        this.holes[meeple.x].push( meeple.y);
                    }
                }
            }

            if (!this.isSpectator){
                dojo.query('.colorA').addClass(this.getPlayerColors(this.player_id)[0]);
                dojo.query('.colorB').addClass(this.getPlayerColors(this.player_id)[1]);
            }

            this.setupNotations();

            if (this.prefs[104].value == 2) {
                this.changeCoordinations(null);
            }

            dojo.connect(dojo.query('#preference_control_104')[0],'change', this ,'changeCoordinations');
            dojo.connect(dojo.query('#preference_control_105')[0],'change', this ,'changeLastmoves');

            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();

            console.log( "Ending game setup" );
        },
       

        ///////////////////////////////////////////////////
        //// Game & client states
        
        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function( stateName, args )
        {
            console.log( 'Entering state: '+stateName );
            
            switch( stateName )
            {
            
            case 'playerTurn':
                if( this.isCurrentPlayerActive() ) {
                    for(var i=1;i<5;i++) {
                        dojo.query('.colorA').removeClass('color'+i);
                        dojo.query('.colorB').removeClass('color'+i);
                    }

                    dojo.query('.colorA').addClass(this.getPlayerColors(this.player_id)[0]);
                    dojo.query('.colorB').addClass(this.getPlayerColors(this.player_id)[1]);

                    dojo.query('.meeplebutton').removeClass('unvisible');
                    dojo.query('.hexagon:not(.full)').addClass('hooverable');

                    var buttons = dojo.query('.meeplebutton');

                    for(i=0;i<buttons.length;i++) {
                        this.handlers.push( dojo.connect(buttons[i],'click', this ,'meepleClick') );
                    }
                }
            break;

            case 'playerTurn2':
                if( this.isCurrentPlayerActive() ) {
                    dojo.query('.meeplebutton').removeClass('unvisible');
                    dojo.query('.hexagon:not(.full)').addClass('hooverable');

                    if (args.args.occupied == 'A') {
                        dojo.query('.colorA').removeClass(this.getPlayerColors(this.player_id)[0]);
                        dojo.query('.colorA').addClass(this.getPlayerColors(this.player_id)[1]);
                        this.blockedColor = 0;
                    } else {
                        dojo.query('.colorB').removeClass(this.getPlayerColors(this.player_id)[1]);
                        dojo.query('.colorB').addClass(this.getPlayerColors(this.player_id)[0]);
                        this.blockedColor = 1;
                    }
                    // dojo.query('.color'+args.args.occupied).addClass('unvisible');

                    var buttons = dojo.query('.meeplebutton');

                    for(i=0;i<buttons.length;i++) {
                        this.handlers.push( dojo.connect(buttons[i],'click', this ,'meepleClick') );
                    }
                }
            break;  
            
            case 'client_confirmPlacement':
                if( this.isCurrentPlayerActive() ) {
                    var x = args.args.x;
                    var y = args.args.y;
                    this.addMeepleOnBoard(x,y, this.getPlayerColors(parseInt(this.player_id))[args.args.color], this.player_id);

                    for( var i=0;i<6;i++ ) {
                        dojo.place( this.format_block( 'jstpl_fence', {
                            xxx: x,
                            yyy: y,
                            zzz: i,
                            variant: 'size'+this.gamedatas.variant
                        } ) , 'fenceplace' );
        
                        this.placeOnObject('fence_'+x+'_'+y+'_'+i,  'hexagon_'+x+'_'+y);
                        dojo.query('#fence_'+x+'_'+y+'_'+i).addClass('yellowdotted');
                    }

                    dojo.query('#meepleitem_'+x+'_'+y).addClass('clickable');
                    this.handlers.push( dojo.connect(dojo.query('#meepleitem_'+x+'_'+y)[0],'click', this ,  dojo.partial(this.meepleClickConfirm, x,y,args.args.color) ) );
                }

            break;
           
            case 'dummmy':
                break;
            }
        },

        // onLeavingState: this method is called each time we are leaving a game state.
        //                 You can use this method to perform some user interface changes at this moment.
        //
        onLeavingState: function( stateName )
        {
            console.log( 'Leaving state: '+stateName );
            
            switch( stateName )
            {
            
            case 'playerTurn':
                    dojo.query('.hexagon').removeClass('hooverable');
                    dojo.forEach(this.handlers,dojo.disconnect);
                    this.handlers = [];
            break;
           
            case 'playerTurn2':
                    dojo.query('.hexagon').removeClass('hooverable');
                    dojo.forEach(this.handlers,dojo.disconnect);
                    this.handlers = [];
                    this.blockedColor = null;
            break;   
            
            case 'client_confirmPlacement':
                dojo.query('.fence:not(.fencelastmove)').forEach(dojo.destroy);
                dojo.forEach(this.handlers,dojo.disconnect);
                this.handlers = [];
                dojo.query('.meepleitem').removeClass('clickable')
            break;
           
            case 'dummmy':
                break;
            }               
        }, 

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //        
        onUpdateActionButtons: function( stateName, args )
        {
            console.log( 'onUpdateActionButtons: '+stateName );
                      
            if( this.isCurrentPlayerActive() )
            {            
                switch( stateName )
                {
                    case 'playerTurn2':                    
                        this.addActionButton( 'button_pass', _('pass'), 'pass_turn' );  
                    break;

                    case 'client_confirmPlacement':                  
                        this.addActionButton( 'button_confirm', _('Confirm'), dojo.partial(this.meepleClickConfirm, args.x,args.y,args.color) );  
                        this.addActionButton( 'button_cancel', _('Cancel'), dojo.partial(this.cancleMove, args.x,args.y,args.color) ); 
                    break;
                }
            }
        },        

        ///////////////////////////////////////////////////
        //// Utility methods
        
        /*
        
            Here, you can defines some utility methods that you can use everywhere in your javascript
            script.
        
        */

        addMeepleOnBoard: function(x,y, color, player_id) {
            dojo.place( this.format_block( 'jstpl_meepleitem', {
                color: color,
                s: 'size'+this.gamedatas.variant,
                xxx: x,
                yyy: y,
                cschema: 'meepleschema'+this.stoneSchema,
            } ) , 'meepleplace' );

            dojo.query('#hexagon_'+x+'_'+y).addClass('full');
            this.placeOnObject('meepleitem_'+x+'_'+y, "overall_player_board_"+player_id);
            var anim = this.slideToObject( 'meepleitem_'+x+'_'+y, 'hexagon_'+x+'_'+y ).play();
        },

        getPlayerColors: function(player_id) {
            var order = this.gamedatas.players[player_id].player_no;

            if (order == 1) {
                return Array('color1','color2');
            } else {
                return Array('color3','color4');
            }
        },

        scoreMeepleAnddestroy: function(hex, player_id) {
            this.slideToObjectAndDestroy( 'meepleitem_'+hex[0]+'_'+hex[1], "overall_player_board_"+player_id, 1000,0 );
            dojo.query('#hexagon_'+hex[0]+'_'+hex[1]).removeClass('full');
        },

        returnMeepleAnddestroy: function(x,y, player_id) {
            this.slideToObjectAndDestroy( 'meepleitem_'+x+'_'+y, "overall_player_board_"+player_id, 1000,0 );
            dojo.query('#hexagon_'+x+'_'+y).removeClass('full');
        },

        addFenceOnBoard: function(border ) {
            var x = border.hex[0];
            var y = border.hex[1];

            for( var i=0;i<border.borders.length;i++ ) {
                dojo.place( this.format_block( 'jstpl_fence', {
                    xxx: x,
                    yyy: y,
                    zzz: border.borders[i],
                    variant: 'size'+this.gamedatas.variant
                } ) , 'fenceplace' );

                this.placeOnObject('fence_'+x+'_'+y+'_'+border.borders[i],  'meepleitem_'+x+'_'+y);
            }
        },

        addLastMoveOnBoard: function(hexes, deleteLast) {

            if (deleteLast) {
                dojo.query('.fencelastmove').forEach(dojo.destroy);
            }

            if (this.lastMovesIndicator == 0) {
                var visible = 'unvisible';
            } else {
                var visible = "";
            }

            for (var j=0;j<hexes.length;j++) {
                var x = hexes[j].x;
                var y = hexes[j].y;

                for( var i=0;i<6;i++ ) {
                    dojo.place( this.format_block( 'jstpl_lastmove', {
                        xxx: x,
                        yyy: y,
                        zzz: i,
                        variant: 'size'+this.gamedatas.variant,
                        visibility: visible,
                    } ) , 'fenceplace' );

                    this.placeOnObject('move_'+x+'_'+y+'_'+i,  'hexagon_'+x+'_'+y);
                }
            }
        },

        setupNotations: function() {
            var xnumber = parseInt(this.gamedatas.variant);
            var max = 2*xnumber-1;
            var alphabet = "ABCDEFGHIJKLMN";
            var add = 0;

            for(i=0;i<max;i++) {

                ynumber = -Math.abs(i-Math.floor(max/2))+Math.floor(max/2);
                add = i> Math.floor(max/2) ? (add+1):0;

                for(j=0;j<(xnumber+ynumber);j++) {
                    dojo.place( this.format_block( 'jstpl_notation', {
                        i: i,
                        j: j,
                        letter: alphabet[i],
                        number: j+add,
                    } ) , 'notationplace' );
    
                    if (this.holes[i]) {
                        if ( this.holes[i][0] == j || this.holes[i][1] == j) {
                            dojo.query('#'+i+'_'+j).addClass('notationhole');
                        }
                    }
                    this.placeOnObject(i+'_'+j,  'hexagon_'+i+'_'+j);
                }
            }
        },

        //--------------------------/** Log injection */-------------------------------------------------------

        /* @Override */
        format_string_recursive : function(log, args) {
            try {
                if (log && args && !args.processed) {

                    args.processed = true;
                    
                    if (!this.isSpectator){
                        args.You = this.divYou(); 
                    }

                    var keys = ['stone','location'];

                    for ( var i in keys) {
                        var key = keys[i];

                        if (typeof args[key] == 'string' ) {
                            args[key] = this.getTokenDiv(key, args);                            
                        }
                    }

                }
            } catch (e) {
                console.error(log,args,"Exception thrown", e.stack);
            }
            return this.inherited(arguments);
        },

        divYou : function() {
            var color = this.gamedatas.players[this.player_id].color;
            var color_bg = "";
            if (this.gamedatas.players[this.player_id] && this.gamedatas.players[this.player_id].color_back) {
                color_bg = "background-color:#" + this.gamedatas.players[this.player_id].color_back + ";";
            }
            var you = "<span style=\"font-weight:bold;color:#" + color + ";" + color_bg + "\">" + __("lang_mainsite", "You") + "</span>";
            return you;
        },

        getTokenDiv : function(key, args) {
            var token = args[key];
            var item_type = token.split("_")[0];

            switch (item_type) {
                case 'meeple':
                    var tokenDiv = this.format_block('jstpl_meeple_log', {
                        color: this.getPlayerColors( token.split("_")[1])[ token.split("_")[2]],
                    }); 
                    return tokenDiv;

                case 'notation':
                    var tokenDiv = this.format_block('jstpl_notation_log', {
                        text: token.split("_")[1],
                    }); 
                    return tokenDiv;    

                default:
                    break;
            }
            return "'" + _('stone') + "'";;
       },

        // changeborders: function(evt) {
        //     dojo.stopEvent( evt );
        //     var target = evt.target || evt.srcElement;

        //     if ( target.id.slice(-1) == 1 ) {
        //         var hexes = dojo.query('.hexagon');
        //         for(i=0;i<hexes.length;i++) {
        //             dojo.addClass( hexes[i],'noborders');
        //         }

        //         hexes = dojo.query('.hexagonbackground');
        //         for(i=0;i<hexes.length;i++) {
        //             dojo.addClass( hexes[i],'noborders');
        //         }
        //     } else {
        //         var hexes = dojo.query('.hexagon');
        //         for(i=0;i<hexes.length;i++) {
        //             dojo.removeClass( hexes[i],'noborders');
        //         }

        //         hexes = dojo.query('.hexagonbackground');
        //         for(i=0;i<hexes.length;i++) {
        //             dojo.removeClass( hexes[i],'noborders');
        //         }
        //     }
        // },

        // changecolor: function(evt) {
        //     dojo.stopEvent( evt );
        //     var target = evt.target || evt.srcElement;

        //     for (var i=1;i<4;i++) {
        //         dojo.query('.hexagon').removeClass('colorscheme'+i);
        //         dojo.query('.hexagonbackground').removeClass('colorschemebackground'+i);
        //     }

        //     dojo.query('.hexagon').addClass('colorscheme'+target.id.slice(-1));
        //     dojo.query('.hexagonbackground').addClass('colorschemebackground'+target.id.slice(-1));
        // },

        // changestone: function(evt) {
        //     dojo.stopEvent( evt );
        //     var target = evt.target || evt.srcElement;

        //     for (var i=1;i<7;i++) {
        //         dojo.query('.meepleitem').removeClass('meepleschema'+i);
        //         dojo.query('.meeplebutton').removeClass('schema'+i);
        //     }
        //     dojo.query('.meepleitem').addClass('meepleschema'+target.id.slice(-1));
        //     dojo.query('.meeplebutton').addClass('schema'+target.id.slice(-1));
        //     this.stoneSchema = parseInt(target.id.slice(-1));
        // },

        changeCoordinations: function(evt) {
            if (evt) {
                dojo.stopEvent( evt );
            }
            dojo.query('.notation').toggleClass('unvisible');
        },

        changeLastmoves: function(evt) {
            dojo.stopEvent( evt );
            if (this.lastMovesIndicator == 0) {
                this.lastMovesIndicator = 1;
            } else {
                this.lastMovesIndicator = 0;
            }

            dojo.query('.fencelastmove').toggleClass('unvisible');
        },

        ///////////////////////////////////////////////////
        //// Player's action
        
        /*
        
            Here, you are defining methods to handle player's action (ex: results of mouse click on 
            game objects).
            
            Most of the time, these methods:
            _ check the action is possible at this game state.
            _ make a call to the game server
        
        */

        meepleClick: function(evt) {
            dojo.stopEvent( evt );

            if( ! this.checkAction( 'placePawn' ) ){
                return; 
            }

            var target = evt.target || evt.srcElement;
            var result = target.id.split("_");

            if (this.blockedColor == null) {
                if (result[3] == 'left') {
                    var color = 0;
                } else {
                    var color = 1;
                }
            } else {
                if (this.blockedColor == 1) {
                    var color = 0;
                } else {
                    var color = 1;
                }
            }

            dojo.query('.meeplebutton').addClass('unvisible');

            this.setClientState( 'client_confirmPlacement', {                
                "descriptionmyturn" : _('${You} must confirm the stone placement'),
                "possibleactions" : ["confirmPlacement", "cancelPlacement"],
                "args" : {"You": '', "x": result[1], "y": result[2],  "color" : color} 
            } );
        },        

        meepleClickConfirm: function(x,y, color, evt) {
            dojo.stopEvent( evt );

            if( ! this.checkAction( 'confirmPlacement' ) ){
                return; 
            }

            this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/placePawn.html", {player: this.player_id, x: x , y: y, color: color, lock : true}, 
                    this, function(result) {}, function(is_error) {
            });
        },

        cancleMove: function(x,y, color, evt) {
            dojo.stopEvent( evt );

            if( ! this.checkAction( 'cancelPlacement' ) ){
                return; 
            }

            this.returnMeepleAnddestroy(x,y,this.player_id);
            this.restoreServerGameState();
        },

        pass_turn: function(evt) {
            dojo.stopEvent( evt );

            if( ! this.checkAction( 'pass' ) ){
                return; 
            }

            dojo.query('.meeplebutton').addClass('unvisible');

            this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/pass.html", {lock : true}, 
                this, function(result) {}, function(is_error) {
            });
        },

        
        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        /*
            setupNotifications:
            
            In this method, you associate each of your game notifications with your local method to handle it.
            
            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your blooms.game.php file.
        
        */
        setupNotifications: function() {
            console.log( 'notifications subscriptions setup' );
            
            dojo.subscribe( 'pawnPlaced', this, "notif_pawnPlaced" );
            this.notifqueue.setSynchronous( 'pawnPlaced', 500 );
            dojo.subscribe( 'pass', this, "notif_pass" );
            dojo.subscribe( 'fenceBloom', this, "notif_fenceBloom" );
            this.notifqueue.setSynchronous( 'fenceBloom', 1000 );
            dojo.subscribe( 'captureBloom', this, "notif_captureBloom" );
            this.notifqueue.setSynchronous( 'captureBloom', 1000 );
            dojo.subscribe( 'destroyFence', this, "notif_destroyFence" );
            this.notifqueue.setSynchronous( 'notif_destroyFence', 1000 );
            dojo.subscribe( 'showLastMove', this, "notif_showLastMove" );
        },  
        

        notif_pawnPlaced: function(notif) {
            if ($('meepleitem_'+notif.args.x+'_'+notif.args.y)) {

            } else {
                this.addMeepleOnBoard(notif.args.x, notif.args.y,this.getPlayerColors(parseInt(notif.args.player_id))[notif.args.color], notif.args.player_id );
            }
            
        },

        notif_pass: function(notif) {

        },

        notif_fenceBloom: function(notif) {
            for(var i=0;i<notif.args.borders.length ;i++) {
                this.addFenceOnBoard(notif.args.borders[i]);
            }
        },

        notif_captureBloom: function(notif) {
            for(i=0;i<notif.args.x ;i++) {
                this.scoreMeepleAnddestroy(notif.args.hexes[i], notif.args.player_id);
            }

            this.scoreCtrl[ notif.args.player_id].incValue( notif.args.x );
        },

        notif_destroyFence: function(notif) {
            dojo.query('.fence:not(.fencelastmove)').forEach(dojo.destroy);
        },

        notif_showLastMove: function(notif) {
            this.addLastMoveOnBoard(notif.args.last_moves, true);
        },


   });             
});
