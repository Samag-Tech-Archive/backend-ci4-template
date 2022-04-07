<?php

namespace SamagTech\Core;

/**
 * Handler per funzionalità da utilizzare nel BaseModel
 *
 * @author Alessandro Marotta <alessandro.marotta@samag.tech>
 */
class BaseModelHandler {

    //---------------------------------------------------------------------------------------------------

    /**
     * Restituisce la formattazione select per la query
     *
     * @static
     *
     * @param string            $table      Tabella su cui viene eseguita la query
     * @param array<int,string> $select     Continene i campi della select (Default [])
     *
     * @return string
     */
    public static function getSelect(string $table, array $select = []) : string {

        if ( empty($select) )  return '*';

        foreach ( $select as &$s ) {

            $s = str_contains($s, '.') ? $table . '.' .$s : $s;
        }

        return implode(',', $select);
    }

    //---------------------------------------------------------------------------------------------------

    /**
     * Restituisce la formattazione del GROUP BY
     *
     * @static
     *
     * @param array<int,string> $groupBy    Contiene i campi del group by
     *
     * @return string
     */
    public static function getGroupBy(array $groupBy) : string {
        return implode(',', $groupBy);
    }

}