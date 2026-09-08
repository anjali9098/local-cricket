<?php
$file = 'resources/views/local/manage-tournament.blade.php';
$content = file_get_contents($file);

// Try generic replace just in case indentation varies
$oldLogicRegex = '/@if\(\$tournament->status == \'draft\'\).*?@endif/s';
$newLogic = "@if(\$tournament->status == 'draft')
              <form method=\"POST\" action=\"{{ route('local.publish-tournament', \$tournament->id) }}\" style=\"margin:0;\">
                  @csrf
                  <button type=\"submit\" style=\"background: #064e3b; color: white; font-weight: 700; padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer; box-shadow: 0 4px 10px rgba(6, 78, 59, 0.15);\">
                      Publish
                  </button>
              </form>
              @elseif(\$tournament->status == 'published')
              <form method=\"POST\" action=\"{{ route('local.mark-ongoing', \$tournament->id) }}\" style=\"margin:0;\">
                  @csrf
                  <button type=\"submit\" style=\"background: #f97316; color: white; font-weight: 700; padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer; box-shadow: 0 4px 10px rgba(249, 115, 22, 0.15);\">
                      Mark Ongoing
                  </button>
              </form>
              @elseif(\$tournament->status == 'ongoing')
              <form method=\"POST\" action=\"{{ route('local.mark-completed', \$tournament->id) }}\" style=\"margin:0;\">
                  @csrf
                  <button type=\"submit\" style=\"background: #064e3b; color: white; font-weight: 700; padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer; box-shadow: 0 4px 10px rgba(6, 78, 59, 0.15);\">
                      Mark Completed
                  </button>
              </form>
              @endif";

// Wait, the original code had 'admin.publish-tournament' etc because they were pointing to the same admin controller in earlier code, let's just make it robust.
// Actually, let's just multi_replace_file_content or a simpler string replacement.
