<!-- ========================================================
     GLOBAL STANDARD IMAGE UPLOADER & GALLERY MODAL
     ======================================================== -->
<div id="globalImageUploaderModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(5px); z-index: 999999; align-items: center; justify-content: center; padding: 12px; overflow-y: auto;">
    
    <div style="background: white; border-radius: 12px; width: 96vw; max-width: 1280px; height: 95vh; max-height: 96vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); overflow: hidden; animation: modalFadeIn 0.2s ease; position: relative;">
        
        <!-- Modal Header -->
        <div style="padding: 14px 22px; background: #0f172a; color: white; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 1.4rem;">🖼️</span>
                <div>
                    <h3 id="modalHeaderTitle" style="margin: 0; font-size: 1.05rem; font-weight: 800; color: #f8fafc; letter-spacing: -0.01em;">
                        Standard Image Uploader &amp; Media Gallery
                    </h3>
                    <div style="font-size: 0.74rem; color: #94a3b8; font-weight: 600; margin-top: 2px;">
                        <span id="modalActionSubtitle">Crop, Zoom, Resize &amp; Insert directly into:</span> <span id="modalTargetTextareaLabel" style="color: #38bdf8; font-weight: 800;">Description</span>
                    </div>
                </div>
            </div>
            <button type="button" onclick="closeGlobalImageUploader()" style="background: transparent; border: none; color: #94a3b8; font-size: 1.7rem; line-height: 1; cursor: pointer; padding: 0 4px;" title="Close Modal">&times;</button>
        </div>

        <!-- Top Floating Success Popup in Modal (Replaces bottom status banner as requested) -->
        <div id="modalTopSuccessPopup" style="display: none; position: absolute; top: 12px; left: 50%; transform: translateX(-50%); z-index: 1000; max-width: 780px; width: calc(100% - 32px); background: #ffffff; border: 2px solid #10b981; border-radius: 10px; box-shadow: 0 20px 45px -5px rgba(0,0,0,0.45); padding: 12px 18px; animation: slideDownPopup 0.25s ease;">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 8px; border-bottom: 1px solid #e2e8f0; padding-bottom: 6px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 1.25rem;">🎉</span>
                    <strong style="font-size: 0.92rem; color: #065f46; font-weight: 800;">Image Successfully Processed &amp; Saved!</strong>
                    <span id="modalTopUploadSizeBadge" style="background: #dcfce7; color: #166534; font-size: 0.72rem; padding: 2px 8px; border-radius: 4px; font-weight: 700;"></span>
                </div>
                <button type="button" onclick="closeModalTopSuccessPopup()" style="background: transparent; border: none; font-size: 1.4rem; color: #64748b; cursor: pointer; line-height: 1;" title="Close Popup">&times;</button>
            </div>
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <img id="modalTopPopupThumbImg" src="" alt="Thumbnail" style="width: 72px; height: 46px; object-fit: cover; border-radius: 6px; border: 1px solid #cbd5e1; background: #0f172a; flex-shrink: 0; cursor: pointer;" onclick="modalPreviewCroppedImage()" title="Click to view full preview">
                <div style="flex: 1; min-width: 200px;">
                    <div style="font-size: 0.72rem; font-weight: 700; color: #475569; margin-bottom: 2px;">Image Direct URL:</div>
                    <div id="modalUploadedUrlText" style="font-size: 0.8rem; color: #047857; font-family: monospace; word-break: break-all; background: #f0fdf4; padding: 6px 10px; border-radius: 6px; border: 1px solid #bbf7d0; max-height: 48px; overflow-y: auto; user-select: all;"></div>
                </div>
                <div style="display: flex; align-items: center; gap: 8px; margin-left: auto; flex-wrap: wrap;">
                    <button type="button" id="btnModalTopCopyUrl" onclick="modalCopyImageUrlToClipboard()" style="background: #10b981; color: white; font-weight: 800; font-size: 0.82rem; padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; box-shadow: 0 2px 4px rgba(16,185,129,0.25);">
                        📋 Copy Image URL
                    </button>
                    <button type="button" id="btnModalTopInsert" onclick="modalInsertCroppedImage()" style="background: #0f172a; color: white; font-weight: 800; font-size: 0.82rem; padding: 8px 14px; border: none; border-radius: 6px; cursor: pointer;">
                        📥 Insert
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Scrollable Body -->
        <div style="padding: 18px 22px; overflow-y: auto; display: flex; flex-direction: column; gap: 14px; flex: 1;">
            
            <!-- Source Selector Tabs (Upload New / Direct URL) -->
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <label style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; background: #0284c7; color: white; border-radius: 4px; font-weight: 800; font-size: 0.82rem; cursor: pointer;">
                        <span>📁 Choose Image File</span>
                        <input type="file" id="modalSourceFile" accept="image/*" onchange="modalHandleFileSelect(event)" style="display: none;">
                    </label>
                    <span id="modalFileNameLabel" style="font-size: 0.78rem; color: #64748b; font-weight: 600;">No file selected</span>
                </div>

                <!-- Aspect Ratio Buttons -->
                <div style="display: flex; align-items: center; gap: 6px;">
                    <span style="font-size: 0.76rem; font-weight: 700; color: #475569;">Ratio:</span>
                    <button type="button" class="modal-aspect-btn active" data-ratio="16/9" onclick="modalSetAspectRatio(16/9, this)" style="padding: 5px 12px; border: 1px solid #10b981; border-radius: 3px; background: #bbf7d0; color: #065f46; font-weight: 800; font-size: 0.8rem; cursor: pointer;">16x9</button>
                    <button type="button" class="modal-aspect-btn" data-ratio="4/3" onclick="modalSetAspectRatio(4/3, this)" style="padding: 5px 12px; border: 1px solid #cbd5e1; border-radius: 3px; background: white; color: #1e293b; font-weight: 800; font-size: 0.8rem; cursor: pointer;">4x3</button>
                    <button type="button" class="modal-aspect-btn" data-ratio="1/1" onclick="modalSetAspectRatio(1/1, this)" style="padding: 5px 12px; border: 1px solid #cbd5e1; border-radius: 3px; background: white; color: #1e293b; font-weight: 800; font-size: 0.8rem; cursor: pointer;">Square</button>
                    <button type="button" class="modal-aspect-btn" data-ratio="9/16" onclick="modalSetAspectRatio(9/16, this)" style="padding: 5px 12px; border: 1px solid #cbd5e1; border-radius: 3px; background: white; color: #1e293b; font-weight: 800; font-size: 0.8rem; cursor: pointer;">9x16</button>
                </div>
            </div>

            <!-- Zoom & Pan Control Toolbar -->
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; background: #f8fafc; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px;">
                <button type="button" onclick="modalAdjustZoom(-0.1)" title="Zoom Out (-)" style="width: 30px; height: 30px; border: 1px solid #cbd5e1; border-radius: 4px; background: white; font-size: 1.1rem; font-weight: 800; cursor: pointer; display: flex; align-items: center; justify-content: center;">-</button>
                <button type="button" onclick="modalAdjustZoom(0.1)" title="Zoom In (+)" style="width: 30px; height: 30px; border: 1px solid #cbd5e1; border-radius: 4px; background: white; font-size: 1.1rem; font-weight: 800; cursor: pointer; display: flex; align-items: center; justify-content: center;">+</button>
                <button type="button" onclick="modalFitToFrame()" title="Full Cover Frame (Fill edge-to-edge)" style="width: 30px; height: 30px; border: 1px solid #cbd5e1; border-radius: 4px; background: white; font-size: 0.85rem; cursor: pointer; display: flex; align-items: center; justify-content: center;">⛶</button>
                
                <span style="font-size: 0.8rem; font-weight: 700; color: #0284c7; min-width: 50px; text-align: center;" id="modalZoomDisplay">100%</span>

                <button type="button" onclick="modalPreviewCroppedImage()" title="Preview Output with Zoom & Crop" style="padding: 5px 12px; background: #0284c7; color: white; border: none; border-radius: 4px; font-size: 0.8rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                    👁️ Preview Output
                </button>

                <div style="height: 20px; width: 1px; background: #cbd5e1; margin: 0 4px;"></div>

                <!-- Text Overlay Input -->
                <input type="text" id="modalOverlayText" placeholder="Overlay text on image..." oninput="modalRenderCanvas()" style="padding: 5px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.8rem; width: 170px; outline: none; background: white;">

                <!-- Text Color -->
                <input type="color" id="modalTextColor" value="#ffffff" onchange="modalRenderCanvas()" title="Text Color" style="width: 30px; height: 30px; border: 1px solid #cbd5e1; border-radius: 4px; padding: 2px; cursor: pointer; background: white;">

                <!-- Stroke width & color -->
                <span style="font-weight: 700; font-size: 0.78rem; color: #475569;">Stroke</span>
                <input type="number" id="modalStrokeWidth" value="0" min="0" max="15" oninput="modalRenderCanvas()" style="width: 44px; padding: 4px 6px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.8rem; text-align: center; background: white;">
                <input type="color" id="modalStrokeColor" value="#000000" onchange="modalRenderCanvas()" title="Stroke Color" style="width: 30px; height: 30px; border: 1px solid #cbd5e1; border-radius: 4px; padding: 2px; cursor: pointer; background: white;">

                <span style="font-size: 0.72rem; color: #64748b; margin-left: auto; font-weight: 600;">
                    💡 Drag image to move &bull; Mouse wheel to zoom
                </span>
            </div>

            <!-- Canvas Viewport with Red Boundary Crop Box (Spacious & Crisp View) -->
            <div id="modalCanvasViewport" style="position: relative; width: 100%; height: 520px; min-height: 520px; background: #0f172a; border: 1px solid #cbd5e1; border-radius: 8px; overflow: hidden; display: flex; align-items: center; justify-content: center; cursor: grab; user-select: none;">
                
                <!-- Red Target Frame Overlay -->
                <div id="modalCropBoundaryBox" style="position: relative; border: 2px solid #ef4444; box-shadow: 0 0 0 9999px rgba(15, 23, 42, 0.7); pointer-events: none; z-index: 10; display: flex; align-items: center; justify-content: center;">
                    <div style="position: absolute; bottom: 6px; right: 8px; font-size: 0.7rem; font-weight: 800; color: #ffffff; background: #ef4444; padding: 2px 6px; border-radius: 3px;">
                        <span id="modalAspectLabel">16:9 Target Frame</span>
                    </div>
                </div>

                <!-- Canvas -->
                <canvas id="modalEditorCanvas" style="position: absolute; z-index: 5;"></canvas>

                <!-- Empty State Message -->
                <div id="modalEmptyNotice" style="position: absolute; z-index: 15; text-align: center; color: #94a3b8;">
                    <div style="font-size: 2.5rem; margin-bottom: 6px; opacity: 0.8;">🖼️</div>
                    <div style="font-weight: 800; font-size: 0.95rem; color: #f8fafc;">No image selected</div>
                    <div style="font-size: 0.78rem; color: #94a3b8; margin-top: 3px;">Click "Choose Image File" above to start</div>
                </div>
            </div>

            <!-- ROW: Preview button & HD Image Only checkbox (matching Screenshot 2) -->
            <div style="display: flex; align-items: center; gap: 16px;">
                <button type="button" onclick="modalPreviewCroppedImage()" style="background: #0284c7; color: white; font-weight: 700; font-size: 0.88rem; padding: 7px 22px; border: none; border-radius: 4px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 1px 3px rgba(2,132,199,0.3);">
                    Preview
                </button>
                <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 700; color: #1e293b; cursor: pointer;">
                    <input type="checkbox" id="modalHdOnlyCheckbox" onchange="modalToggleHdOnly()" style="width: 15px; height: 15px; accent-color: #0284c7;">
                    HD Image Only
                </label>
            </div>

            <!-- INLINE PREVIEW OUTPUT SECTION (Matching Screenshot 3) -->
            <div id="modalInlinePreviewSection" style="display: none; border-top: 2px dashed #cbd5e1; padding-top: 14px; margin-top: 2px; animation: fadeIn 0.2s ease;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; flex-wrap: wrap; gap: 6px;">
                    <div style="font-weight: 800; font-size: 0.92rem; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                        <span>👁️</span> Cropped Preview Output (Zoom &amp; Ratio Applied)
                    </div>
                    <div id="modalCropPreviewMetaBadges" style="display: flex; align-items: center; gap: 6px; font-size: 0.72rem; font-weight: 700; flex-wrap: wrap;"></div>
                </div>
                
                <div style="background: #0f172a; border-radius: 8px; padding: 12px; text-align: center; overflow: hidden; margin-bottom: 12px; box-shadow: inset 0 0 10px rgba(0,0,0,0.5);">
                    <img id="modalInlinePreviewImg" src="" alt="Cropped Preview" style="max-width: 100%; max-height: 440px; object-fit: contain; margin: 0 auto; display: block; border-radius: 4px; border: 1px solid #334155; box-shadow: 0 4px 12px rgba(0,0,0,0.35);">
                </div>
            </div>

            <!-- Resolutions, Format & Folder Settings -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 16px; display: flex; flex-direction: column; gap: 10px;">
                
                <!-- Folder, Name, Format -->
                <div style="display: grid; grid-template-columns: 1.2fr 2fr 1fr; gap: 12px; align-items: center;">
                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 3px;">Destination Folder</label>
                        <select id="modalFolderSelect" style="width: 100%; padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.82rem; color: #0f172a; outline: none; background: white;">
                            <option value="articles">Latest Articles</option>
                            <option value="news">Latest News</option>
                            <option value="series">Series</option>
                            <option value="match_preview">Match Preview</option>
                            <option value="prediction">Prediction &amp; Fantasy</option>
                            <option value="players">Players</option>
                            <option value="venues">Venues</option>
                            <option value="glossary">Glossary</option>
                        </select>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 3px;">Image File Name (Slug)</label>
                        <input type="text" id="modalImageNameInput" placeholder="cricket-photo (auto-slugified)" style="width: 100%; padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.82rem; outline: none; box-sizing: border-box; background: white;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 3px;">Format</label>
                        <div style="display: flex; align-items: center; gap: 10px; height: 30px;">
                            <label style="display: inline-flex; align-items: center; gap: 4px; font-size: 0.8rem; font-weight: 700; color: #0284c7; cursor: pointer;">
                                <input type="radio" name="modalTargetFormat" value="webp" checked> WEBP
                            </label>
                            <label style="display: inline-flex; align-items: center; gap: 4px; font-size: 0.8rem; font-weight: 700; color: #7c3aed; cursor: pointer;">
                                <input type="radio" name="modalTargetFormat" value="avif"> AVIF
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Sizes & Resolutions -->
                <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap; border-top: 1px solid #e2e8f0; padding-top: 8px;">
                    <span style="font-size: 0.75rem; font-weight: 800; color: #475569;">Target Sizes:</span>
                    <label style="display: inline-flex; align-items: center; gap: 4px; font-size: 0.76rem; font-weight: 600; color: #334155; cursor: pointer;">
                        <input type="checkbox" id="modalResTiny" style="accent-color: #0284c7;"> Tiny (160x90)
                    </label>
                    <label style="display: inline-flex; align-items: center; gap: 4px; font-size: 0.76rem; font-weight: 600; color: #334155; cursor: pointer;">
                        <input type="checkbox" id="modalResSmall" style="accent-color: #0284c7;"> Small (240x135)
                    </label>
                    <label style="display: inline-flex; align-items: center; gap: 4px; font-size: 0.76rem; font-weight: 600; color: #334155; cursor: pointer;">
                        <input type="checkbox" id="modalResMedium" style="accent-color: #0284c7;"> Medium (320x180)
                    </label>
                    <label style="display: inline-flex; align-items: center; gap: 4px; font-size: 0.8rem; font-weight: 800; color: #0284c7; cursor: pointer;">
                        <input type="checkbox" id="modalResStandard" checked style="accent-color: #0284c7;"> Standard (640x360)
                    </label>
                    <label style="display: inline-flex; align-items: center; gap: 4px; font-size: 0.76rem; font-weight: 600; color: #334155; cursor: pointer;">
                        <input type="checkbox" id="modalResLarge" style="accent-color: #0284c7;"> Large (800x450)
                    </label>
                    <label style="display: inline-flex; align-items: center; gap: 4px; font-size: 0.76rem; font-weight: 600; color: #334155; cursor: pointer;">
                        <input type="checkbox" id="modalResHD" style="accent-color: #0284c7;"> HD (1280x720)
                    </label>
                </div>
            </div>

            <!-- Direct URL Option Alternative -->
            <details style="background: #fafafa; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 14px;">
                <summary style="font-size: 0.8rem; font-weight: 700; color: #475569; cursor: pointer;">🔗 Or Insert Direct Image URL without upload</summary>
                <div style="display: flex; gap: 8px; margin-top: 8px;">
                    <input type="text" id="modalDirectUrlInput" placeholder="https://example.com/image.jpg" style="flex: 1; padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.82rem; outline: none;">
                    <button type="button" onclick="modalInsertDirectUrl()" style="padding: 6px 14px; background: #0f172a; color: white; border: none; border-radius: 4px; font-weight: 700; font-size: 0.8rem; cursor: pointer;">Insert URL</button>
                </div>
            </details>

        </div>

        <!-- Modal Footer Actions -->
        <div style="padding: 14px 22px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; flex-shrink: 0;">
            <button type="button" onclick="closeGlobalImageUploader()" style="padding: 8px 18px; border: 1px solid #cbd5e1; border-radius: 4px; background: white; font-weight: 700; font-size: 0.85rem; color: #475569; cursor: pointer;">
                Cancel
            </button>

            <div style="display: flex; align-items: center; gap: 10px;">
                <button type="button" id="btnModalUpload" onclick="modalUploadToServer()" style="background: #b91c1c; color: white; font-weight: 800; font-size: 0.85rem; padding: 8px 22px; border-radius: 4px; border: none; cursor: pointer; letter-spacing: 0.03em; box-shadow: 0 2px 4px rgba(185,28,28,0.25);">
                    🚀 UPLOAD TO SERVER
                </button>

                <button type="button" id="btnModalInsert" onclick="modalInsertCroppedImage()" style="background: #0284c7; color: white; font-weight: 800; font-size: 0.85rem; padding: 8px 24px; border-radius: 4px; border: none; cursor: pointer; box-shadow: 0 2px 4px rgba(2,132,199,0.3); display: inline-flex; align-items: center; gap: 6px;">
                    📥 INSERT INTO DESCRIPTION
                </button>
            </div>
        </div>

    </div>
