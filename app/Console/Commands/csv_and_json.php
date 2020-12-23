<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class csv_and_json extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'categorias:ventas {path_csv : esto es el directorio del CSV} {path_json : esto es el directorio del json}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mostrará las categorias con sus ganancias';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        /*$CSVFile = app_path('Console\Commands\Hoja-de-cálculo-sin-título.csv');
        if(!file_exists($CSVFile) || !is_readable($CSVFile))
            return false;

        $header = null;
        $data = array();
        if (($handle = fopen($CSVFile,'r')) !== false){
            while (($row = fgetcsv($handle, 1000, ',')) !==false){
                if (!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }*/
        $CSVFile = app_path($this->argument('path_csv'));
        $JSONFile = app_path($this->argument('path_json'));
        /*$CSVFile = app_path('Console\Commands\Hoja-de-cálculo-sin-título.csv');
        $JSONFile = app_path('Console\Commands\categoria_data.json');*/
        if(!file_exists($CSVFile) || !is_readable($CSVFile))
            return false;

        $header = null;
        $data = array();
        if (($handle = fopen($CSVFile,'r')) !== false){
            while (($row = fgetcsv($handle, 1000, ',')) !==false){
                if (!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }

        $json = json_decode(file_get_contents($JSONFile), true);



        $new_array = array();
        $result = array();


        foreach ($data as $k => $v) {

            $price2= ( array_key_exists($v['CATEGORY'], $json['categorias']) ) ? $json['categorias'][$v['CATEGORY']] : $json['categorias']['*'];
            $price=preg_replace('/[$€,]/', '', $price2);
            $cost=floatval(preg_replace('/[$€,]/', '', $v['COST']));
            $quantity=(int)preg_replace('/[$€,.]/', '', $v['QUANTITY']);

            $total=$this->number_format($price, floatval($cost), floatval($quantity));

            if (array_key_exists($v['CATEGORY'], $new_array)) {

                $new_array[$v['CATEGORY']]["PRICE"]= $new_array[$v['CATEGORY']]["PRICE"]+$total;

            } else {
                $new_array[$v['CATEGORY']] = [

                    "CATEGORY" => $v['CATEGORY'],
                    "PRICE" => $total


                ];
            }

         }

         $title=['CATEGORY', 'TOTAL'];
        $this->table($title,$new_array);

    }

    public function number_format($data, $cost, $quantity){

        if (strpos($data, "%")) {
                do {

                    $porcentaje_ubicacion = strpos($data, "%");

                        for( $i = $porcentaje_ubicacion; $i >= 0; $i-- ) {


                            if( substr($data, $i,1) == "-" or substr($data, $i,1) == "+" or substr($data, $i,1) == "/" or substr($data, $i,1) == "*") { break; }

                        }
                        $porcentaje_fin=$i;
                        $digito_porcentaje=$porcentaje_ubicacion-($porcentaje_fin+1);
                        $total_multiplo_porsentaje = substr($data,$porcentaje_fin+1,$digito_porcentaje)/100;
                        $total_remp_new= $cost*$total_multiplo_porsentaje;
                        $numero_remp=substr($data,$porcentaje_fin+1,$digito_porcentaje+1);
                        $data=str_replace($numero_remp, $total_remp_new, $data);



                } while (strpos($data, "%"));
        }



        eval("\$data = $data;");

        return floatval($data*$quantity);

    }
}
