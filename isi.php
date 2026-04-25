<?php
// File: isi.php
// (session_start() sudah ada di index.php)

// Pastikan variabel $koneksi sudah ada dari index.php
if (!isset($koneksi) || !($koneksi instanceof mysqli)) {
    $koneksiPath = __DIR__ . DIRECTORY_SEPARATOR . "koneksi" . DIRECTORY_SEPARATOR . "koneksi.php";
    if (file_exists($koneksiPath)) {
        require_once $koneksiPath;
    } else {
        $_SESSION['gagal'] = "Koneksi database tidak tersedia untuk memproses formulir.";
    }
}


// Logika pemrosesan form telah dipindahkan ke proses_form.php yang di-include di index.php
// File ini sekarang hanya berisi tampilan (View) formulir.

// Fetch latest profile again if needed inside partial (though available in index.php scope)

// Fetch latest profile again if needed inside partial (though available in index.php scope)
if (!isset($profile)) {
    // Fallback if not set
     $profile = ['nama_perusahaan' => 'Buku Tamu Digital', 'foto' => 'default-logo.png'];
}

$old_tamu = $_SESSION['old_tamu'] ?? [];
?>

<?php if (isset($_GET['page']) && $_GET['page'] === 'spk'): ?>
    <!-- === TAMPILAN FORM KEPUASAN (SPK) === -->
    <div class="card card-tamu shadow-lg rounded-4 overflow-hidden border-0">
        <!-- Header Card -->
        <div class="card-header bg-white border-bottom pt-4 pb-3 text-center">
            <div class="d-inline-flex align-items-center justify-content-center gap-2 mb-2">
                <img src="admin/images/<?= htmlspecialchars($profile['foto'] ?? 'default-logo.png') ?>" alt="Logo" style="height: 40px; width: 40px; object-fit: contain;">
                <h5 class="mb-0 fw-bold text-dark text-uppercase letter-spacing-1"><?= htmlspecialchars($profile['nama_perusahaan'] ?? 'Buku Tamu') ?></h5>
            </div>
            <p class="small text-muted mb-0">Survei Kepuasan Pelayanan</p>
        </div>

        <div class="card-body p-4 p-md-5">
            <form id="formKepuasan" method="POST" action="index.php?page=spk">
                <input type="hidden" name="submit_kepuasan" value="1"> 
                
                <div class="mb-4">
                    <label class="form-label small fw-bold text-uppercase text-muted letter-spacing-1">Identitas Anda</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted ps-3"><i class="bi bi-person-circle"></i></span>
                        <input type="text" class="form-control bg-light border-start-0 py-3" id="nama_responden" name="nama_responden" placeholder="Nama Anda (Opsional)">
                    </div>
                </div>

                <div class="alert alert-light border-start border-4 border-primary rounded-3 shadow-sm mb-4">
                    <div class="d-flex">
                         <div class="me-3 text-primary"><i class="bi bi-info-circle-fill fs-4"></i></div>
                         <div><small class="text-muted">Mohon kesediaan Anda memberikan penilaian atas pelayanan yang kami berikan. Skala 1 (Buruk) hingga 5 (Sangat Baik).</small></div>
                    </div>
                </div>

                <!-- Rating Section Modern -->
                <div class="row g-3 mb-4">
                     <?php 
                     $criteria = [
                        'nilai_pelayanan' => ['icon' => 'headset', 'label' => 'Kualitas Pelayanan'],
                        'nilai_fasilitas' => ['icon' => 'building-gear', 'label' => 'Fasilitas'],
                        'nilai_keramahan' => ['icon' => 'emoji-smile', 'label' => 'Keramahan Staf'],
                        'nilai_kecepatan' => ['icon' => 'lightning-charge', 'label' => 'Kecepatan']
                     ];
                     foreach($criteria as $name => $data): ?>
                    <div class="col-md-6">
                        <div class="p-3 border rounded-4 bg-white shadow-sm h-100 position-relative">
                             <div class="d-flex align-items-center mb-3">
                                <div class="bg-light rounded-circle p-2 me-2 text-primary"><i class="bi bi-<?= $data['icon'] ?>"></i></div>
                                <h6 class="mb-0 fw-bold text-dark"><?= $data['label'] ?></h6>
                             </div>
                            <div class="d-flex justify-content-between px-1">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <div class="form-check text-center mx-1">
                                    <input class="form-check-input float-none mb-1 shadow-none" type="radio" name="<?= $name ?>" id="<?= $name ?>_<?= $i ?>" value="<?= $i ?>" required style="cursor: pointer;">
                                    <label class="form-check-label d-block small text-muted fw-medium" for="<?= $name ?>_<?= $i ?>" style="cursor: pointer;"><?= $i ?></label>
                                </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="mb-5">
                    <label class="form-label small fw-bold text-uppercase text-muted letter-spacing-1">Saran & Masukan</label>
                    <div class="input-group">
                         <span class="input-group-text bg-light border-end-0 text-muted ps-3 align-items-start pt-3"><i class="bi bi-chat-heart"></i></span>
                        <textarea class="form-control bg-light border-start-0 py-3" id="saran_masukan" name="saran_masukan" rows="3" placeholder="Tulis saran Anda di sini..."></textarea>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill py-3 fw-bold shadow-sm" style="background: var(--primary-gradient); border: none;">
                        <i class="bi bi-send-fill me-2"></i> KIRIM SURVEI
                    </button>
                    <a href="index.php" class="btn btn-link text-muted mt-3 text-decoration-none small">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Form Tamu
                    </a>
                </div>
            </form>
        </div>
    </div>

