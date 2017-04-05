<?php
class RelationController extends Module{
    private $ph;
    
    function __construct() {
      
        Routes::Get('relations/add', $this, 'RelationAdd');
        Routes::Post('relations/add', $this, 'RelationAddPost');
        Routes::Get('relations', $this, 'RelationList');
        // Hook events to methods
        // ModulesController::HookEvent(event, module, method);
        
        $this->ph = new PersonsHelper();
    }
    
    public function RelationList($data){
        $relations = self::$Data['relations'] = DB::table('persons_relation')->get();
        
        /* if(isset($data['id']) && $data['id'] != null){
            self::$Data['relations'] = DB::table('persons_relation')->where('person_id',$data['id'])->get();
        */

        foreach($relations as $key => $relation){
            $relation = json_decode(json_encode($relation), true);
            $relation['person_name'] = $this->ph->GetPersonClaims($relation['person_id']);
            $relation['person2_name'] = $this->ph->GetPersonClaims($relation['person2_id']) ;
            $relation['disposal'] = $relation['disposal'];

            $relations[$key] = $relation;
        };


        self::$Data['relations'] = $relations;
        
        return $this->OkResult('List');
    }
    
    public function RelationAdd($data){
        self::$Data['employees'] = $this->ph->GetPersonsInGroupsWithClaims([6,7,8,9,10,11], [19, 32]);
        return $this->OkResult('Add');
    }
    
    public function RelationAddPost($data){
        $relation = array(
            "person_id" => $data['person_1'],
            "person2_id" => $data['person_2'],
            //"connection_type" => null
            "rate_per_hour" => $data['rate_per_hour'],
            "rate_per_kilometer" => $data['rate_per_kilometer'],
            "begin_date" => $data['begin_date'],
            "end_date" => $data['end_date'],
            "disposal" => $data['disposal'],
        );
                
        DB::table('persons_relation')->insert($relation);
        Permissions::Add('relation', $data['person_2'], $data['person_1']);

        return $this->RedirectResult('/relations');
    }
}