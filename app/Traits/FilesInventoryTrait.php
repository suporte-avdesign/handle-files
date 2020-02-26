<?php

namespace App\Traits;


trait FilesInventoryTrait
{
    /**
     * Parameters of source and recipient files.
     *
     * @return mixed
     */
    public function filesDirectories()
    {
        $arr = [
            'etq_inventory' => [
                'init' => 39,                #Inicia o loop
                'copy' => true,
                'move' => true,
                'read' => true,
                'download' => true,
                'name' => 'MATR460',
                'search' => 'MATR460',
                'format' => 'Ydm',
                'start' => 11,
                'lenght' => 8,
                'storage' => 'local',
                'origin' => storage_path('app').'/origem/files',
                'origin_ext' => '##r',
                'receiver' => 'inventory/'.date('d-m-Y'),
                'receiver_ext' => '.txt',
            ]
        ];

        return json_decode(json_encode($arr, FALSE));
    }

}