<?php else: ?>

    <!-- === TAMPILAN FORM REGISTRASI TAMU === -->
    <?php catat_log("Merender form registrasi tamu (isi.php)"); ?>
    <div class="card card-tamu shadow-lg rounded-4 overflow-hidden border-0">
        <!-- Modern Header Card -->
        <div class="card-header bg-white border-bottom pt-4 pb-3 text-center position-relative">
            <div class="d-inline-flex align-items-center justify-content-center gap-2 mb-2">
                <img src="admin/images/<?= htmlspecialchars($profile['foto'] ?? 'default-logo.png') ?>" alt="Logo" style="height: 40px; width: 40px; object-fit: contain;">
                <h5 class="mb-0 fw-bold text-dark text-uppercase letter-spacing-1"><?= htmlspecialchars($profile['nama_perusahaan'] ?? 'Buku Tamu') ?></h5>
            </div>
            <p class="small text-muted mb-0">Silakan isi data diri Anda untuk keperluan kunjungan.</p>
        </div>

        <div class="card-body p-4 p-md-5">
            <form id="formRegistrasiTamu" method="POST" action="index.php" class="needs-validation" onsubmit="return validateForm()" novalidate>
                
                <!-- Section Identitas -->
                <div class="mb-4">
                    <h6 class="text-uppercase text-primary fw-bold small letter-spacing-2 mb-3 border-bottom pb-2 d-inline-block">Data Diri</h6>
                    <div class="row g-3">
                        <!-- Nama Lengkap -->
                        <div class="col-12">
                            <div class="input-group shadow-sm hover-shadow transition-all rounded-3 overflow-hidden">
                                <span class="input-group-text bg-white border-0 ps-3 text-muted"><i class="bi bi-person-vcard fs-5"></i></span>
                                <input type="text" class="form-control bg-light border-0 py-3" id="nama_tamu" name="nama_tamu" placeholder="Nama Lengkap Anda" value="<?php echo htmlspecialchars($old_tamu['nama_tamu'] ?? ''); ?>" required>
                                <div class="invalid-feedback">Wajib diisi.</div>
                            </div>
                        </div>
                        
                        <!-- Instansi & Jabatan -->
                        <div class="col-md-6">
                            <div class="input-group shadow-sm hover-shadow transition-all rounded-3 overflow-hidden">
                                <span class="input-group-text bg-white border-0 ps-3 text-muted"><i class="bi bi-building fs-5"></i></span>
                                <input type="text" class="form-control bg-light border-0 py-3" id="asal_instansi" name="asal_instansi" placeholder="Alamat/Instansi" value="<?php echo htmlspecialchars($old_tamu['asal_instansi'] ?? ''); ?>" required>
                                <div class="invalid-feedback">Wajib diisi.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                             <div class="input-group shadow-sm hover-shadow transition-all rounded-3 overflow-hidden">
                                <span class="input-group-text bg-white border-0 ps-3 text-muted"><i class="bi bi-person-badge fs-5"></i></span>
                                <input type="text" class="form-control bg-light border-0 py-3" id="jabatan" name="jabatan" placeholder="Pekerjaan/Jabatan" value="<?php echo htmlspecialchars($old_tamu['jabatan'] ?? ''); ?>" required>
                                <div class="invalid-feedback">Wajib diisi.</div>
                            </div>
                        </div>

                        <!-- Kontak -->
                        <div class="col-md-6">
                            <div class="input-group shadow-sm hover-shadow transition-all rounded-3 overflow-hidden">
                                <span class="input-group-text bg-white border-0 ps-3 text-muted"><i class="bi bi-whatsapp fs-5"></i></span>
                                <input type="tel" class="form-control bg-light border-0 py-3" id="no_telepon" name="no_telepon" placeholder="No. WA / HP" value="<?php echo htmlspecialchars($old_tamu['no_telepon'] ?? ''); ?>" required>
                                <div class="invalid-feedback">Wajib diisi.</div>
                            </div>
                    </div>
                </div>

                <!-- Section Kunjungan -->
                <div class="mb-4">
                     <h6 class="text-uppercase text-primary fw-bold small letter-spacing-2 mb-3 border-bottom pb-2 d-inline-block">Detail Kunjungan</h6>
                     <div class="row g-3">
                        <div class="col-12">
                             <div class="input-group shadow-sm hover-shadow transition-all rounded-3 overflow-hidden">
                                <span class="input-group-text bg-white border-0 ps-3 text-muted"><i class="bi bi-people fs-5"></i></span>
                                <select class="form-select bg-light border-0 py-3" id="bertemu_dengan" name="bertemu_dengan" required>
                                    <option value="" disabled selected>-- Pilih Tujuan (Bertemu Siapa) --</option>
                                    <?php 
                                    $old_bertemu = $old_tamu['bertemu_dengan'] ?? '';
                                    foreach ($list_tujuan as $tujuan) { 
                                        $selected = ($old_bertemu === $tujuan) ? 'selected' : '';
                                        echo "<option value=\"" . htmlspecialchars($tujuan) . "\" $selected>" . htmlspecialchars($tujuan) . "</option>";
                                    } 
                                    ?>
                                </select>
                                <div class="invalid-feedback">Wajib dipilih.</div>
                            </div>
                        </div>
                        <div class="col-12">
                             <div class="input-group shadow-sm hover-shadow transition-all rounded-3 overflow-hidden">
                                <span class="input-group-text bg-white border-0 ps-3 text-muted align-items-center"><i class="bi bi-card-text fs-5"></i></span>
                                <select class="form-select bg-light border-0 py-3" id="keperluan" name="keperluan" required>
                                    <option value="" disabled selected>-- Pilih Keperluan --</option>
                                    <?php 
                                    $old_keperluan = $old_tamu['keperluan'] ?? '';
                                    foreach ($list_keperluan as $kep) { 
                                        $selected = ($old_keperluan === $kep) ? 'selected' : '';
                                        echo "<option value=\"" . htmlspecialchars($kep) . "\" $selected>" . htmlspecialchars($kep) . "</option>";
                                    } 
                                    ?>
                                </select>
                                <div class="invalid-feedback">Wajib dipilih.</div>
                            </div>
                        </div>
                     </div>
                </div>

                <!-- Section Camera Widget -->
                <div class="mb-5">
                    <h6 class="text-uppercase text-primary fw-bold small letter-spacing-2 mb-3 border-bottom pb-2 d-inline-block">Foto Identitas</h6>
                    
                    <div class="camera-widget bg-light rounded-4 p-3 border border-2 border-dashed position-relative text-center" style="border-color: #cbd5e1 !important;">
                        
                        <!-- Camera Preview Areas -->
                        <div class="camera-display position-relative overflow-hidden rounded-4 shadow-sm mb-3 bg-white" style="min-height: 250px; display: flex; align-items: center; justify-content: center;">
                            
                            <!-- Placeholder View -->
                            <div id="kameraPlaceholder" class="text-center w-100 py-5">
                                <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex p-4 mb-3">
                                    <i class="bi bi-camera-fill fs-1 text-primary"></i>
                                </div>
                                <h6 class="fw-bold text-dark">Ambil Foto Selfie</h6>
                                <p class="small text-muted mb-0">Kami perlu memverifikasi identitas Anda.</p>
                            </div>

                            <!-- Live Video -->
                            <video id="kameraTamuPreview" class="w-100 h-100 object-fit-cover position-absolute top-0 start-0 d-none" autoplay playsinline style="transform: scaleX(-1);"></video>
                            
                            <!-- Captured Image -->
                            <img id="kameraTamuSnapshot" class="w-100 h-100 object-fit-cover position-absolute top-0 start-0 d-none" alt="Snapshot">
                        </div>

                        <!-- Controls -->
                        <div class="camera-controls d-flex justify-content-center gap-3">
                            <button type="button" class="btn btn-outline-primary rounded-pill px-4 fw-medium" id="btnMulaiKamera">
                                <i class="bi bi-camera-video me-2"></i>Aktifkan Kamera
                            </button>
                            
                            <!-- Shutter Button (Hidden initially) -->
                            <button type="button" class="btn btn-danger rounded-circle shadow-lg p-0 d-none d-flex align-items-center justify-content-center" id="btnAmbilFoto" style="width: 60px; height: 60px; border: 4px solid white;">
                                <i class="bi bi-camera fs-4"></i>
                            </button>
                             
                             <button type="button" class="btn btn-secondary rounded-pill px-4 fw-medium d-none" id="btnUlangFoto">
                                <i class="bi bi-arrow-counterclockwise me-2"></i>Ulang
                            </button>
                        </div>
                        <input type="hidden" name="foto_tamu_data" id="fotoTamuData">
                        <small class="d-block text-muted mt-2 fst-italic" id="kameraStatus"></small>
                    </div>
                </div>

                <div class="d-grid gap-3">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill py-3 fw-bold shadow-lg transform-hover" style="background: var(--primary-gradient); border: none;">
                        SIMPAN <i class="bi bi-chevron-right ms-2 small"></i>
                    </button>
                    <a href="?page=spk" class="btn btn-outline-primary rounded-pill py-2 fw-medium">
                        <i class="bi bi-star me-1"></i> Isi Survei Kepuasan
                    </a>
                </div>

            </form>
        </div>
    </div>

    <!-- Script Kamera Modern -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const startBtn = document.getElementById('btnMulaiKamera');
            const captureBtn = document.getElementById('btnAmbilFoto');
            const retakeBtn = document.getElementById('btnUlangFoto');
            const videoEl = document.getElementById('kameraTamuPreview');
            const snapshotImg = document.getElementById('kameraTamuSnapshot');
            const placeholder = document.getElementById('kameraPlaceholder');
            const statusEl = document.getElementById('kameraStatus');
            const hiddenInput = document.getElementById('fotoTamuData');
            const widgetContainer = document.querySelector('.camera-widget');

            let streamHandle = null;

            function setWidgetState(state) {
                // reset all
                placeholder.classList.add('d-none');
                videoEl.classList.add('d-none');
                snapshotImg.classList.add('d-none');
                startBtn.classList.add('d-none');
                captureBtn.classList.add('d-none'); // d-flex removed
                captureBtn.classList.remove('d-flex');
                retakeBtn.classList.add('d-none');

                if (state === 'idle') {
                    placeholder.classList.remove('d-none');
                    startBtn.classList.remove('d-none');
                } else if (state === 'active') {
                    videoEl.classList.remove('d-none');
                    captureBtn.classList.remove('d-none');
                    captureBtn.classList.add('d-flex');
                } else if (state === 'captured') {
                    snapshotImg.classList.remove('d-none');
                    retakeBtn.classList.remove('d-none');
                }
            }

            if (startBtn) {
                startBtn.addEventListener('click', async () => {
                    try {
                        streamHandle = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
                        if (videoEl) {
                            videoEl.srcObject = streamHandle;
                            await videoEl.play();
                            setWidgetState('active');
                            statusEl.innerText = "Kamera aktif.";
                        }
                    } catch (error) {
                        statusEl.innerText = "Gagal akses kamera: " + error.message;
                        statusEl.className = "d-block text-danger mt-2 fw-bold";
                    }
                });
            }

            if (captureBtn) {
                captureBtn.addEventListener('click', () => {
                    const canvas = document.createElement('canvas');
                    canvas.width = videoEl.videoWidth;
                    canvas.height = videoEl.videoHeight;
                    // Mirror context if needed, but CSS transform: scaleX(-1) handles display. 
                    // For saved image, usually we want standard orientation.
                    canvas.getContext('2d').drawImage(videoEl, 0, 0);
                    
                    const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
                    hiddenInput.value = dataUrl;
                    snapshotImg.src = dataUrl;
                    
                    // Stop stream
                    streamHandle.getTracks().forEach(track => track.stop());
                    
                    setWidgetState('captured');
                    statusEl.innerText = "Foto berhasil diambil.";
                    statusEl.className = "d-block text-success mt-2 fw-bold";
                });
            }

            if (retakeBtn) {
                retakeBtn.addEventListener('click', () => {
                    hiddenInput.value = '';
                    setWidgetState('idle'); // Back to start to re-initiate stream properly
                    statusEl.innerText = "";
                });
            }

            const formTamu = document.getElementById('formRegistrasiTamu');
            if (formTamu) {
                const requiredFields = formTamu.querySelectorAll('input[required], textarea[required], select[required]');
                requiredFields.forEach((field) => {
                    field.addEventListener('blur', () => {
                        if (!field.checkValidity()) {
                            field.classList.add('is-invalid');
                        } else {
                            field.classList.remove('is-invalid');
                        }
                    });
                    field.addEventListener('input', () => {
                        if (field.checkValidity()) {
                            field.classList.remove('is-invalid');
                        }
                    });
                });

                formTamu.addEventListener('submit', (event) => {
                    if (!formTamu.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    formTamu.classList.add('was-validated');
                });
            }
        });
    </script>

    <style>
        .hover-shadow:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05) !important;
        }
        .transition-all {
            transition: all 0.3s ease;
        }
        .letter-spacing-1 { letter-spacing: 1px; }
        .letter-spacing-2 { letter-spacing: 2px; }
        
        /* Custom Input Focus */
        .form-control:focus {
            background-color: #fff !important;
            box-shadow: none;
        }
        .input-group:focus-within {
            box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.25) !important;
            border-radius: 0.5rem;
        }
    </style>

<?php unset($_SESSION['old_tamu']); ?>

<?php endif; ?>
