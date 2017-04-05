<?php
/* 
 * --- Persons ---
 * public function GetPerson($id, $onlyActive = true)
 * public function GetPersons($onlyActive = true)
 * public function AddPerson()
 * public function RemovePerson($personId)
 * 
 * --- Groups ---
 * public function AddGroup($groupName)
 * public function ChangeGroup($groupId, $groupName)
 * 
 * public function AddPersonToGroup($personId, $groupId)
 * public function CheckPersonIsMemberOfGroup($personId, $groupId)
 * public function RemovePersonFromGroup($personId, $groupId)
 * 
 * --- Claims --- 
 * public function AddClaim($name, $type = 'text', $value = null)
 * public function AddClaimToGroup($claim, $groupId)
 * 
 * public function AddPersonClaim($personId, $claimId, $claimValue)
 * public function AddPersonClaims($personId, $claims = array())
 * public function GetPersonClaim($personId, $claimId)
 * public function GetPersonClaims($personId)
 * public function RemovePersonClaim($personId, $claimId)
 * 
 * --- Permissions ---
 * public function AddPermission($route, $access)
 * public function AddPermissionToGroup($permission, $groupId)
 * public function RemovePermissionFromGroup($permission, $groupId)
 * 
 * --- Users ---
 * public function AddUser($personId, $username, $password, $active = true, $blocked = false)
 * public function ChangePassword($personId, $newPassword)
 * public function CheckPassword($personId, $Password)
 * 
 * --- Privates ---
 * private function Encrypt($password)
 * private function CompareHash($password, $hash)
 *  
 */
class PersonsHelper {

//    --- Persons ---
    public function GetPerson($id, $onlyActive = true){
        $table = DB::table('persons_person');
        if($onlyActive)
            $table = $table->where('active', true);
            
        return $table->find($id);
    }
    
    public function GetPersonsWithClaims($onlyActive = true, $claims){
        $query = DB::table('persons_person')
            ->join('persons_person_claim', 'persons_person.id', '=', 'persons_person_claim.person_id')
            ->join('persons_claim', 'persons_person_claim.claim_id', '=', 'persons_claim.id');
        
        if($claims != null)
            $query = $query->whereIn('persons_person_claim.claim_id', $claims);
        
        $query = $query->select(array('persons_person_claim.person_id', 'persons_person.active', 'persons_person.active_change_date', 'persons_person_claim.claim_id', 'persons_claim.name', 'persons_person_claim.value'))
            ->groupBy(array('person_id', 'claim_id'));
        
        $persons = $query->get();
        $result = array();
        
        $claimsArray = is_array($claims) ? array_flip($claims) : null;
        
        foreach ($persons as $person) {
            if(!isset($result[$person->person_id])){
                $result[$person->person_id] = array(
                    'id' => $person->person_id,
                    'active' => $person->active,
                    'active_change_date' => $person->active_change_date,
                    'claims' => $claimsArray
                );
            }
            
            if(isset($claimsArray[$person->claim_id])){
                $result[$person->person_id]['claims'][$person->claim_id] = array(
                    'id' => $person->claim_id,
                    'name' => $person->name,
                    'value' => $person->value
                );
            }
        }
            
        return $result;
    }
    
    public function GetPersons($onlyActive = true){
        $table = DB::table('persons_person');
        if($onlyActive)
            $table = $table->where('active', true);
            
        return $table->get();
    }
    
    public function AddPerson(){
        return DB::table('persons_person')
            ->insert(array('created_on' => time()));
    }
    
    public function RemovePerson($personId){
        return DB::table('persons_person')
            ->where('id', $personId)->delete();
    }

//    --- Groups ---
    public function GetPersonGroups($personId){
        $groups = DB::table('persons_person_group')
            ->where('person_id', $personId)
            ->select('group_id')
            ->get();

        $groupsArray = [];

        foreach ($groups as $group) {
            $groupsArray[] = (int) $group->group_id;
        }

        return $groupsArray;
    }
    
    public function GetGroups(){
        return DB::table('persons_group')->get();
    }   
    
    public function AddGroup($groupName){
        return DB::table('persons_group')
            ->insert(array('name' => $groupName));
    }
    