</div>



<style>
@keyframes modalFadeIn {
    from { opacity: 0; transform: scale(0.96); }
    to { opacity: 1; transform: scale(1); }
}
.modal-aspect-btn.active {
    border-color: #10b981 !important;
    background: #bbf7d0 !important;
    color: #065f46 !important;
}
</style>

<script>
let currentTargetId = null;
let currentTargetTextareaId = null;
let modalTargetMode = 'content'; // 'content' (textarea) or 'poster' (input & preview)
let currentPreviewImgId = null;
let currentStatusBadgeId = null;
let modalActiveImage = null;
let modalTargetAspectRatio = 16 / 9;
let modalImageScale = 1.0;
let modalBaseScale = 1.0;
let modalImageX = 0;
let modalImageY = 0;
let modalIsDragging = false;
let modalDragStartX = 0;
let modalDragStartY = 0;
let modalLastUploadedUrl = null;
let isUploadingToServer = false;

function modalInvalidateUpload() {
    modalLastUploadedUrl = null;
    const popup = document.getElementById('modalTopSuccessPopup');
    if (popup) popup.style.display = 'none';
}

function openGlobalImageUploader(targetId, defaultFolder = 'articles', mode = 'content', previewImgId = null, badgeId = null) {
    currentTargetId = targetId;
    currentTargetTextareaId = targetId;
    modalTargetMode = mode || 'content';
    currentPreviewImgId = previewImgId;
    currentStatusBadgeId = badgeId;
    modalInvalidateUpload();

    const modal = document.getElementById('globalImageUploaderModal');
    if (!modal) return;
    
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    // Update header & buttons based on mode
    const headerTitle = document.getElementById('modalHeaderTitle');
    const actionSubtitle = document.getElementById('modalActionSubtitle');
    const targetLabel = document.getElementById('modalTargetTextareaLabel');
    const btnInsert = document.getElementById('btnModalInsert');
    const btnTopInsert = document.getElementById('btnModalTopInsert');

    if (modalTargetMode === 'poster') {
        if (headerTitle) headerTitle.innerText = 'Standard Image Uploader (Poster / Profile Photo)';
        if (actionSubtitle) actionSubtitle.innerText = 'Crop, Zoom, Resize & Set Poster for:';
        if (targetLabel) targetLabel.innerText = '#' + targetId;
        if (btnInsert) btnInsert.innerHTML = '📌 SET AS POSTER IMAGE';
        if (btnTopInsert) btnTopInsert.innerHTML = '📌 Set as Poster';
    } else {
        if (headerTitle) headerTitle.innerText = 'Standard Image Uploader & Media Gallery';
        if (actionSubtitle) actionSubtitle.innerText = 'Crop, Zoom, Resize & Insert directly into:';
        if (targetLabel) targetLabel.innerText = '#' + targetId;
        if (btnInsert) btnInsert.innerHTML = '📥 INSERT INTO DESCRIPTION';
        if (btnTopInsert) btnTopInsert.innerHTML = '📥 Insert';
    }

    // Set default folder
    const folderSelect = document.getElementById('modalFolderSelect');
    if (folderSelect && defaultFolder) {
        folderSelect.value = defaultFolder;
    }

    // Determine initial aspect ratio based on folder or target
    let targetRatio = 16 / 9;
    let ratioBtn = document.querySelector('.modal-aspect-btn[data-ratio="16/9"]');
    if (defaultFolder === 'players' || (targetId && (targetId.includes('player') || targetId.includes('logo') || targetId.includes('profile')))) {
        targetRatio = 1 / 1;
        ratioBtn = document.querySelector('.modal-aspect-btn[data-ratio="1/1"]');
    }

    modalSetAspectRatio(targetRatio, ratioBtn);

    setTimeout(() => {
        modalInitCanvas();
        modalUpdateCropFrame();
        modalRenderCanvas();
    }, 60);
    setTimeout(() => {
        modalInitCanvas();
        modalUpdateCropFrame();
        if (modalActiveImage) modalFitToFrame();
        modalRenderCanvas();
    }, 280);
}

