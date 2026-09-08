<?php
$file = 'app/Http/Controllers/AdminController.php';
$content = file_get_contents($file);

$append = <<<EOT

    public function publishTournament(\$id)
    {
        \$tournament = Tournament::findOrFail(\$id);
        \$tournament->status = 'ongoing';
        \$tournament->save();
        return back()->with('success', 'Tournament published as ongoing!');
    }

    public function markCompleted(\$id)
    {
        \$tournament = Tournament::findOrFail(\$id);
        \$tournament->status = 'completed';
        \$tournament->save();
        return back()->with('success', 'Tournament marked as completed!');
    }
}
EOT;

$content = preg_replace('/}\s*$/', $append, $content);
file_put_contents($file, $content);
echo "Done";
