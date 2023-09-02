<?php

namespace App\Http\Controllers\V1\Auth\Traits;


use App\City;
use App\Country;
use App\District;
use App\Exports\ProductExport;
use App\Supports\Message;
use App\Ward;

trait ImportTrait
{
    //////////////////////////////////////////// IMPORT PRODUCT ///////////////////////////////////////////////

    private function checkValidProduct(&$row, $existedChk = [])
    {
        $errorDetail = "";

        // Check Required
        foreach ($this->required as $key => $name) {
            if (!isset($row[$key]) || $row[$key] === "" || $row[$key] === null) {
                $errorDetail .= Message::get("V001", $name) . PHP_EOL;
            }
        }

        // Check Existed
        foreach ($this->_notExisted as $key => $name) {
            if (empty($row[$key])) {
                continue;
            }

            $val = trim(strtoupper($row[$key]));

            if (!isset($existedChk[$name][$val])) {
                $errorDetail .= Message::get("V003", $this->_header[$key]) . "\n";
            } else {
                if ($name == 'code') {
                    $row[$key] = $existedChk[$name][$val];
                    continue;
                }
                $row[$key] = $val;
            }
        }

        // Check Date format row 1
        if (!empty($row[1])) {
            if ($row[1] instanceof \DateTime) {
                $date = $row[1]->format('Y-m-d H:i:s');
            } else {
                $date = str_replace("/", "-", $row[1] . ":00");
                $date = date("Y-m-d H:i:s", strtotime($date));
            }

            if (empty($date) || strtotime($date) <= 0) {
                $errorDetail .= Message::get("V002", $this->_header[1]) . "\n";
            }
        }

        // Check Price row 2
        if (!is_numeric($row[2])) {
            $errorDetail .= Message::get("V002", $this->_header[2]) . "\n";
        }

        // Check Type row 4
        if (!in_array($row[4], ["", "Nông sản", "Phân bón"])) {
            $errorDetail .= Message::get("V002", $this->_header[4]) . "\n";
        }

        // Check Address row 5, 6, 7
        if (!empty($row[5])) {
            if (!$this->getValidAddress(1, $row[5], $row[6], $row[7])) {
                $errorDetail .= Message::get("V002",
                        "[" . $this->_header[5] . "-" . $this->_header[6] . "-" . $this->_header[7]) . "]\n";
            }
        }

        return $errorDetail;
    }

    private function writeExcelProduct($dir, $data, $return)
    {
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
            chmod($dir, 0777);
        }
        $file = $this->_file_name . ".xlsx";
        //\Excel::store(new ProductExport(['data' => $data]));

        \Excel::create($this->_file_name, function ($file) use ($data) {

            $file->sheet('Report', function ($sheet) use ($data) {
                $maxCharRow = "I";
                $sheet->setAutoSize(true);
                $sheet->setHeight(1, 30);

                $countData = count($data);
                // Fill suggest Data
                $sheet->fromArray($data, null, 'A1', true, false);

                // Wrap Text
                $sheet->getStyle("{$maxCharRow}1:$maxCharRow" . (count($data)))->getAlignment()->setWrapText(true);

                $sheet->setBorder("A1:$maxCharRow" . (count($data)), 'thin');
                $sheet->cell("A1:{$maxCharRow}1", function ($cell) {
                    $cell->setAlignment('center');
                    $cell->setFontWeight();
                    $cell->setBackground("#3399ff");
                    $cell->setFontColor("#ffffff");
                });

                $sheet->cell("A1:$maxCharRow" . (count($data)), function ($cell) {
                    $cell->setValignment('center');
                });

                $columsAlign = [
                    "A1:C" . $countData => "left",
                    "D1:J" . $countData => "center",
                    "L1:M" . $countData => "center",
                    "{$maxCharRow}1:$maxCharRow" . $countData => "left",
                    "{$maxCharRow}1:$maxCharRow" . $countData => "left",
                ];

                foreach ($columsAlign as $cols => $align) {
                    $sheet->cell($cols, function ($cell) use ($align) {
                        $cell->setAlignment($align);
                    });
                }

            });
        })->store('xlsx', $dir);

        //$file = $this->_file_name . ".xlsx";
        readfile("$dir/$file");
    }

    private function getValidAddress($countryId = 0, $cityId = 0, $districtId = 0, $wardId = 0)
    {
        $validCountryId = $validCityId = $validDistrictId = $validWardId = 0;
        if (empty($wardId)) {
            if (empty($districtId)) {
                if (empty($cityId)) {
                    if (empty($countryId)) {
                        return [];
                    } else {
                        $country = Country::find($countryId);
                        if (empty($country)) {
                            return [];
                        }
                        $validCountryId = $countryId;
                    }
                } else {
                    $city = City::find($cityId);
                    if (empty($city)) {
                        return [];
                    }

                    $validCityId = $cityId;
                    $validCountryId = $this->getParentLocationId('country_id', $cityId, City::class);
                }
            } else {
                $district = District::find($districtId);
                if (empty($district)) {
                    return [];
                }
                $validDistrictId = $districtId;
                $validCityId = $this->getParentLocationId('city_id', $districtId, District::class);
                $validCountryId = $this->getParentLocationId('country_id', $validCityId, City::class);
            }
        } else {
            $validWardId = $wardId;
            $validDistrictId = $this->getParentLocationId('district_id', $wardId, Ward::class);
            $validCityId = $this->getParentLocationId('city_id', $validDistrictId, District::class);
            $validCountryId = $this->getParentLocationId('country_id', $validCityId, City::class);
        }

        if (empty($validCountryId)) {
            return [];
        }

        return array_filter([
            'country_id' => $validCountryId,
            'city_id' => $validCityId,
            'district_id' => $validDistrictId,
            'ward_id' => $validWardId,
        ]);
    }

    private function getParentLocationId($parentColumn, $childLocationId, $childModel)
    {
        $child = $childModel::find($childLocationId);
        if (empty($child)) {
            return 0;
        }
        return $child->{$parentColumn};
    }
}