function openGlobalPosterUploader(targetInputId, previewImgId = null, defaultFolder = 'articles', badgeId = null) {
    openGlobalImageUploader(targetInputId, defaultFolder, 'poster', previewImgId, badgeId);
}

window.openGlobalImageUploader = openGlobalImageUploader;
window.openGlobalPosterUploader = openGlobalPosterUploader;

function closeGlobalImageUploader() {
    const modal = document.getElementById('globalImageUploaderModal');
    if (modal) modal.style.display = 'none';
    document.body.style.overflow = '';
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('globalImageUploaderModal');
        if (modal && modal.style.display === 'flex') {
            closeGlobalImageUploader();
        }
    }
});

window.addEventListener('resize', function() {
    const modal = document.getElementById('globalImageUploaderModal');
    if (modal && modal.style.display === 'flex') {
        modalInitCanvas();
        modalUpdateCropFrame();
        if (modalActiveImage) modalFitToFrame();
        modalRenderCanvas();
    }
});

function modalInitCanvas() {
    const viewport = document.getElementById('modalCanvasViewport');
    const canvas = document.getElementById('modalEditorCanvas');
    if (!viewport || !canvas) return;

    canvas.width = viewport.clientWidth || 1150;
    canvas.height = viewport.clientHeight || 520;

    // Attach drag & wheel events once
    if (!canvas.dataset.eventsAttached) {
        viewport.addEventListener('mousedown', modalStartDrag);
        window.addEventListener('mousemove', modalOnDrag);
        window.addEventListener('mouseup', modalEndDrag);
        viewport.addEventListener('wheel', modalOnWheel, { passive: false });
        canvas.dataset.eventsAttached = 'true';
    }
}

