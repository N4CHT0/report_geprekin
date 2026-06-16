<!-- FLOATING TOAST AI PROGRESS -->
<div id="aiFloatingToast" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1055; display: none;">
    <div class="toast show bg-white shadow-lg border-0" style="border-radius: 12px; min-width: 320px;">
        <div class="toast-header border-bottom-0 pb-0" style="background: linear-gradient(135deg, #1e3a8a, #2563eb); color: white; border-top-left-radius: 12px; border-top-right-radius: 12px;">
            <i class="bi bi-camera-video me-2"></i>
            <strong class="me-auto">AI Video Detection</strong>
            <button type="button" class="btn-close btn-close-white" onclick="document.getElementById('aiFloatingToast').style.display='none'; localStorage.removeItem('ai_polling_job_id'); clearInterval(aiPollInterval);"></button>
        </div>
        <div class="toast-body p-3">
            <div id="aiToastProgressArea">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold text-primary" id="aiToastStatus">Mengunggah Video...</span>
                </div>
                <div class="progress" style="height: 8px; border-radius: 8px; background-color: #e2e8f0;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" id="aiToastProgressBar" style="width: 100%"></div>
                </div>
            </div>
            <div id="aiToastResultArea" class="d-none mt-2">
                <div class="alert alert-success mb-2 p-2 small text-center">
                    <i class="bi bi-check-circle-fill me-1"></i> Analisis Selesai!
                    <div class="mt-1"><b id="aiToastMotor">0</b> Motor, <b id="aiToastPerson">0</b> Pejalan</div>
                </div>
                <button type="button" class="btn btn-sm btn-primary w-100 fw-bold" onclick="applyAiResult24Jam()">
                    <i class="bi bi-magic me-1"></i> Terapkan Ekstrapolasi 24 Jam
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL AI -->
<div class="modal fade" id="aiDetectionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border:none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-primary"><i class="bi bi-robot"></i> Video Detection AI</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                
                <form id="aiDetectionForm">
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size: 13px;">Sumber Video</label>
                        <select id="aiSourceType" class="form-select" required onchange="toggleAiSource()">
                            <option value="upload">Upload File Video</option>
                            <option value="google_drive">Link Google Drive</option>
                        </select>
                    </div>
                    <div class="mb-3" id="aiUploadWrapper">
                        <label class="form-label fw-bold" style="font-size: 13px;">Upload File (.mp4, .mov)</label>
                        <input type="file" id="aiVideoFile" class="form-control" accept="video/*">
                    </div>
                    <div class="mb-3 d-none" id="aiDriveWrapper">
                        <label class="form-label fw-bold" style="font-size: 13px;">Link Google Drive</label>
                        <input type="url" id="aiDriveUrl" class="form-control" placeholder="https://drive.google.com/file/d/...">
                    </div>
                    
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold" style="font-size: 13px;">Hari</label>
                            <select id="aiDayType" class="form-select" required>
                                <option value="weekday">Weekday</option>
                                <option value="weekend">Weekend</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold" style="font-size: 13px;">Rentang Jam (1 Jam)</label>
                            <select id="aiHourRange" class="form-select" required>
                                <option value="0">00:00 - 01:00</option>
<option value="1">01:00 - 02:00</option>
<option value="2">02:00 - 03:00</option>
<option value="3">03:00 - 04:00</option>
<option value="4">04:00 - 05:00</option>
<option value="5">05:00 - 06:00</option>
<option value="6">06:00 - 07:00</option>
<option value="7">07:00 - 08:00</option>
<option value="8">08:00 - 09:00</option>
<option value="9">09:00 - 10:00</option>
<option value="10">10:00 - 11:00</option>
<option value="11">11:00 - 12:00</option>
<option value="12">12:00 - 13:00</option>
<option value="13">13:00 - 14:00</option>
<option value="14">14:00 - 15:00</option>
<option value="15">15:00 - 16:00</option>
<option value="16">16:00 - 17:00</option>
<option value="17">17:00 - 18:00</option>
<option value="18">18:00 - 19:00</option>
<option value="19">19:00 - 20:00</option>
<option value="20">20:00 - 21:00</option>
<option value="21">21:00 - 22:00</option>
<option value="22">22:00 - 23:00</option>
<option value="23">23:00 - 00:00</option>

                            </select>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary w-100 fw-bold" style="border-radius: 8px;" id="btnMulaiAi">
                            Mulai Analisis
                        </button>
                    </div>
                </form>


                <div id="aiProgressArea" class="mt-4 text-center d-none">
                    <div class="spinner-border text-primary mb-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h6 class="fw-bold text-primary" id="aiStatusText">Mengunggah Video...</h6>
                    <p class="text-muted" style="font-size: 12px;">Mohon jangan tutup jendela ini.</p>
                </div>
                
                <div id="aiResultArea" class="mt-4 text-center d-none">
                    <div class="mb-3">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="fw-bold text-success">Analisis Selesai!</h5>
                    
                    <div class="my-3 text-center">
                        <p class="fw-bold mb-1" style="font-size: 13px;">Snapshot Peak Minute</p>
                        <img id="resPeakFrame" src="" alt="Peak Frame" class="img-fluid rounded shadow-sm d-none" style="max-height: 200px; object-fit: contain; border: 2px solid #e2e8f0; margin: 0 auto;">
                    </div>

                    <p class="mb-1">Motor: <strong id="resMotor">0</strong></p>
                    <p class="mb-3">Pejalan Kaki: <strong id="resPejalan">0</strong></p>
                    <button type="button" class="btn btn-success w-100 fw-bold" style="border-radius: 8px;" onclick="applyAiResult()">
                        Terapkan ke Form
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
