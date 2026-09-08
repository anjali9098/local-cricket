<?php
$file = 'resources/views/admin/dashboard.blade.php';
$content = file_get_contents($file);

$oldForm = '<form method="POST" action="{{ route(\'admin.create-tournament\') }}">';
$endForm = '</form>';

$formStartPos = strpos($content, $oldForm);
$formEndPos = strpos($content, $endForm, $formStartPos) + strlen($endForm);

$newFormHtml = '<form method="POST" action="{{ route(\'admin.create-tournament\') }}" style="max-height: 70vh; overflow-y: auto; padding-right: 8px;">
            @csrf
            
            <h3 style="font-size: 1.1rem; font-weight: 800; margin-bottom: 16px; color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">Basic Details</h3>
            
            <div style="margin-bottom: 16px;">
                <label style="display:block; margin-bottom: 8px; font-weight: 700; color: #475569;">Tournament Name *</label>
                <input type="text" name="name" placeholder="e.g. Mumbai Premier League 2026" required style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 1rem; color: #0f172a; outline: none; box-sizing:border-box;">
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="display:block; margin-bottom: 8px; font-weight: 700; color: #475569;">City *</label>
                    <input type="text" name="city" required style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 1rem; color: #0f172a; outline: none; box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block; margin-bottom: 8px; font-weight: 700; color: #475569;">State</label>
                    <input type="text" name="state" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 1rem; color: #0f172a; outline: none; box-sizing:border-box;">
                </div>
            </div>
            
            <div style="margin-bottom: 16px;">
                <label style="display:block; margin-bottom: 8px; font-weight: 700; color: #475569;">Venue</label>
                <input type="text" name="venue" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 1rem; color: #0f172a; outline: none; box-sizing:border-box;">
            </div>
            
            <div style="margin-bottom: 24px;">
                <label style="display:block; margin-bottom: 8px; font-weight: 700; color: #475569;">Banner URL</label>
                <input type="url" name="banner_url" placeholder="https://..." style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 1rem; color: #0f172a; outline: none; box-sizing:border-box;">
            </div>
            
            <h3 style="font-size: 1.1rem; font-weight: 800; margin-bottom: 16px; color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">Format</h3>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="display:block; margin-bottom: 8px; font-weight: 700; color: #475569;">Format *</label>
                    <select name="format" required style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 1rem; color: #0f172a; outline: none; box-sizing:border-box;">
                        <option value="T20">T20</option>
                        <option value="ODI">ODI</option>
                        <option value="Test">Test</option>
                        <option value="T10">T10</option>
                    </select>
                </div>
                <div>
                    <label style="display:block; margin-bottom: 8px; font-weight: 700; color: #475569;">Overs per Innings</label>
                    <input type="number" name="overs" value="20" required style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 1rem; color: #0f172a; outline: none; box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block; margin-bottom: 8px; font-weight: 700; color: #475569;">Type</label>
                    <select name="type" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 1rem; color: #0f172a; outline: none; box-sizing:border-box;">
                        <option value="Knockout">Knockout</option>
                        <option value="League">League</option>
                    </select>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="display:block; margin-bottom: 8px; font-weight: 700; color: #475569;">Start Date</label>
                    <input type="date" name="start_date" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 1rem; color: #0f172a; outline: none; box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block; margin-bottom: 8px; font-weight: 700; color: #475569;">End Date</label>
                    <input type="date" name="end_date" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 1rem; color: #0f172a; outline: none; box-sizing:border-box;">
                </div>
            </div>
            
            <div style="margin-bottom: 24px;">
                <label style="display:block; margin-bottom: 8px; font-weight: 700; color: #475569;">Description</label>
                <textarea name="description" rows="3" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 1rem; color: #0f172a; outline: none; box-sizing:border-box; resize: vertical;"></textarea>
            </div>
            
            <div style="display: flex; gap: 12px; justify-content: flex-end; position: sticky; bottom: 0; background: white; padding-top: 16px; border-top: 1px solid #e2e8f0;">
                <button type="button" onclick="closeCreateModal()" style="background: transparent; color: #64748b; font-weight: 700; padding: 10px 20px; border: none; cursor: pointer;">Cancel</button>
                <button type="submit" style="background: #0ea5e9; color: white; font-weight: 700; padding: 10px 24px; border-radius: 8px; border: none; cursor: pointer;">Create Tournament</button>
            </div>
        </form>';

$newContent = substr_replace($content, $newFormHtml, $formStartPos, $formEndPos - $formStartPos);
// Also increase modal max-width since the form has columns now
$newContent = str_replace('max-width: 500px;', 'max-width: 600px;', $newContent);

file_put_contents($file, $newContent);
echo "Form updated";
