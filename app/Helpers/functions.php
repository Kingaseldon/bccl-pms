<?php
    function randomString(): string
    {
        $possible = "aBcDeFgHiJkLmNoPqRsTuVwXyZAbCdEfGhIjKlMnOpQrStUvWxYz123456789";
        $randomString = "";
        while(strlen($randomString)<5){
            $strShuffled = str_shuffle($possible);
            $randomCharacter = substr($strShuffled,rand(0,59),1);
            if($randomCharacter){
                $randomString .= $randomCharacter;
            }
        }
        return $randomString;
    }
    function UUID(): string{
        $uuidQuery = DB::select("select UUID() as Id");
        return $uuidQuery[0]->Id;
    }
    function convertDateTimeToClientFormat($date): string
    {
        $newDate = date_create($date);
        return date_format($newDate,'jS M, Y \a\t h:i A');
    }
    function convertDateToClientFormat($date): string
    {
        $newDate = date_create($date);
        return date_format($newDate,'jS M, Y');
    }
    function convertNumberToWord($number): string
    {
        $number = (int)$number;
        return match ($number) {
            1 => 'one',
            2 => 'two',
            3 => 'three',
            4 => 'four',
            5 => 'five',
            6 => 'six',
            7 => 'seven',
            8 => 'eight',
            9 => 'nine',
            10 => 'ten',
            11 => 'eleven',
            12 => 'twelve',
            13 => 'thirteen',
            14 => 'fourteen',
            15 => 'fifteen',
            16 => 'sixteen',
            17 => 'seventeen',
            18 => 'eighteen',
            19 => 'nineteen',
            20 => 'twenty',
            21 => 'twentyone',
            22 => 'twentytwo',
            23 => 'twentythree',
            24 => 'twentyfour',
            25 => 'twentyfive',
            26 => 'twentysix',
            27 => 'twentyseven',
            28 => 'twentyeight',
            29 => 'twentynine',
            30 => 'thirty',
            31 => 'thirtyone',
            32 => 'thirtytwo',
            33 => 'thirtythree',
            34 => 'thirtyfour',
            35 => 'thirtyfive',
            36 => 'thirtysix',
            37 => 'thirtyseven',
            38 => 'thirtyeight',
            39 => 'thirtynine',
            40 => 'forty',
            41 => 'fortyone',
            42 => 'fortytwo',
            43 => 'fortythree',
            44 => 'fortyfour',
            45 => 'fortyfive',
            46 => 'fortysix',
            47 => 'fortyseven',
            48 => 'fortyeight',
            49 => 'fortynine',
            50 => 'fifty',
            51 => 'fiftyone',
            52 => 'fiftytwo',
            53 => 'fiftythree',
            54 => 'fiftyfour',
            55 => 'fiftyfive',
            56 => 'fiftysix',
            57 => 'fiftyseven',
            58 => 'fiftyeight',
            59 => 'fiftynine',
            60 => 'sixty',
            61 => 'sixtyone',
            62 => 'sixtytwo',
            63 => 'sixtythree',
            64 => 'sixtyfour',
            65 => 'sixtyfive',
            66 => 'sixtysix',
            67 => 'sixtyseven',
            68 => 'sixtyeight',
            69 => 'sixtynine',
            70 => 'seventy',
            71 => 'seventyone',
            72 => 'seventytwo',
            default => 'thirty',
        };
    }
    function setUserDepartmentAndGrade(){
        $department = DB::table('mas_department')->where('Id',Auth::user()->DepartmentId)->pluck('Name');
        $gradeId = DB::table('mas_employee as T1')->join('mas_gradestep as T2','T2.Id','=','T1.GradeStepId')->where('T1.Id',Auth::user()->Id)->pluck('T2.GradeId');
        $gradeId = $gradeId[0] ?? NULL;
        Session::put('UserDepartment',$department[0]);
        Session::put('GradeId',$gradeId);
    }
    function arrayToString($array,$oldKey = null): string
    {
        $string = '';
        foreach($array as $key=>$value):
            if(gettype($value) == 'array'){
                $string.=arrayToString($value,$key);
            }else{
                if($oldKey){
                    $string .= "<br/>$oldKey:$value";
                }else{
                    $string .= "<br/>$key:$value";
                }
            }
        endforeach;
        return ($string=='')?'[]':$string;
    }
    function getPMSDetails(){
        $year = 2007;
        $counter = 0;
        for($i=$year; $i<2019; $i++):
            $counter += 1;
            DB::table('sys_pmsnumber')->insert(['StartDate'=>"$i-01-01",'PMSNumber'=>$counter]);
            $counter += 1;
            DB::table('sys_pmsnumber')->insert(['StartDate'=>"$i-07-01",'PMSNumber'=>$counter]);
        endfor;
    }
