<?php

/**
 * Cut string as UTF8
 *
 * @param string $string string
 * @param integer $length max words to reveal
 * @param string $symbol content replacement
 */
function smarty_modifier_cut_str($string, $length = 30, $symbol = '...') {
	return $app->cutStr($string, $length, $symbol);
}
