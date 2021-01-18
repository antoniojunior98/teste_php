<?php
class Report
{
    private string $path;
    private int $salesman;
    private float $salary;
    private int $customer;
    private array $sales;

    public function __construct()
    {
        $this->path = "C:\\xampp\\htdocs\\teste_php-main\\";
        $this->salesman = 0;
        $this->salary = 0;
        $this->customer = 0;
        $this->sales = [];
    }

    public function generate(): void
    {
        $this->read();
        if($this->write()){
            echo "Relatorio gerado com sucesso!";
        }
    }

    private function read(): void
    {
        $file = $this->path."data\\in\\loja.dat";
        $fileSize = filesize($file);
        
        $file = fopen($file, "r");

        while (!feof($file)) {
            $line = fgets($file, $fileSize);
            $this->process($line);
        }
        $this->worstSalesman();
        fclose($file);
    }

    private function process(string $data): void
    {
        $item = explode(",", $data);
        $id = $item[0];

        switch ($id) {
            case '001':
                $this->salesman++;
                $this->salary += floatval($item[3]);
                break;
            case '002':
                $this->customer++;
                break;
            case '003':
                $this->sales($item);
                break;
            default:
                throw new Exception("Error: id does not exist ");
                break;
        }
    }

    private function write(): bool
    {
        $file = $this->path."data\\out\\loja_relatorio.done.dat";
        $file = fopen($file, 'w');

        $salesman = "numero de vendedores: " . $this->salesman.chr(13).chr(10);
        $customer = "numero de clientes: " . $this->customer.chr(13).chr(10);
        $salary = "media salarial:" . $this->calculateSalary().chr(13).chr(10);
        $biggestSale = $this->biggestSale().chr(13).chr(10);
        $worstSalesman = "Piores vendedores: ".$this->worstSalesman();

        fwrite($file, $salesman);
        fwrite($file, $customer);
        fwrite($file, $salary);
        fwrite($file, $biggestSale);
        fwrite($file, $worstSalesman);
        fclose($file);
        if($file){
            return true;
        }
        return false;
    }

    private function calculateSalary(): string
    {
        return 'R$ '.round($this->salary / $this->salesman, 2);
    }

    private function sales(array $data): void
    {
        $prices = array_slice($data, 2, -1);
        $priceFinal = 0;

        foreach($prices as $price){
            $price = explode("_", $price);
            $priceFinal += floatval($price[1])*floatval($price[2]);
        }

        $this->sales[] = [
            "id" => $data[1],
            "salesman" => end($data),
            "value" => $priceFinal
        ];
    }

    private function biggestSale(): string
    {
        $sale = null;
        
        foreach($this->sales as $sales){
            $price = null;
            if($sales['value'] >= $price){
                $price = $sales['value'];
                $sale = "A ultima compra mais cara: id: {$sales['id']}, vendedor: {$sales['salesman']}.";
            }
        }
        
        $sale = is_null($sale)? "Nenhuma compra no momento." : $sale;  
        return $sale;       
    }

    private function worstSalesman(): string
    {
        $valueBySalesman = array();

        foreach($this->sales as $sales){
           if(!array_key_exists($sales['salesman'], $valueBySalesman)){
                $value = $this->valueBySalesman($sales['salesman']);
                $valueBySalesman[$sales['salesman']] = $value; 
           } 
        }
       $worst = array_keys($valueBySalesman, min($valueBySalesman));
       return $worst = implode(",", $worst);
    }

    private function valueBySalesman(string $name): float
    {
        $value = 0;
        foreach($this->sales as $sales){
            if($sales['salesman'] == $name){
                $value += $sales['value'];
            }
        }

        return $value;
    }

}

$report = new Report();
$report->generate();
