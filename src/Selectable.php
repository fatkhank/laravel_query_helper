<?php

namespace Hamba\QueryGet;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

trait Selectable
{
    /**
     * Get mapping for selection
     *
     * @param [type] $opt
     * @return void
     */
    public static function getSelectMapping($opt){
        $classObj = new static;
        $className = get_class($classObj);

        //get from queryables
        $queryableCollection = collect();
        if(method_exists($className, 'collectNormalizedQueryables')){
            $queryableCollection = $className::collectNormalizedQueryables('key');
        }

        //get from selectable
        $selectables = $classObj->selectable;

        //merge all mapping
        $merged = $queryableCollection->merge($selectables);

        //make mergeds uniform
        $normalized = $merged->mapWithKeys(function ($key, $alias) {
            if (is_numeric($alias)) {
                //no alias used
                $alias = $key;
                $config = [
                    'key' => $key
                ];
            } else {
                //alias is used
                if(is_array($key)){
                    //use key as config
                    $config = $key;
                }else{
                    $config = [];
                }

                //add alias as key if key config not specified
                if(!array_key_exists('key', $config)){
                    $config['key'] = $alias;
                }
            }

            return [$alias => $config];
        });

        //filter selections by option
        if(array_key_exists('only', $opt)){
            $only = array_keys($opt['only']);
            $normalized = QG::onlyKeys($normalized, $only);
        }else if(array_key_exists('except', $opt)){
            $except = array_keys($opt['except']);
            $normalized = QG::exceptKeys($normalized, $except);
        }

        return $normalized->toArray();
    }
}
