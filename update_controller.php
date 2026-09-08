<?php
$file = 'app/Http/Controllers/AdminController.php';
$content = file_get_contents($file);

$oldLogic = '
    public function createTournament(Request $request)
    {
        $name = trim($request->input(\'name\', \'\'));
        if (!empty($name)) {
            $shortName = strtoupper(substr(preg_replace(\'/[^A-Za-z0-9]/\', \'\', $name), 0, 6));
            Tournament::create([
                \'user_id\' => \Illuminate\Support\Facades\Auth::id(),
                \'name\' => $name,
                \'short_name\' => $shortName,
                \'format\' => $request->input(\'format\', \'T20\'),
                \'series_type\' => \'GLOBAL\',
                \'category\' => \'international\',
                \'start_date\' => $request->input(\'start_date\'),
                \'end_date\' => $request->input(\'end_date\')
            ]);

            return redirect()->route(\'admin.dashboard\')->with(\'success\', \'Tournament created successfully!\');
        }

        return redirect()->route(\'admin.dashboard\')->with(\'error\', \'Tournament name is required.\');
    }';

$newLogic = '
    public function createTournament(Request $request)
    {
        $name = trim($request->input(\'name\', \'\'));
        if (!empty($name)) {
            $shortName = strtoupper(substr(preg_replace(\'/[^A-Za-z0-9]/\', \'\', $name), 0, 6));
            Tournament::create([
                \'user_id\' => \Illuminate\Support\Facades\Auth::id(),
                \'name\' => $name,
                \'short_name\' => $shortName,
                \'city\' => $request->input(\'city\'),
                \'state\' => $request->input(\'state\'),
                \'venue\' => $request->input(\'venue\'),
                \'banner_url\' => $request->input(\'banner_url\'),
                \'format\' => $request->input(\'format\', \'T20\'),
                \'overs\' => (int)$request->input(\'overs\', 20),
                \'type\' => $request->input(\'type\', \'Knockout\'),
                \'series_type\' => \'GLOBAL\',
                \'category\' => \'international\',
                \'start_date\' => $request->input(\'start_date\'),
                \'end_date\' => $request->input(\'end_date\'),
                \'description\' => $request->input(\'description\')
            ]);

            return redirect()->route(\'admin.dashboard\')->with(\'success\', \'Tournament created successfully!\');
        }

        return redirect()->route(\'admin.dashboard\')->with(\'error\', \'Tournament name is required.\');
    }';

$content = str_replace($oldLogic, $newLogic, $content);
file_put_contents($file, $content);
echo "Controller updated";
