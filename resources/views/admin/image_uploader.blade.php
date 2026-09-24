@extends('layouts.admin')

@section('content')
<div style="max-width: 1260px; margin: 0 auto; display: flex; flex-direction: column; gap: 16px;">
    
    <!-- Top Action Toolbar matching Screenshot -->
    <div style="background: white; border: 1px solid #cbd5e1; border-radius: 6px; padding: 12px 18px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="display: flex; align-items: center; gap: 10px;">
            <a href="{{ route('admin.dashboard') }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border: 1px solid #cbd5e1; border-radius: 4px; background: #f8fafc; color: #1e293b; font-weight: 700; font-size: 0.85rem; text-decoration: none;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                Home
            </a>
            <button type="button" onclick="resetUploader()" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border: 1px solid #cbd5e1; border-radius: 4px; background: #f8fafc; color: #1e293b; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>
                RESET
            </button>
        </div>

        <div style="font-weight: 800; font-size: 1.15rem; color: #0f172a; letter-spacing: -0.01em;">
            Standard Image Uploader
        </div>

        <div style="font-size: 0.8rem; font-weight: 700; color: #0284c7; background: #e0f2fe; padding: 4px 10px; border-radius: 4px; border: 1px solid #bae6fd;">
            ⚡ WEBP &amp; AVIF Ultra-Fast Processing
        </div>
    </div>

    <!-- Direct Form Link Notification (Shown if opened from Article, News, etc.) -->
    <div id="targetFormBanner" style="display: none; background: #eff6ff; border: 1px solid #93c5fd; border-radius: 6px; padding: 12px 18px; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 1.3rem;">🎯</span>
            <div>
                <div style="font-weight: 800; font-size: 0.9rem; color: #1e40af;">
                    Connected to: <span id="targetFormLabel">Latest Article</span>
                </div>
                <div style="font-size: 0.78rem; color: #3b82f6;">
                    Jab aap <strong>UPLOAD</strong> karenge, ye image automatically aapke iss section ke form me attach ho jayegi!
                </div>
            </div>
        </div>
        <button type="button" onclick="applyDirectlyToParent()" style="background: #0284c7; color: white; font-weight: 700; font-size: 0.82rem; padding: 6px 14px; border: none; border-radius: 4px; cursor: pointer;">
            ⚡ Auto-Sync Active
        </button>
    </div>

    <!-- Main Uploader Card -->
    <div style="background: white; border: 1px solid #cbd5e1; border-radius: 6px; padding: 20px 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.04); display: flex; flex-direction: column; gap: 16px;">
        
        <!-- ROW 1: Select an Image & Aspect Ratio Buttons -->
        <div style="display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <div>
                <label style="display: block; font-weight: 800; font-size: 0.88rem; color: #0f172a; margin-bottom: 6px;">
                    Select an Image
                </label>
                <input type="file" id="sourceImageInput" accept=".webp, .avif, image/webp, image/avif, image/jpeg, image/png" onchange="handleFileSelect(event)" style="font-size: 0.85rem; color: #475569;">
            </div>

            <!-- Aspect Ratio Buttons matching Screenshot -->
            <div style="display: flex; align-items: center; gap: 8px;">
                <button type="button" class="aspect-btn active" onclick="setAspectRatio(16/9, this)" style="padding: 10px 18px; border: 1px solid #10b981; border-radius: 2px; background: #bbf7d0; color: #065f46; font-weight: 800; font-size: 0.9rem; cursor: pointer; min-width: 65px;">
                    16x9
                </button>
                <button type="button" class="aspect-btn" onclick="setAspectRatio(4/3, this)" style="padding: 10px 18px; border: 1px solid #cbd5e1; border-radius: 2px; background: white; color: #1e293b; font-weight: 800; font-size: 0.9rem; cursor: pointer; min-width: 65px;">
                    4x3
                </button>
                <button type="button" class="aspect-btn" onclick="setAspectRatio(1/1, this)" style="padding: 10px 18px; border: 1px solid #cbd5e1; border-radius: 2px; background: white; color: #1e293b; font-weight: 800; font-size: 0.9rem; cursor: pointer; min-width: 65px;">
                    Square
                </button>
                <button type="button" class="aspect-btn" onclick="setAspectRatio(9/16, this)" style="padding: 10px 18px; border: 1px solid #cbd5e1; border-radius: 2px; background: white; color: #1e293b; font-weight: 800; font-size: 0.9rem; cursor: pointer; min-width: 65px;">
                    9x16
                </button>
            </div>
        </div>

        <!-- ROW 2: Tool bar (Zoom -, +, Fit, Type Text, Stroke, Color Pickers) -->
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; background: #f8fafc; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 4px;">
            <button type="button" onclick="adjustZoom(-0.1)" title="Zoom Out" style="width: 32px; height: 32px; border: 1px solid #cbd5e1; border-radius: 3px; background: white; font-size: 1.1rem; font-weight: 800; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                -
            </button>
            <button type="button" onclick="adjustZoom(0.1)" title="Zoom In" style="width: 32px; height: 32px; border: 1px solid #cbd5e1; border-radius: 3px; background: white; font-size: 1.1rem; font-weight: 800; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                +
            </button>
            <button type="button" onclick="fitImageToFrame()" title="Fit to Frame" style="width: 32px; height: 32px; border: 1px solid #cbd5e1; border-radius: 3px; background: white; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 3 21 3 21 9"></polyline><polyline points="9 21 3 21 3 15"></polyline><line x1="21" y1="3" x2="14" y2="10"></line><line x1="3" y1="21" x2="10" y2="14"></line></svg>
            </button>

            <!-- Text Overlay input -->
            <input type="text" id="overlayTextInput" placeholder="Type Text" oninput="renderCanvas()" style="padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 3px; font-size: 0.85rem; width: 170px; outline: none; background: white;">

            <!-- Text Color picker box -->
            <div style="position: relative; display: flex; align-items: center;" title="Text Color">
                <input type="color" id="textColorPicker" value="#ffffff" onchange="renderCanvas()" style="width: 36px; height: 32px; border: 1px solid #cbd5e1; border-radius: 3px; padding: 2px; cursor: pointer; background: white;">
            </div>

            <!-- Stroke controls -->
            <span style="font-weight: 700; font-size: 0.85rem; color: #1e293b; margin-left: 6px;">Stroke</span>
            <input type="number" id="strokeWidthInput" value="0" min="0" max="15" oninput="renderCanvas()" style="width: 50px; padding: 5px 6px; border: 1px solid #cbd5e1; border-radius: 3px; font-size: 0.85rem; text-align: center; background: white;">
            
            <!-- Stroke Color picker box -->
            <div style="position: relative; display: flex; align-items: center;" title="Stroke Color">
                <input type="color" id="strokeColorPicker" value="#000000" onchange="renderCanvas()" style="width: 36px; height: 32px; border: 1px solid #cbd5e1; border-radius: 3px; padding: 2px; cursor: pointer; background: white;">
            </div>

            <span style="font-size: 0.75rem; color: #64748b; margin-left: auto; font-weight: 600;">
                💡 Drag image to position &bull; Mouse wheel to zoom
            </span>
        </div>

        <!-- Canvas Viewport with Red Boundary Crop Box (Crisp White Canvas Background) -->
        <div id="canvasViewport" style="position: relative; width: 100%; min-height: 520px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; overflow: hidden; display: flex; align-items: center; justify-content: center; cursor: grab; user-select: none; box-shadow: inset 0 0 12px rgba(0,0,0,0.03);">
            
            <!-- Red Bounding Box representing Crop Target Area -->
            <div id="cropBoundaryBox" style="position: relative; border: 2px solid #ef4444; box-shadow: 0 0 0 9999px rgba(241, 245, 249, 0.75); pointer-events: none; z-index: 10; display: flex; align-items: center; justify-content: center;">
                <div style="position: absolute; bottom: 6px; right: 8px; font-size: 0.72rem; font-weight: 800; color: #ffffff; background: #ef4444; padding: 3px 8px; border-radius: 3px; letter-spacing: 0.02em;">
                    <span id="aspectRatioLabel">16:9 Target Frame</span>
                </div>
            </div>

            <!-- The Drawing Canvas -->
            <canvas id="editorCanvas" style="position: absolute; z-index: 5;"></canvas>

            <!-- Placeholder message if no image selected -->
            <div id="emptyCanvasNotice" style="position: absolute; z-index: 15; text-align: center; color: #64748b;">
                <div style="font-size: 3rem; margin-bottom: 8px; opacity: 0.85;">🖼️</div>
                <div style="font-weight: 800; font-size: 1.05rem; color: #0f172a;">No image loaded</div>
                <div style="font-size: 0.84rem; color: #64748b; margin-top: 4px;">Click "Select an Image" above to start</div>
            </div>
        </div>

        <!-- ROW 3: Preview button & HD Image Only checkbox -->
        <div style="display: flex; align-items: center; gap: 16px;">
            <button type="button" onclick="previewRenderedModal()" style="background: #0284c7; color: white; font-weight: 700; font-size: 0.88rem; padding: 7px 22px; border: none; border-radius: 3px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 1px 3px rgba(2,132,199,0.3);">
                Preview
            </button>
            <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 700; color: #1e293b; cursor: pointer;">
                <input type="checkbox" id="hdOnlyCheckbox" onchange="renderCanvas()" style="width: 15px; height: 15px; accent-color: #0284c7;">
                HD Image Only
            </label>
        </div>

        <!-- INLINE CROPPED PREVIEW SECTION (Matching Screenshot 3) -->
        <div id="inlinePreviewSection" style="display: none; border-top: 2px dashed #cbd5e1; padding-top: 16px; margin-top: 4px; animation: fadeIn 0.2s ease;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; flex-wrap: wrap; gap: 8px;">
                <div style="font-weight: 800; font-size: 0.95rem; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                    <span>👁️</span> Cropped Preview Output (Zoom &amp; Ratio Applied)
                </div>
                <div id="inlinePreviewMetaBadges" style="display: flex; align-items: center; gap: 6px; font-size: 0.74rem; font-weight: 700; flex-wrap: wrap;"></div>
            </div>
            
            <div style="background: #0f172a; border-radius: 8px; padding: 14px; text-align: center; overflow: hidden; margin-bottom: 14px; box-shadow: inset 0 0 12px rgba(0,0,0,0.5);">
                <img id="inlinePreviewImg" src="" alt="Cropped Preview" style="max-width: 100%; max-height: 520px; object-fit: contain; margin: 0 auto; display: block; border-radius: 4px; border: 1px solid #334155; box-shadow: 0 4px 14px rgba(0,0,0,0.35);">
            </div>
        </div>

        <!-- ROW 4: Image Folder, Image Name, Resolutions (Tiny, Small, Medium, Standard, Large, HD, Original) -->
        <div style="border-top: 1px solid #e2e8f0; padding-top: 16px; display: flex; flex-direction: column; gap: 14px;">
            
            <div style="display: flex; align-items: center; gap: 24px; flex-wrap: wrap;">
                <!-- Image Folder dropdown with all sidebar sections requested -->
                <div>
                    <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #475569; margin-bottom: 4px;">
                        Image Folder
                    </label>
                    <select id="imageFolderSelect" style="padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.85rem; color: #0f172a; outline: none; background: white; min-width: 180px;">
                        <option value="articles" selected>Latest Articles</option>
                        <option value="news">Latest News</option>
                        <option value="series">Series</option>
                        <option value="match_preview">Match Preview</option>
                        <option value="prediction">Prediction &amp; Fantasy Tips</option>
                        <option value="teams">Most Popular Teams</option>
                        <option value="web_stories">Web Stories</option>
                        <option value="glossary">Glossary Terms</option>
                        <option value="players">Players</option>
                        <option value="venues">Venues</option>
                    </select>
                </div>

                <!-- Image Name input -->
                <div style="flex: 1; min-width: 260px;">
                    <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #475569; margin-bottom: 4px;">
                        Image Name
                    </label>
                    <input type="text" id="imageNameInput" value="" placeholder="e.g. cricket-match-photo (auto-filled on select)" style="width: 100%; padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.85rem; outline: none; box-sizing: border-box;">
                </div>

                <!-- Format Selection (WebP / AVIF) as explicitly requested by user -->
                <div>
                    <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #475569; margin-bottom: 4px;">
                        Target Format
                    </label>
                    <div style="display: flex; align-items: center; gap: 12px; height: 32px;">
                        <label style="display: inline-flex; align-items: center; gap: 5px; font-size: 0.85rem; font-weight: 700; color: #0284c7; cursor: pointer;">
                            <input type="radio" name="targetFormat" value="webp" checked>
                            WEBP
                        </label>
                        <label style="display: inline-flex; align-items: center; gap: 5px; font-size: 0.85rem; font-weight: 700; color: #7c3aed; cursor: pointer;">
                            <input type="radio" name="targetFormat" value="avif">
                            AVIF
                        </label>
                    </div>
                </div>
            </div>

            <!-- Resolution Presets using Loop Engineering (Dynamic with Aspect Ratio) -->
            <div>
                <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #475569; margin-bottom: 6px;">
                    Resolution Preset (Click to choose output dimension):
                </label>
                <div id="resolutionPresetsContainer" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; background: #fafafa; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 4px;">
                    <!-- Dynamically populated via Loop Engineering -->
                </div>
            </div>

            <!-- ROW 5: Action Buttons (Download, UPLOAD, COPY IMG) matching screenshot -->
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin-top: 4px;">
                <button type="button" onclick="downloadImageFile()" style="background: #0284c7; color: white; font-weight: 800; font-size: 0.82rem; padding: 8px 18px; border-radius: 3px; border: none; cursor: pointer; box-shadow: 0 1px 3px rgba(2,132,199,0.3);">
                    Download WEBP / JPG
                </button>
                <button type="button" onclick="uploadToServer()" id="btnUploadAction" style="background: #b91c1c; color: white; font-weight: 900; font-size: 0.85rem; padding: 8px 24px; border-radius: 3px; border: none; cursor: pointer; letter-spacing: 0.04em; box-shadow: 0 1px 3px rgba(185,28,28,0.3);">
                    UPLOAD
                </button>
                <button type="button" onclick="copyImageUrlToClipboard()" style="background: #ea580c; color: white; font-weight: 800; font-size: 0.82rem; padding: 8px 18px; border-radius: 3px; border: none; cursor: pointer; box-shadow: 0 1px 3px rgba(234,88,12,0.3);">
                    COPY IMG
                </button>
                
                <span id="uploadStatusBadge" style="display: none; font-size: 0.82rem; font-weight: 700; padding: 4px 10px; border-radius: 4px;"></span>
            </div>

        </div>

    </div>

