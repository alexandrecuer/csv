<?php

// no direct access
defined('EMONCMS_EXEC') or die('Restricted access');

function createmeta($dir,$id,$meta) 
{
    if (!$metafile = fopen($dir.$id.".meta", 'wb')) return false;
    if (!fwrite($metafile,pack("I",0))) return false;
    if (!fwrite($metafile,pack("I",0))) return false; 
    if (!fwrite($metafile,pack("I",$meta->interval))) return false;
    if (!fwrite($metafile,pack("I",$meta->start_time))) return false; 
    if (!fclose($metafile)) return false;
    return true;
}

function csv_controller() {

    global $homedir,$session,$route,$mysqli,$redis,$settings;
    if (!isset($homedir)) $homedir = "/home/pi";
    $dir = $settings["feed"]["phpfina"]["datadir"];
    $csv_store=$homedir."/data/csv_files";
    
    include "Modules/feed/feed_model.php";
    $feed = new Feed($mysqli,$redis,$settings["feed"]);
    
    // Default route format
    $route->format = 'json';
    
    // Result can be passed back at the end or part way in the controller
    $result = false;
    
    
    // Read access API's and pages
    if ($session['read']) {
    
    }
    
    
    if ($session['write']) {
        
        if ($route->action == "view") {
            $route->format = 'html';
            return view("Modules/csv/csv_view.php", array());
        }
        
        if ($route->action == "timezone") {
            if (date_default_timezone_get()) $result = date_default_timezone_get() ;
            else $result="no timezone set - please set one";
        }
        
        //list csv files to import
        if ($route->action == "list") {
            $route->format="json";
            $csv_to_convert=[];
            if (is_dir($csv_store)) {
                if ($handle = opendir($csv_store))
                    if ($handle = opendir($csv_store)) {
                        while (false !== ($entry = readdir($handle))) {
                            if ($entry != "." && $entry != "..") {
                                $csv_to_convert[]['name']=$entry;
                            }
                        }
                    } 
                if (!$csv_to_convert) 
                    return array('content'=>"no csv to import in $csv_store - please add some");
                else $result=$csv_to_convert;
            } else return array('content'=>"no directory $csv_store - please create and fill");
        }
        
        //get all feednames associated to a csv
        if ($route->action == "getfeeds") {
            $route->format="json";
            $params = json_decode(file_get_contents('php://input'));
            if(!$params) return array('content' => "not possible without a csv name");
            $fname = $csv_store."/".$params;
            if(!is_file($fname)) return array('content' => "this is not an existing file");
            $csv = new SplFileObject($fname);
            $csv->setFlags(SplFileObject::READ_CSV);
            $csv->setCsvControl(';');
            //we just read the first line which should contain all feed names in string format
            $a=preg_replace('/[^\w\s-:]/','',$csv->current());
            $result = $a;
        }
        
        if ($route->action == "create") {
            $params = json_decode(file_get_contents('php://input'));
            if(!$params) return array('content' => "you have to specify a csv file and a column number");
            if(!$params->name) return array('content' => "not possible without a csv name");
            $feednbr=(int) $params->feednbr;
            if(!$params->feednbr) return array('content' => "not possible without a column number");
            if($feednbr<1) return array('content'=> "column number must be integer and more than 0");
            
            $fname = $csv_store."/".$params->name;
            if(!is_file($fname)) return array('content' => "this is not an existing file");
            
            $result = "csv name: $params->name \n";
            
            $csv = new SplFileObject($fname);
            $csv->setFlags(SplFileObject::READ_CSV);
            $csv->setCsvControl(';');
            
            foreach($csv as $line){
                if ($csv->key()==0) $name=preg_replace('/[^\w\s-:]/','',$line[$feednbr]);
                if ($csv->key()==1) $start_time = strtotime($line[0]);
                if ($csv->key()==2) $interval = strtotime($line[0]) - $start_time;
                if ($csv->key()>0) {
                    $values[] = preg_replace("/,/", ".", $line);   
                }
            }
            
            $writes=array_column($values,$feednbr);
            $nbpoints=count($writes);
            $result.="column number in the csv: $feednbr \n-feed name: $name \n-start_time: $start_time \n-interval: $interval \n-nbpoints: $nbpoints \n";
            
            $name=preg_replace('/[^\p{N}\p{L}_\s-:]/u','',$name);
            $c = $feed->create($session['userid'],"csv",$name,DataType::REALTIME,Engine::PHPFINA,json_decode('{"interval":3600}'));
            if (!$c['success'])
                return array('content'=>"feed could not be created in $dir");
            $id=$c['feedid'];
            $result.="We have created a PHPFINA feed with number $id in dir $dir \n";
            if (!$fh = @fopen($dir.$id.".dat", 'ab')) return array('content'=>"ERROR: could not open $dir $id.dat");
            $buffer="";
            
            foreach($writes as $write) {
                $write=trim($write);
                if ($write=="") $write=NAN;
                //print("$write |");
                $buffer.=pack("f",(float)$write);
            }
            if(!$written_bytes=fwrite($fh,$buffer)) {
                fclose($fh);
                return array('content'=>"ERROR: unable to write to the file with id=$id");
            }
            $result.="$written_bytes bytes written for $nbpoints float values \n";
            $meta = new stdClass();
            $meta->interval=$interval;
            $meta->start_time=$start_time;
            if (!createmeta($dir,$id,$meta))
                return array('content'=>"meta could not be updated");
            fclose($fh);
            $route->format="text";
            $result.='so far so good, your feed has been created';
        }
        
    }

    // Pass back result
    return array('content' => $result);
}
