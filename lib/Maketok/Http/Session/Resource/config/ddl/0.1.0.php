<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
return array(
    $this->getDdlCode() => array(
        'columns' => array(
            'session_id' => array(
                'type' => 'varchar',
                'length' => 32,
            ),
            'data' => array(
                'type' => 'text',
            ),
            'updated_at' => array(
                'type' => 'datetime',
            ),
        ),
        'constraints' => array(
            'primary' => array(
                'type' => 'primaryKey',
                'definition' => ['session_id'],
            )
        ),
    )
);
