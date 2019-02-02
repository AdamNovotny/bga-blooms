{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- blooms implementation : © Adam Novotny <adam.novotny.ck@gmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------
-->

<div id="board_wrap" class="board_wrap">
    <div id="board" class="board">

        <div class="innerboard">
            <div class="hexagonbackground hexagonbackground{bg_size}">
            </div>
        </div>

        <div class="innerboard" id="patchplace">

        </div>

        <div class="innerboard">
            <div  class="center">
            <!-- BEGIN hexRows -->
            <div id="row_{x}" class="hexrow" style="width:{width}px; margin: {percent}% auto;">
                    <!-- BEGIN hexTiles -->
                            <div class="hexagon_wrapper">
                                <div id="hexagon_{x}_{y}" class="hexagon {size}">
                                    <span>
                                        <div class="insidehex">
                                            <div class="meeplebutton colorA {size} {schema}" id="hexagon_{x}_{y}_left"></div>
                                            <div class="meeplebutton colorB {size} {schema}" id="hexagon_{x}_{y}_right"></div>
                                        </div>
                                    </span>
                                </div>
                            </div>
                    <!-- END hexTiles -->
            </div>
            <!-- END hexRows -->
            </div>
        </div>

        <div class="innerboard" id="notationplace" style="visibility: hidden;">
        </div>

        <div class="innerboard" id="meepleplace" style="visibility: hidden;">
        </div>

        <div class="innerboard" id="fenceplace" style="visibility: hidden;">
        </div>

    </div>
</div>

<script type="text/javascript">

var jstpl_meepleitem = '<div class="meepleitem ${color} ${s} ${cschema}" id="meepleitem_${xxx}_${yyy}"></div>';
var jstpl_fence = '<div class="fence fence${zzz} ${variant}" id="fence_${xxx}_${yyy}_${zzz}"></div>';
var jstpl_meeple_panel = '<div style="display: flex;justify-content: center;"><div class="meepleitem logmeeple panelmeeple ${color1}"></div><div class="meepleitem logmeeple panelmeeple ${color2}"></div></div>';
var jstpl_meeple_log = '<div class="meepleitem logmeeple small ${color}"></div>';
var jstpl_notation_log = '<span>${text}</span><div style="display: block; height: 15px;"></div>';
var jstpl_holepatch = '<div class="holepatch ${v}" id="patch_${xxxx}_${yyyy}"></div>';
var jstpl_notation = '<div class="notation" id="${i}_${j}">${letter}${number}</div>';
var jstpl_lastmove = '<div class="fence fence${zzz} ${variant} fencelastmove ${visibility}" id="move_${xxx}_${yyy}_${zzz}"></div>';

</script>  

{OVERALL_GAME_FOOTER}
