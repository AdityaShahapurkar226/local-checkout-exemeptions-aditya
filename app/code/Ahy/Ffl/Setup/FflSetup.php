<?php
/**
 * Copyright © Ahy Consulting  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ahy\Ffl\Setup;

use Magento\Eav\Setup\EavSetup;

class FflSetup extends EavSetup
{

    public function getDefaultEntities()
    {
        return [
             \Ahy\Ffl\Model\Ffl::ENTITY => [
                'entity_model' => \Ahy\Ffl\Model\ResourceModel\Ffl::class,
                'table' => 'ahy_ffl_entity',
                'attributes' => [
                    'title' => [
                        'type' => 'static'
                    ]
                ]
            ]
        ];
    }
}

