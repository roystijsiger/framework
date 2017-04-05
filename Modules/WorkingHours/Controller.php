<?php
    class WorkingHoursController extends Module
    {
        function __construct()
        {
            Routes::Get('working-hours', $this, 'WorkingHoursList');
            Routes::Get('working-hours/add', $this, 'WorkingHoursAdd');
            Routes::Post('working-hours/add', $this, 'WorkingHoursAddPost');
        }

        public function WorkingHoursList()
        {
            dump(DB::table('workinghours_workinghours')
                ->where('client_id', $_SESSION['context']['client'])
                ->where('careprovider_id', $_SESSION['user']['id'])
                ->get());
        }

        public function WorkingHoursAdd()
        {

        }

        public function WorkingHoursAddPost()
        {

        }
    }
