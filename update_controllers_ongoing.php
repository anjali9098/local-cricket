<?php
$adminFile = 'app/Http/Controllers/AdminController.php';
$adminContent = file_get_contents($adminFile);
$adminLogic = "
    public function markOngoing(\$id)
    {
        \$tournament = Tournament::findOrFail(\$id);
        \$tournament->status = 'ongoing';
        \$tournament->save();
        return back()->with('success', 'Tournament marked as ongoing!');
    }

    public function markCompleted(\$id)";
$adminContent = str_replace("    public function markCompleted(\$id)", $adminLogic, $adminContent);
file_put_contents($adminFile, $adminContent);

$localFile = 'app/Http/Controllers/LocalController.php';
$localContent = file_get_contents($localFile);
$localLogic = "
    public function markOngoing(\$id)
    {
        \$tournament = Tournament::findOrFail(\$id);
        \$tournament->status = 'ongoing';
        \$tournament->save();
        return back()->with('success', 'Tournament marked as ongoing!');
    }

    public function markCompleted(\$id)";
$localContent = str_replace("    public function markCompleted(\$id)", $localLogic, $localContent);
file_put_contents($localFile, $localContent);

echo "Controllers updated";
