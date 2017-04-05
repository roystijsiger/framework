<?php

class TargetController extends Module {
    function __construct() {
        // Hook events to methods
        // ModulesController::HookEvent(event, module, method);
        Routes::Get('targets', $this, 'TargetList');
        Routes::Get('targets/view', $this, 'TargetView');
        Routes::Get('targets/add', $this, 'TargetAdd');
        Routes::Post('targets/addPost', $this, 'TargetAddPost');
        Routes::Get('targets/edit', $this, 'TargetUpdate');
    }

    public function CompleteTarget() {

        Modules::PushEvent('target.targetCompleted', array(
            'target' => $target,
            'client' => $client
        ));
    }

    public function TargetList() {
        
    }

    public function TargetView($data) {
        $target = $this->ActiveClients()
                ->find($data['id']);

        if ($target != null) {
            self::$Data['target'] = $target;
            return $this->OkResult('View');
        }

        return $this->NotFoundResult('ClientNotFound');
    }

    public function TargetAdd() {
        self::$Data['posturl'] = '/clients/add';
        return $this->OkResult('Edit');
    }

    public function TargetAddPost($data) {
        $insertId = DB::table('clients_client')->insert(self::ClientInit($data));
        return $this->RedirectResult('/clients?addsuccess=1');
    }

    public function TargetDelete() {
        $query = DB::table('targets_target')->where('id', $data['id'])->delete();
        //$query = DB::table('clients_user_client')->where('ClientId', $data['client_id'])->delete();

        return $this->RedirectResult('/targets');
    }

    Public function TargetUpdate() {
        return "";
    }

    Public function TargetUpdatePost($data) {

        return $this->RedirectResult('/clients/view?updatesuccess=1&id=' . $data['id']);
    }
}
