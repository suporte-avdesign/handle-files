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
                'copy' => true,
                'read' => true,
                'download' => true,
                'name' => 'MATR460',
                'search' => 'MATR460',
                'format' => 'Ydm',
                'start' => 11,
                'lenght' => 8,
                'origin' => storage_path('app').'/origem/files',
                'origin_ext' => '##r',
                'receiver' => 'inventory/'.date('d-m-Y'),
                'receiver_ext' => '.txt',
                'storage' => 'local',
            ]
        ];

        return $this->typeObject($arr);
    }

    /**
     * Create Directories (storage)
     *
     * @return mixed
     */
    public function getDirectories()
    {
        $arr = [

        ];

        return $this->typeObject($arr);
    }

    /**
     * Retorna um objeto json
     * @param $array
     * @return mixed
     */
    public function typeObject($array)
    {
        return json_decode(json_encode($array, FALSE));
    }

}
