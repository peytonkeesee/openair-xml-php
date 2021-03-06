<?php

namespace OpenAir;

use OpenAir\Base\Command;
use OpenAir\DataTypes\Address;
use OpenAir\DataTypes\Flag;

class Response extends OpenAir
{
    private $commands = [];

    function __construct($strXMLResponse)
    {
        $objXML = new \SimpleXMLElement($strXMLResponse);
        foreach($objXML as $strOrigCommand => $aryDataTypes){
            $strCommand = '\\OpenAir\\Commands\\'.$strOrigCommand;
            if(class_exists($strCommand)){
                $objCommand = new $strCommand();
            }else{
                $objCommand = new \stdClass();
            }
            $objCommand->setResponseStatus((int)$aryDataTypes[0]->attributes()['status'][0]);
            foreach($aryDataTypes as $dataType => $objRespnseDataDataType){
                $strDataType = '\\OpenAir\\DataTypes\\'.$dataType;
                if(class_exists($strCommand)){
                    $objDataType = new $strDataType();
                }else{
                    $objDataType = new \stdClass();
                }
                foreach($objRespnseDataDataType as $key => $objXmlVal){
                    if(count($objXmlVal) == 0){
                        $objDataType->$key = (string)$objXmlVal;
                    }elseif(count($objXmlVal) == 1 && isset($objXmlVal->Date)){
                        //mktime(hour, min, sec, month, day, year)
                        $strDate = mktime(
                            (int)$objXmlVal->Date->hour,
                            (int)$objXmlVal->Date->minute,
                            (int)$objXmlVal->Date->second,
                            (int)$objXmlVal->Date->month,
                            (int)$objXmlVal->Date->day,
                            (int)$objXmlVal->Date->year
                        );
                        $objDataType->$key = $strDate;
                    }elseif($key == 'flags'){
                        $aryFlags = [];
                        foreach($objXmlVal->Flag as $intKey => $objFlag){
                            $aryFlags[] = new Flag((string)$objFlag->name,(string)$objFlag->setting);
                        }
                        $objDataType->$key = $aryFlags;
                    }elseif($key == 'addr'){
                        $objAddress = new Address();
                        foreach($objXmlVal->Address as $key2 => $val2){
                            $objAddress->$key2 = (string)$val2;
                        }
                        $objDataType->$key = $objAddress;
                    }else{
                        throw new \Exception('Unsure how to handle datatype '.$key);
                    }
                }
                $objCommand->addDataType($objDataType);
            }
            $this->commands[$strOrigCommand] = $objCommand;
        }
    }

    function getCommandResposne($strCommand){
        if(array_key_exists($strCommand, $this->commands)){
            return $this->commands[$strCommand];
        }
    }
}