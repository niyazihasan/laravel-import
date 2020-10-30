<?php

namespace App\Http\Controllers;

use App\Models\{Sale, City};

class SaleController extends Controller
{
    protected function sale()
    {
        return view('sale/import');
    }

    protected function import()
    {
        try {
            $php_excel = \PHPExcel_IOFactory::load(request('file'));
            $fields_keys = ['address' => 'адрес', 'city' => 'град', 'sales' => 'продажби'];
            $address_index = false;
            $city_index = false;
            $sales_index = false;
            $errors = [];
            $document_titles = [];
            $created_count = 0;
            $updated_count = 0;
            $worksheet = $php_excel->getActiveSheet();
            foreach ($worksheet->getRowIterator() as $index => $row) {
                $cell_values = [];
                foreach ($row->getCellIterator() as $cell) {
                    $cell_values[] = $cell->getValue();
                }
                if (empty($document_titles)) {
                    $document_titles = array_map('mb_strtolower', $cell_values);
                    $address_index = array_search($fields_keys['address'], $document_titles);
                    $city_index = array_search($fields_keys['city'], $document_titles);
                    $sales_index = array_search($fields_keys['sales'], $document_titles);
                    if (false === $address_index) {
                        $errors = ['missing_address_column' => ''];
                        break;
                    }
                    if (false === $city_index) {
                        $errors = ['missing_city_column' => ''];
                        break;
                    }
                    if (false === $sales_index) {
                        $errors = ['missing_sales_column' => ''];
                        break;
                    }
                    continue;
                }

                $address = $cell_values[$address_index];
                if (empty($address)) {
                    $errors[] = ['missing_address_value' => "line: $index"];
                    continue;
                }

                $city_name = $cell_values[$city_index];
                if (empty($city_name)) {
                    $errors[] = ['missing_city_value' => "line: $index"];
                    continue;
                }

                $count_sales = $cell_values[$sales_index];
                if (empty($count_sales)) {
                    $errors[] = ['missing_sales_value' => "line: $index"];
                    continue;
                } else if (filter_var($count_sales, FILTER_VALIDATE_INT) === false) {
                    $errors[] = ['invalid_sales_value' => "line: $index"];
                    continue;
                }

                $city_name_ = trim(str_replace(['.', 'гр'], '', mb_strtolower($city_name)));
                if ($city_name === "СОФИЯ-ГРАД") {
                    $city_name_ = "София";
                }

                $city = null;
                if ($city_index !== false) {
                    $city = City::where('name', $city_name_)->first();
                    if (null === $city) {
                        $errors[] = ['city_not_found' => "line: $index"];
                        continue;
                    }
                }

                $address_ = $this->parse_address($city_name_, $address);
                if (false === $address_) {
                    $errors[] = ['address_number_not_found' => "line: $index"];
                    continue;
                }

                $sale = $city->sales->where('address', $address_)->first();
                if (null !== $sale) {
                    $sale->update(['count' => $sale->count + $count_sales]);
                    $updated_count++;
                } else {
                    $city->addSale(new Sale(['address' => $address_, 'count' => $count_sales]));
                    $created_count++;
                }
            }
        } catch (\Exception $e) {
            $errors[] = ['error_import_file' => $e->getMessage()];
        }

        $success = [
            'import_created_count' => $created_count ?? 0,
            'import_updated_count' => $updated_count ?? 0
        ];

        return redirect()->route('sale.index')->with(compact('errors', 'success'));

    }

    private function parse_address($city_name, $address)
    {
        $matches = [];
        $address_ = trim(str_replace([',', '.', 'ул', ' no', '№', 'гр', mb_strtolower($city_name)], '', mb_strtolower($address)));
        $pattern = '/^([\p{Cyrillic}]+\s+[\p{Cyrillic}]+)[\s]+([\d\-]+)[\s]*([\p{Cyrillic}]*)[\s]*([\d]*)$/u';
        preg_match($pattern, $address_, $matches);
        if ($matches) {
            $street = $matches[1] ? mb_convert_case($matches[1], MB_CASE_TITLE) : '';
            $number = $matches[2] ? " № $matches[2]" : '';
            $str = $matches[3] ? ", $matches[3]" : '';
            $num = $matches[4] ? " № $matches[4]" : '';
            return "ул. $street$number$str$num";
        }
        return false;
    }
}
