<?php
defined('ABSPATH') || exit;

/* * * * * * * * * * * * * * * * * * * * * *
 * @author   : Daan van den Bergh
 * @url      : 
 * @copyright: (c) Daan van den Bergh
 * @license  : GPL2v2 or later
 * * * * * * * * * * * * * * * * * * * * * */

class OmgfPro_FallbackFontStacks
{
    const MAP = [
        ''            => '',
        'arial'       => 'Arial, "Helvetica Neue", Helvetica, sans-serif',
        'baskerville' => 'Baskerville, "Baskerville Old Face", Garamond, "Times New Roman", serif',
        'bodoni-mt'   => '"Bodoni MT", "Bodoni 72", Didot, "Didot LT STD", "Hoefler Text", Garamond, "Times New Roman", serif',
        'calibri'     => 'Calibri, Candara, Segoe, "Segoe UI", Optima, Arial, sans-serif',
        'calisto-mt'  => '"Calisto MT", "Bookman Old Style", Bookman, "Goudy Old Style", Garamond, "Hoefler Text", "Bitstream Charter", Georgia, serif',
        'cambria'     => 'Cambria, Georgia, serif',
        'candara'     => 'Candara, Calibri, Segoe, "Segoe UI", Optima, Arial, sans-serif',
        'century-gothic' => '"Century Gothic", CenturyGothic, AppleGothic, sans-serif',
        'consolas'       => 'Consolas, monaco, monospace',
        'copperplate-gothic' => '"Copperplate Gothic", "Copperplate Gothic Light", fantasy',
        'courier-new'        => '"Courier New", Courier, "Lucida Sans Typewriter", "Lucida Typewriter", monospace',
        'dejavu-sans'        => '"Dejavu Sans", Arial, Verdana, sans-serif',
        'didot'              => 'Didot, "Didot LT STD", "Hoefler Text", Garamond, "Calisto MT", "Times New Roman", serif',
        'franklin-gothic'    => '"Franklin Gothic", "Arial Bold"',
        'garamond'           => 'Garamond, Baskerville, "Baskerville Old Face", "Hoefler Text", "Times New Roman", serif',
        'georgia'            => 'Georgia, Times, "Times New Roman", serif',
        'gill-sans'          => '"Gill Sans", "Gill Sans MT", Calibri, sans-serif',
        'goudy-old-style'    => '"Goudy Old Style", Garamond, "Big Caslon", "Times New Roman", serif',
        'helvetica'          => '"Helvetica Neue", Helvetica, Arial, sans-serif',
        'impact'             => 'Impact, Charcoal, "Helvetica Inserat", "Bitstream Vera Sans Bold", "Arial Black", sans-serif',
        'lucida-bright'      => '"Lucida Bright", Georgia, serif',
        'lucida-sans'        => '"Lucida Sans", Helvetica, Arial, sans-serif',
        'ms-sans-serif'      => '"MS Sans Serif", sans-serif',
        'optima'             => 'Optima, Segoe, "Segoe UI", Candara, Calibri, Arial, sans-serif',
        'palatino'           => 'Palatino, "Palatino Linotype", "Palatino LT STD", "Book Antiqua", Georgia, serif',
        'perpetua'           => 'Perpetua, Baskerville, "Big Caslon", "Palatino Linotype", Palatino, serif',
        'rockwell'           => 'Rockwell, "Courier Bold", Courier, Georgia, Times, "Times New Roman", serif',
        'segoe-ui'           => '"Segoe UI", Frutiger, "Dejavu Sans", "Helvetica Neue", Arial, sans-serif',
        'tahoma'             => 'Tahoma, Verdana, Segoe, sans-serif',
        'trebuchet-ms'       => '"Trebuchet MS", "Lucida Grande", "Lucida Sans Unicode", "Lucida Sans", sans-serif',
        'verdana'            => 'Verdana, Geneva, sans-serif'
    ];
}
