<?php

// ------------------------------------------------------------------------

/**
 * Stampa un print_r formattato con <pre>.
 * Passando FALSE come secondo parametro, restituisce la stringa.
 *
 * @access public
 * @param mixed $d
 * @param mixed $echo (default: TRUE)
 * @return void
 */
if ( ! function_exists('pr')) {
	function pr($d, $echo = TRUE) {
		if ($echo) {
			echo '<pre>'. print_r($d, TRUE) .'</pre>';
		} else {
			return print_r($d, TRUE);
		}
	}
}

// --------------------------------------------------------------------------

/**
 * Esce dopo pr.
 *
 * @access public
 * @param mixed $d
 * @return void
 */
if ( ! function_exists('prd')) {
	function prd($d) {
	   pr($d);
	   die;
	}
}


// --------------------------------------------------------------------------

/**
 * Funzione pr() con lista di argomenti.
 *
 * @access public
 * @param mixed $d
 * @return void
 */
if ( ! function_exists('prl')) {
	function prl(...$d) {
	   pr($d);
	}
}

// --------------------------------------------------------------------------

/**
 * Funzione prd() con lista di argomenti.
 *
 * @access public
 * @param mixed $d
 * @return void
 */
if ( ! function_exists('prdl')) {
	function prdl(...$d) {
	   prd($d);
	}
}

// --------------------------------------------------------------------------

/**
 * Formatta un array assegnando una chiave scelta ed i restanti dati
 * sono assegnati alla chiave scelta
 *
 * @param array<string,mixed>   $array    Array da formattare
 * @param string                $key      Chiave da utilizzare
 *
 * @return array
 */
if ( ! function_exists('array_format_by_key') ) {

    function array_format_by_key(array $array, string $key) {

        $formatted = [];

        foreach ( $array as $a ) {
            $formatted[$a[$key]] = $a;
        }

        return $formatted;
    }
}

// --------------------------------------------------------------------------
