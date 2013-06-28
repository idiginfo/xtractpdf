<?php
  
/**
 * XtractPDF - A PDF Content Extraction and Curation Tool
 *
 * This program is free software under the GNU General Public License (v2)
 * See LICENSE.md for a complete copy of the license
 *
 * @package     XtractPDF
 * @author      Florida State University iDigInfo (http://idiginfo.org)
 * @copyright   Copyright (C) 2013 Florida State University (http://fsu.edu)
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt
 * @link        http://idiginfo.org
 */

// ------------------------------------------------------------------

namespace XtractPDF\Helper;

/**
 * Compute the difference between two arrays (recursively)
 */
class ArrayDifferator
{
    /**
     * Returns all of the values in $aArray1 that are not in $aArray2 (recursively)
     *
     * Static convenience method
     *
     * @param array $aArray1
     * @param array $aArray2
     */
    public static function recursiveDiff($aArray1, $aArray2)
    {
        $clsName = get_called_class();
        $that = new $clsName;
        return $that->arrayRecursiveDiff($aArray1, $aArray2);
    }

    // --------------------------------------------------------------

    /**
     * Returns all of the values in $aArray1 that are not in $aArray2 (recursively)
     *
     * @param array $aArray1
     * @param array $aArray2
     */
    public function arrayRecursiveDiff($aArray1, $aArray2)
    {
        $aReturn = array();

        foreach ($aArray1 as $mKey => $mValue) {

            if (array_key_exists($mKey, $aArray2)) {
                if (is_array($mValue)) {
                    $aRecursiveDiff = $this->arrayRecursiveDiff($mValue, $aArray2[$mKey]);
                    if (count($aRecursiveDiff)) { 
                        $aReturn[$mKey] = $aRecursiveDiff; 
                    }
                } 
                else {
                    if ($mValue != $aArray2[$mKey]) {
                        $aReturn[$mKey] = $mValue;
                    }
                }
            }
            else {
                $aReturn[$mKey] = $mValue;
            }
        }

        return $aReturn;
    } 
}

/* EOF: ArrayDifferator.php */