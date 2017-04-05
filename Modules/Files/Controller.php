<?php

class FilesController extends Module {
    private $ph;

    function __construct() {
        Routes::Get('files', $this, 'Files');

        Modules::HookEvent('person.created', $this, 'CreateFolderForNewPerson');

        $this->ph = new PersonsHelper();
    }

    public function Files(){
        return $this->OkResult('Files');
    }

    public function CreateFolderForNewPerson($personId){
        mkdir(UPLOAD_DIR . 'persons/' . $personId);
        $groups = $this->ph->GetPersonGroups($personId);

        $folders = DB::table('files_folders')->where('is_personal', true)->whereIn('group_id', $groups)->get();

        foreach ($folders as $folder) {
            mkdir(UPLOAD_DIR  . 'persons/' . $personId . '/' . $folder->name);
        }
    }
}