</div>

<!-- Top Floating Success Popup Modal (Replaces bottom card as requested by user) -->
<div id="topUploadSuccessPopup" style="display: none; position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 9999999; max-width: 760px; width: calc(100% - 32px); background: #ffffff; border: 2px solid #10b981; border-radius: 12px; box-shadow: 0 20px 45px -5px rgba(0,0,0,0.35); padding: 16px 20px; animation: slideDownPopup 0.28s cubic-bezier(0.16, 1, 0.3, 1);">
    
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 12px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <span style="font-size: 1.3rem;">🎉</span>
            <strong style="font-size: 0.96rem; color: #065f46; font-weight: 800;">Image Successfully Processed &amp; Saved!</strong>
            <span id="topUploadSizeBadge" style="background: #dcfce7; color: #166534; font-size: 0.72rem; padding: 2px 8px; border-radius: 4px; font-weight: 700;"></span>
            <span id="autoAttachedNotice" style="display: none; background: #bbf7d0; color: #15803d; font-size: 0.72rem; padding: 2px 8px; border-radius: 4px; font-weight: 700;">✓ Attached to Form</span>
        </div>
        <button type="button" onclick="closeTopSuccessPopup()" style="background: transparent; border: none; font-size: 1.5rem; color: #64748b; cursor: pointer; line-height: 1; padding: 0 4px;" title="Close Popup">&times;</button>
    </div>

    <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
        <!-- Particular Cropped Image Preview Thumbnail -->
        <img id="topPopupThumbImg" src="" alt="Thumbnail" style="width: 76px; height: 48px; object-fit: cover; border-radius: 6px; border: 1px solid #cbd5e1; background: #0f172a; flex-shrink: 0; box-shadow: 0 2px 6px rgba(0,0,0,0.12); cursor: pointer;" onclick="previewRenderedModal()" title="Click to view full preview">
        
        <div style="flex: 1; min-width: 220px;">
            <div style="font-size: 0.74rem; font-weight: 700; color: #475569; margin-bottom: 4px;">Image Direct URL:</div>
            <div id="uploadedUrlText" style="font-size: 0.82rem; color: #047857; font-family: monospace; word-break: break-all; background: #f0fdf4; padding: 7px 10px; border-radius: 6px; border: 1px solid #bbf7d0; max-height: 52px; overflow-y: auto; user-select: all;"></div>
        </div>

        <div style="display: flex; align-items: center; gap: 8px; margin-left: auto; flex-wrap: wrap;">
            <button type="button" id="btnTopCopyUrl" onclick="copyImageUrlToClipboard()" style="background: #10b981; color: white; font-weight: 800; font-size: 0.85rem; padding: 9px 18px; border: none; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(16,185,129,0.25); transition: background 0.15s;">
                📋 Copy Image URL
            </button>
            <button type="button" onclick="previewRenderedModal()" style="background: #0284c7; color: white; font-weight: 700; font-size: 0.82rem; padding: 9px 14px; border: none; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                👁️ Full Preview
            </button>
            <button type="button" id="btnApplyToFormAction" onclick="applyDirectlyToParent()" style="display: none; background: #0284c7; color: white; font-weight: 800; font-size: 0.85rem; padding: 9px 16px; border: none; border-radius: 6px; cursor: pointer;">
                ⚡ Insert into Form
            </button>
        </div>
    </div>
