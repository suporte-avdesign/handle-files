<?php


namespace App\Services;

use App\Traits\FilesInventoryTrait;
use Illuminate\Contracts\Filesystem\Factory as FilesFactory;
use Illuminate\Support\Facades\Log;


class FilesInventoryService
{
    use FilesInventoryTrait;

    private $factory;

    public function __construct()
    {
        $this->factory = app(FilesFactory::class);
    }

    /**
     * Obter informações do arquivo
     *
     * @param $config
     * @return mixed
     */
    public function getInfoFile($config)
    {
        return $this->allFilesDirectory($config);
    }

    /**
     * Faz o download do arquivo
     *
     * @param $config
     * @param $info
     */
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
                $disk->put($path, utf8_encode($content));
            } else {
                $disk->append($path, utf8_encode($content));
            }
        }
    }

    /**
     * Retorna os dados para criar a tabela
     *
     * @param $config
     * @param $info
     */
    public function readFileInventory($config, $info)
    {
        $content = $this->getContent($info);
        if ($this->searchName($content, $config->search)) {
            $lines = explode(PHP_EOL, $content);
            $i=2;
            foreach ($lines as $line) {
                //Firma
                $patternFirma = "(^\| FIRMA:)";
                $successFirma = preg_match($patternFirma, $line, $match);
                if($successFirma){
                    $lastFirma[$i] = trim(substr($line, 8, 60));
                    $lastFilial[$i] = trim(substr($line, 68, 50));
                }

                //Em Estoque
                $patternEstoque = "((\*) (.+?) (\*))";
                $successEstoque = preg_match($patternEstoque, $line, $match);
                if($successEstoque){
                    $lastCategory[$i] = $match[2];
                }

                //Identificação de Produtos
                $patternProducts = "(^\|[0-9]{8})";
                $successProducts = preg_match($patternProducts, $line, $match);
                if($successProducts){
                    //$produtos[$i]['firma'] = $lastFirma;
                    //$produtos[$i]['filial'] = $lastFilial;
                    //$produtos[$i]['categoria_estoque'] = $lastCategory;
                    $produtos[$i]['classificacao_fiscal'] = substr($line, 1, 8);
                    $produtos[$i]['codigo'] = substr($line, 16, 12);
                    $produtos[$i]['produto'] = mb_convert_encoding(substr($line, 31, 20), 'UTF-8', 'Windows-1252');
                    $produtos[$i]['unid'] = substr($line, 55, 2);
                    $produtos[$i]['qtd'] = floatval(str_replace(',', '.', str_replace('.', '', substr($line, 60, 13))));
                    $produtos[$i]['unitario'] = floatval(str_replace(',', '.', str_replace('.', '', substr($line, 75, 17))));
                    $produtos[$i]['parcial'] = floatval(str_replace(',', '.', str_replace('.', '', substr($line, 94, 17))));
                    $produtos[$i]['total'] = floatval(str_replace(',', '.', str_replace('.', '', substr($line, 113, 17))));
                    $produtos[$i]['data_ref'] = '2020-02-20';

                }

                $i++;
            }


            $collection = collect($produtos);

            dd($collection->take(50));


        }




        /*

        $matches = array();
        preg_match_all("/([0-9]+) \(([FOLHA:]+)\) ([\d]+)/", $content, $matches);

        dd($content);
        $lines = explode(PHP_EOL, $this->getContent($info));
        if ($this->searchNameLine($config, $lines)) {
            $i=0;

            $pattern = "/\bo'FIRMA\b/i"; // only O'Reilly books



            foreach ($lines as $line) {
                if ($i >= $config->init)

                    if (preg_match($pattern, $line)) {
                        $ora_books[ ] = $line;
                    }


                    dd($ora_books);


                $i++;
            }
        }

        */

    }

    /**
     * Verifica se existe o nome no conteudo
     *
     * @param $content
     * @param $name
     * @return false|int
     */
    public function searchName($content, $name)
    {
        $pattern = "/{$name}/";
        return preg_match_all($pattern, $content, $match);
    }

    /**
     * Cria as pastas especificadas ao config
     *
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
                Log::debug('Erro ao criar o diretório!');
                // Return noticication or log
            }
        }
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
        return json_decode(json_encode($data, FALSE));
    }

    /**
     * Obter o conteúdo do arquivo
     *
     * @param $info
     * @return false|string
     */
    public function getContent($info)
    {
        foreach ($info as $value) {
            $link = "{$value->dirname}/{$value->basename}";
            $content = file_get_contents($link);
            if (!$content) {
                Log::debug('Error: getContent($info),  ao ler o conteudo do arquivo');
            } else {
                return $content;
            }
        }
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