    public function ChangeGroup($groupId, $groupName){
        return DB::table('persons_group')
            ->where('id', $groupId)
            ->update(array('name' => $groupName));
    }

    public function AddPersonToGroup($personId, $groupId){
        return DB::table('persons_person_group')
            ->insert(array('person_id' => $personId, 'group_id' => $groupId));
    }
    
    public function GetPersonsInGroupsWithClaims($groups, $claims = null, $activeOnly = true){
        $query = $this->GetPersonsInGroup($groups)
            ->join('persons_person_claim', 'persons_person.id', '=', 'persons_person_claim.person_id')
            ->join('persons_claim', 'persons_person_claim.claim_id', '=', 'persons_claim.id');
        
        
        
        if($claims != null)
            $query = $query->whereIn('persons_person_claim.claim_id', $claims);    
        
        $query = $query->select(array('persons_person_claim.person_id', 'persons_person.active', 'persons_person.active_change_date', 'persons_person_claim.claim_id', 'persons_claim.name', 'persons_person_claim.value'))
            ->groupBy(array('person_id', 'claim_id'));
            
        $persons = $query->get();
        $result = array();
        
        $claimsArray = is_array($claims) ? array_flip($claims) : null;
        
        foreach ($persons as $person) {
            if(!isset($result[$person->person_id])){
                $result[$person->person_id] = array(
                    'id' => $person->person_id,
                    'active' => $person->active,
                    'active_change_date' => $person->active_change_date,
                    'claims' => $claimsArray
                );
            }
            
            if(isset($claimsArray[$person->claim_id])){
                $result[$person->person_id]['claims'][$person->claim_id] = array(
                    'id' => $person->claim_id,
                    'name' => $person->name,
                    'value' => $person->value
                );
            }
        }
        return $result;
    }
    
    public function CheckPersonIsMemberOfGroups($personId, $groups){
        $query = DB::table('persons_person_group');
        
            foreach ($groups as $groupId) {
                $query->where(function($q)
                {
                    $q->where('person_id', $personId);
                    $q->andWhere('group_id', $groupId);
                });
            }
            
        return count($groups) === 1;
    }
    
    public function RemovePersonFromGroup($personId, $groupId){
        return DB::table('persons_person_group')
            ->where('person_id', $personId)
            ->where('group_id', $groupId)
            ->delete();
    }

//    --- Claims --- 
    
    public function GetClaimsForGroup($groups){
        $query = DB::table('persons_group_claim');
        
        if(is_array($groups))
            $query = $query->whereIn('persons_group_claim.group_id', $groups);
        else
            $query = $query->where('persons_group_claim.group_id', $groups);
            
        $claims = $query->join('persons_claim', 'persons_group_claim.claim_id', '=', 'persons_claim.id')
            ->orderBy('persons_claim.order')
            ->select('persons_claim.*', 'persons_group_claim.required', 'persons_group_claim.hidden')
            ->get();
        
        foreach ($claims as $claim) {
            $claim->value = json_decode($claim->value, true);
        }

        return $claims;
    }
    
    public function AddClaim($name, $type = 'text', $value = null){
        $claim = array(
            'name' => $name,
            'type' => $type,
            'value' => $value
        );
        
        return DB::table('persons_claim')
            ->insert($claim);
    }
    
    public function AddClaimToGroup($claimId, $groupId, $required = false, $hidden = false){    
        $groupClaim = array(
            'group_id' => $groupId,
            'claim_id' => $claimId,
            'required' => $required,
            'hidden' => $hidden
        );
        
        $onExistingClaim = array(
            'required' => $required,
            'hidden' => $hidden
        );
        
        return DB::table('persons_group_claim')
            ->onDuplicateKeyUpdate($onExistingClaim)
            ->insert($groupClaim);
    }

    public function AddPersonClaim($personId, $claimId, $claimValue){
        $personClaim = array(
            'person_id' => $personId,
            'claim_id' => $claimId,
            'value' => $claimValue
        );
        
        return DB::table('persons_person_claim')
            ->onDuplicateKeyUpdate(array('value' => $claimValue))
            ->insert($personClaim);
    }
    
    public function AddPersonClaims($personId, $claims = array()){
        foreach ($claims as $claim) {
            $this->AddPersonClaim($personId, $claim['id'], $claim['value']);
        }
    }
    
