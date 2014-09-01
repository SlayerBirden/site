<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl;

use Maketok\App\Site;
use Maketok\Installer\AbstractArrayMerger;
use Maketok\Installer\MergerException;
use Maketok\Installer\MergerInterface;

class DdlMerger extends AbstractArrayMerger implements MergerInterface
{

    /**
     * Merge arbitrary number of configs
     * Merger assumes that top level keys of config array are client names
     *
     * @internal param $ [array $a1 [, array $a2 [, array $a3]]]
     * @return array
     */
    public function merge()
    {
        $args = func_get_args();
        $this->_mergedConfig = [];
        $conflicts = [];
        foreach ($this->_getSimpleKeys($args) as $el => $cfg) {
            if (($cnt = count($cfg['clients'])) > 1) {
                $this->_sharedKeys = array_merge($this->getSharedKeys(), $cfg['clients']);
                $pairs = [];
                for ($i = 0; $i < $cnt; ++$i) {
                    for ($j = 0; $j < $cnt; ++$j) {
                        // we need this in order to not compare same elements,
                        // and not compare pairs that have been already compared
                        if ($i == $j || isset($pairs[$i . $j]) || isset($pairs[$j . $i])) {
                            continue;
                        }
                        $pairs[$i . $j] = $i . $j;
                        $pairs[$j . $i] = $j . $i;

                        // next structure elements
                        foreach (['columns', 'constraints'] as $type) {
                            $nextElements = $cfg['def'][$i][$type];
                            foreach ($nextElements as $key => $nextDef) {
                                $nextDef2 = $cfg['def'][$j][$type][$key];
                                if (!$this->configElementArrayCompare($nextDef,
                                    $cfg['def'][$j][$type][$key])) {
                                    // conflict
                                    $conflicts[$cfg['clients'][$i]][$type][$key] = $nextDef;
                                    $conflicts[$cfg['clients'][$j]][$type][$key] = $nextDef2;
                                }
                            }
                        }
                        $this->_mergedConfig['shared'][$el] = array_replace_recursive($cfg['def'][$i],
                            $cfg['def'][$j]);
                        $this->_mergedConfig['shared'][$el]['conflicts'] = $conflicts;
                    }
                }
                // now remove elements that are conflicted
                if (isset($this->_mergedConfig['shared'][$el]) &&
                    isset($this->_mergedConfig['shared'][$el]['conflicts'])) {
                    foreach (['columns', 'constraints'] as $type) {
                        foreach ($this->_mergedConfig['shared'][$el][$type] as $key => $nextDef) {
                            foreach ($this->_mergedConfig['shared'][$el]['conflicts'] as $cfg) {
                                if (isset($cfg[$type][$key])) {
                                    // we need to remove if from main array
                                    unset($this->_mergedConfig['shared'][$el][$type][$key]);
                                    break;
                                }
                            }
                        }
                    }
                }
            } else {
                $clientKey = current($cfg['clients']);
                $clientDef = current($cfg['def']);
                $this->_mergedConfig[$clientKey][$el] = $clientDef;
            }
        }
        $this->_sharedKeys = array_unique($this->_sharedKeys);
        return $this->_mergedConfig;
    }

    /**
     * @param array $configs
     * @throws \Maketok\Installer\MergerException
     * @return array
     */
    protected function _getSimpleKeys(array $configs = [])
    {
        if (!isset($this->_simpleKeys)) {
            foreach ($configs as $config) {
                // some error handling
                if (!is_array($config)) {
                    $message = sprintf("One of the Merger arguments is not array, but %s.",
                        get_class($config));
                    Site::getServiceContainer()
                        ->get('logger')
                        ->err($message . "\n" . print_r($config, 1));
                    throw new MergerException($message);
                }
                foreach ($config as $key => $structure) {
                    foreach ($structure as $el => $def) {
                        $this->_simpleKeys[$el]['clients'][] = $key;
                        $this->_simpleKeys[$el]['def'][] = $def;
                    }
                }
            }
        }
        return $this->_simpleKeys;
    }

    /**
     * @param string $key
     * @return array|bool
     */
    public function unMerge($key)
    {
        if (!is_array($this->_mergedConfig)) {
            return false;
        }
        foreach ($this->_getSimpleKeys() as $k => &$el) {
            if (($i = array_search($key, $el['clients'])) !== false) {
                unset($el['clients'][$i]);
                unset($el['def'][$i]);
            }
            if (empty($el['clients'])) {
                unset($this->_simpleKeys[$k]);
            }
        }
        return $this->merge();
    }

    /**
     * @return bool
     */
    public function hasConflicts()
    {
        if (!isset($this->_hasConflicts)) {
            $this->_hasConflicts = false;
            if (isset($this->_mergedConfig['shared'])) {
                foreach ($this->_mergedConfig['shared'] as $element) {
                    if (is_array($element) &&
                        isset($element['conflicts']) &&
                        !empty($element['conflicts'])) {
                        $this->_hasConflicts = true;
                        break;
                    }
                }
            }
        }
        return $this->_hasConflicts;
    }

    /**
     * @return array
     */
    public function getConflictedKeys()
    {
        if (!isset($this->_conflictedKeys)) {
            $this->_conflictedKeys = [];
            if ($this->hasConflicts()) {
                foreach ($this->_mergedConfig['shared'] as $element) {
                    if (is_array($element) &&
                        isset($element['conflicts']) &&
                        !empty($element['conflicts'])) {
                        $this->_conflictedKeys = array_merge($this->_conflictedKeys,
                            array_keys($element['conflicts']));
                    }
                }
                $this->_conflictedKeys = array_unique($this->_conflictedKeys);
            }
        }
        return $this->_conflictedKeys;
    }

    /**
     * @return array
     */
    public function getSharedKeys()
    {
        if (!isset($this->_sharedKeys)) {
            return [];
        }
        return $this->_sharedKeys;
    }
}