function modalSetAspectRatio(ratio, btn) {
    modalTargetAspectRatio = ratio;
    modalInvalidateUpload();
    document.querySelectorAll('.modal-aspect-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');

    const label = document.getElementById('modalAspectLabel');
    if (label) {
        if (ratio === 16/9) label.innerText = '16:9 Target Frame';
        else if (ratio === 4/3) label.innerText = '4:3 Target Frame';
        else if (ratio === 1/1) label.innerText = '1:1 Square Frame';
        else if (ratio === 9/16) label.innerText = '9:16 Vertical Frame';
    }

    modalUpdateCropFrame();
    modalFitToFrame();
    modalRenderCanvas();
}

function modalUpdateCropFrame() {
    const viewport = document.getElementById('modalCanvasViewport');
    const box = document.getElementById('modalCropBoundaryBox');
    if (!viewport || !box) return;

    const vpW = viewport.clientWidth || 1150;
    const vpH = viewport.clientHeight || 520;
    const padding = 24;
    const maxW = Math.max(400, vpW - padding);
    const maxH = Math.max(300, vpH - padding);

    let targetW = maxW;
    let targetH = targetW / modalTargetAspectRatio;

    if (targetH > maxH) {
        targetH = maxH;
        targetW = targetH * modalTargetAspectRatio;
    }

    box.style.width = Math.round(targetW) + 'px';
    box.style.height = Math.round(targetH) + 'px';
}

