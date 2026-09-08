<?php
$file = 'resources/views/local/manage-tournament.blade.php';
$content = file_get_contents($file);

$oldLogic = "@if(\$tournament->status == 'draft')
              <form method=\"POST\" action=\"{{ route('admin.publish-tournament', \$tournament->id) }}\" style=\"margin:0;\">
                  @csrf
                  <button type=\"submit\" style=\"background: #064e3b; color: white; font-weight: 700; padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer; box-shadow: 0 4px 10px rgba(6, 78, 59, 0.15);\">
                      Publish
                  </button>
              </form>
              @elseif(\$tournament->status == 'published' || \$tournament->status == 'ongoing')
              <form method=\"POST\" action=\"{{ route('admin.mark-completed', \$tournament->id) }}\" style=\"margin:0;\">
                  @csrf
                  <button type=\"submit\" style=\"background: #064e3b; color: white; font-weight: 700; padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer; box-shadow: 0 4px 10px rgba(6, 78, 59, 0.15);\">
                      Mark Completed
                  </button>
              </form>
              @elseif(\$tournament->status == 'completed')
              <form method=\"POST\" action=\"{{ route('admin.publish-tournament', \$tournament->id) }}\" style=\"margin:0;\">
                  @csrf
                  <button type=\"submit\" style=\"background: #f97316; color: white; font-weight: 700; padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer; box-shadow: 0 4px 10px rgba(249, 115, 22, 0.15);\">
                      Mark Ongoing
                  </button>
              </form>
              @endif";

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

$content = str_replace($oldLogic, $newLogic, $content);
file_put_contents($file, $content);
echo "Local view updated";
