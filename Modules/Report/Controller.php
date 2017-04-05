<?php

class ReportController extends Module {
    private $ph;
    
    function __construct() {
        Routes::Get('reports', $this, 'ReportsList');
        Routes::Get('reports/add', $this, 'ReportAdd');
        Routes::Post('reports/add', $this, 'ReportAddPost');
        Routes::Get('reports/view', $this, 'ReportView');
        Routes::Get('reports/edit', $this, 'ReportEdit');
        Routes::Post('reports/edit', $this, 'ReportEditPost');
        Routes::Get('attachment/delete', $this, 'ReportAttachmentDelete');
        // Hook events to methods
        // ModulesController::HookEvent(event, module, method);
        
        $this->ph = new PersonsHelper();
    }
    
    
    public function ReportAttachmentDelete($data){
        DB::table('reports_report')
            ->where('id',$data['id'])
            ->update(array('attachment' => null));
            
        
        return $this->RedirectResult($_SERVER['HTTP_REFERER']); 
    }
    
    public function ReportsList() {
        $between_start_date = $_SESSION['context']['year'] . '-' . $_SESSION['context']['month'] . '-01 00:00:00';
        $between_end_date = $_SESSION['context']['year'] . '-' . ($_SESSION['context']['month'] + 1) . '-01 00:00:00';
        
        self::$Data['reports'] = json_decode(json_encode(DB::table('reports_report')->whereBetween('report_date',$between_start_date,$between_end_date)->where('client_id',$_SESSION['context']['client'])->get()), true);
        self::$Data['reportDomains'] = json_decode(json_encode(DB::table('reports_domain')->get()), true);
        self::$Data['clients'] = $this->ph->GetPersonsInGroupsWithClaims([5], [19, 32]);
        self::$Data['domains'] = DB::table('reports_domain')->get();
                
//        foreach ($reports as $key => $report) {
//            $reportDomainId = $report['domain'];
//            $domain = reset(array_filter($reportDomains, function($rd) use($reportDomainId){
//                return $reportDomainId == $rd['id'];
//            }));
//            
//            dump($domain);
//            
//            //$report->domain = $domain;
//            $reportsWithDomains[$key] = $report; 
//      }
        
        
        return $this->OkResult('List');
    }
    
    public function ReportEdit($data){
        $report = json_decode(json_encode(DB::table('reports_report')->find($data['id'])), true);
        $domains = json_decode(json_encode(DB::table('reports_domain')->get()), true);
        self::$Data['report'] = $report;
        self::$Data['ReportDomains'] =  $domains;
        self::$Data['report']['report_date'] = date('Y-m-d',strtotime(self::$Data['report']['report_date']));
        $time = date('U');
        $secondsInDay = 24 * 60 * 60;
        $createTimeEpoch = strtotime($report['create_date']);
        return $createTimeEpoch + $secondsInDay < $time ? $this->HttpResult(401, '401', null, array(), 'Page') : $this->OkResult('Edit');
    }
    
    public function ReportEditPost($data){
        
      
         if ($_FILES["attachment"]["size"] > 0) {
            $attachment = $_FILES["attachment"];

            if ($attachment["error"] !== UPLOAD_ERR_OK) {
                echo "<p>An error occurred.</p>";
                exit;
            }

            // ensure a safe filename
            $name = preg_replace("/[^A-Z0-9._-]/i", "_", $attachment["name"]);

            $publicDir = '/UserUpload/Rapportage bestanden/';
            $publicFilename = '/UserUpload/Rapportage bestanden/' . $name;
            $uploadDir = ROOT_DIR . $publicDir;
            $filename = $uploadDir . $name;
            
            // don't overwrite an existing file
            $i = 0;
            $parts = pathinfo($name);
            while (file_exists($uploadDir . $name)) {
                $i++;
                $name = $parts["filename"] . "-" . $i . "." . $parts["extension"];
            }
            
            // preserve file from temporary directory
            $success = move_uploaded_file($attachment["tmp_name"], $filename);
            if (!$success) {
                echo "<p>Unable to save file.</p>";
                exit;
            }

            // set proper permissions on the new file
            chmod($filename, 0644);
        }
        
        $id = DB::table('reports_report')
            ->where('id',$data['id'])
            ->update(array(
                'report' => $data['report'],
                'client_id' => $_SESSION['context']['client'],
                'writer_id' => $_SESSION['user']['id'],
                'domain' => $data['domain'],
                'target' => $data['target'],
                'report_date' => $data['report_date'],
                'attachment' => isset($publicFilename) ? $publicFilename : null
            ));
        
        return $this->RedirectResult('/reports/view?id=' . $data['id']);
    }
    
    public function ReportAdd(){
        self::$Data['ReportDomains'] = DB::table('reports_domain')->get();
        self::$Data['clients'] = $this->ph->GetPersonsInGroupsWithClaims([5], [19, 32]);
       // dump(self::$Data['clients']);
        return $this->OkResult('Add');
    }

    public function ReportAddPost($data) {
        if ($_FILES["attachment"]["size"] > 0) {
            $attachment = $_FILES["attachment"];

            if ($attachment["error"] !== UPLOAD_ERR_OK) {
                echo "<p>An error occurred.</p>";
                exit;
            }

            // ensure a safe filename
            $name = preg_replace("/[^A-Z0-9._-]/i", "_", $attachment["name"]);

            $publicDir = '/UserUpload/Rapportage bestanden/';
            $publicFilename = '/UserUpload/Rapportage bestanden/' . $name;
            $uploadDir = ROOT_DIR . $publicDir;
            $filename = $uploadDir . $name;
            
            // don't overwrite an existing file
            $i = 0;
            $parts = pathinfo($name);
            while (file_exists($uploadDir . $name)) {
                $i++;
                $name = $parts["filename"] . "-" . $i . "." . $parts["extension"];
            }
            
            // preserve file from temporary directory
            $success = move_uploaded_file($attachment["tmp_name"], $filename);
            if (!$success) {
                echo "<p>Unable to save file.</p>";
                exit;
            }

            // set proper permissions on the new file
            chmod($filename, 0644);
        }
        
        $id = DB::table('reports_report')
            ->insert(array(
                'report' => $data['report'],
                'client_id' => $_SESSION['context']['client'],
                'writer_id' => $_SESSION['user']['id'],
                'domain' => $data['domain'],
                'target' => $data['target'],
                'report_date' => $data['report_date'],
                'attachment' => isset($publicFilename) ? $publicFilename : null
            ));
        
        return $this->RedirectResult('/reports/view?id=' . $id);
    }

    public function ReportView($data){
        $report = json_decode(json_encode(DB::table('reports_report')->find($data['id'])), true);
        self::$Data['report'] = $report;
        $domain = json_decode(json_encode(DB::table('reports_domain')->find(self::$Data['report']['domain'])), true);
        self::$Data['report']['domain'] = $domain['name'];
        self::$Data['report']['client']['claims'] = 
                
                $this->ph->GetPersonClaims($report['client_id']);
       // dump(self::$Data['report']['client']['claims']);
        return $this->OkResult('View');
    }

}