</div>

<!-- Enhanced Full Preview Modal (Zoom & Crop Inspector) -->
<div id="previewModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(4px); z-index: 9999999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: white; border-radius: 10px; max-width: 860px; width: 100%; padding: 22px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); display: flex; flex-direction: column; gap: 14px; animation: modalPop 0.2s ease;">
        
        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 1.3rem;">👁️</span>
                <div>
                    <h3 style="margin: 0; font-weight: 800; font-size: 1.05rem; color: #0f172a;">Cropped Output Preview</h3>
                    <div id="previewMetaBadges" style="display: flex; align-items: center; gap: 8px; margin-top: 4px; font-size: 0.75rem; font-weight: 700; flex-wrap: wrap;"></div>
                </div>
            </div>
            <button type="button" onclick="closePreviewModal()" style="background: transparent; border: none; font-size: 1.6rem; color: #64748b; cursor: pointer; line-height: 1; padding: 0 4px;" title="Close">&times;</button>
        </div>

        <div style="text-align: center; background: #0f172a; border-radius: 8px; overflow: hidden; padding: 14px; min-height: 260px; display: flex; align-items: center; justify-content: center;">
            <img id="modalPreviewImg" src="" alt="Preview" style="max-width: 100%; max-height: 500px; object-fit: contain; margin: 0 auto; display: block; border-radius: 6px; box-shadow: 0 10px 25px rgba(0,0,0,0.4);">
        </div>

        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; border-top: 1px solid #e2e8f0; padding-top: 12px;">
            <div style="font-size: 0.8rem; color: #64748b; font-weight: 600;">
                💡 This is the exact zoomed/sized crop that will be exported and uploaded.
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <button type="button" onclick="copyImageUrlToClipboard()" style="padding: 8px 16px; background: #10b981; color: white; border: none; border-radius: 4px; font-weight: 700; font-size: 0.82rem; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                    📋 Copy URL
                </button>
                <button type="button" onclick="downloadImageFile()" style="padding: 8px 16px; background: #0284c7; color: white; border: none; border-radius: 4px; font-weight: 700; font-size: 0.82rem; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                    💾 Download Image
                </button>
                <button type="button" onclick="closePreviewModal()" style="padding: 8px 18px; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; border-radius: 4px; font-weight: 700; font-size: 0.82rem; cursor: pointer;">
                    Close
                </button>
            </div>
        </div>

    </div>
