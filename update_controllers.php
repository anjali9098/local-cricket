<?php
$adminFile = 'app/Http/Controllers/AdminController.php';
$adminContent = file_get_contents($adminFile);
$adminLogic = '
    public function deleteTeam($id)
    {
        $team = \App\Models\Team::findOrFail($id);
        $team->delete();
        return back()->with(\'success\', \'Team deleted successfully.\');
    }

    public function deletePlayer($id)
    {
        $player = \App\Models\Player::findOrFail($id);
        $player->delete();
        return back()->with(\'success\', \'Player deleted successfully.\');
    }
}';
$adminContent = preg_replace('/\}\s*$/', $adminLogic, $adminContent);
file_put_contents($adminFile, $adminContent);

$localFile = 'app/Http/Controllers/LocalController.php';
$localContent = file_get_contents($localFile);
$localLogic = '
    public function deleteTeam($id)
    {
        $team = \App\Models\Team::findOrFail($id);
        $team->delete();
        return back()->with(\'success\', \'Team deleted successfully.\');
    }

    public function deletePlayer($id)
    {
        $player = \App\Models\Player::findOrFail($id);
        $player->delete();
        return back()->with(\'success\', \'Player deleted successfully.\');
    }
}';
$localContent = preg_replace('/\}\s*$/', $localLogic, $localContent);
file_put_contents($localFile, $localContent);

echo "Delete methods added";
