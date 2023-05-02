<?php
defined('ABSPATH') || exit;

/**
 * @package   OMGF Pro
 * @author    Daan van den Bergh
 *            
 * @copyright © 2022 Daan van den Bergh
 * @license   BY-NC-ND-4.0
 *            http://creativecommons.org/licenses/by-nc-nd/4.0/
 */
class OmgfPro_UnicodeRanges
{
    const MAP = [
        "arabic"       => "U+0600-06FF,U+200C-200E,U+2010-2011,U+204F,U+2E41,U+FB50-FDFF,U+FE80-FEFC",
        "bengali"      => "U+0964-0965,U+0981-09FB,U+200C-200D,U+20B9,U+25CC",
        "cyrillic"     => "U+0400-045F,U+0490-0491,U+04B0-04B1,U+2116",
        "cyrillic-ext" => "U+0460-052F,U+1C80-1C88,U+20B4,U+2DE0-2DFF,U+A640-A69F,U+FE2E-FE2F",
        "devanagari"   => "U+0900-097F,U+1CD0-1CF6,U+1CF8-1CF9,U+200C-200D,U+20A8,U+20B9,U+25CC,U+A830-A839,U+A8E0-A8FB",
        "greek"        => "U+0370-03FF",
        "greek-ext"    => "U+1F00-1FFF",
        "gujarati"     => "U+0964-0965,U+0A80-0AFF,U+200C-200D,U+20B9,U+25CC,U+A830-A839",
        "gurmukhi"     => "U+0964-0965,U+0A01-0A75,U+200C-200D,U+20B9,U+25CC,U+262C,U+A830-A839",
        "hebrew"       => "U+0590-05FF,U+20AA,U+25CC,U+FB1D-FB4F",
        "khmer"        => "U+1780-17FF,U+200C,U+25CC",
        "kannada"      => "U+0964-0965,U+0C82-0CF2,U+200C-200D,U+20B9,U+25CC",
        "latin"        => "U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+2000-206F,U+2074,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD",
        "latin-ext"    => "U+0100-024F,U+0259,U+1E00-1EFF,U+2020,U+20A0-20AB,U+20AD-20CF,U+2113,U+2C60-2C7F,U+A720-A7FF",
        "malayalam"    => "U+0307,U+0323,U+0964-0965,U+0D02-0D7F,U+200C-200D,U+20B9,U+25CC",
        "myanmar"      => "U+1000-109F,U+200C-200D,U+25CC",
        "oriya"        => "U+0964-0965,U+0B01-0B77,U+200C-200D,U+20B9,U+25CC",
        "tamil"        => "U+0964-0965,U+0B82-0BFA,U+200C-200D,U+20B9,U+25CC",
        "telugu"       => "U+0951-0952,U+0964-0965,U+0C00-0C7F,U+1CDA,U+200C-200D,U+25CC",
        "thai"         => "U+0E01-0E5B,U+200C-200D,U+25CC",
        "vietnamese"   => "U+0102-0103,U+0110-0111,U+1EA0-1EF9,U+20AB",
        "sinhala"      => "U+0964-0965,U+0D82-0DF4,U+200C-200D,U+25CC"
    ];
}
