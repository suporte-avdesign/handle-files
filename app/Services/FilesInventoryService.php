<?php


namespace App\Services;

use App\Traits\FilesInventoryTrait;
use Illuminate\Contracts\Filesystem\Factory as FilesFactory;


class FilesInventoryService
{
    use FilesInventoryTrait;

    private $factory;

    public function __construct()
    {
        $this->factory = app(FilesFactory::class);
    }

    /**
     * @param $config
     * @return mixed
     */
    public function getInfoFile($config)
    {
        return $this->allFilesDirectory($config);
    }


    public function downloadFile($config, $info)
    {

        foreach ($info as $value) {
            $link = "{$value->dirname}/{$value->basename}";
            $content = file_get_contents($link);
            //$parse = parse_url($path);
            //$basename = basename($parse['path']); #mesmo nome
            //$file = mb_convert_encoding($content, "UTF-8", "auto");
            $name = $config->name.$config->receiver_ext;
            $path = "{$config->receiver}/{$name}";
            $disk = $this->factory->disk($config->storage);
            if ($disk->exists($path)) {
                $disk->append($path, utf8_encode($content));
            } else {
                $disk->put($path, utf8_encode($content));
            }
            /*
            $file = fopen($name, "w+");
            fwrite($file, $content);
            fclose($file);
            */
        }
    }


    /**
     * @param $config
     * @throws \Exception
     */
    public function makeDirectory($config)
    {
        $path  = "{$this->getPath($config->storage)}/{$config->receiver}";
        if (!is_dir($path)) {
            try {
                $this->factory->makeDirectory($config->receiver);
            } catch (\Exception $e) {
                // Return noticication or log

            }
        }
    }


    public function copyFiles($config)
    {

    }

    /**
     * Define local no disco
     *
     * @param $path
     * @return string
     */
    public function getPath($path)
    {
        return storage_path($path);
    }

    /**
     * Retorna o arquivo específico
     *
     * @param $config
     * @param $files
     * @return mixed
     */
    public function allFilesDirectory($config)
    {

        $data = array();
        $files = $this->factory->allFiles();
        foreach ($files as $file) {
            $path = $config->origin. DIRECTORY_SEPARATOR . $file;
            $info = pathinfo($path);
            $date = date($config->format);
            if ($info['extension'] == $config->origin_ext) {
                $info['dirname'] = $config->origin;
                $dateFile = substr($info['filename'], $config->start, $config->lenght);
                if ($dateFile == $date) {
                    $info['size'] = $this->getSize($info);
                    $info['modified'] = $this->getModified($info);
                    array_push($data, $info);
                }
            }
        }
        return $this->typeObject($data);
    }

    /**
     * Tamanho do arquivo
     *
     * @param $info
     * @return false|int
     */
    public function getSize($info)
    {
        return filesize($this->dirname($info));
    }

    /**
     * Data que o arquivo foi criado ou modificado
     *
     * @param $info
     * @return false|string
     */
    public function getModified($info)
    {
        return date('YmdHis', filemtime($this->dirname($info)));
    }

    /**
     * Caminho completo do arquivo
     *
     * @param $info
     * @return string
     */
    public function dirname($info)
    {
        return $info['dirname']. DIRECTORY_SEPARATOR . $info['basename'];
    }


}
