<?php

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
    public static function arrayRecDiff($aArray1, $aArray2)
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