<?php

namespace App\Conversions;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AnnouncementConversion extends Controller implements IConversion
{
    public function getIdField() :string { return 'id'; }
    public function getTableName() :string { return 'announcements'; }

    public function getApiColumns() :array
    {
        return [
            ['db' => 'title', 'dt' => 0],
            ['db' => 'creator', 'dt' => 1],
            ['db' => 'date_added', 'dt' => 2,
                'formatter' => function($d) { return date( 'jS M y', strtotime($d)); }
            ],
            ['db' => 'date_updated', 'dt' => 3,
                'formatter' => function($d) { return !empty($d) ? date('jS M y', strtotime($d)) : ''; }
            ],
            ['db' => 'id', 'dt' => 4,
                'formatter' => function($id) {
                    return "<a class='btn btn-info' href='/announcements/view/$id/'>View notice</a>";
                }
            ]
        ];
    }
}
