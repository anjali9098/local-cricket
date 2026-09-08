<?php
$file = 'resources/views/local/manage-tournament.blade.php';
$content = file_get_contents($file);

$search = '/<a href="#" style="background: rgba\(255, 255, 255, 0\.05\);/';
$replace = '<a href="{{ route(\'tournaments\') }}" style="background: rgba(255, 255, 255, 0.05);';
$content = preg_replace($search, $replace, $content);

$search2 = '/@if\(\$tournament->status == \'draft\'\)\s*<form method="POST" action="\{\{ route\(\'local\.publish-tournament\', \$tournament->id\) \}\}">\s*@csrf\s*<button type="submit" style="background: var\(--primary\); color: var\(--text-main\); font-weight: 700; padding: 10px 18px; border-radius: 6px; border: none; cursor: pointer;">\s*Publish Tournament\s*<\/button>\s*<\/form>\s*@endif/';

$replace2 = <<<EOT
@if(\$tournament->status == 'draft')
            <form method="POST" action="{{ route('local.publish-tournament', \$tournament->id) }}">
                @csrf
                <button type="submit" style="background: var(--primary); color: var(--text-main); font-weight: 700; padding: 10px 18px; border-radius: 6px; border: none; cursor: pointer;">
                    Publish Tournament
                </button>
            </form>
            @elseif(\$tournament->status == 'published' || \$tournament->status == 'ongoing')
            <form method="POST" action="{{ route('local.mark-completed', \$tournament->id) }}">
                @csrf
                <button type="submit" style="background: #16a34a; color: var(--text-main); font-weight: 700; padding: 10px 18px; border-radius: 6px; border: none; cursor: pointer;">
                    Mark Completed
                </button>
            </form>
            @elseif(\$tournament->status == 'completed')
            <form method="POST" action="{{ route('local.publish-tournament', \$tournament->id) }}">
                @csrf
                <button type="submit" style="background: #ea580c; color: var(--text-main); font-weight: 700; padding: 10px 18px; border-radius: 6px; border: none; cursor: pointer;">
                    Mark Ongoing
                </button>
            </form>
            @endif
EOT;

$content = preg_replace($search2, $replace2, $content);
file_put_contents($file, $content);
echo "Done";