</div>

<style>
@keyframes slideDownPopup {
    from { opacity: 0; transform: translate(-50%, -24px); }
    to { opacity: 1; transform: translate(-50%, 0); }
}
@keyframes modalPop {
    from { opacity: 0; transform: scale(0.96); }
    to { opacity: 1; transform: scale(1); }
}
</style>

<script>
    // Global Editor State
    let sourceImage = null;
    let currentAspectRatio = 16 / 9;
    let zoomLevel = 1.0;
    let imgPosX = 0;
    let imgPosY = 0;
    let isDragging = false;
    let dragStartX = 0;
    let dragStartY = 0;
    let lastUploadedUrl = '';

    const canvas = document.getElementById('editorCanvas');
    const ctx = canvas.getContext('2d');
    const viewport = document.getElementById('canvasViewport');
    const cropBox = document.getElementById('cropBoundaryBox');
    const emptyNotice = document.getElementById('emptyCanvasNotice');

    // Check URL parameters for direct linking to articles, news, etc.
    const urlParams = new URLSearchParams(window.location.search);
    const targetInputId = urlParams.get('target');
    const previewImgId = urlParams.get('preview');
    const paramFolder = urlParams.get('folder');
    const paramLabel = urlParams.get('label');

    function initFormLinking() {
        if (paramFolder && document.getElementById('imageFolderSelect')) {
            document.getElementById('imageFolderSelect').value = paramFolder;
        }

        if (targetInputId) {
            const banner = document.getElementById('targetFormBanner');
            if (banner) banner.style.display = 'flex';
            const lbl = document.getElementById('targetFormLabel');
            if (lbl && paramLabel) lbl.innerText = paramLabel;
            
            const btnApply = document.getElementById('btnApplyToFormAction');
            if (btnApply) {
                btnApply.style.display = 'inline-flex';
                btnApply.innerText = `⚡ Insert into ${paramLabel || 'Article'}`;
            }
        }
    }

    // Resolution presets using Loop Engineering
    const RESOLUTION_PRESETS = [
        { id: 'tiny', label: 'Tiny', baseW: 160 },
        { id: 'small', label: 'Small', baseW: 240 },
        { id: 'medium', label: 'Medium', baseW: 320 },
        { id: 'standard', label: 'Standard', baseW: 640, isDefault: true },
        { id: 'large', label: 'Large', baseW: 800 },
        { id: 'hd', label: 'HD', baseW: 1280 }
    ];
    let activeResolutionId = 'standard';

    function calculatePresetDimensions(preset, ratio) {
        let w, h;
        if (Math.abs(ratio - (9/16)) < 0.05) {
            const verticalWidths = {
                tiny: 90,
                small: 135,
                medium: 180,
                standard: 360,
                large: 450,
                hd: 720
            };
            w = verticalWidths[preset.id] || Math.round(preset.baseW * (9/16));
            h = Math.round(w / ratio);
        } else {
            w = preset.baseW;
            h = Math.round(w / ratio);
        }
        return { width: w, height: h };
    }

    function renderResolutionPresets() {
        const container = document.getElementById('resolutionPresetsContainer');
        if (!container) return;
        container.innerHTML = '';

        for (let i = 0; i < RESOLUTION_PRESETS.length; i++) {
            const preset = RESOLUTION_PRESETS[i];
            const dims = calculatePresetDimensions(preset, currentAspectRatio);
            const isSelected = (activeResolutionId === preset.id);

            const label = document.createElement('label');
            label.className = 'res-preset-chip';
            label.style.display = 'inline-flex';
            label.style.alignItems = 'center';
            label.style.gap = '6px';
            label.style.padding = '6px 14px';
            label.style.borderRadius = '4px';
            label.style.cursor = 'pointer';
            label.style.border = isSelected ? '1.5px solid #0284c7' : '1px solid #cbd5e1';
            label.style.background = isSelected ? '#e0f2fe' : '#ffffff';
            label.style.color = isSelected ? '#0369a1' : '#1e293b';
            label.style.fontWeight = isSelected ? '800' : '600';
            label.style.fontSize = '0.82rem';
            label.style.userSelect = 'none';

            label.innerHTML = `
                <input type="radio" name="resSelection" value="${preset.id}" ${isSelected ? 'checked' : ''} style="accent-color: #0284c7; cursor: pointer;">
                <span>${preset.label}</span>
                <span style="font-size:0.7rem; color:${isSelected ? '#0284c7' : '#64748b'}; font-weight:700;">${dims.width}×${dims.height}</span>
            `;

            label.onclick = (function(pId) {
                return function(e) {
                    setResolutionPreset(pId);
                };
            })(preset.id);

            container.appendChild(label);
        }
    }

    function setResolutionPreset(id) {
        activeResolutionId = id;
        renderResolutionPresets();
        const hdCheckbox = document.getElementById('hdOnlyCheckbox');
        if (hdCheckbox) {
            hdCheckbox.checked = (id === 'hd');
        }
        if (sourceImage) {
            renderCanvas();
            const inlineSec = document.getElementById('inlinePreviewSection');
            if (inlineSec && inlineSec.style.display !== 'none') {
                previewRenderedModal();
            }
        }
    }

    function setAspectRatio(ratio, btnElement) {
        currentAspectRatio = ratio;
        document.querySelectorAll('.aspect-btn').forEach(b => {
            b.classList.remove('active');
            b.style.background = 'white';
            b.style.borderColor = '#cbd5e1';
            b.style.color = '#1e293b';
        });
        btnElement.classList.add('active');
        btnElement.style.background = '#bbf7d0';
        btnElement.style.borderColor = '#10b981';
        btnElement.style.color = '#065f46';

        let label = '16:9 Target Frame';
        if (Math.abs(ratio - 4/3) < 0.05) label = '4:3 Target Frame';
        else if (Math.abs(ratio - 1) < 0.05) label = '1:1 Square Frame';
        else if (Math.abs(ratio - 9/16) < 0.05) label = '9:16 Story Frame';
        document.getElementById('aspectRatioLabel').innerText = label;

        updateCropBoxDimensions();
        renderResolutionPresets();
        if (sourceImage) {
            fitImageToFrame();
        }
    }

    function updateCropBoxDimensions() {
        const vpWidth = viewport.clientWidth || 800;
        const vpHeight = viewport.clientHeight || 480;
        const padX = 40;
        const padY = 40;

        const maxBoxW = vpWidth - padX;
        const maxBoxH = vpHeight - padY;

        let boxW = maxBoxW;
        let boxH = boxW / currentAspectRatio;

        if (boxH > maxBoxH) {
            boxH = maxBoxH;
            boxW = boxH * currentAspectRatio;
        }

        cropBox.style.width = Math.round(boxW) + 'px';
        cropBox.style.height = Math.round(boxH) + 'px';

        canvas.width = vpWidth;
        canvas.height = vpHeight;

        renderCanvas();
    }

    function handleFileSelect(e) {
        const file = e.target.files && e.target.files[0];
        if (!file) return;

        // Auto populate name from filename
        const rawName = file.name.replace(/\.[^/.]+$/, "");
        const slugName = rawName.toLowerCase().replace(/[^a-z0-9_-]/g, '-');
        const nameInput = document.getElementById('imageNameInput');
        if (nameInput && !nameInput.value.trim()) {
            nameInput.value = slugName;
        }

        const reader = new FileReader();
        reader.onload = function(evt) {
            const img = new Image();
            img.onload = function() {
                sourceImage = img;
                emptyNotice.style.display = 'none';
                fitImageToFrame();
            };
            img.src = evt.target.result;
        };
        reader.readAsDataURL(file);
    }

    function fitImageToFrame() {
        if (!sourceImage) return;
        const boxW = parseFloat(cropBox.style.width) || 640;
        const boxH = parseFloat(cropBox.style.height) || 360;

        const scaleX = boxW / sourceImage.width;
        const scaleY = boxH / sourceImage.height;
        zoomLevel = Math.min(scaleX, scaleY);

        imgPosX = canvas.width / 2;
        imgPosY = canvas.height / 2;

        renderCanvas();
    }

    function adjustZoom(delta) {
        if (!sourceImage) return;
        zoomLevel = Math.max(0.1, zoomLevel + delta);
        renderCanvas();
    }

    function renderCanvas() {
        if (!ctx) return;
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        if (!sourceImage) return;

        ctx.save();
        ctx.translate(imgPosX, imgPosY);
        ctx.scale(zoomLevel, zoomLevel);
        ctx.drawImage(sourceImage, -sourceImage.width / 2, -sourceImage.height / 2);
        ctx.restore();

        // Render live overlay text if specified
        const overlayText = document.getElementById('overlayTextInput').value.trim();
        if (overlayText) {
            const textColor = document.getElementById('textColorPicker').value || '#ffffff';
            const strokeWidth = parseInt(document.getElementById('strokeWidthInput').value || '0', 10);
            const strokeColor = document.getElementById('strokeColorPicker').value || '#000000';

            const boxW = parseFloat(cropBox.style.width) || 640;
            const boxH = parseFloat(cropBox.style.height) || 360;
            const boxLeft = (canvas.width - boxW) / 2;
            const boxTop = (canvas.height - boxH) / 2;

            ctx.save();
            ctx.font = 'bold 24px Inter, sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';

            const textX = boxLeft + (boxW / 2);
            const textY = boxTop + (boxH / 2);

            if (strokeWidth > 0) {
                ctx.lineWidth = strokeWidth * 2;
                ctx.strokeStyle = strokeColor;
                ctx.strokeText(overlayText, textX, textY);
            }

            ctx.fillStyle = textColor;
            ctx.fillText(overlayText, textX, textY);
            ctx.restore();
        }
    }

    // Drag / Pan Events
    viewport.addEventListener('mousedown', (e) => {
        if (!sourceImage) return;
        isDragging = true;
        dragStartX = e.clientX - imgPosX;
        dragStartY = e.clientY - imgPosY;
        viewport.style.cursor = 'grabbing';
    });

    window.addEventListener('mousemove', (e) => {
        if (!isDragging || !sourceImage) return;
        imgPosX = e.clientX - dragStartX;
        imgPosY = e.clientY - dragStartY;
        renderCanvas();
    });

    window.addEventListener('mouseup', () => {
        isDragging = false;
        viewport.style.cursor = 'grab';
    });

    viewport.addEventListener('wheel', (e) => {
        if (!sourceImage) return;
        e.preventDefault();
        const delta = e.deltaY < 0 ? 0.05 : -0.05;
        adjustZoom(delta);
    }, { passive: false });

    // Generate Cropped High Quality Image Canvas
    function getCroppedCanvas(targetWidth, targetHeight) {
        if (!sourceImage) return null;

        const boxW = parseFloat(cropBox.style.width) || 640;
        const boxH = parseFloat(cropBox.style.height) || 360;
        const boxLeft = (canvas.width - boxW) / 2;
        const boxTop = (canvas.height - boxH) / 2;

        const outCanvas = document.createElement('canvas');
        outCanvas.width = targetWidth || 640;
        outCanvas.height = targetHeight || Math.round(targetWidth / currentAspectRatio);
        const outCtx = outCanvas.getContext('2d');

        const scaleRatio = outCanvas.width / boxW;

        outCtx.save();
        outCtx.scale(scaleRatio, scaleRatio);
        outCtx.translate(-boxLeft, -boxTop);

        // Draw image transformed
        outCtx.translate(imgPosX, imgPosY);
        outCtx.scale(zoomLevel, zoomLevel);
        outCtx.drawImage(sourceImage, -sourceImage.width / 2, -sourceImage.height / 2);
        outCtx.restore();

        // Draw overlay text
        const overlayText = document.getElementById('overlayTextInput').value.trim();
        if (overlayText) {
            const textColor = document.getElementById('textColorPicker').value || '#ffffff';
            const strokeWidth = parseInt(document.getElementById('strokeWidthInput').value || '0', 10);
            const strokeColor = document.getElementById('strokeColorPicker').value || '#000000';

            outCtx.save();
            const fontSize = Math.round(24 * (outCanvas.width / 640));
            outCtx.font = `bold ${fontSize}px Inter, sans-serif`;
            outCtx.textAlign = 'center';
            outCtx.textBaseline = 'middle';

            const textX = outCanvas.width / 2;
            const textY = outCanvas.height / 2;

            if (strokeWidth > 0) {
                outCtx.lineWidth = Math.round(strokeWidth * 2 * (outCanvas.width / 640));
                outCtx.strokeStyle = strokeColor;
                outCtx.strokeText(overlayText, textX, textY);
            }

            outCtx.fillStyle = textColor;
            outCtx.fillText(overlayText, textX, textY);
            outCtx.restore();
        }

        return outCanvas;
    }

    function getTargetDimensions() {
        const isHDCheckbox = document.getElementById('hdOnlyCheckbox')?.checked;
        if (isHDCheckbox) {
            const hdPreset = RESOLUTION_PRESETS.find(p => p.id === 'hd') || RESOLUTION_PRESETS[5];
            return calculatePresetDimensions(hdPreset, currentAspectRatio);
        }
        const activePreset = RESOLUTION_PRESETS.find(p => p.id === activeResolutionId) || RESOLUTION_PRESETS[3];
        return calculatePresetDimensions(activePreset, currentAspectRatio);
    }

    function previewRenderedModal() {
        if (!sourceImage) {
            alert('Please select an image first.');
            return;
        }
        const dims = getTargetDimensions();
        const cropped = getCroppedCanvas(dims.width, dims.height);
        if (!cropped) return;

        const format = document.querySelector('input[name="targetFormat"]:checked')?.value || 'webp';
        const dataUrl = cropped.toDataURL(`image/${format}`, 0.92);

        // Update inline preview image (Matching Screenshot 3)
        const inlineImg = document.getElementById('inlinePreviewImg');
        if (inlineImg) inlineImg.src = dataUrl;

        // Populate badges
        const zoomPercent = Math.round(zoomLevel * 100);
        let aspectText = '16:9 Target';
        if (Math.abs(currentAspectRatio - (4/3)) < 0.05) aspectText = '4:3 Target';
        else if (Math.abs(currentAspectRatio - 1) < 0.05) aspectText = '1:1 Square';
        else if (Math.abs(currentAspectRatio - (9/16)) < 0.05) aspectText = '9:16 Vertical';

        const badgeHtml = `
            <span style="background:#e0f2fe; color:#0369a1; padding:3px 10px; border-radius:4px; font-weight:800;">Ratio: ${aspectText}</span>
            <span style="background:#f1f5f9; color:#334155; padding:3px 10px; border-radius:4px; font-weight:800;">Size: ${dims.width} × ${dims.height} px</span>
            <span style="background:#fef3c7; color:#92400e; padding:3px 10px; border-radius:4px; font-weight:800;">Format: ${format.toUpperCase()}</span>
            <span style="background:#dcfce7; color:#166534; padding:3px 10px; border-radius:4px; font-weight:800;">Zoom: ${zoomPercent}%</span>
        `;

        const inlineBadges = document.getElementById('inlinePreviewMetaBadges');
        if (inlineBadges) inlineBadges.innerHTML = badgeHtml;

        const inlineSec = document.getElementById('inlinePreviewSection');
        if (inlineSec) {
            inlineSec.style.display = 'block';
            inlineSec.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    function closePreviewModal() {
        document.getElementById('previewModal').style.display = 'none';
    }

    function downloadImageFile() {
        if (!sourceImage) {
            alert('Please select an image first.');
            return;
        }
        const dims = getTargetDimensions();
        const cropped = getCroppedCanvas(dims.width, dims.height);
        if (!cropped) return;

        const format = document.querySelector('input[name="targetFormat"]:checked')?.value || 'webp';
        const dataUrl = cropped.toDataURL(`image/${format}`, 0.92);

        let name = document.getElementById('imageNameInput').value.trim() || 'image';
        name = name.toLowerCase().replace(/[^a-z0-9_-]/g, '-');

        const link = document.createElement('a');
        link.download = `${name}.${format}`;
        link.href = dataUrl;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    function uploadToServer() {
        if (!sourceImage) {
            alert('Please select an image first.');
            return;
        }

        const dims = getTargetDimensions();
        const cropped = getCroppedCanvas(dims.width, dims.height);
        if (!cropped) return;

        const format = document.querySelector('input[name="targetFormat"]:checked')?.value || 'webp';
        const dataUrl = cropped.toDataURL(`image/${format}`, 0.92);

        const folder = document.getElementById('imageFolderSelect').value || 'articles';
        let name = document.getElementById('imageNameInput').value.trim();
        if (!name) {
            name = folder + '_' + new Date().toISOString().replace(/[^0-9]/g, '').slice(0, 14);
        } else {
            name = name.toLowerCase().replace(/[^a-z0-9_-]/g, '-');
        }

        const btn = document.getElementById('btnUploadAction');
        const badge = document.getElementById('uploadStatusBadge');
        if (btn) {
            btn.disabled = true;
            btn.innerText = 'UPLOADING...';
        }
        if (badge) {
            badge.style.display = 'inline-block';
            badge.style.background = '#fef3c7';
            badge.style.color = '#b45309';
            badge.innerText = '⏳ Processing...';
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

        fetch("{{ route('admin.image-uploader.upload') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                folder: folder,
                image_name: name,
                format: format,
                image_data: dataUrl
            })
        })
        .then(res => {
            if (!res.ok) {
                return res.json().then(errData => { throw new Error(errData.message || 'Server error ' + res.status); });
            }
            return res.json();
        })
        .then(data => {
            if (btn) {
                btn.disabled = false;
                btn.innerText = 'UPLOAD';
            }
            if (data.success) {
                lastUploadedUrl = data.url;
                if (badge) {
                    badge.style.background = '#dcfce7';
                    badge.style.color = '#16a34a';
                    badge.innerText = `✓ Uploaded (${data.size_kb} KB)`;
                }

                // Display Top Floating Popup Modal with image thumbnail & Copy URL button
                showTopSuccessPopup(data.url, data.size_kb, dataUrl);

                // If launched from an article/news form, automatically sync into parent window
                if (targetInputId) {
                    const btnApply = document.getElementById('btnApplyToFormAction');
                    if (btnApply) btnApply.style.display = 'inline-flex';

                    if (window.opener && !window.opener.closed) {
                        try {
                            const pInput = window.opener.document.getElementById(targetInputId);
                            if (pInput) {
                                pInput.value = data.url;
                                pInput.dispatchEvent(new Event('input', { bubbles: true }));
                            }
                            if (previewImgId) {
                                const pImg = window.opener.document.getElementById(previewImgId);
                                if (pImg) {
                                    pImg.src = data.url;
                                    pImg.style.display = 'block';
                                }
                            }
                            const notice = document.getElementById('autoAttachedNotice');
                            if (notice) notice.style.display = 'inline-block';
                            if (badge) badge.innerText = `✓ Attached to ${paramLabel || 'Form'}!`;
                        } catch(e) {
                            console.log('Cross-window sync:', e);
                        }
                    }
                }
            } else {
                if (badge) {
                    badge.style.background = '#fee2e2';
                    badge.style.color = '#b91c1c';
                    badge.innerText = '✕ Upload failed';
                }
                alert(data.message || 'Upload failed');
            }
        })
        .catch(err => {
            if (btn) {
                btn.disabled = false;
                btn.innerText = 'UPLOAD';
            }
            if (badge) {
                badge.style.background = '#fee2e2';
                badge.style.color = '#b91c1c';
                badge.innerText = '✕ Error';
            }
            console.error(err);
        });
    }

    function showTopSuccessPopup(url, sizeKb = null, dataUrl = null) {
        const popup = document.getElementById('topUploadSuccessPopup');
        if (!popup) return;

        const urlBox = document.getElementById('uploadedUrlText');
        if (urlBox) urlBox.innerText = url;

        const sizeBadge = document.getElementById('topUploadSizeBadge');
        if (sizeBadge && sizeKb) {
            sizeBadge.innerText = `${sizeKb} KB`;
            sizeBadge.style.display = 'inline-block';
        }

        const thumb = document.getElementById('topPopupThumbImg');
        if (thumb) {
            thumb.src = dataUrl || url;
        }

        const copyBtn = document.getElementById('btnTopCopyUrl');
        if (copyBtn) {
            copyBtn.innerText = '📋 Copy Image URL';
            copyBtn.style.background = '#10b981';
        }

        popup.style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function closeTopSuccessPopup() {
        const popup = document.getElementById('topUploadSuccessPopup');
        if (popup) popup.style.display = 'none';
    }

    function applyDirectlyToParent() {
        if (!lastUploadedUrl) {
            alert('Pehle image select karke UPLOAD button dabayein.');
            return;
        }

        if (window.opener && !window.opener.closed && targetInputId) {
            try {
                const pInput = window.opener.document.getElementById(targetInputId);
                if (pInput) {
                    pInput.value = lastUploadedUrl;
                    pInput.dispatchEvent(new Event('input', { bubbles: true }));
                }
                if (previewImgId) {
                    const pImg = window.opener.document.getElementById(previewImgId);
                    if (pImg) {
                        pImg.src = lastUploadedUrl;
                        pImg.style.display = 'block';
                    }
                }
                alert(`✓ Image successfully attached to your ${paramLabel || 'Article'} form!`);
                window.opener.focus();
                return;
            } catch(e) {
                console.log('Sync to opener failed:', e);
            }
        }

        // Fallback: Copy to clipboard
        copyImageUrlToClipboard();
        alert('Image URL copied to clipboard! Paste it into your form.');
    }


    function copyImageUrlToClipboard() {
        let copyText = lastUploadedUrl;
        if (!copyText) {
            const folder = document.getElementById('imageFolderSelect').value || 'articles';
            let name = document.getElementById('imageNameInput').value.trim() || 'image';
            name = name.toLowerCase().replace(/[^a-z0-9_-]/g, '-');
            const format = document.querySelector('input[name="targetFormat"]:checked')?.value || 'webp';
            copyText = `${window.location.origin}/uploads/${folder}/${name}.${format}`;
        }

        navigator.clipboard.writeText(copyText).then(() => {
            const btn = document.getElementById('btnTopCopyUrl');
            if (btn) {
                btn.innerText = '✓ URL Copied!';
                btn.style.background = '#059669';
                setTimeout(() => {
                    btn.innerText = '📋 Copy Image URL';
                    btn.style.background = '#10b981';
                }, 2500);
            }

            const badge = document.getElementById('uploadStatusBadge');
            if (badge) {
                badge.style.display = 'inline-block';
                badge.style.background = '#ffedd5';
                badge.style.color = '#ea580c';
                badge.innerText = '✓ Image URL Copied!';
                setTimeout(() => { badge.style.display = 'none'; }, 2500);
            }

            // Also show top popup if hidden
            showTopSuccessPopup(copyText);
        }).catch(() => {
            prompt('Copy Image URL:', copyText);
        });
    }

    function resetUploader() {
        sourceImage = null;
        activeResolutionId = 'standard';
        document.getElementById('sourceImageInput').value = '';
        document.getElementById('overlayTextInput').value = '';
        document.getElementById('strokeWidthInput').value = '0';
        document.getElementById('emptyCanvasNotice').style.display = 'block';
        const hdCb = document.getElementById('hdOnlyCheckbox');
        if (hdCb) hdCb.checked = false;
        setAspectRatio(16/9, document.querySelector('.aspect-btn'));
        renderResolutionPresets();
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        const b = document.getElementById('uploadStatusBadge');
        if (b) b.style.display = 'none';
        closeTopSuccessPopup();
        const inlineSec = document.getElementById('inlinePreviewSection');
        if (inlineSec) inlineSec.style.display = 'none';
    }

    window.addEventListener('DOMContentLoaded', () => {
        initFormLinking();
        updateCropBoxDimensions();
        renderResolutionPresets();
    });

    window.addEventListener('resize', () => {
        updateCropBoxDimensions();
    });
</script>
@endsection