function modalHandleFileSelect(e) {
    const file = e.target.files[0];
    if (!file) return;

    modalInvalidateUpload();

    const nameLabel = document.getElementById('modalFileNameLabel');
    if (nameLabel) nameLabel.innerText = file.name;

    const nameInput = document.getElementById('modalImageNameInput');
    if (nameInput) {
        nameInput.value = file.name.replace(/\.[^/.]+$/, '').toLowerCase().replace(/[^a-z0-9_-]/g, '-');
    }

    const reader = new FileReader();
    reader.onload = function(evt) {
        const img = new Image();
        img.onload = function() {
            modalActiveImage = img;
            const notice = document.getElementById('modalEmptyNotice');
            if (notice) notice.style.display = 'none';

            modalFitToFrame();
            modalRenderCanvas();
        };
        img.src = evt.target.result;
    };
    reader.readAsDataURL(file);
}

function modalConstrainImagePosition() {
    if (!modalActiveImage) return;
    const canvas = document.getElementById('modalEditorCanvas');
    const box = document.getElementById('modalCropBoundaryBox');
    if (!canvas || !box) return;

    const curScale = modalBaseScale * modalImageScale;
    const drawW = modalActiveImage.width * curScale;
    const drawH = modalActiveImage.height * curScale;

    const boxW = box.offsetWidth;
    const boxH = box.offsetHeight;

    const boxLeft = (canvas.width - boxW) / 2;
    const boxRight = boxLeft + boxW;
    const boxTop = (canvas.height - boxH) / 2;
    const boxBottom = boxTop + boxH;

    // Horizontal constraint: do not let black gaps show if drawW >= boxW
    if (drawW >= boxW) {
        const minX = boxRight - drawW / 2;
        const maxX = boxLeft + drawW / 2;
        modalImageX = Math.max(minX, Math.min(maxX, modalImageX));
    } else {
        modalImageX = canvas.width / 2;
    }

    // Vertical constraint: do not let black gaps show if drawH >= boxH
    if (drawH >= boxH) {
        const minY = boxBottom - drawH / 2;
        const maxY = boxTop + drawH / 2;
        modalImageY = Math.max(minY, Math.min(maxY, modalImageY));
    } else {
        modalImageY = canvas.height / 2;
    }
}

