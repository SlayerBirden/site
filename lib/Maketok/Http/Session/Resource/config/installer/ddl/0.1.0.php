<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
