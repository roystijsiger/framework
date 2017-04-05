<?php
class Permissions {
    public static function Init(){
        if (!class_exists('DB')){
            exit('DatabaseController must be initialized before the PermissionsController');
        }
    }

    public static function Add($namespace, $property, $personId){
        $existingPermissions = DB::table('users_permission')
            ->where('namespace', $namespace)
            ->where('property', $property)
            ->get();

        if(!empty($existingPermissions)) {
            $existingPermission = $existingPermissions[0];
            $persons = json_decode($existingPermission->persons);

            if(in_array($personId, $persons))
                return;

            array_push($persons, $personId);
            $existingPermission->persons = json_encode($persons);
            DB::table('users_permission')
                ->where('id', $existingPermission->id)         
                ->update(json_decode(json_encode($existingPermission), true));

            return;
        }

        DB::table('users_permission')->insert([
            'namespace' => $namespace,
            'property' => $property,
            'persons' => json_encode([$personId])
        ]);
    }
    
    public static function Check($personId, $namespace, $property){
        $permissions = DB::table('users_permission')
            ->where('namespace', $namespace)
            ->where('property', $property)
            ->get();
            //->getQuery()->getRawSql()

        if(count($permissions) === 0){
            return true;
        }

        foreach ($permissions as $permission) {
            $permissionPersons = json_decode($permission->persons);
            if($permissionPersons != null) {
                if (in_array($personId, $permissionPersons)) {
                    return true;
                }
            }
        }

        $personGroups = (new PersonsHelper())->GetPersonGroups($personId);

        foreach ($permissions as $permission) {
            $permissionGroups = json_decode($permission->groups);

            if($permissionGroups != null) {
                if (count(array_intersect($permissionGroups, $personGroups)) === 1) {
                    return true;
                }
            }
        }

        return false;
    }
}