function modalFitToFrame() {
    if (!modalActiveImage) return;
    const box = document.getElementById('modalCropBoundaryBox');
    if (!box) return;

    const boxW = box.offsetWidth;
    const boxH = box.offsetHeight;

    // Use Math.max (cover mode) so the image always completely covers the crop box in both width and height,
    // leaving NO black/empty gaps on the sides or top/bottom regardless of image size or aspect ratio.
    const scaleX = boxW / modalActiveImage.width;
    const scaleY = boxH / modalActiveImage.height;
    modalBaseScale = Math.max(scaleX, scaleY);
    modalImageScale = 1.0;

    const canvas = document.getElementById('modalEditorCanvas');
    if (canvas) {
        modalImageX = canvas.width / 2;
        modalImageY = canvas.height / 2;
    }

    modalConstrainImagePosition();
    modalUpdateZoomDisplay();
    modalRenderCanvas();
}

function modalAdjustZoom(delta) {
    if (!modalActiveImage) return;
    modalImageScale = Math.max(1.0, Math.min(5.0, modalImageScale + delta));
    modalConstrainImagePosition();
    modalUpdateZoomDisplay();
    modalRenderCanvas();
}

function modalUpdateZoomDisplay() {
    const disp = document.getElementById('modalZoomDisplay');
    if (disp) {
        disp.innerText = Math.round(modalImageScale * 100) + '%';
    }
}

function modalOnWheel(e) {
    e.preventDefault();
    if (!modalActiveImage) return;
    const delta = e.deltaY < 0 ? 0.08 : -0.08;
    modalAdjustZoom(delta);
}

function modalStartDrag(e) {
    if (!modalActiveImage) return;
    modalIsDragging = true;
    modalDragStartX = e.clientX - modalImageX;
    modalDragStartY = e.clientY - modalImageY;
    const vp = document.getElementById('modalCanvasViewport');
    if (vp) vp.style.cursor = 'grabbing';
}

function modalOnDrag(e) {
    if (!modalIsDragging || !modalActiveImage) return;
    modalImageX = e.clientX - modalDragStartX;
    modalImageY = e.clientY - modalDragStartY;
    modalConstrainImagePosition();
    modalRenderCanvas();
}

function modalEndDrag() {
    modalIsDragging = false;
    const vp = document.getElementById('modalCanvasViewport');
    if (vp) vp.style.cursor = 'grab';
}

