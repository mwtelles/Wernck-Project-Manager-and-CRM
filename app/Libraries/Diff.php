<?php

namespace App\Libraries;

class Diff {

    public function htmlDiff($old, $new) {
        $ret = '';
        $diff = $this->createDiff(preg_split("/[\s]+/", $old), preg_split("/[\s]+/", $new));
        foreach ($diff as $k) {
            if (is_array($k))
                $ret .= (!empty($k['d']) ? "<del>" . implode(' ', $k['d']) . "</del> " : '') .
                        (!empty($k['i']) ? "<ins>" . implode(' ', $k['i']) . "</ins> " : '');
            else
                $ret .= $k . ' ';
        }
        return $ret;
    }

    private function createDiff($old, $new) {
        $matrix = array();
        $maxlen = 0;
        foreach ($old as $oindex => $ovalue) {
            $nkeys = array_keys($new, $ovalue);
            foreach ($nkeys as $nindex) {
                $matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1]) ?
                        $matrix[$oindex - 1][$nindex - 1] + 1 : 1;
                if ($matrix[$oindex][$nindex] > $maxlen) {
                    $maxlen = $matrix[$oindex][$nindex];
                    $omax = $oindex + 1 - $maxlen;
                    $nmax = $nindex + 1 - $maxlen;
                }
            }
        }
        if ($maxlen == 0)
            return array(array('d' => $old, 'i' => $new));
        return array_merge(
                $this->createDiff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),
                array_slice($new, $nmax, $maxlen),
                $this->createDiff(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen)));
    }

}
