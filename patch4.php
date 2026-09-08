<?php
$file = 'app/Http/Controllers/LocalController.php';
$content = file_get_contents($file);

$append = <<<EOT

    public function scorer(\$id)
    {
        \$match = CricketMatch::with(['team1', 'team2', 'tournament'])->findOrFail(\$id);
        \$isLocal = true;
        return view('admin.scorer', compact('match', 'isLocal'));
    }
}
EOT;

$content = preg_replace('/}\s*$/', $append, $content);
file_put_contents($file, $content);
echo "Done";