function modalRenderCanvas() {
    const canvas = document.getElementById('modalEditorCanvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    if (!modalActiveImage) return;

    const curScale = modalBaseScale * modalImageScale;
    const drawW = modalActiveImage.width * curScale;
    const drawH = modalActiveImage.height * curScale;

    ctx.save();
    ctx.drawImage(
        modalActiveImage,
        modalImageX - drawW / 2,
        modalImageY - drawH / 2,
        drawW,
        drawH
    );

    // Overlay text if specified
    const overlayText = document.getElementById('modalOverlayText')?.value;
    if (overlayText && overlayText.trim() !== '') {
        const box = document.getElementById('modalCropBoundaryBox');
        const boxH = box ? box.offsetHeight : 150;
        const fontSize = Math.max(16, Math.round(boxH * 0.12));

        ctx.font = `bold ${fontSize}px sans-serif`;
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';

        const textColor = document.getElementById('modalTextColor')?.value || '#ffffff';
        const strokeWidth = parseInt(document.getElementById('modalStrokeWidth')?.value || '0', 10);
        const strokeColor = document.getElementById('modalStrokeColor')?.value || '#000000';

        const textX = modalImageX;
        const textY = modalImageY + (drawH * 0.35);

        if (strokeWidth > 0) {
            ctx.lineWidth = strokeWidth * 2;
            ctx.strokeStyle = strokeColor;
            ctx.strokeText(overlayText, textX, textY);
        }

        ctx.fillStyle = textColor;
        ctx.fillText(overlayText, textX, textY);
    }

    ctx.restore();
}

function modalGetTargetDimensions() {
    let outW = 640;
    if (document.getElementById('modalResHD')?.checked) outW = 1280;
    else if (document.getElementById('modalResLarge')?.checked) outW = 800;
    else if (document.getElementById('modalResMedium')?.checked) outW = 320;
    else if (document.getElementById('modalResSmall')?.checked) outW = 240;
    else if (document.getElementById('modalResTiny')?.checked) outW = 160;

    const outH = Math.round(outW / modalTargetAspectRatio);
    return { width: outW, height: outH };
}

function modalGetCroppedDataUrl() {
    if (!modalActiveImage) return null;
    const canvas = document.getElementById('modalEditorCanvas');
    const box = document.getElementById('modalCropBoundaryBox');
    if (!canvas || !box) return null;

    const vpRect = canvas.getBoundingClientRect();
    const boxRect = box.getBoundingClientRect();

    const cropX = boxRect.left - vpRect.left;
    const cropY = boxRect.top - vpRect.top;
    const cropW = boxRect.width;
    const cropH = boxRect.height;

    const dims = modalGetTargetDimensions();
    const outCanvas = document.createElement('canvas');
    outCanvas.width = dims.width;
    outCanvas.height = dims.height;

    const outCtx = outCanvas.getContext('2d');
    outCtx.drawImage(canvas, cropX, cropY, cropW, cropH, 0, 0, dims.width, dims.height);

    let format = document.querySelector('input[name="modalTargetFormat"]:checked')?.value || 'webp';
    let dataUrl = outCanvas.toDataURL(`image/${format}`, 0.92);
    // If avif is unsupported by browser canvas (browser falls back to png), use high-efficiency webp instead
    if (format === 'avif' && dataUrl.startsWith('data:image/png')) {
        const webpUrl = outCanvas.toDataURL('image/webp', 0.92);
        if (webpUrl.startsWith('data:image/webp')) {
            dataUrl = webpUrl;
        }
    }
    return dataUrl;
}

function modalToggleHdOnly() {
    const isHd = document.getElementById('modalHdOnlyCheckbox')?.checked;
    const hdRadio = document.getElementById('modalResHD');
    if (hdRadio) hdRadio.checked = isHd;
    if (modalActiveImage) {
        modalPreviewCroppedImage();
    }
}

function modalPreviewCroppedImage() {
    if (!modalActiveImage) {
        alert('Please choose an image file first.');
        return;
    }
    const dataUrl = modalGetCroppedDataUrl();
    if (!dataUrl) return;

    const dims = modalGetTargetDimensions();
    const format = document.querySelector('input[name="modalTargetFormat"]:checked')?.value || 'webp';
    const zoomPercent = Math.round(modalImageScale * 100);

    let aspectText = '16:9 Target';
    if (Math.abs(modalTargetAspectRatio - (4/3)) < 0.05) aspectText = '4:3 Target';
    else if (Math.abs(modalTargetAspectRatio - 1) < 0.05) aspectText = '1:1 Square';
    else if (Math.abs(modalTargetAspectRatio - (9/16)) < 0.05) aspectText = '9:16 Vertical';

    const badges = document.getElementById('modalCropPreviewMetaBadges');
    if (badges) {
        badges.innerHTML = `
            <span style="background:#e0f2fe; color:#0369a1; padding:3px 8px; border-radius:4px; font-weight:800;">Ratio: ${aspectText}</span>
            <span style="background:#f1f5f9; color:#334155; padding:3px 8px; border-radius:4px; font-weight:800;">Size: ${dims.width} × ${dims.height} px</span>
            <span style="background:#fef3c7; color:#92400e; padding:3px 8px; border-radius:4px; font-weight:800;">Format: ${format.toUpperCase()}</span>
            <span style="background:#dcfce7; color:#166534; padding:3px 8px; border-radius:4px; font-weight:800;">Zoom: ${zoomPercent}%</span>
        `;
    }

    // Set inline preview image directly on the page/modal
    const inlineImg = document.getElementById('modalInlinePreviewImg');
    if (inlineImg) inlineImg.src = dataUrl;

    const inlineSec = document.getElementById('modalInlinePreviewSection');
    if (inlineSec) {
        inlineSec.style.display = 'block';
        inlineSec.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

function closeModalCropPreview() {
    const inlineSec = document.getElementById('modalInlinePreviewSection');
    if (inlineSec) inlineSec.style.display = 'none';
}

function showModalTopSuccessPopup(url, sizeKb = null, dataUrl = null) {
    const popup = document.getElementById('modalTopSuccessPopup');
    if (!popup) return;

    const urlText = document.getElementById('modalUploadedUrlText');
    if (urlText) urlText.innerText = url;

    const sizeBadge = document.getElementById('modalTopUploadSizeBadge');
    if (sizeBadge && sizeKb) {
        sizeBadge.innerText = `${sizeKb} KB`;
        sizeBadge.style.display = 'inline-block';
    }

    const thumb = document.getElementById('modalTopPopupThumbImg');
    if (thumb) {
        thumb.src = dataUrl || url;
    }

    const copyBtn = document.getElementById('btnModalTopCopyUrl');
    if (copyBtn) {
        copyBtn.innerText = '📋 Copy Image URL';
        copyBtn.style.background = '#10b981';
    }

    popup.style.display = 'block';
}

function closeModalTopSuccessPopup() {
    const popup = document.getElementById('modalTopSuccessPopup');
    if (popup) popup.style.display = 'none';
}

function modalCopyImageUrlToClipboard() {
    let copyText = modalLastUploadedUrl;
    if (!copyText) {
        alert('Please click UPLOAD TO SERVER first to generate the saved image URL.');
        return;
    }

    navigator.clipboard.writeText(copyText).then(() => {
        const btn = document.getElementById('btnModalTopCopyUrl');
        if (btn) {
            btn.innerText = '✓ URL Copied!';
            btn.style.background = '#059669';
            setTimeout(() => {
                btn.innerText = '📋 Copy Image URL';
                btn.style.background = '#10b981';
            }, 2500);
        }
        showModalTopSuccessPopup(copyText);
        if (typeof showAdminToast === 'function') {
            showAdminToast('Image URL copied to clipboard!');
        }
    }).catch(() => {
        prompt('Copy Image URL:', copyText);
    });
}

function modalPerformUpload() {
    return new Promise((resolve, reject) => {
        const dataUrl = modalGetCroppedDataUrl();
        if (!dataUrl) {
            return reject(new Error('Please choose an image file first.'));
        }

        const folder = document.getElementById('modalFolderSelect')?.value || 'articles';
        let imageName = document.getElementById('modalImageNameInput')?.value?.trim();
        if (!imageName) {
            imageName = folder + '_' + Date.now();
        }
        let format = document.querySelector('input[name="modalTargetFormat"]:checked')?.value || 'webp';
        if (dataUrl.startsWith('data:image/webp')) format = 'webp';
        else if (dataUrl.startsWith('data:image/jpeg') || dataUrl.startsWith('data:image/jpg')) format = 'jpg';
        else if (dataUrl.startsWith('data:image/png')) format = 'png';
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        fetch('{{ route('admin.image-uploader.upload') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                image_data: dataUrl,
                folder: folder,
                image_name: imageName,
                format: format
            })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success && res.url) {
                modalLastUploadedUrl = res.url;
                showModalTopSuccessPopup(res.url, res.size_kb, dataUrl);
                resolve(res.url);
            } else {
                reject(new Error(res.message || 'Upload failed on server.'));
            }
        })
        .catch(err => {
            reject(err);
        });
    });
}