    public function GetPersonClaim($personId, $claimId){
        $claims = DB::table('persons_person_claim')
            ->where('person_id', $personId)
            ->where('claim_id', $claimId)
            ->get();
        
        $claim = $claims[0];
        return $claim->value;
    }
    
    public function GetPersonClaims($personId){
        $claims = DB::table('persons_person_claim')
            ->where('person_id', $personId)
            ->join('persons_claim', 'persons_person_claim.claim_id', '=', 'persons_claim.id')
            ->orderBy('persons_claim.order')
            ->select(array('persons_claim.value' => 'claim_value'))
            ->select(array('persons_person_claim.value' => 'value'))
            ->select(array('claim_id'))
            ->select(array('name'))
            ->select(array('type'))
            ->get();
        
        $result = array();
        
        foreach ($claims as $claim) {
            $claim->claim_value = json_decode($claim->claim_value, true);
            $result[$claim->claim_id] = $claim;
        }
        
        return $result;
    }
    
    public function RemovePersonClaim($personId, $claimId){
        return DB::table('persons_person_claim')
            ->where('person_id', $personId)
            ->where('claim_id', $claimId)
            ->delete();
    }

//    --- Permissions ---
    public function AddPermission($route, $access){
        $permission = array(
            'route' => $route,
            'access' => $access
        );
        
        return DB::table('users_permissions')
            ->insert($permission);
    }
    
    public function AddPermissionToGroup($permissionId, $groupId){
        $groupPermission = array(
            'group_id' => $groupId,
            'permission_id' => $permissionId,
        );
        
        return DB::table('users_group_permission')
            ->insert($groupPermission);
    }
    
    public function RemovePermissionFromGroup($permissionId, $groupId){
        return DB::table('persons_person_claim')
            ->where('group_id', $groupId)
            ->where('permission_id', $permissionId)
            ->delete();
    }

//    --- Users ---
    public function AddUser($personId, $username, $password, $active = true, $blocked = false){
        $user = array(
            'person_id' => $personId,
            'username' => $username,
            'password' => $this->Encrypt($password),
            'active' => $active,
            'blocked' => $blocked
        );
        
        return DB::table('users_user')
            ->insert($user);
    }
    
    public function ChangePassword($personId, $newPassword){
        return DB::table('users_user')
            ->where('person_id', $personId)
            ->update(array('password' => $this->Encrypt($newPassword)));
    }
    
    public function CheckPassword($personId, $password){
        $users = DB::table('users_user')
            ->where('person_id', $personId)
            ->select('password')
            ->get();
        
        if(empty($users)){
            return false;
        }
        
        $user = $users[0];
        $hash = $user->password;
        
        return $this->CompareHash($password, $hash);
    }
    
    private function GetPersonsInGroup($groups){
        $query = DB::table('persons_person')
            ->join('persons_person_group', 'persons_person_group.person_id', '=', 'persons_person.id');

        if(is_array($groups)){
            $query = $query->whereIn('group_id', $groups);
        } else {
            $query = $query->where('group_id', $groups);
        }

        $query = $query->join('persons_group', 'persons_group.id', '=', 'persons_person_group.group_id');
        
        return $query;
    }
    
    private function GetAllPersonsNotInGroups($groups){
        $query = DB::table('persons_person')
            ->join('persons_person_group', 'persons_person_group.person_id', '=', 'persons_person.id');
        
        if(is_array($groups)){
            $query = $query->whereNotIn('group_id', $groups);
        } else {
            $query = $query->whereNot('group_id', $groups);
        }

        return $query;
    }
        
    private function Encrypt($password) {
        $cost = 10;

        // Create a random salt
        $salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');

        // Prefix information about the hash so PHP knows how to verify it later.
        // "$2a$" Means we're using the Blowfish algorithm. The following two digits are the cost parameter.
        $salt = sprintf("$2a$%02d$", $cost) . $salt;

        // Value:
        // $2a$10$eImiTXuWVxfM37uY4JANjQ==
        // Hash the password with the salt
        return crypt($password, $salt);
    }

    private function CompareHash($password, $hash) {
        // Hashing the password with its hash as the salt returns the same hash
        return hash_equals($hash, crypt($password, $hash));
    }
}
