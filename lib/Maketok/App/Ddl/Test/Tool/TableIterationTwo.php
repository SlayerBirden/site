<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
namespace Maketok\App\Ddl\Test\Tool;

use Maketok\App\Ddl\InstallerApplicableInterface;

class TableIterationTwo implements InstallerApplicableInterface
{

    /**
     * @return array
     */
    public static function getDdlConfig()
    {
        return array(
            'table_one' => array(
                'columns' => array(
                    'id' => array(
                        'type' => 'integer',
                        'nullable' => false,
                    ),
                    'title' => array(
                        'type' => 'varchar',
                        'length' => '255',
                    ),
                    'code' => array(
                        'type' => 'varchar',
                        'length' => '255',
                    ),
                    'raw_data' => array(
                        'type' => 'blob',
                        'length' => '255',
                    ),
                    'data' => array(
                        'type' => 'text',
                    ),
                    'created_at' => array(
                        'type' => 'datetime',
                    ),

                ),
                'constraints' => array(
                    'primary' => array(
                        'type' => 'primaryKey',
                        'def' => 'id',
                    ),
                    'KEY_UNIQUE_CODE' => array(
                        'type' => 'uniqueKey',
                        'def' => 'code',
                    ),
                    'KEY_FLAG' => array(
                        'type' => 'index',
                        'def' => 'flag',
                    ),
                ),
            ),
            'table_two' => array(
                'columns' => array(
                    'id' => array(
                        'type' => 'integer',
                        'nullable' => false,
                    ),
                    'title' => array(
                        'type' => 'varchar',
                        'length' => '255',
                    ),
                    'created_at' => array(
                        'type' => 'datetime',
                    ),
                    'parent_id' => array(
                        'type' => 'integer',
                        'nullable' => false,
                    ),
                    'flag' => array(
                        'type' => 'integer',
                    ),
                ),
                'constraints' => array(
                    'primary' => array(
                        'type' => 'primaryKey',
                        'def' => 'id',
                    ),
                    'FK_KEY_UNIQUE_CODE' => array(
                        'type' => 'foreignKey',
                        'def' => 'parent_id',
                        'referenceTable' => 'table_one',
                        'referenceColumn' => 'id',
                    ),
                    'KEY_FLAG' => array(
                        'type' => 'index',
                        'def' => 'flag',
                    ),
                ),
            ),
        );
    }

    /**
     * @return string
     */
    public static function getDdlConfigVersion()
    {
        return '0.1.1';
    }

    /**
     * @return string
     */
    public static function getDdlConfigName()
    {
        return 'test_tables';
    }
}