function modalUploadToServer() {
    if (!modalActiveImage) {
        alert('Please choose an image file first.');
        return;
    }

    const btn = document.getElementById('btnModalUpload');
    if (btn) {
        btn.disabled = true;
        btn.innerText = '⏳ Uploading...';
    }

    modalPerformUpload()
        .then(url => {
            if (btn) {
                btn.disabled = false;
                btn.innerText = '🚀 UPLOAD TO SERVER';
            }
            if (typeof showAdminToast === 'function') {
                showAdminToast('Image processed & saved to server successfully!');
            }
        })
        .catch(err => {
            if (btn) {
                btn.disabled = false;
                btn.innerText = '🚀 UPLOAD TO SERVER';
            }
            alert('Upload error: ' + (err.message || err));
        });
}

async function modalInsertCroppedImage() {
    if (isUploadingToServer) return;

    let url = modalLastUploadedUrl;

    if (!url) {
        if (!modalActiveImage) {
            const directUrl = document.getElementById('modalDirectUrlInput')?.value?.trim();
            if (directUrl) {
                url = directUrl;
            } else {
                alert('Please choose an image file first.');
                return;
            }
        } else {
            // AUTOMATICALLY UPLOAD TO SERVER FIRST!
            isUploadingToServer = true;
            const btnInsert = document.getElementById('btnModalInsert');
            const btnTop = document.getElementById('btnModalTopInsert');
            const origInsertText = btnInsert ? btnInsert.innerHTML : '';
            const origTopText = btnTop ? btnTop.innerHTML : '';

            if (btnInsert) {
                btnInsert.disabled = true;
                btnInsert.innerHTML = '⏳ Uploading to Server...';
            }
            if (btnTop) {
                btnTop.disabled = true;
                btnTop.innerHTML = '⏳ Uploading...';
            }

            try {
                url = await modalPerformUpload();
            } catch (err) {
                alert('Upload failed: ' + (err.message || err));
                if (btnInsert) {
                    btnInsert.disabled = false;
                    btnInsert.innerHTML = origInsertText;
                }
                if (btnTop) {
                    btnTop.disabled = false;
                    btnTop.innerHTML = origTopText;
                }
                isUploadingToServer = false;
                return;
            } finally {
                isUploadingToServer = false;
                if (btnInsert) {
                    btnInsert.disabled = false;
                    btnInsert.innerHTML = origInsertText;
                }
                if (btnTop) {
                    btnTop.disabled = false;
                    btnTop.innerHTML = origTopText;
                }
            }
        }
    }

    if (!url) {
        alert('Could not obtain valid image URL.');
        return;
    }

    if (modalTargetMode === 'poster') {
        applyImageAsPoster(url);
    } else {
        const alt = document.getElementById('modalImageNameInput')?.value || 'Cricket Image';
        insertImageIntoTargetTextarea(url, alt);
    }

    closeGlobalImageUploader();
}

function applyImageAsPoster(url) {
    if (!currentTargetId) {
        alert('No target poster input specified.');
        return;
    }

    const input = document.getElementById(currentTargetId);
    if (input) {
        input.value = url;
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
    }

    const preview = currentPreviewImgId ? document.getElementById(currentPreviewImgId) : null;
    if (preview) {
        preview.src = url;
        preview.style.display = 'block';
    }

    const badge = currentStatusBadgeId ? document.getElementById(currentStatusBadgeId) : null;
    if (badge) {
        badge.innerText = '✓ Standard Image Ready';
        badge.style.display = 'inline-block';
        badge.style.background = '#dcfce7';
        badge.style.color = '#16a34a';
        badge.style.padding = '2px 8px';
        badge.style.borderRadius = '4px';
        badge.style.fontSize = '0.75rem';
        badge.style.fontWeight = '700';
    }

    if (typeof showAdminToast === 'function') {
        showAdminToast('Poster image uploaded and attached successfully!');
    }
}

function modalInsertDirectUrl() {
    const url = document.getElementById('modalDirectUrlInput')?.value?.trim();
    if (!url) {
        alert('Please enter a valid image URL.');
        return;
    }
    if (modalTargetMode === 'poster') {
        applyImageAsPoster(url);
    } else {
        insertImageIntoTargetTextarea(url, 'Cricket Image');
    }
    closeGlobalImageUploader();
}

function insertImageIntoTargetTextarea(imageUrl, altText = 'Cricket Image') {
    const targetId = currentTargetId || currentTargetTextareaId;
    if (!targetId) {
        alert('No target textarea selected.');
        return;
    }

    const textarea = document.getElementById(targetId);
    if (!textarea) return;

    const start = textarea.selectionStart || 0;
    const end = textarea.selectionEnd || 0;
    const before = textarea.value.substring(0, start);
    const after = textarea.value.substring(end);

    const imgTag = `\n<img src="${imageUrl}" alt="${altText}" style="max-width: 100%; height: auto; border-radius: 8px; margin: 14px 0; display: block;" />\n`;

    textarea.value = before + imgTag + after;
    textarea.focus();
    const newCursor = start + imgTag.length;
    textarea.setSelectionRange(newCursor, newCursor);
    textarea.dispatchEvent(new Event('input', { bubbles: true }));

    // Show toast
    if (typeof showAdminToast === 'function') {
        showAdminToast('Image inserted into description successfully!');
    }
}
</script>
