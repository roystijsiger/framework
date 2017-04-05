<?php
class PersonController extends Module {
    private $ph;

    function __construct() {
        // Hook events to methods
        Modules::HookEvent('routes.pageload', $this, 'PageLoad');
  
        Routes::Get('persons', $this, 'PersonList');
        Routes::Get('persons/view', $this, 'PersonView');
        Routes::Get('persons/files', $this, 'ViewPersonFilesGet');
        Routes::Post('persons/files', $this, 'ViewPersonFilesPost');
        Routes::Get('persons/add', $this, 'PersonAdd');
        Routes::Post('persons/add', $this, 'PersonAddPost');
        Routes::Get('persons/edit', $this, 'PersonUpdate');
        Routes::Post('persons/edit', $this, 'PersonUpdatePost');
        Routes::Get('persons/activate', $this, 'PersonActivate');
        Routes::Get('persons/deactivate', $this, 'PersonDeactivate');
        Routes::Get('persons/delete', $this, 'PersonDelete');
        // RoutesController::Add('persons/userconnection/edit', $this, 'PersonUserConnectionUpdate');
        
        $this->ph = new PersonsHelper();
    }
    public function PageLoad() {
        $groups = 5;
        $claims = array(18, 19,32);

        self::$Data['clients'] = $this->ph->GetPersonsInGroupsWithClaims($groups, $claims);
        self::$Data['users'] = $this->ph->GetPersonsInGroupsWithClaims([8, 10, 6, 5, 11, 9, 7], $claims);
    }

    public function PersonList($data){
        $groups = isset($data['groups']) ? [$data['groups']] : null;
        $claims = isset($data['claims']) ? explode(',', $data['claims']) : null;
        $persons = $this->ph->GetPersonsInGroupsWithClaims($groups, $claims);
        
        $claim_names = DB::table('persons_claim')->whereIn('id', $claims)->get();
        $result = array(); // result array
        /*foreach($claim_names as $claim_name){
            $result[array_search($claim_name, $claims)] = $claim_name; // adding values
        }*/
        
        foreach($claims as $claim){
            foreach($claim_names as $claim_name){
                if($claim_name->id == $claim){
                    $result[] = $claim_name;
                    break;
                }
            }  
        }
        
        self::$Data['groups'] = DB::table('persons_group')->whereIn('id', $groups)->get();
        self::$Data['claims'] = $result;
        self::$Data['edit_permissions'] = Permissions::Check($_SESSION['user']['id'], 'persons', 'groups/' . $groups[0] . '/edit');
        self::$Data['view_permissions'] = Permissions::Check($_SESSION['user']['id'], 'persons', 'groups/' . $groups[0] . '/view');
        //dump(DB::table('persons_group')->whereIn('id', $groups));
       // self::$Data['claimNames'] = $this->ph->GetClaimNames($claims);

        foreach($persons as $key => $person){
            $persons[$key] = $person = (array)$person;
            $persons[$key]['is_delete_allowed'] = !$person['active'] && date('U',strtotime($person['active_change_date']) + 604800) < (date('U'));
            $persons[$key]['is_edit_allowed'] = self::$Data['edit_permissions'] || Permissions::Check($_SESSION['user']['id'], 'relation', $person['id']);
            $persons[$key]['is_view_allowed'] = self::$Data['view_permissions'] || Permissions::Check
            ($_SESSION['user']['id'], 'relation', $person['id']);
        }
        self::$Data['persons'] = $persons;
        return $this->OkResult('List');
    }
    
     public function PersonView($data) { 
         if(!Permissions::Check($_SESSION['user']['id'], 'relation', $data['id'])){
             return $this->RedirectResult('/forbidden');
         }

        $person = array(
            'id' => $data['id'],
            'claims' => $this->ph->GetPersonClaims($data['id'])
        );
        
        if($person != null){
            self::$Data['person'] = $person;
            return $this->OkResult('View');
        }
        
        return $this->NotFoundResult('PersonNotFound');
    }

    public function ViewPersonFilesGet($data){
        $fm = new FileManager('persons/' . $data['id']);
        //dump($fm);
        $fm->Run();
        exit();
    }
    
    public function ViewPersonFilesPost($data){
        $fm = new FileManager('persons/' . $data['id']);
        //dump($fm);
        $fm->Run();
        exit();
    }
    
    public function PersonAdd($data){
        $groupId = isset($data['group']) ? $data['group'] : 5;
        self::$Data['claims'] = $this->ph->GetClaimsForGroup($groupId);

        return $this->OkResult('Edit');
    }
    
    public function PersonAddPost($data){        
        $personId = $this->ph->AddPerson();
        $this->ph->AddPersonToGroup($personId, $data['group']);

        Modules::PushEvent('person.created', $personId);

        foreach ($data['claims'] as $claimId => $claimValue) {
            $this->ph->AddPersonClaim($personId, $claimId, $claimValue);
        }
                
        return $this->RedirectResult('/persons?addsuccess=1&groups=' . $data['group'] . '&claims=25,19,32');
    }    
    
    public function PersonUpdate($data) {
        $person = array(
            'id' => $data['id'],
            'claims' => $this->ph->GetPersonClaims($data['id']),
            'groups' => $this->ph->GetPersonGroups($data['id'])
        );
        
        self::$Data['claims'] = $this->ph->GetClaimsForGroup($person['groups']);
        self::$Data['person'] = $person;
        
        return $this->OkResult('Edit');
    }
    
    public function PersonUpdatePost($data) {
        foreach ($data['claims'] as $claimId => $claimValue) {
            $this->ph->AddPersonClaim($data['id'], $claimId, $claimValue);
        }
        
        return $this->RedirectResult('/persons/view?updatesuccess=1&id=' . $data['id']);
    }
    
    public function PersonDeactivate($data) { 
        $query = DB::table('persons_person')
                ->where('id', $data['id'])
                ->update(array("active" => 0));
        
        return $this->RedirectResult('/persons');
    }
    
    public function PersonActivate($data) {
        $query = DB::table('persons_person')
                ->where('id', $data['id'])
                ->update(array("active" => 1));
        
        return $this->RedirectResult('/persons');
    }
    
    public function PersonDelete($data) {
        $query = DB::table('persons_person')->where('id', $data['id'])->delete();

        return $this->RedirectResult('/persons');
    }
    
    private function ActivePersons(){
        return DB::table('persons_persons')
            ->where('active', '=', true);
    }
    
    private function AddIfExists($array, $data, $key) {
        if (!isset($data[$key])) {
            return $array;
        }

        $array[$key] = $data[$key];

        return $array;
    }
    
}
