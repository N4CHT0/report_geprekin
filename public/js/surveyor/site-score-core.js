// ─────────────────────────────────────────────────────────────────────────────
// STANDARDS — identik 1:1 dengan surveyor_site_score_standards di DB
// Bobot = nilai kontribusi langsung (bukan grade/5 × bobot)
// ─────────────────────────────────────────────────────────────────────────────
const STANDARDS = {
    // Traffic Motor — threshold = per-sesi × 6 sesi (spreadsheet scoring: 1000,2000,3000,4000,5000)
    traffic_motor: {
        tipe: 'PLUS', grades: [
            { min: 6000, max: 11999, bobot: 0.06 },
            { min: 12000, max: 17999, bobot: 0.12 },
            { min: 18000, max: 23999, bobot: 0.18 },
            { min: 24000, max: 29999, bobot: 0.24 },
            { min: 30000, max: 9999999, bobot: 0.30 }]
    },
    // Traffic Pejalan — threshold = jumlah per-sesi (WD+WE pagi/siang/petang)
    traffic_pejalan: {
        tipe: 'PLUS', grades: [
            { min: 1, max: 81, bobot: 0.04 },
            { min: 82, max: 139, bobot: 0.08 },
            { min: 140, max: 193, bobot: 0.12 },
            { min: 194, max: 249, bobot: 0.16 },
            { min: 250, max: 9999999, bobot: 0.20 }]
    },

    // Rumah Q1-Q4 — spreadsheet scoring: 500, 600, 750, 900, 1000
    rumah_q1: {
        tipe: 'PLUS', grades: [
            { min: 500, max: 599, bobot: 0.03 },
            { min: 600, max: 749, bobot: 0.06 },
            { min: 750, max: 899, bobot: 0.09 },
            { min: 900, max: 999, bobot: 0.12 },
            { min: 1000, max: 9999999, bobot: 0.15 }]
    },
    rumah_q2: {
        tipe: 'PLUS', grades: [
            { min: 500, max: 599, bobot: 0.02 },
            { min: 600, max: 749, bobot: 0.04 },
            { min: 750, max: 899, bobot: 0.06 },
            { min: 900, max: 999, bobot: 0.08 },
            { min: 1000, max: 9999999, bobot: 0.10 }]
    },
    rumah_q3: {
        tipe: 'PLUS', grades: [
            { min: 500, max: 599, bobot: 0.01 },
            { min: 600, max: 749, bobot: 0.02 },
            { min: 750, max: 899, bobot: 0.03 },
            { min: 900, max: 999, bobot: 0.04 },
            { min: 1000, max: 9999999, bobot: 0.05 }]
    },
    rumah_q4: {
        tipe: 'PLUS', grades: [
            { min: 500, max: 599, bobot: 0.01 },
            { min: 600, max: 749, bobot: 0.02 },
            { min: 750, max: 899, bobot: 0.03 },
            { min: 900, max: 999, bobot: 0.04 },
            { min: 1000, max: 9999999, bobot: 0.05 }]
    },

    // Sekolah — spreadsheet scoring: 3, 5, 10, 15, 20
    sekolah: {
        tipe: 'PLUS', grades: [
            { min: 3, max: 4, bobot: 0.01 },
            { min: 5, max: 9, bobot: 0.02 },
            { min: 10, max: 14, bobot: 0.03 },
            { min: 15, max: 19, bobot: 0.04 },
            { min: 20, max: 9999999, bobot: 0.05 }]
    },
    // Market — spreadsheet scoring: 1,1,2,2,3 (double-grade pattern)
    market: {
        tipe: 'PLUS', grades: [
            { min: 1, max: 1, bobot: 0.02 },
            { min: 2, max: 2, bobot: 0.04 },
            { min: 3, max: 9999999, bobot: 0.05 }]
    },
    // Perkantoran — spreadsheet scoring: 1,1,2,2,3 (double-grade pattern)
    perkantoran: {
        tipe: 'PLUS', grades: [
            { min: 1, max: 1, bobot: 0.010 },
            { min: 2, max: 2, bobot: 0.020 },
            { min: 3, max: 9999999, bobot: 0.025 }]
    },
    // Kesehatan — spreadsheet scoring: 2,2,3,3,4
    kesehatan: {
        tipe: 'PLUS', grades: [
            { min: 2, max: 2, bobot: 0.010 },
            { min: 3, max: 3, bobot: 0.020 },
            { min: 4, max: 9999999, bobot: 0.025 }]
    },
    // Kompetitor Geprek — threshold sesuai spreadsheet scoring: 0,2,4,6,8
    kompetitor_geprek: {
        tipe: 'MINUS', grades: [
            { min: 1, max: 1, bobot: 0.005 },
            { min: 2, max: 3, bobot: 0.010 },
            { min: 4, max: 5, bobot: 0.015 },
            { min: 6, max: 7, bobot: 0.020 },
            { min: 8, max: 9999999, bobot: 0.025 }]
    },
    // Kompetitor Lokal — threshold sesuai spreadsheet scoring: 10,15,20,25,30
    kompetitor_lokal: {
        tipe: 'MINUS', grades: [
            { min: 1, max: 9, bobot: 0.005 },
            { min: 10, max: 14, bobot: 0.010 },
            { min: 15, max: 19, bobot: 0.015 },
            { min: 20, max: 24, bobot: 0.020 },
            { min: 25, max: 9999999, bobot: 0.025 }]
    },
};

const THRESHOLD = {
    LDP: { approved: 0.60, consideration: 0.50 },
    BDP: { approved: 1.00, consideration: 0.60 },
};

const RASIO = { motor: 0.0050, pejalan: 0.0025, q1: 0.0100, q2: 0.0025, q3: 0.00125, q4: 0.00125 };

// ─────────────────────────────────────────────────────────────────────────────
// HELPERS
// ─────────────────────────────────────────────────────────────────────────────
function toNumber(v) { const n = parseFloat(v || 0); return isNaN(n) ? 0 : n; }
function sumInputs(sel) { let t = 0; document.querySelectorAll(sel).forEach(el => t += toNumber(el.value)); return t; }
function getVal(name) {
    const radios = document.querySelectorAll(`input[type="radio"][name="${name}"]`);
    if (radios.length > 0) {
        const checked = document.querySelector(`input[type="radio"][name="${name}"]:checked`);
        return checked ? (isNaN(checked.value) ? checked.value : toNumber(checked.value)) : null;
    }
    const checkbox = document.querySelector(`input[type="checkbox"][name="${name}"]`);
    if (checkbox) {
        return checkbox.checked ? toNumber(checkbox.value) : 0;
    }
    const el = document.querySelector(`[name="${name}"]`);
    if (el && el.tagName === 'SELECT') {
        return isNaN(el.value) ? el.value : toNumber(el.value);
    }
    return el ? toNumber(el.value) : 0;
}
function formatRupiah(n) { return 'Rp ' + Math.round(n).toLocaleString('id-ID'); }

function hitungScore(kode, nilai) {
    const std = STANDARDS[kode];
    if (!std || nilai <= 0) return 0;
    // Removed Math.floor to support float values
    for (const g of std.grades) {
        if (nilai >= g.min && nilai <= g.max) return g.bobot;
    }
    return 0;
}

function getGradeLabel(kode, nilai) {
    const std = STANDARDS[kode];
    if (!std || nilai <= 0) return 0;
    // Removed Math.floor to support float values
    for (let i = 0; i < std.grades.length; i++) {
        if (nilai >= std.grades[i].min && nilai <= std.grades[i].max) return i + 1;
    }
    return 0;
}

function getTipeOutlet() {
    const el = document.querySelector('[name="tipe_outlet"]:checked');
    return el ? el.value : 'LDP';
}

let previewMap = null;
let previewMapMarkers = [];
let previewRadiusCircle = null;
let masterOutlets = [];
let redZoneCircles = [];

// Fetch Master Outlets on load
document.addEventListener('DOMContentLoaded', () => {
    fetch('/api/master-outlets')
        .then(res => res.json())
        .then(data => {
            masterOutlets = data;
            if (previewMap) updateMapsPreview(); // redraw if map already loaded
        })
        .catch(err => console.error("Failed to load master outlets:", err));
});

window.initMapsPreview = function () {
    updateMapsPreview();

    // Add step=any to all calc-input and facility inputs so they support float values natively in the UI
    document.querySelectorAll('.calc-input, .facility-input, .motor-input, .pejalan-input, .competitor-input').forEach(el => {
        if (!el.hasAttribute('step')) el.setAttribute('step', '0.01');
    });
};

function clearMapOverlays() {
    if (previewMapMarkers.length > 1) { // Keep the first marker (target location)
        for (let i = 1; i < previewMapMarkers.length; i++) {
            previewMapMarkers[i].setMap(null);
        }
        previewMapMarkers = [previewMapMarkers[0]];
    }
    if (previewRadiusCircle) {
        previewRadiusCircle.setMap(null);
        previewRadiusCircle = null;
    }
}

function updateMapsPreview() {
    const latStr = document.getElementById('latitude').value;
    const lngStr = document.getElementById('longitude').value;
    const lat = parseFloat(latStr);
    const lng = parseFloat(lngStr);
    const maps = document.getElementById('maps_url');
    const btn = document.getElementById('openMapsBtn');
    const previewContainer = document.getElementById('mapPreview');

    if (latStr && lngStr && !isNaN(lat) && !isNaN(lng)) {
        const url = 'https://www.google.com/maps?q=' + lat + ',' + lng;
        maps.value = maps.value || url;
        btn.href = url;
        btn.classList.remove('disabled');

        if (typeof google === 'object' && typeof google.maps === 'object') {
            if (!document.getElementById('actualMap')) {
                previewContainer.style.padding = '0';
                previewContainer.innerHTML = '<div id="actualMap" style="width: 100%; height: 100%; border-radius: 15px; min-height: 220px;"></div>';

                previewMap = new google.maps.Map(document.getElementById('actualMap'), {
                    center: { lat: lat, lng: lng },
                    zoom: 14,
                    mapTypeControl: false,
                    streetViewControl: false
                });

                const marker = new google.maps.Marker({
                    position: { lat: lat, lng: lng },
                    map: previewMap,
                    title: "Lokasi Target",
                    icon: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png',
                    zIndex: 9999
                });
                previewMapMarkers = [marker];

                // Create and add Legend
                const legend = document.createElement('div');
                legend.id = 'map-legend';
                legend.style.background = 'white';
                legend.style.padding = '12px';
                legend.style.margin = '10px';
                legend.style.borderRadius = '5px';
                legend.style.fontFamily = 'Arial, sans-serif';
                legend.style.fontSize = '14px';
                legend.style.lineHeight = '24px';
                legend.style.boxShadow = '0 2px 6px rgba(0,0,0,.3)';
                legend.style.zIndex = '0';
                legend.style.maxHeight = '200px';
                legend.style.overflowY = 'auto';

                legend.innerHTML = `
                    <div style="font-weight:bold;margin-bottom:8px;font-size:16px;position:sticky;top:0;background:white;z-index:1;">Legenda Maps</div>
                    <div><img src="https://maps.google.com/mapfiles/ms/icons/red-dot.png" width="20" style="vertical-align:middle"> Lokasi Target</div>
                    <div><img src="https://maps.google.com/mapfiles/ms/icons/blue-dot.png" width="20" style="vertical-align:middle"> Sekolah</div>
                    <div><img src="https://maps.google.com/mapfiles/ms/icons/purple-dot.png" width="20" style="vertical-align:middle"> Kampus</div>
                    <div><img src="https://maps.google.com/mapfiles/ms/icons/pink-dot.png" width="20" style="vertical-align:middle"> Kesehatan</div>
                    <div><img src="https://maps.google.com/mapfiles/ms/icons/green-dot.png" width="20" style="vertical-align:middle"> Market / Mall</div>
                    <div><img src="https://maps.google.com/mapfiles/ms/icons/ltblue-dot.png" width="20" style="vertical-align:middle"> Perkantoran / Pabrik</div>
                    <div><img src="https://maps.google.com/mapfiles/ms/icons/orange-dot.png" width="20" style="vertical-align:middle"> Kompetitor Utama (Geprek)</div>
                    <div><img src="https://maps.google.com/mapfiles/ms/icons/yellow-dot.png" width="20" style="vertical-align:middle"> F&B Lain (Lokal)</div>
                    <div><img src="https://labs.google.com/ridefinder/images/mm_20_white.png" width="20" style="vertical-align:middle"> Perumahan / Apartemen</div>
                `;
                previewMap.controls[google.maps.ControlPosition.LEFT_BOTTOM].push(legend);
            } else if (previewMap) {
                previewMap.setCenter({ lat: lat, lng: lng });
                if (previewMapMarkers.length > 0) {
                    previewMapMarkers[0].setPosition({ lat: lat, lng: lng });
                }
            }

            // Draw Red Zones & Check Cannibalization
            if (previewMap && masterOutlets.length > 0) {
                // Clear old red zones and markers
                redZoneCircles.forEach(c => c.setMap(null));
                redZoneCircles = [];

                let cannibalCount = 0;
                let cannibalNames = [];
                const targetLoc = new google.maps.LatLng(lat, lng);

                // Draw a SINGLE 3KM scan radar around the Target
                const scanRadar = new google.maps.Circle({
                    strokeColor: "#dc3545",
                    strokeOpacity: 0.8,
                    strokeWeight: 2,
                    strokePosition: google.maps.StrokePosition.OUTSIDE,
                    fillColor: "#dc3545",
                    fillOpacity: 0.05,
                    map: previewMap,
                    center: targetLoc,
                    radius: 3000
                });
                redZoneCircles.push(scanRadar);

                masterOutlets.forEach(outlet => {
                    const outLat = parseFloat(outlet.latitude);
                    const outLng = parseFloat(outlet.longitude);
                    if (isNaN(outLat) || isNaN(outLng)) return;

                    const outletLoc = new google.maps.LatLng(outLat, outLng);
                    const dist = google.maps.geometry.spherical.computeDistanceBetween(targetLoc, outletLoc);

                    // Only mark outlets that are inside the 3KM radius
                    if (dist <= 3000) {
                        cannibalCount++;
                        cannibalNames.push(outlet.nama_outlet + ' (' + Math.round(dist) + 'm)');

                        const outletMarker = new google.maps.Marker({
                            position: outletLoc,
                            map: previewMap,
                            title: outlet.nama_outlet,
                            icon: 'https://maps.google.com/mapfiles/ms/icons/orange-dot.png',
                            label: { text: "G", color: "white", fontWeight: "bold" }
                        });
                        redZoneCircles.push(outletMarker); // push to array so it can be cleared
                    }
                });

                // Update Alert UI
                const alertBox = document.getElementById('cannibalizationAlert');
                const canibIcon = document.getElementById('canibIcon');
                const canibTitle = document.getElementById('canibTitle');
                const canibMessage = document.getElementById('canibMessage');

                if (alertBox) {
                    alertBox.classList.remove('d-none');
                    if (cannibalCount > 0) {
                        alertBox.classList.remove('alert-success');
                        alertBox.classList.add('alert-danger');
                        canibIcon.className = 'bi bi-shield-x fs-3 me-3 text-danger';
                        canibTitle.className = 'fw-bold mb-1 text-danger';
                        canibTitle.innerText = 'WARNING: ZONA KANIBALISASI TERDETEKSI!';
                        canibMessage.innerText = `Target berada dalam radius 3KM dari ${cannibalCount} cabang Geprekin Aja:\n- ${cannibalNames.join('\n- ')}`;
                    } else {
                        alertBox.classList.remove('alert-danger');
                        alertBox.classList.add('alert-success');
                        canibIcon.className = 'bi bi-shield-check fs-3 me-3 text-success';
                        canibTitle.className = 'fw-bold mb-1 text-success';
                        canibTitle.innerText = 'Status Jaringan Internal: Aman';
                        canibMessage.innerText = 'Tidak ditemukan cabang Geprekin Aja dalam radius 3KM (Zona Konflik).';
                    }
                }
            }
        } else {
            const embedUrl = 'https://maps.google.com/maps?q=' + lat + ',' + lng + '&hl=id&z=15&output=embed';
            previewContainer.style.padding = '0';
            previewContainer.innerHTML = '<iframe src="' + embedUrl + '" width="100%" height="100%" style="border:0; border-radius: 15px; min-height: 220px;" allowfullscreen="" loading="lazy"></iframe>';
        }
    } else {
        btn.href = '#';
        btn.classList.add('disabled');
        previewContainer.style.padding = '18px';
        previewContainer.innerHTML =
            '<div><i class="bi bi-geo-alt fs-1 text-primary"></i>' +
            '<div class="mt-2">Klik Ambil Titik GPS atau paste latitude / longitude.</div></div>';
    }
}

// Helper: Haversine distance in meters
function calculateHaversineDistance(lat1, lon1, lat2, lon2) {
    const R = 6371000;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
        Math.sin(dLon / 2) * Math.sin(dLon / 2);
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

function calculateCannibalizationPenalty() {
    const latStr = document.getElementById('latitude').value;
    const lngStr = document.getElementById('longitude').value;
    const lat = parseFloat(latStr);
    const lng = parseFloat(lngStr);

    if (isNaN(lat) || isNaN(lng) || masterOutlets.length === 0) return 0;

    let closestDist = 9999999;
    masterOutlets.forEach(outlet => {
        const outLat = parseFloat(outlet.latitude);
        const outLng = parseFloat(outlet.longitude);
        if (isNaN(outLat) || isNaN(outLng)) return;
        const dist = calculateHaversineDistance(lat, lng, outLat, outLng);
        if (dist < closestDist) closestDist = dist;
    });

    if (closestDist <= 3000) {
        let penalty = (closestDist <= 1500) ? 0.25 : 0.10;
        const q1 = parseFloat(document.getElementById('rumah_q1')?.value || 0);
        const q2 = parseFloat(document.getElementById('rumah_q2')?.value || 0);
        if ((q1 + q2) > 2000) {
            penalty = penalty / 2; // Density Mitigation
        }
        return penalty;
    }
    return 0;
}

// ─────────────────────────────────────────────────────────────────────────────
// MAIN CALCULATOR
// ─────────────────────────────────────────────────────────────────────────────
function hitungLiveScore() {
    // 1. Parse custom config for Harga Kompetitor extras & Multipliers first
    let hargaAcuan = 10000;
    let bonusPersen = 0.10; // 10%
    let multiplierRumah = 1.0;
    let multiplierTraffic = 1.0;

    let currentRasio = { ...RASIO };

    const isCustom = document.getElementById('formulaCustom')?.checked;

    if (isCustom) {
        const existingJsonStr = document.getElementById('custom_weights_json')?.value;
        if (existingJsonStr && existingJsonStr !== '{}') {
            try {
                const parsed = JSON.parse(existingJsonStr);
                if (parsed.extras) {
                    if (parsed.extras.harga_acuan > 0) hargaAcuan = parsed.extras.harga_acuan;
                    if (parsed.extras.bonus_persen !== undefined) bonusPersen = parsed.extras.bonus_persen / 100;
                }
                if (parsed.multipliers) {
                    if (parsed.multipliers.rumah > 0) multiplierRumah = parsed.multipliers.rumah;
                    if (parsed.multipliers.traffic > 0) multiplierTraffic = parsed.multipliers.traffic;
                }
                if (parsed.ratios) {
                    if (parsed.ratios.motor > 0) currentRasio.motor = parsed.ratios.motor;
                    if (parsed.ratios.pejalan > 0) currentRasio.pejalan = parsed.ratios.pejalan;
                    if (parsed.ratios.q1 > 0) currentRasio.q1 = parsed.ratios.q1;
                    if (parsed.ratios.q2 > 0) currentRasio.q2 = parsed.ratios.q2;
                    if (parsed.ratios.q3 > 0) currentRasio.q3 = parsed.ratios.q3;
                    if (parsed.ratios.q4 > 0) currentRasio.q4 = parsed.ratios.q4;
                }
            } catch (e) { }
        }
    }

    // 2. Ambil raw inputs dan terapkan multiplier sejak awal
    const totalMotor = sumInputs('.motor-input') * multiplierTraffic;
    const totalPejalan = sumInputs('.pejalan-input') * multiplierTraffic;
    const q1 = getVal('rumah_q1') * multiplierRumah;
    const q2 = getVal('rumah_q2') * multiplierRumah;
    const q3 = getVal('rumah_q3') * multiplierRumah;
    const q4 = getVal('rumah_q4') * multiplierRumah;
    const avgCheck = getVal('average_check') || 21000;

    const scoreMotor = hitungScore('traffic_motor', totalMotor);
    const scorePejalan = hitungScore('traffic_pejalan', totalPejalan);

    const hargaKomp = getVal('harga_kompetitor');
    let bonusHarga = 0;

    // Logika Nilai Ekstra dari Harga Kompetitor
    if (hargaKomp > 0) {
        if (hargaKomp > hargaAcuan) {
            bonusHarga = bonusPersen; // Harga kompetitor lebih mahal = Keuntungan kita
        } else if (hargaKomp < hargaAcuan) {
            bonusHarga = -bonusPersen; // Harga kompetitor lebih murah = Kerugian kita
        }
    }

    const scoreRumah = hitungScore('rumah_q1', q1) + hitungScore('rumah_q2', q2)
        + hitungScore('rumah_q3', q3) + hitungScore('rumah_q4', q4);
    const scoreFasilitas = hitungScore('sekolah', getVal('sekolah'))
        + hitungScore('market', getVal('market'))
        + hitungScore('perkantoran', getVal('perkantoran'))
        + hitungScore('kesehatan', getVal('kesehatan'));
    const scoreKompetitor = hitungScore('kompetitor_geprek', getVal('kompetitor_geprek'))
        + hitungScore('kompetitor_lokal', getVal('kompetitor_lokal'));

    // --- LOGIKA BONUS FISIK BANGUNAN ---
    let bonusFisik = 0;

    // Get dynamic max weights (convert % to decimal, e.g., 5 -> 0.05)
    let maxAkses = 0.05, maxVisibilitas = 0.05, maxParkir = 0.05;
    const aInp = document.querySelector('.config-weight[data-cat="bonus_akses"]');
    if (aInp) maxAkses = (parseFloat(aInp.value) || 0) / 100;

    const vInp = document.querySelector('.config-weight[data-cat="bonus_visibilitas"]');
    if (vInp) maxVisibilitas = (parseFloat(vInp.value) || 0) / 100;

    const pInp = document.querySelector('.config-weight[data-cat="bonus_parkir"]');
    if (pInp) maxParkir = (parseFloat(pInp.value) || 0) / 100;

    // 1. Akses Jalan (fractions of maxAkses)
    if (getVal('akses_mobil') == 1) bonusFisik += (maxAkses * 0.4);
    if (getVal('jenis_jalan') === '2 Arah') bonusFisik += (maxAkses * 0.3);
    if (getVal('lebar_jalan') >= 6) bonusFisik += (maxAkses * 0.3);

    // 2. Visibilitas (fractions of maxVisibilitas)
    if (getVal('terlihat_jalan_utama') == 1) bonusFisik += (maxVisibilitas * 0.4);
    if (getVal('posisi_hook') == 1) bonusFisik += (maxVisibilitas * 0.3);
    if (getVal('terhalang_pohon_kabel') == 0) bonusFisik += (maxVisibilitas * 0.3);

    // 3. Parkir & Fasade (fractions of maxParkir)
    const frontage = getVal('frontage');
    if (frontage >= 6) bonusFisik += (maxParkir * 0.6);
    else if (frontage >= 4) bonusFisik += (maxParkir * 0.3);

    if (getVal('ruang_signage') == 1) bonusFisik += (maxParkir * 0.2);
    if (getVal('penerangan_malam') == 1) bonusFisik += (maxParkir * 0.2);

    window.lastBonusFisik = bonusFisik; // store globally for UI

    // Calculate Smart Cannibalization Penalty
    const internalCannibalPenalty = calculateCannibalizationPenalty();
    window.lastCannibalPenalty = internalCannibalPenalty;

    const totalPenambah = scoreMotor + scorePejalan + scoreRumah + scoreFasilitas + (bonusHarga > 0 ? bonusHarga : 0) + bonusFisik;
    const totalPengurang = scoreKompetitor + (bonusHarga < 0 ? Math.abs(bonusHarga) : 0) + internalCannibalPenalty;
    const finalScore = Math.min(1.0, Math.max(0, totalPenambah - totalPengurang));
    const finalPercent = finalScore * 100;

    // Tipe outlet & threshold
    const tipe = getTipeOutlet();
    const thresh = THRESHOLD[tipe];

    let rekomendasi = 'REJECTED', statusClass = '';
    if (finalScore >= thresh.approved) { rekomendasi = 'APPROVED'; statusClass = 'approved'; }
    else if (finalScore >= thresh.consideration) { rekomendasi = 'CONSIDERATION'; statusClass = 'consideration'; }

    // Grade label untuk tabel
    const gMotor = getGradeLabel('traffic_motor', totalMotor);
    const gPejalan = getGradeLabel('traffic_pejalan', totalPejalan);

    // Update baris motor
    document.querySelectorAll('.motor-input').forEach(function (input) {
        const row = input.closest('tr'); if (!row) return;
        const val = toNumber(input.value);
        const gc = row.querySelector('.motor-grade');
        const nc = row.querySelector('.motor-nilai');
        if (gc) gc.innerText = val > 0 ? gMotor : 0;
        if (nc) nc.innerText = val > 0 ? (scoreMotor * 100).toFixed(2) + '%' : '0.00%';
    });

    // Update baris pejalan
    document.querySelectorAll('.pejalan-input').forEach(function (input) {
        const row = input.closest('tr'); if (!row) return;
        const val = toNumber(input.value);
        const gc = row.querySelector('.pejalan-grade');
        const nc = row.querySelector('.pejalan-nilai');
        if (gc) gc.innerText = val > 0 ? gPejalan : 0;
        if (nc) nc.innerText = val > 0 ? (scorePejalan * 100).toFixed(2) + '%' : '0.00%';
    });

    document.getElementById('totalMotorCell').innerText = totalMotor.toLocaleString('id-ID');
    document.getElementById('totalPejalanCell').innerText = totalPejalan.toLocaleString('id-ID');
    document.getElementById('motorScoreCell').innerText = (scoreMotor * 100).toFixed(2) + '%';
    document.getElementById('pejalanScoreCell').innerText = (scorePejalan * 100).toFixed(2) + '%';

    // Panel kanan
    document.getElementById('finalScoreDisplay').innerText = finalPercent.toFixed(1) + '%';
    document.getElementById('summaryMotor').innerText = totalMotor.toLocaleString('id-ID');
    document.getElementById('summaryPejalan').innerText = totalPejalan.toLocaleString('id-ID');
    document.getElementById('totalPlusDisplay').innerText = (totalPenambah * 100).toFixed(2) + '%';
    if (document.getElementById('bonusFisikDisplay')) document.getElementById('bonusFisikDisplay').innerText = '+' + (bonusFisik * 100).toFixed(2) + '%';

    // Breakdown Penambah
    if (document.getElementById('bdMotor')) document.getElementById('bdMotor').innerText = '+' + (scoreMotor * 100).toFixed(2) + '%';
    if (document.getElementById('bdPejalan')) document.getElementById('bdPejalan').innerText = '+' + (scorePejalan * 100).toFixed(2) + '%';
    if (document.getElementById('bdRumah')) document.getElementById('bdRumah').innerText = '+' + (scoreRumah * 100).toFixed(2) + '%';
    if (document.getElementById('bdFasilitas')) document.getElementById('bdFasilitas').innerText = '+' + (scoreFasilitas * 100).toFixed(2) + '%';
    if (document.getElementById('bdBonusFisik')) document.getElementById('bdBonusFisik').innerText = '+' + (bonusFisik * 100).toFixed(2) + '%';
    const bdBonusHargaCont = document.getElementById('bdBonusHargaContainer');
    if (bdBonusHargaCont) {
        if (bonusHarga > 0) {
            bdBonusHargaCont.style.display = 'flex';
            document.getElementById('bdBonusHarga').innerText = '+' + (bonusHarga * 100).toFixed(2) + '%';
        } else {
            bdBonusHargaCont.style.display = 'none';
        }
    }

    document.getElementById('totalMinusDisplay').innerText = (totalPengurang * 100).toFixed(2) + '%';

    // Breakdown Pengurang
    if (document.getElementById('bdKanibal')) document.getElementById('bdKanibal').innerText = '-' + (internalCannibalPenalty * 100).toFixed(2) + '%';
    if (document.getElementById('bdKompetitor')) document.getElementById('bdKompetitor').innerText = '-' + (scoreKompetitor * 100).toFixed(2) + '%';
    const bdMinusHargaCont = document.getElementById('bdMinusHargaContainer');
    if (bdMinusHargaCont) {
        if (bonusHarga < 0) {
            bdMinusHargaCont.style.display = 'flex';
            document.getElementById('bdMinusHarga').innerText = '-' + (Math.abs(bonusHarga) * 100).toFixed(2) + '%';
        } else {
            bdMinusHargaCont.style.display = 'none';
        }
    }
    document.getElementById('tipeOutletDisplay').innerText = tipe;
    document.getElementById('thresholdDisplay').innerText = '≥ ' + (thresh.approved * 100).toFixed(0) + '%';

    const status = document.getElementById('recommendationDisplay');
    status.innerText = rekomendasi;
    status.className = 'score-status ' + statusClass;

    document.getElementById('scoreProgress').style.width = Math.min(100, finalPercent) + '%';

    // Omset calculation matching exactly with Excel logic
    let moe = (document.querySelector('[name="provinsi"]')?.value || '').toLowerCase().includes('madura') ? 0.30 : 0.20;
    const moeInput = document.getElementById('margin_of_error');
    if (moeInput && moeInput.value !== '') {
        moe = parseFloat(moeInput.value) / 100;
    }

    // 1. Traffic (Expanded to Weekly total: Weekday x 5 + Weekend x 2)
    // Terapkan multiplier ke individual inputs agar sejalan dengan totalMotor
    const m_wd = (getVal('motor_weekday_pagi') + getVal('motor_weekday_siang') + getVal('motor_weekday_sore')) * multiplierTraffic;
    const m_we = (getVal('motor_weekend_pagi') + getVal('motor_weekend_siang') + getVal('motor_weekend_sore')) * multiplierTraffic;
    const totalMotorWeekly = (m_wd * 5) + (m_we * 2);

    const p_wd = (getVal('pejalan_weekday_pagi') + getVal('pejalan_weekday_siang') + getVal('pejalan_weekday_sore')) * multiplierTraffic;
    const p_we = (getVal('pejalan_weekend_pagi') + getVal('pejalan_weekend_siang') + getVal('pejalan_weekend_sore')) * multiplierTraffic;
    const totalPejalanWeekly = (p_wd * 5) + (p_we * 2);

    // --- ENHANCEMENT 1: Diminishing Returns (Batas Jenuh Jalan Raya/Arteri) ---
    const totalMotorDaily = totalMotorWeekly / 7;
    let effectiveMotorDaily = totalMotorDaily;
    if (totalMotorDaily > 20000) {
        effectiveMotorDaily = 20000 + ((totalMotorDaily - 20000) * 0.20);
    }

    // --- ENHANCEMENT 2: Traffic Friction Multiplier (Efek Hambatan Jalan 1 Arah) ---
    let trafficFriction = 1.0;
    const jenisJalanEl = document.querySelector('[name="jenis_jalan"]:checked');
    const uTurnEl = document.querySelector('[name="u_turn_lampu_merah"]:checked');
    const jenisJalan = jenisJalanEl ? jenisJalanEl.value : '';
    const uTurn = uTurnEl ? parseInt(uTurnEl.value) : 0;

    if (jenisJalan === '1 Arah' && uTurn === 0) {
        trafficFriction = 0.7; // Potong 30%
    }

    const omsetMotorPerhari = (effectiveMotorDaily * currentRasio.motor * trafficFriction * avgCheck);
    const omsetPejalanPerhari = (totalPejalanWeekly * currentRasio.pejalan * avgCheck) / 7;

    // 2. Rumah Penduduk (Harian = Perminggu / 7) - q1 dkk sudah dikali multiplier di atas
    const omsetQ1 = (q1 * currentRasio.q1 * avgCheck) / 7;
    const omsetQ2 = (q2 * currentRasio.q2 * avgCheck) / 7;
    const omsetQ3 = (q3 * currentRasio.q3 * avgCheck) / 7;
    const omsetQ4 = (q4 * currentRasio.q4 * avgCheck) / 7;

    let subTotalOmset = omsetMotorPerhari + omsetPejalanPerhari + omsetQ1 + omsetQ2 + omsetQ3 + omsetQ4;

    // --- ENHANCEMENT 3: Visibility Gravity Bonus ---
    let visibilityMultiplier = 1.0;
    const hookEl = document.querySelector('[name="posisi_hook"]:checked');
    const jalanUtamaEl = document.querySelector('[name="terlihat_jalan_utama"]:checked');
    if (hookEl && parseInt(hookEl.value) === 1 && jalanUtamaEl && parseInt(jalanUtamaEl.value) === 1) {
        visibilityMultiplier = 1.15; // +15% bonus omset
    }

    subTotalOmset = subTotalOmset * visibilityMultiplier;
    let grandTotalOmset = subTotalOmset * (1 - moe);

    // ==================================================
    // AI PREDICTION OVERRIDE (Fase 3 - Machine Learning)
    // ==================================================
    if (typeof window.aiPredictedOmsetHarian !== 'undefined' && window.aiPredictedOmsetHarian > 0) {
        // grandTotalOmset = window.aiPredictedOmsetHarian; // Dinonaktifkan sementara agar Omset sinkron dengan Score
    }
    // ==================================================

    document.getElementById('omsetPerhariDisplay').innerText = formatRupiah(grandTotalOmset);
    if (document.getElementById('moeDisplay')) {
        document.getElementById('moeDisplay').innerText = (moe * 100).toFixed(0) + '%';
    }

    // Calculate CU and other periods
    const omsetHarian = grandTotalOmset;
    const omsetMingguan = omsetHarian * 7;
    const omsetBulanan = omsetHarian * 30;

    const cuHarian = avgCheck > 0 ? (omsetHarian / avgCheck) : 0;
    const cuMingguan = avgCheck > 0 ? (omsetMingguan / avgCheck) : 0;
    const cuBulanan = avgCheck > 0 ? (omsetBulanan / avgCheck) : 0;

    // Output to UI Displays
    if (document.getElementById('omsetMingguanDisplay')) document.getElementById('omsetMingguanDisplay').innerText = formatRupiah(omsetMingguan);
    if (document.getElementById('omsetBulananDisplay')) document.getElementById('omsetBulananDisplay').innerText = formatRupiah(omsetBulanan);

    // Target Kontribusi Omset (Organik vs Online)
    const rasioOrganikInput = document.getElementById('config_rasio_organik');
    const rasioOnlineInput = document.getElementById('config_rasio_online');

    let rasioOrganik = 85;
    if (rasioOrganikInput) {
        rasioOrganik = parseFloat(rasioOrganikInput.value);
        if (isNaN(rasioOrganik)) rasioOrganik = 85;
        if (rasioOrganik < 0) rasioOrganik = 0;
        if (rasioOrganik > 100) rasioOrganik = 100;

        const rasioOnline = 100 - rasioOrganik;
        if (rasioOnlineInput) rasioOnlineInput.value = rasioOnline;

        const omsetOrganik = omsetHarian * (rasioOrganik / 100);
        const omsetOjol = omsetHarian * (rasioOnline / 100);

        if (document.getElementById('omsetOrganikDisplay')) document.getElementById('omsetOrganikDisplay').innerText = formatRupiah(omsetOrganik);
        if (document.getElementById('omsetOjolDisplay')) document.getElementById('omsetOjolDisplay').innerText = formatRupiah(omsetOjol);
        if (document.getElementById('labelRasioOrganik')) document.getElementById('labelRasioOrganik').innerText = rasioOrganik + '%';
        if (document.getElementById('labelRasioOnline')) document.getElementById('labelRasioOnline').innerText = rasioOnline + '%';
    }

    // Hitung Label Outlet (Berdasarkan Omset Harian)
    let labelOutlet = "Di Bawah Standar";
    let badgeClass = "bg-danger";

    if (omsetHarian >= 3500001) {
        labelOutlet = "Plus";
        badgeClass = "bg-success";
    } else if (omsetHarian >= 2000001) {
        labelOutlet = "Flagship";
        badgeClass = "bg-primary";
    } else if (omsetHarian >= 1400001) {
        labelOutlet = "Express";
        badgeClass = "bg-info text-dark";
    } else if (omsetHarian >= 750000) {
        labelOutlet = "Mini";
        badgeClass = "bg-warning text-dark";
    }

    const labelEl = document.getElementById('labelOutletDisplay');
    if (labelEl) {
        labelEl.innerText = labelOutlet;
        labelEl.className = 'badge ' + badgeClass;
    }

    if (document.getElementById('cuHarianDisplay')) document.getElementById('cuHarianDisplay').innerText = Math.round(cuHarian) + ' CU';
    if (document.getElementById('cuMingguanDisplay')) document.getElementById('cuMingguanDisplay').innerText = Math.round(cuMingguan) + ' CU';
    if (document.getElementById('cuBulananDisplay')) document.getElementById('cuBulananDisplay').innerText = Math.round(cuBulanan) + ' CU';

    if (document.getElementById('averageCheckDisplay')) {
        document.getElementById('averageCheckDisplay').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(avgCheck);
    }

    // Populate hidden inputs for form submission
    const setHidden = (id, val) => { const el = document.getElementById(id); if (el) el.value = val; };
    setHidden('input_omset_perhari', omsetHarian);
    setHidden('input_omset_perminggu', omsetMingguan);
    setHidden('input_omset_perbulan', omsetBulanan);
    setHidden('input_cu_perhari', cuHarian);
    setHidden('input_cu_perminggu', cuMingguan);
    setHidden('input_cu_perbulan', cuBulanan);

    updateMapsPreview();
    updateJamRamai();
}

// ─────────────────────────────────────────────────────────────────────────────
// GPS
// ─────────────────────────────────────────────────────────────────────────────
function autoFillAlamat(lat, lng) {
    // Gunakan OpenStreetMap Nominatim API (Gratis, tanpa API key)
    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`)
        .then(response => response.json())
        .then(data => {
            if (data && data.address) {
                const addr = data.address;
                // Lokasi / Jalan
                const jalan = addr.road || addr.pedestrian || addr.suburb || addr.village || addr.neighbourhood || '';
                if (jalan && !document.querySelector('[name="lokasi"]').value) {
                    document.querySelector('[name="lokasi"]').value = jalan;
                }

                // Kota / Kabupaten
                const kota = addr.city || addr.town || addr.county || addr.municipality || '';
                if (kota && !document.querySelector('[name="kota"]').value) {
                    document.querySelector('[name="kota"]').value = kota.replace('Kabupaten', 'Kab.');
                }

                // Provinsi
                const provinsi = addr.state || '';
                if (provinsi) {
                    document.querySelector('[name="provinsi"]').value = provinsi;
                    // Trigger live score karena provinsi mempengaruhi Margin of Error (MoE)
                    hitungLiveScore();
                }
            }
        })
        .catch(err => console.error("Reverse geocoding error:", err));
}

// Helper function to calculate a destination point given distance and bearing
function computeOffset(lat, lng, distance, heading) {
    const R = 6378137; // Earth radius in meters
    const d = distance;
    const lat1 = lat * Math.PI / 180;
    const lng1 = lng * Math.PI / 180;
    const brng = heading * Math.PI / 180;

    const lat2 = Math.asin(Math.sin(lat1) * Math.cos(d / R) +
        Math.cos(lat1) * Math.sin(d / R) * Math.cos(brng));
    const lng2 = lng1 + Math.atan2(Math.sin(brng) * Math.sin(d / R) * Math.cos(lat1),
        Math.cos(d / R) - Math.sin(lat1) * Math.sin(lat2));

    return {
        lat: lat2 * 180 / Math.PI,
        lng: lng2 * 180 / Math.PI
    };
}

// Chaikin's Algorithm for smoothing polygon
function smoothPolygon(paths, iterations = 3) {
    if (!paths || paths.length < 3) return paths;
    let smoothed = [...paths];
    for (let iter = 0; iter < iterations; iter++) {
        let newPaths = [];
        for (let i = 0; i < smoothed.length; i++) {
            let p1 = smoothed[i];
            let p2 = smoothed[(i + 1) % smoothed.length];

            let lat1 = p1.lat * 0.75 + p2.lat * 0.25;
            let lng1 = p1.lng * 0.75 + p2.lng * 0.25;

            let lat2 = p1.lat * 0.25 + p2.lat * 0.75;
            let lng2 = p1.lng * 0.25 + p2.lng * 0.75;

            newPaths.push({ lat: lat1, lng: lng1 });
            newPaths.push({ lat: lat2, lng: lng2 });
        }
        smoothed = newPaths;
    }
    return smoothed;
}

async function drawIsochrone(lat, lng, maxMinutes) {
    if (!previewMap) return;

    const maxRadiusFasum = parseFloat(document.getElementById('scan_radius_fasum')?.value || 1500);
    const origin = { lat, lng };
    const dmService = new google.maps.DistanceMatrixService();

    const headings = [];
    for (let i = 0; i < 360; i += 10) headings.push(i); // 36 rays

    // Tahap 1: Generate 5 sensors per ray (20% to 100%)
    const sensors = [];
    headings.forEach((heading, rayIdx) => {
        for (let s = 1; s <= 5; s++) {
            const dist = maxRadiusFasum * (s * 0.2);
            sensors.push({
                rayIdx: rayIdx,
                heading: heading,
                dist: dist,
                loc: computeOffset(lat, lng, dist, heading)
            });
        }
    });

    // Chunk into 25 destinations per request (Google Maps limits)
    const chunks = [];
    for (let i = 0; i < sensors.length; i += 25) chunks.push(sensors.slice(i, i + 25));

    try {
        const fetchChunk = (chunk, delayMs) => {
            return new Promise((resolve) => {
                setTimeout(() => {
                    dmService.getDistanceMatrix({
                        origins: [origin],
                        destinations: chunk.map(s => s.loc),
                        travelMode: 'WALKING'
                    }, (res, status) => {
                        if (status === 'OK' && res.rows[0]) {
                            resolve(res.rows[0].elements);
                        } else {
                            resolve(chunk.map(() => ({ status: 'ERROR' })));
                        }
                    });
                }, delayMs);
            });
        };

        // Fire batches with 150ms delay to prevent OVER_QUERY_LIMIT
        const batchResults = await Promise.all(chunks.map((chunk, i) => fetchChunk(chunk, i * 150)));

        const elements = [];
        batchResults.forEach(batch => elements.push(...batch));

        let rayFinalDistances = headings.map(() => 0); // Start at 0
        let rayBlocked = headings.map(() => false);

        // Evaluasi dari terdekat hingga terjauh
        sensors.forEach((sensor, idx) => {
            const el = elements[idx];
            if (!rayBlocked[sensor.rayIdx]) {
                // Jarak aspal maksimal 1.3x dari jarak lurus + 400m flat buffer (mentolerir U-turn dan jalan perumahan berliku di jarak dekat)
                if (el.status === 'OK' && el.distance.value <= (sensor.dist * 1.3 + 400)) {
                    rayFinalDistances[sensor.rayIdx] = sensor.dist;
                } else {
                    // Terblokir sungai/rel, potong persis di titik terakhir yang valid
                    rayBlocked[sensor.rayIdx] = true;
                }
            }
        });

        // Tahap 2: Distribusi Luberan
        let totalSavedBudget = 0;
        let unblockedCount = 0;

        rayFinalDistances.forEach((dist, rayIdx) => {
            if (rayBlocked[rayIdx]) {
                totalSavedBudget += (maxRadiusFasum - dist);
            } else {
                unblockedCount++;
            }
        });

        let polygonPaths = headings.map((heading, idx) => computeOffset(lat, lng, rayFinalDistances[idx] || (maxRadiusFasum * 0.1), heading));

        // Luberkan ke sinar yang bebas
        if (totalSavedBudget > 0 && unblockedCount > 0) {
            const extraPerRay = totalSavedBudget / unblockedCount;

            const spilloverSensors = [];
            headings.forEach((heading, rayIdx) => {
                if (!rayBlocked[rayIdx]) {
                    // Batasi luberan maksimal 2.0x (bisa melar hingga 200%) agar bisa menyerap market lebih luas (omset maksimal)
                    const newDist = Math.min(maxRadiusFasum + extraPerRay, maxRadiusFasum * 2.0);
                    spilloverSensors.push({
                        rayIdx: rayIdx,
                        dist: newDist,
                        loc: computeOffset(lat, lng, newDist, heading)
                    });
                }
            });

            if (spilloverSensors.length > 0) {
                const spChunks = [];
                for (let i = 0; i < spilloverSensors.length; i += 25) spChunks.push(spilloverSensors.slice(i, i + 25));

                const spBatchResults = await Promise.all(spChunks.map((chunk, i) => fetchChunk(chunk, i * 150)));
                const spElements = [];
                spBatchResults.forEach(b => spElements.push(...b));

                spilloverSensors.forEach((sensor, idx) => {
                    const el = spElements[idx];
                    if (el.status === 'OK' && el.distance.value <= (sensor.dist * 1.3 + 400)) {
                        polygonPaths[sensor.rayIdx] = sensor.loc; // Berhasil meluber!
                    } else {
                        // Mentok juga saat meluber, tahan di maxRadiusFasum awal
                        polygonPaths[sensor.rayIdx] = computeOffset(lat, lng, maxRadiusFasum, headings[sensor.rayIdx]);
                    }
                });
            }
        }

        // Simpan titik murni Amuba sebelum di-smooth untuk penentuan batas Crosshair Q1-Q4
        window.amoebaPoints = polygonPaths;

        // Terapkan Smoothing (Liquid Amoeba)
        let finalSmoothPaths = smoothPolygon(polygonPaths, 3);

        if (typeof previewRadiusCircle !== 'undefined' && previewRadiusCircle) {
            previewRadiusCircle.setMap(null);
        }

        previewRadiusCircle = new google.maps.Polygon({
            paths: finalSmoothPaths,
            strokeColor: "#3b82f6",
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: "#3b82f6",
            fillOpacity: 0.2,
            map: previewMap
        });

        const bounds = new google.maps.LatLngBounds();
        finalSmoothPaths.forEach(p => bounds.extend(p));
        previewMap.fitBounds(bounds);

    } catch (e) {
        console.error("Spillover Isochrone failed:", e);
    }
}


async function radarFasilitas() {
    const latStr = document.getElementById('latitude').value;
    const lngStr = document.getElementById('longitude').value;
    const lat = parseFloat(latStr);
    const lng = parseFloat(lngStr);

    if (!lat || !lng || isNaN(lat) || isNaN(lng)) {
        alert('Titik koordinat belum ada! Silakan ambil titik GPS atau paste link Maps terlebih dahulu.');
        return;
    }

    const radiusFasum = parseFloat(document.getElementById('scan_radius_fasum')?.value || 1500);
    const radiusKomp = parseFloat(document.getElementById('scan_radius_kompetitor')?.value || 500);

    if (typeof google === 'undefined' || typeof google.maps === 'undefined' || !google.maps.places) {
        alert('Google Maps API belum siap. Pastikan Anda memiliki koneksi internet dan coba muat ulang halaman.');
        return;
    }

    const btn = document.getElementById('btnRadar');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Scanning...';
    btn.disabled = true;

    try {
        let service;
        if (previewMap) {
            service = new google.maps.places.PlacesService(previewMap);
            clearMapOverlays();

            // Gambar Isochrone (Poligon Jangkauan Waktu Tempuh 5 Menit)
            await drawIsochrone(lat, lng, 5);
        } else {
            service = new google.maps.places.PlacesService(document.createElement('div'));
        }

        let sekolah = 0, kampus = 0, pabrik = 0, kesehatan = 0, market = 0, perkantoran = 0, kompetitor = 0, kompetitor_geprek = 0;
        let rumahQ1 = 0, rumahQ2 = 0, rumahQ3 = 0, rumahQ4 = 0;

        const calculateBearing = (startLat, startLng, destLat, destLng) => {
            startLat = startLat * Math.PI / 180;
            startLng = startLng * Math.PI / 180;
            destLat = destLat * Math.PI / 180;
            destLng = destLng * Math.PI / 180;
            const y = Math.sin(destLng - startLng) * Math.cos(destLat);
            const x = Math.cos(startLat) * Math.sin(destLat) - Math.sin(startLat) * Math.cos(destLat) * Math.cos(destLng - startLng);
            return (Math.atan2(y, x) * 180 / Math.PI + 360) % 360;
        };

        window.radarData = { internal: [], pendidikan: [], market: [], bank: [], kesehatan: [], kompetitor: [] };
        const addRadarData = (category, places) => {
            places.forEach(p => {
                let dist = 0;
                if (p.geometry && p.geometry.location) {
                    const plat = typeof p.geometry.location.lat === 'function' ? p.geometry.location.lat() : p.geometry.location.lat;
                    const plng = typeof p.geometry.location.lng === 'function' ? p.geometry.location.lng() : p.geometry.location.lng;
                    dist = Math.round(haversineDist(lat, lng, plat, plng));
                }
                window.radarData[category].push({ name: p.name, dist: dist });
            });
        };

        // Helper function for Places API Search
        const searchPlaces = (req) => {
            return new Promise((resolve) => {
                let allResults = [];
                let pageCount = 0;
                const callback = (results, status, pagination) => {
                    if (status === google.maps.places.PlacesServiceStatus.OK && results) {
                        allResults = allResults.concat(results);
                        pageCount++;
                        if (pagination && pagination.hasNextPage && pageCount < 3) {
                            // Google Maps requires a slight delay before calling nextPage()
                            setTimeout(() => {
                                pagination.nextPage();
                            }, 1500);
                            return;
                        }
                    }
                    resolve(allResults);
                };
                service.nearbySearch(req, callback);
            });
        };

        const loc = { lat, lng };

        // Helper untuk memecah pencarian API agar tidak kena limit 60 hasil (Satelit Kuadran)
        const probeQuadrantPlaces = async (radius, keyword, type) => {
            const offsets = [
                computeOffset(lat, lng, 1500, 45),
                computeOffset(lat, lng, 1500, 135),
                computeOffset(lat, lng, 1500, 225),
                computeOffset(lat, lng, 1500, 315)
            ];

            const probePromises = offsets.map(offsetLoc => {
                const req = { location: offsetLoc, radius: radius };
                if (keyword) req.keyword = keyword;
                if (type) req.type = type;
                return searchPlaces(req);
            });

            const resultsMatrix = await Promise.all(probePromises);
            let combined = [];
            let seenIds = new Set();

            resultsMatrix.forEach(results => {
                results.forEach(p => {
                    if (p.place_id && !seenIds.has(p.place_id)) {
                        seenIds.add(p.place_id);
                        combined.push(p);
                    }
                });
            });
            return combined;
        };

        // Filter by Polygon (Holy Boundary)
        const filterByRoutingDistance = async (targetLoc, places, maxDistanceMeters) => {
            if (!places || !places.length || !previewRadiusCircle) return places;
            const validPlaces = [];
            places.forEach(p => {
                if (p.geometry && p.geometry.location) {
                    const pLoc = p.geometry.location;
                    // Hanya titik di dalam Poligon Amuba yang dihitung
                    if (google.maps.geometry.poly.containsLocation(pLoc, previewRadiusCircle)) {
                        validPlaces.push(p);
                    }
                }
            });
            return validPlaces;
        };

        const haversineDist = (lat1, lon1, lat2, lon2) => {
            const R = 6371000;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                Math.sin(dLon / 2) * Math.sin(dLon / 2);
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        };

        const calcDensityWeight = (places) => {
            let totalWeight = 0;
            places.forEach(p => {
                let dist = 1000;
                if (p.geometry && p.geometry.location) {
                    const plat = typeof p.geometry.location.lat === 'function' ? p.geometry.location.lat() : p.geometry.location.lat;
                    const plng = typeof p.geometry.location.lng === 'function' ? p.geometry.location.lng() : p.geometry.location.lng;
                    dist = haversineDist(lat, lng, plat, plng);
                }

                let w = 0.5;
                if (dist <= 250) w = 2.0;
                else if (dist <= 500) w = 1.3;
                else if (dist <= 1000) w = 0.8;

                totalWeight += w;
            });
            return parseFloat(totalWeight.toFixed(1));
        };

        // ==========================================
        // GOOGLE PLACES API (FASILITAS SUPER LENGKAP)
        // Batas routing diperlebar 1.5x dari Euclidean untuk mentolerir rute aspal normal.
        // Jika melebihi itu, berarti menyeberang tanpa jembatan.
        // ==========================================
        const promises = [
            // Pendidikan (Pencarian dilipatgandakan 2x untuk menangkap luberan)
            searchPlaces({ location: loc, radius: radiusFasum * 2.0, type: 'school' }).then(async res => { res = await filterByRoutingDistance(loc, res, radiusFasum * 1.1); sekolah += calcDensityWeight(res); addMarkers(res, 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png'); addRadarData('pendidikan', res); }),
            searchPlaces({ location: loc, radius: radiusFasum * 2.0, type: 'university' }).then(async res => { res = await filterByRoutingDistance(loc, res, radiusFasum * 1.1); kampus += calcDensityWeight(res); addMarkers(res, 'https://maps.google.com/mapfiles/ms/icons/purple-dot.png'); addRadarData('pendidikan', res); }),

            // Kesehatan
            searchPlaces({ location: loc, radius: radiusFasum * 2.0, type: 'hospital' }).then(async res => { res = await filterByRoutingDistance(loc, res, radiusFasum * 1.1); kesehatan += calcDensityWeight(res); addMarkers(res, 'https://maps.google.com/mapfiles/ms/icons/pink-dot.png'); addRadarData('kesehatan', res); }),

            // Market / Perbelanjaan & Bank
            searchPlaces({ location: loc, radius: radiusFasum * 2.0, type: 'supermarket' }).then(async res => { res = await filterByRoutingDistance(loc, res, radiusFasum * 1.1); market += calcDensityWeight(res); addMarkers(res, 'https://maps.google.com/mapfiles/ms/icons/green-dot.png'); addRadarData('market', res); }),
            searchPlaces({ location: loc, radius: radiusFasum * 2.0, type: 'convenience_store' }).then(async res => { res = await filterByRoutingDistance(loc, res, radiusFasum * 1.1); market += calcDensityWeight(res); addMarkers(res, 'https://maps.google.com/mapfiles/ms/icons/green-dot.png'); addRadarData('market', res); }),
            searchPlaces({ location: loc, radius: radiusFasum * 2.0, type: 'bank' }).then(async res => { res = await filterByRoutingDistance(loc, res, radiusFasum * 1.1); addRadarData('bank', res); }),

            // Perkantoran & Pabrik
            searchPlaces({ location: loc, radius: radiusFasum * 2.0, type: 'local_government_office' }).then(async res => { res = await filterByRoutingDistance(loc, res, radiusFasum * 1.1); perkantoran += calcDensityWeight(res); addMarkers(res, 'https://maps.google.com/mapfiles/ms/icons/ltblue-dot.png'); }),
            searchPlaces({ location: loc, radius: radiusFasum * 2.0, keyword: 'pabrik' }).then(async res => { res = await filterByRoutingDistance(loc, res, radiusFasum * 1.1); pabrik += calcDensityWeight(res); }),

            // Internal Network (Cannibalization Check)
            searchPlaces({ location: loc, radius: 3000, keyword: 'geprekin aja' }).then(res => {
                let internalRes = [];
                res.forEach(p => {
                    if (p.name.toLowerCase().includes('geprekin')) internalRes.push(p);
                });
                addRadarData('internal', internalRes);
            }),

            // Demographic Proxy (Sistem Satelit Kuadran untuk bypass limit 60 Places API)
            probeQuadrantPlaces(2500, 'masjid', null).then(async res => {
                res = await filterByRoutingDistance(loc, res, 2500 * 1.1);
                addMarkers(res, 'https://labs.google.com/ridefinder/images/mm_20_white.png');
                res.forEach(p => {
                    if (p.geometry && p.geometry.location) {
                        const plat = typeof p.geometry.location.lat === 'function' ? p.geometry.location.lat() : p.geometry.location.lat;
                        const plng = typeof p.geometry.location.lng === 'function' ? p.geometry.location.lng() : p.geometry.location.lng;
                        const brng = calculateBearing(lat, lng, plat, plng);
                        const w = calcDensityWeight([p]) * 150; // 1 Masjid ~ 150 houses
                        if (brng >= 0 && brng <= 90) rumahQ1 += w;
                        else if (brng > 90 && brng <= 180) rumahQ2 += w;
                        else if (brng > 180 && brng <= 270) rumahQ3 += w;
                        else rumahQ4 += w;
                    }
                });
            }),
            probeQuadrantPlaces(2500, 'minimarket', null).then(async res => {
                res = await filterByRoutingDistance(loc, res, 2500 * 1.1);
                addMarkers(res, 'https://labs.google.com/ridefinder/images/mm_20_white.png');
                res.forEach(p => {
                    if (p.geometry && p.geometry.location) {
                        const plat = typeof p.geometry.location.lat === 'function' ? p.geometry.location.lat() : p.geometry.location.lat;
                        const plng = typeof p.geometry.location.lng === 'function' ? p.geometry.location.lng() : p.geometry.location.lng;
                        const brng = calculateBearing(lat, lng, plat, plng);
                        const w = calcDensityWeight([p]) * 300; // 1 Minimarket ~ 300 houses
                        if (brng >= 0 && brng <= 90) rumahQ1 += w;
                        else if (brng > 90 && brng <= 180) rumahQ2 += w;
                        else if (brng > 180 && brng <= 270) rumahQ3 += w;
                        else rumahQ4 += w;
                    }
                });
            }),
            probeQuadrantPlaces(2500, 'apartemen', null).then(async res => {
                res = await filterByRoutingDistance(loc, res, 2500 * 1.1);
                window.tempApartemenDensity = calcDensityWeight(res);
                addMarkers(res, 'https://labs.google.com/ridefinder/images/mm_20_white.png');
                res.forEach(p => {
                    if (p.geometry && p.geometry.location) {
                        const plat = typeof p.geometry.location.lat === 'function' ? p.geometry.location.lat() : p.geometry.location.lat;
                        const plng = typeof p.geometry.location.lng === 'function' ? p.geometry.location.lng() : p.geometry.location.lng;
                        const brng = calculateBearing(lat, lng, plat, plng);
                        const w = calcDensityWeight([p]) * 500; // Apartments hold massive populations
                        if (brng >= 0 && brng <= 90) rumahQ1 += w;
                        else if (brng > 90 && brng <= 180) rumahQ2 += w;
                        else if (brng > 180 && brng <= 270) rumahQ3 += w;
                        else rumahQ4 += w;
                    }
                });
            })
        ];

        // Kompetitor Spesifik
        let customKeywordsUtama = [];
        let customKeywordsLokal = [];
        let capMotor = 3500;
        let capRumah = 4000;
        const existingJsonStr = document.getElementById('custom_weights_json')?.value;
        if (existingJsonStr && existingJsonStr !== '{}') {
            try {
                const parsed = JSON.parse(existingJsonStr);
                if (parsed.keywords) {
                    if (parsed.keywords.kompetitor_utama) {
                        customKeywordsUtama = parsed.keywords.kompetitor_utama.split(',').map(k => k.trim()).filter(k => k);
                    }
                    if (parsed.keywords.kompetitor_lokal) {
                        customKeywordsLokal = parsed.keywords.kompetitor_lokal.split(',').map(k => k.trim()).filter(k => k);
                    }
                }
                if (parsed.caps) {
                    if (parsed.caps.max_motor > 0) capMotor = parsed.caps.max_motor;
                    if (parsed.caps.max_rumah > 0) capRumah = parsed.caps.max_rumah;
                }
            } catch (e) { }
        }

        if (customKeywordsUtama.length === 0 && customKeywordsLokal.length === 0) {
            // Default fallback
            promises.push(
                searchPlaces({ location: loc, radius: radiusKomp, keyword: 'ayam geprek' }).then(async res => { res = await filterByRoutingDistance(loc, res, radiusKomp * 1.1); kompetitor_geprek += calcDensityWeight(res); addMarkers(res, 'https://maps.google.com/mapfiles/ms/icons/orange-dot.png'); addRadarData('kompetitor', res); }),
                searchPlaces({ location: loc, radius: radiusKomp, keyword: 'fried chicken' }).then(async res => {
                    res = await filterByRoutingDistance(loc, res, radiusKomp * 1.5);
                    let nonGeprek = [];
                    res.forEach(p => { if (!p.name.toLowerCase().includes('geprek')) nonGeprek.push(p); });
                    kompetitor += calcDensityWeight(nonGeprek);
                    addMarkers(res, 'https://maps.google.com/mapfiles/ms/icons/yellow-dot.png');
                    addRadarData('kompetitor', res);
                })
            );
        } else {
            // Custom keywords Utama (Geprek / FC)
            customKeywordsUtama.forEach((kw) => {
                promises.push(
                    searchPlaces({ location: loc, radius: radiusKomp, keyword: kw }).then(async res => {
                        res = await filterByRoutingDistance(loc, res, radiusKomp * 1.1);
                        kompetitor_geprek += calcDensityWeight(res);
                        addMarkers(res, 'https://maps.google.com/mapfiles/ms/icons/orange-dot.png');
                        addRadarData('kompetitor', res);
                    })
                );
            });
            // Custom keywords Lokal (F&B lainnya)
            customKeywordsLokal.forEach((kw) => {
                promises.push(
                    searchPlaces({ location: loc, radius: radiusKomp, keyword: kw }).then(async res => {
                        res = await filterByRoutingDistance(loc, res, radiusKomp * 1.1);
                        kompetitor += calcDensityWeight(res);
                        addMarkers(res, 'https://maps.google.com/mapfiles/ms/icons/yellow-dot.png');
                        addRadarData('kompetitor', res);
                    })
                );
            });
        }
        await Promise.all(promises);

        if (typeof google === 'object' && typeof google.maps === 'object' && previewMap) {
            if (!window.radarPolylines) window.radarPolylines = [];
            window.radarPolylines.forEach(l => l.setMap(null));
            window.radarPolylines = [];

            // 1. SCI-FI CROSSHAIR (Garis Terpotong Dinamis)
            // Mengambil koordinat persis dari tepi Amuba (Utara: 0, Timur: 9, Selatan: 18, Barat: 27)
            const n = window.amoebaPoints && window.amoebaPoints[0] ? window.amoebaPoints[0] : computeOffset(lat, lng, 2000, 0);
            const e = window.amoebaPoints && window.amoebaPoints[9] ? window.amoebaPoints[9] : computeOffset(lat, lng, 2000, 90);
            const s = window.amoebaPoints && window.amoebaPoints[18] ? window.amoebaPoints[18] : computeOffset(lat, lng, 2000, 180);
            const w = window.amoebaPoints && window.amoebaPoints[27] ? window.amoebaPoints[27] : computeOffset(lat, lng, 2000, 270);

            const lineNS = new google.maps.Polyline({
                path: [{ lat: n.lat, lng: n.lng }, { lat, lng }, { lat: s.lat, lng: s.lng }],
                geodesic: true,
                strokeColor: '#FF0000',
                strokeOpacity: 0.8,
                strokeWeight: 2,
                map: previewMap
            });

            const lineEW = new google.maps.Polyline({
                path: [{ lat: e.lat, lng: e.lng }, { lat, lng }, { lat: w.lat, lng: w.lng }],
                geodesic: true,
                strokeColor: '#FF0000',
                strokeOpacity: 0.8,
                strokeWeight: 2,
                map: previewMap
            });

            window.radarPolylines.push(lineNS, lineEW);

            // 2. LABEL Q1-Q4 ANTI AMBIGU (Diposisikan Proporsional Dalam Amuba)
            const safeDistance = (pt) => {
                if (!pt) return 600;
                let dist = haversineDist(lat, lng, pt.lat, pt.lng);
                return Math.max(100, Math.min(dist * 0.6, 800)); // Posisikan di 60% jarak, maks 800m
            };

            const q1Dist = window.amoebaPoints ? safeDistance(window.amoebaPoints[4]) : 600;
            const q2Dist = window.amoebaPoints ? safeDistance(window.amoebaPoints[13]) : 600;
            const q3Dist = window.amoebaPoints ? safeDistance(window.amoebaPoints[22]) : 600;
            const q4Dist = window.amoebaPoints ? safeDistance(window.amoebaPoints[31]) : 600;

            const q1Pos = computeOffset(lat, lng, q1Dist, 45);
            const q2Pos = computeOffset(lat, lng, q2Dist, 135);
            const q3Pos = computeOffset(lat, lng, q3Dist, 225);
            const q4Pos = computeOffset(lat, lng, q4Dist, 315);

            const labelConfig = (text) => ({
                text: text,
                color: "#ff0000",
                fontSize: "28px",
                fontWeight: "900",
                className: "map-quadrant-label"
            });

            const invisibleIcon = {
                path: google.maps.SymbolPath.CIRCLE,
                scale: 0
            };

            const q1Label = new google.maps.Marker({ position: q1Pos, map: previewMap, label: labelConfig("Q1"), icon: invisibleIcon });
            const q2Label = new google.maps.Marker({ position: q2Pos, map: previewMap, label: labelConfig("Q2"), icon: invisibleIcon });
            const q3Label = new google.maps.Marker({ position: q3Pos, map: previewMap, label: labelConfig("Q3"), icon: invisibleIcon });
            const q4Label = new google.maps.Marker({ position: q4Pos, map: previewMap, label: labelConfig("Q4"), icon: invisibleIcon });

            window.radarPolylines.push(q1Label, q2Label, q3Label, q4Label);
        }

        document.querySelector('input[name="rumah_q1"]').value = Math.round(rumahQ1);
        document.querySelector('input[name="rumah_q2"]').value = Math.round(rumahQ2);
        document.querySelector('input[name="rumah_q3"]').value = Math.round(rumahQ3);
        document.querySelector('input[name="rumah_q4"]').value = Math.round(rumahQ4);

        document.querySelector('input[name="sekolah"]').value = Math.round(sekolah + kampus);

        // ==========================================
        // ALGORITMA ESTIMASI RUMAH (HEURISTIC)
        // ==========================================
        let hRumahBase = 600, hRumahMarket = 200, hRumahSekolah = 150, hRumahPerkantoran = 100;
        let hMotorBase = 500, hMotorMarket = 300, hMotorSekolah = 200, hMotorPerkantoran = 250;

        if (existingJsonStr && existingJsonStr !== '{}') {
            try {
                const parsed = JSON.parse(existingJsonStr);
                if (parsed.heuristics) {
                    if (parsed.heuristics.rumah_base !== undefined) hRumahBase = parsed.heuristics.rumah_base;
                    if (parsed.heuristics.rumah_market !== undefined) hRumahMarket = parsed.heuristics.rumah_market;
                    if (parsed.heuristics.rumah_sekolah !== undefined) hRumahSekolah = parsed.heuristics.rumah_sekolah;
                    if (parsed.heuristics.rumah_perkantoran !== undefined) hRumahPerkantoran = parsed.heuristics.rumah_perkantoran;

                    if (parsed.heuristics.motor_base !== undefined) hMotorBase = parsed.heuristics.motor_base;
                    if (parsed.heuristics.motor_market !== undefined) hMotorMarket = parsed.heuristics.motor_market;
                    if (parsed.heuristics.motor_sekolah !== undefined) hMotorSekolah = parsed.heuristics.motor_sekolah;
                    if (parsed.heuristics.motor_perkantoran !== undefined) hMotorPerkantoran = parsed.heuristics.motor_perkantoran;
                }
            } catch (e) { }
        }

        // Pseudo-random number generator (Deterministic) seeded by Latitude, Longitude, and Year
        const currentYear = new Date().getFullYear();
        const seedString = `${lat.toFixed(5)}-${lng.toFixed(5)}-${currentYear}`;
        let pseudoSeed = 0;
        for (let i = 0; i < seedString.length; i++) {
            pseudoSeed = ((pseudoSeed << 5) - pseudoSeed) + seedString.charCodeAt(i);
            pseudoSeed |= 0;
        }
        const seededRandom = () => {
            const x = Math.sin(pseudoSeed++) * 10000;
            return x - Math.floor(x);
        };

        // Market Cannibalization (Kompetitor memotong porsi pasar)
        const cannibalizationRatio = Math.min(0.5, (kompetitor_geprek * 0.02) + (kompetitor * 0.005));

        // Karena Places API tidak mengembalikan total bangunan, kita estimasikan secara empiris:
        let totalHouses = hRumahBase + (market * hRumahMarket) + (sekolah * hRumahSekolah) + (perkantoran * hRumahPerkantoran);
        totalHouses = totalHouses * (1 - cannibalizationRatio); // Potong karena kanibalisasi
        if (totalHouses > capRumah) {
            const excess = totalHouses - capRumah;
            totalHouses = capRumah + (excess * 0.3) + (seededRandom() * 500); // Soft cap realism
        }

        // Hitung Indikator "Kekayaan/Keramaian" area
        const elitScore = market + perkantoran + (kompetitor * 0.5);
        let pQ1 = 0.10, pQ2 = 0.25, pQ3 = 0.45, pQ4 = 0.20; // Default Suburban

        let elitThresh = 20;
        let menengahThresh = 10;
        if (existingJsonStr && existingJsonStr !== '{}') {
            try {
                const parsed = JSON.parse(existingJsonStr);
                if (parsed.elit) {
                    if (parsed.elit.threshold_elit !== undefined) elitThresh = parsed.elit.threshold_elit;
                    if (parsed.elit.threshold_menengah !== undefined) menengahThresh = parsed.elit.threshold_menengah;
                }
            } catch (e) { }
        }

        if (elitScore > elitThresh) {
            // Area Sangat Komersial / Elit
            pQ1 = 0.25; pQ2 = 0.35; pQ3 = 0.30; pQ4 = 0.10;
        } else if (elitScore > menengahThresh) {
            // Area Menengah
            pQ1 = 0.15; pQ2 = 0.30; pQ3 = 0.40; pQ4 = 0.15;
        } else {
            // Area Perkampungan Padat / Pinggiran
            pQ1 = 0.05; pQ2 = 0.15; pQ3 = 0.50; pQ4 = 0.30;
        }

        const rumah_q1 = Math.round(totalHouses * pQ1);
        const rumah_q2 = Math.round(totalHouses * pQ2);
        const rumah_q3 = Math.round(totalHouses * pQ3);
        const rumah_q4 = Math.round(totalHouses * pQ4);

        // ==========================================
        // ALGORITMA ESTIMASI TRAFFIC (HEURISTIC)
        // ==========================================
        let baseMotor = hMotorBase + (sekolah * hMotorSekolah) + (market * hMotorMarket) + (perkantoran * hMotorPerkantoran);
        baseMotor = baseMotor * (1 - cannibalizationRatio); // Potong karena kanibalisasi
        if (baseMotor > capMotor) {
            const excess = baseMotor - capMotor;
            baseMotor = capMotor + (excess * 0.3) + (seededRandom() * 500); // Soft cap realism
        }

        const vary = (val) => Math.round(val * (0.8 + seededRandom() * 0.4));

        let tWdPagi = 0.40, tWdSiang = 0.20, tWdSore = 0.40;
        let tWePagi = 0.15, tWeSiang = 0.35, tWeSore = 0.50;

        // Dynamic Traffic Curve based on Demographic
        const totalFasum = sekolah + kampus + perkantoran + market + 1;
        const ratioSekolah = (sekolah + kampus) / totalFasum;
        const ratioKantor = perkantoran / totalFasum;
        const ratioMarket = market / totalFasum;

        if (ratioKantor > 0.35) {
            // Dominan Perkantoran
            tWdPagi = 0.45; tWdSiang = 0.10; tWdSore = 0.45;
            tWePagi = 0.10; tWeSiang = 0.20; tWeSore = 0.10; // Weekend sangat sepi
        } else if (ratioMarket > 0.35) {
            // Dominan Pasar / Mall
            tWdPagi = 0.20; tWdSiang = 0.30; tWdSore = 0.50;
            tWePagi = 0.20; tWeSiang = 0.40; tWeSore = 0.60; // Weekend meledak
        } else if (ratioSekolah > 0.35) {
            // Dominan Sekolah / Kampus
            tWdPagi = 0.35; tWdSiang = 0.45; tWdSore = 0.20; // Siang meledak (pulang sekolah)
            tWePagi = 0.15; tWeSiang = 0.25; tWeSore = 0.30; // Weekend agak sepi
        }

        if (existingJsonStr && existingJsonStr !== '{}') {
            try {
                const parsed = JSON.parse(existingJsonStr);
                if (parsed.traffic) {
                    if (parsed.traffic.wd_pagi !== undefined) tWdPagi = parsed.traffic.wd_pagi / 100;
                    if (parsed.traffic.wd_siang !== undefined) tWdSiang = parsed.traffic.wd_siang / 100;
                    if (parsed.traffic.wd_sore !== undefined) tWdSore = parsed.traffic.wd_sore / 100;

                    if (parsed.traffic.we_pagi !== undefined) tWePagi = parsed.traffic.we_pagi / 100;
                    if (parsed.traffic.we_siang !== undefined) tWeSiang = parsed.traffic.we_siang / 100;
                    if (parsed.traffic.we_sore !== undefined) tWeSore = parsed.traffic.we_sore / 100;
                }
            } catch (e) { }
        }

        const motor_wd_pagi = vary(baseMotor * tWdPagi);
        const motor_wd_siang = vary(baseMotor * tWdSiang);
        const motor_wd_sore = vary(baseMotor * tWdSore);

        const motor_we_pagi = vary(baseMotor * tWePagi);
        const motor_we_siang = vary(baseMotor * tWeSiang);
        const motor_we_sore = vary(baseMotor * tWeSore);

        let pBase = 0.05, pSekolah = 0.01, pMarket = 0.005, pMax = 0.15;
        if (existingJsonStr && existingJsonStr !== '{}') {
            try {
                const parsed = JSON.parse(existingJsonStr);
                if (parsed.pedestrian) {
                    if (parsed.pedestrian.base !== undefined) pBase = parsed.pedestrian.base;
                    if (parsed.pedestrian.sekolah !== undefined) pSekolah = parsed.pedestrian.sekolah;
                    if (parsed.pedestrian.market !== undefined) pMarket = parsed.pedestrian.market;
                    if (parsed.pedestrian.max_cap !== undefined) pMax = parsed.pedestrian.max_cap;
                }
            } catch (e) { }
        }

        let pedRatio = pBase + (sekolah * pSekolah) + (market * pMarket);
        if (pedRatio > pMax) pedRatio = pMax;

        const pejalan_wd_pagi = vary(motor_wd_pagi * pedRatio);
        const pejalan_wd_siang = vary(motor_wd_siang * pedRatio * 1.5);
        const pejalan_wd_sore = vary(motor_wd_sore * pedRatio);

        const pejalan_we_pagi = vary(motor_we_pagi * pedRatio);
        const pejalan_we_siang = vary(motor_we_siang * pedRatio * 1.5);
        const pejalan_we_sore = vary(motor_we_sore * pedRatio * 1.5);

        // Auto-fill form (with visual indicator)
        const setVal = (name, val) => {
            const el = document.querySelector(`[name="${name}"]`);
            if (el) {
                el.value = val;
                el.style.backgroundColor = '#e8f5e9';
                setTimeout(() => el.style.backgroundColor = '', 2000);
            }
        };

        setVal('sekolah', Math.round(sekolah));
        setVal('kampus', Math.round(kampus));
        setVal('pabrik', Math.round(pabrik));
        setVal('kesehatan', Math.round(kesehatan));
        setVal('market', Math.round(market));
        setVal('perkantoran', Math.round(perkantoran));
        setVal('kompetitor_lokal', Math.round(kompetitor));
        setVal('kompetitor_geprek', Math.round(kompetitor_geprek));

        setVal('rumah_q1', Math.round(rumahQ1));
        setVal('rumah_q2', Math.round(rumahQ2));
        setVal('rumah_q3', Math.round(rumahQ3));
        setVal('rumah_q4', Math.round(rumahQ4));

        // --- 3. QUADRANT PROFILING (GELAR KUADRAN) ---
        const qProfiles = [
            { id: 'Q1', rumah: rumahQ1, name: 'Timur Laut' },
            { id: 'Q2', rumah: rumahQ2, name: 'Tenggara' },
            { id: 'Q3', rumah: rumahQ3, name: 'Barat Daya' },
            { id: 'Q4', rumah: rumahQ4, name: 'Barat Laut' }
        ];
        qProfiles.sort((a, b) => b.rumah - a.rumah);
        const topQ = qProfiles[0];
        const totalRumah = rumahQ1 + rumahQ2 + rumahQ3 + rumahQ4;

        const qAlertEl = document.getElementById('quadrantProfileAlert');
        const qMsgEl = document.getElementById('quadrantProfileMessage');
        const qIconEl = document.getElementById('qProfileIcon');

        if (qAlertEl && qMsgEl && totalRumah > 0) {
            const dominance = topQ.rumah / totalRumah;
            qAlertEl.classList.remove('d-none', 'alert-info', 'alert-warning', 'alert-danger');

            if (dominance >= 0.70) {
                qAlertEl.classList.add('alert-danger');
                qIconEl.className = 'bi bi-exclamation-triangle-fill fs-3 me-3 text-danger';
                qMsgEl.innerHTML = `<span class="text-danger fw-bold">Peringatan Resiko (-5% Score):</span> <b>${topQ.id} (${topQ.name})</b> memonopoli ${Math.round(dominance * 100)}% pasar! Lokasi sangat rapuh karena mengandalkan satu arah saja.`;
            } else if (dominance >= 0.40) {
                qAlertEl.classList.add('alert-info');
                qIconEl.className = 'bi bi-compass fs-3 me-3 text-info';
                qMsgEl.innerHTML = `<b>${topQ.id} (${topQ.name})</b> adalah <b>Pusat Populasi Utama</b> (${Math.round(dominance * 100)}%). Fokuskan tebar flyer pemasaran ke arah ini.`;
            } else {
                qAlertEl.classList.add('alert-success');
                qIconEl.className = 'bi bi-check-circle fs-3 me-3 text-success';
                qMsgEl.innerHTML = `Pasar menyebar sehat di semua arah. Tidak ada dominasi berbahaya.`;
            }
        }

        const skippedDays = [];
        if (typeof window.lockedDays !== 'undefined' && window.lockedDays.has('weekday')) {
            skippedDays.push('Weekday');
        } else {
            setVal('motor_weekday_pagi', motor_wd_pagi);
            setVal('motor_weekday_siang', motor_wd_siang);
            setVal('motor_weekday_sore', motor_wd_sore);
            setVal('pejalan_weekday_pagi', pejalan_wd_pagi);
            setVal('pejalan_weekday_siang', pejalan_wd_siang);
            setVal('pejalan_weekday_sore', pejalan_wd_sore);
        }
        if (typeof window.lockedDays !== 'undefined' && window.lockedDays.has('weekend')) {
            skippedDays.push('Weekend');
        } else {
            setVal('motor_weekend_pagi', motor_we_pagi);
            setVal('motor_weekend_siang', motor_we_siang);
            setVal('motor_weekend_sore', motor_we_sore);
            setVal('pejalan_weekend_pagi', pejalan_we_pagi);
            setVal('pejalan_weekend_siang', pejalan_we_siang);
            setVal('pejalan_weekend_sore', pejalan_we_sore);

            let kKomp = document.querySelector('input[name="total_kompetitor"]');
            let totalKomp = kompetitor + kompetitor_geprek;
            if (kKomp) kKomp.value = totalKomp;

            // --- 1. OTOMATISASI FISIK BANGUNAN VIA GEOCODER ---
            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ location: loc }, (results, status) => {
                if (status === 'OK' && results[0]) {
                    const routeObj = results[0].address_components.find(c => c.types.includes('route'));
                    if (routeObj) {
                        const routeName = routeObj.short_name.toLowerCase();
                        if (routeName.includes('raya') || routeName.includes('nasional') || routeName.includes('jend')) {
                            document.querySelector('input[name="lebar_jalan"]').value = 8;
                            document.querySelector('input[name="jenis_jalan"][value="2 Arah"]').checked = true;
                            document.querySelector('input[name="terlihat_jalan_utama"][value="1"]').checked = true;
                            document.querySelector('input[name="akses_mobil"][value="1"]').checked = true;
                        } else if (routeName.includes('gg') || routeName.includes('gang')) {
                            document.querySelector('input[name="lebar_jalan"]').value = 2;
                            document.querySelector('input[name="jenis_jalan"][value="1 Arah"]').checked = true;
                            document.querySelector('input[name="terlihat_jalan_utama"][value="0"]').checked = true;
                            document.querySelector('input[name="akses_mobil"][value="0"]').checked = true;
                        } else {
                            document.querySelector('input[name="lebar_jalan"]').value = 5;
                            document.querySelector('input[name="jenis_jalan"][value="2 Arah"]').checked = true;
                            document.querySelector('input[name="terlihat_jalan_utama"][value="0"]').checked = true;
                            document.querySelector('input[name="akses_mobil"][value="1"]').checked = true;
                        }
                    }
                }

                // --- 2. OTOMATISASI POSISI HOOK VIA INTERSECTION SEARCH ---
                searchPlaces({ location: loc, radius: 30, type: 'intersection' }).then(intersections => {
                    if (intersections && intersections.length > 0) {
                        document.querySelector('input[name="posisi_hook"][value="1"]').checked = true;
                    } else {
                        document.querySelector('input[name="posisi_hook"][value="0"]').checked = true;
                    }

                    // --- 3. OTOMATISASI TARGET KONTRIBUSI OMSET (ORGANIK VS OJOL) ---
                    // Organik Ecosystem: Sekolah, Kampus, Market, Perkantoran
                    const organikScore = (sekolah * 1.5) + (kampus * 2) + market + (perkantoran * 1.2);
                    // Ojol Ecosystem: Kesehatan + Perumahan/Apartemen (diambil dari radius 2.5km)
                    const ojolScore = (kesehatan * 1.5) + (window.tempPerumahanDensity || 0) + (window.tempApartemenDensity || 0);

                    let rasioOrganik = 85; // Base default
                    if (organikScore > 0 || ojolScore > 0) {
                        // Hitung persentase dominasi (dengan batas minimum 50% organik, maksimal 95%)
                        const totalEcosystem = organikScore + ojolScore;
                        const dynamicOrganik = Math.round((organikScore / totalEcosystem) * 100);

                        // Normalisasi agar tidak terlalu ekstrim
                        rasioOrganik = Math.max(50, Math.min(95, dynamicOrganik));

                        // Jika ojol sangat dominan (di atas 40%), kita beri porsi ojol lebih besar
                        if (ojolScore > organikScore * 1.5) {
                            rasioOrganik = Math.max(50, 85 - Math.round((ojolScore / totalEcosystem) * 30));
                        }
                    }

                    const orgInput = document.getElementById('config_rasio_organik');
                    if (orgInput) {
                        orgInput.value = rasioOrganik;
                        orgInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }

                    renderRadarReport();

                    hitungLiveScore();
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    alert('Pemindaian Radar AI & Intelligence Report Selesai!');
                });
            }).catch(e => {
                hitungLiveScore();
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }

        hitungLiveScore();

        // ==========================================
        // AI ML PREDICTION FETCH (Fase 3)
        // ==========================================
        try {
            document.getElementById('omsetPerhariDisplay').innerText = "AI Memprediksi...";
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            const response = await fetch('/surveyor/ai-predict', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    sekolah: sekolah,
                    kampus: kampus,
                    kompetitor: kompetitor_geprek + kompetitor
                })
            });
            const data = await response.json();

            if (data.status === 'success') {
                window.aiPredictedOmsetHarian = data.predicted_omset_harian;
                // Recalculate score again using AI prediction override
                hitungLiveScore();

                alert(`Radar GIS Google Maps selesai!\n\n🤖 Prediksi Omset AI K-NN Berhasil!\nKembaran Terdekat: ${data.k_neighbors.join(', ')}\n\nFasilitas (${radiusFasum / 1000}km Radius):\n- ${sekolah} Sekolah, ${kampus} Kampus\n- ${kesehatan} FasKes, ${market} Market\n- ${perkantoran} Perkantoran, ${pabrik} Pabrik\n- ${kompetitor_geprek + kompetitor} Total Kompetitor (${radiusKomp / 1000}km Radius)`);
            }
        } catch (e) {
            console.error("AI Prediction Error", e);
            let alertMsg = `Radar GIS Google Maps selesai!\n\nEstimasi ${totalHouses} Rumah Penduduk.\nDistribusi: Q1: ${rumah_q1}, Q2: ${rumah_q2}, Q3: ${rumah_q3}, Q4: ${rumah_q4}\n\nFasilitas (${radiusFasum / 1000}km Radius):\n- ${sekolah} Sekolah, ${kampus} Kampus\n- ${kesehatan} FasKes\n- ${market} Market\n- ${perkantoran} Perkantoran\n- ${pabrik} Pabrik\n- ${kompetitor_geprek} Kompetitor Geprek (${radiusKomp / 1000}km Radius)`;
            if (skippedDays.length > 0) {
                alertMsg += `\n\nTraffic ${skippedDays.join(' & ')} TIDAK diubah karena dikunci video.`;
            }
            alert(alertMsg);
        }

    } catch (err) {
        console.error(err);
        alert('Terjadi kesalahan saat memindai Google Maps API. Cek koneksi Anda.');
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}

function addMarkers(places, iconUrl) {
    if (!previewMap) return;
    places.forEach(place => {
        if (!place.geometry || !place.geometry.location) return;
        const marker = new google.maps.Marker({
            position: place.geometry.location,
            map: previewMap,
            title: place.name,
            icon: {
                url: iconUrl,
                scaledSize: new google.maps.Size(24, 24)
            }
        });
        previewMapMarkers.push(marker);
    });
}


// ─────────────────────────────────────────────────────────────────────────────
// JAM RAMAI (ESTIMASI DARI DATA TRAFFIC)
// ─────────────────────────────────────────────────────────────────────────────
function synthesizeTrafficCurveJS(pagi, siang, sore, day) {
    let baseCurve = {};

    // Google Maps-like organic curves per day
    if (day === 'Jum') {
        // Friday - evening peak is higher and stretches later
        baseCurve = {
            6: 0.2, 7: 0.5, 8: 0.7, 9: 0.5, 10: 0.4,
            11: 0.6, 12: 1.0, 13: 1.0, 14: 0.7, 15: 0.6,
            16: 0.8, 17: 0.9, 18: 1.0, 19: 1.2, 20: 1.1, 21: 0.9, 22: 0.6, 23: 0.4
        };
    } else if (day === 'Sab') {
        // Saturday - slow morning, steady afternoon, very high night
        baseCurve = {
            6: 0.1, 7: 0.2, 8: 0.4, 9: 0.6, 10: 0.7,
            11: 0.8, 12: 1.0, 13: 1.0, 14: 0.9, 15: 0.9,
            16: 1.0, 17: 1.0, 18: 1.1, 19: 1.3, 20: 1.4, 21: 1.2, 22: 0.9, 23: 0.6
        };
    } else if (day === 'Min') {
        // Sunday - morning activity (sports/CFD), high lunch, tapers early night
        baseCurve = {
            6: 0.4, 7: 0.7, 8: 0.9, 9: 0.8, 10: 0.7,
            11: 0.8, 12: 1.0, 13: 0.9, 14: 0.8, 15: 0.7,
            16: 0.8, 17: 0.9, 18: 0.9, 19: 0.8, 20: 0.6, 21: 0.4, 22: 0.2, 23: 0.1
        };
    } else {
        // Standard Weekday (Sen, Sel, Rab, Kam) - typical commute patterns
        baseCurve = {
            6: 0.2, 7: 0.6, 8: 0.8, 9: 0.5, 10: 0.4,
            11: 0.6, 12: 1.0, 13: 0.8, 14: 0.6, 15: 0.5,
            16: 0.7, 17: 0.9, 18: 1.0, 19: 0.8, 20: 0.6, 21: 0.4, 22: 0.3, 23: 0.1
        };
    }

    // Dynamically calculate sums so the distribution is perfectly accurate to the inputs
    let sumMorning = 0, sumNoon = 0, sumEvening = 0;
    for (let h = 6; h <= 10; h++) sumMorning += baseCurve[h];
    for (let h = 11; h <= 15; h++) sumNoon += baseCurve[h];
    for (let h = 16; h <= 23; h++) sumEvening += baseCurve[h];

    sumMorning = sumMorning || 1;
    sumNoon = sumNoon || 1;
    sumEvening = sumEvening || 1;

    const hourlyData = {};
    for (let h = 6; h <= 23; h++) {
        let count = 0;
        if (h <= 10) count = pagi * (baseCurve[h] / sumMorning);
        else if (h <= 15) count = siang * (baseCurve[h] / sumNoon);
        else count = sore * (baseCurve[h] / sumEvening);

        // Add tiny 2-4% organic variance to make it look realistic
        let variance = 1.0 + ((Math.random() * 0.08) - 0.04);
        hourlyData[h] = Math.max(0, Math.round(count * variance));
    }
    return hourlyData;
}

function updateJamRamai() {
    const activeDayEl = document.querySelector('.jam-ramai-day.active');
    const day = activeDayEl ? activeDayEl.dataset.day : 'Sen';
    const isWeekend = ['Sab', 'Min'].includes(day);

    // Ambil data traffic input
    const mPagi = getVal(isWeekend ? 'motor_weekend_pagi' : 'motor_weekday_pagi');
    const mSiang = getVal(isWeekend ? 'motor_weekend_siang' : 'motor_weekday_siang');
    const mSore = getVal(isWeekend ? 'motor_weekend_sore' : 'motor_weekday_sore');

    const pPagi = getVal(isWeekend ? 'pejalan_weekend_pagi' : 'pejalan_weekday_pagi');
    const pSiang = getVal(isWeekend ? 'pejalan_weekend_siang' : 'pejalan_weekday_siang');
    const pSore = getVal(isWeekend ? 'pejalan_weekend_sore' : 'pejalan_weekday_sore');

    // Pass the specific day so the curve matches the day profile!
    const motorCurve = synthesizeTrafficCurveJS(mPagi, mSiang, mSore, day);
    const pejalanCurve = synthesizeTrafficCurveJS(pPagi, pSiang, pSore, day);

    const hoursData = [];
    let maxVal = 0;

    for (let h = 6; h <= 23; h++) {
        let motor = motorCurve[h];
        let pejalan = pejalanCurve[h];

        if (motor > maxVal) maxVal = motor;
        if (pejalan > maxVal) maxVal = pejalan;
        hoursData.push({ hour: h, motor: motor, pejalan: pejalan });
    }

    const chart = document.getElementById('jamRamaiChart');
    if (!chart) return;

    chart.innerHTML = '';

    if (maxVal === 0) {
        chart.innerHTML = '<div style="width:100%; text-align:center; color:#9aa0a6; font-size:12px; margin-bottom:10px;">Isi data traffic untuk melihat estimasi jam ramai</div>';
        return;
    }

    hoursData.forEach(d => {
        const heightM = (d.motor / maxVal) * 100;
        const heightP = (d.pejalan / maxVal) * 100; // Relative to max motor

        const hourFmt = d.hour.toString().padStart(2, '0') + ':00';

        const group = document.createElement('div');
        group.className = 'jam-ramai-bar-group';

        const barM = document.createElement('div');
        barM.className = 'jam-ramai-bar';
        barM.style.height = Math.max(2, heightM) + '%';

        const barP = document.createElement('div');
        barP.className = 'jam-ramai-bar pejalan';
        barP.style.height = Math.max(0, heightP) + '%';

        const tooltip = document.createElement('div');
        tooltip.className = 'jam-ramai-tooltip';
        tooltip.innerText = `${hourFmt}\nMotor: ${d.motor}\nPejalan: ${d.pejalan}`;

        group.appendChild(barM);
        group.appendChild(barP);
        group.appendChild(tooltip);
        chart.appendChild(group);
    });
}

function ambilTitikGPS() {
    if (!navigator.geolocation) { alert('Browser tidak mendukung GPS.'); return; }
    navigator.geolocation.getCurrentPosition(function (pos) {
        const lat = pos.coords.latitude.toFixed(7);
        const lng = pos.coords.longitude.toFixed(7);
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;
        updateMapsPreview();
        autoFillAlamat(lat, lng);
    }, function () {
        alert('Gagal mengambil lokasi. Pastikan izin lokasi browser aktif.');
    }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 });
}

// ─────────────────────────────────────────────────────────────────────────────
// INIT
// ─────────────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.calc-input, .cell-input, .config-weight, .config-ratio, .config-heuristic, .config-cap, .config-target, #latitude, #longitude').forEach(function (el) {
        el.addEventListener('input', hitungLiveScore);
        el.addEventListener('change', hitungLiveScore); // for radios/checkboxes
    });

    document.getElementById('maps_url').addEventListener('input', async function (e) {
        const val = e.target.value.trim();
        if (!val) return;

        // Try direct regex first (for long URLs)
        const match = val.match(/@(-?\d+\.\d+),(-?\d+\.\d+)/);
        if (match) {
            document.getElementById('latitude').value = match[1];
            document.getElementById('longitude').value = match[2];
            updateMapsPreview();
            autoFillAlamat(match[1], match[2]);
            return;
        }

        // If it looks like a Google Maps link but no coordinates found (likely shortlink)
        if (val.includes('maps.app.goo.gl') || val.includes('goo.gl/maps') || val.includes('maps.google.com')) {
            const previewContainer = document.getElementById('mapPreview');
            previewContainer.style.padding = '18px';
            previewContainer.innerHTML = '<div><div class="spinner-border text-primary" role="status"></div><div class="mt-2 fw-bold text-primary">Mengekstrak Koordinat...</div><div class="small">Sedang membaca link Maps</div></div>';

            try {
                // Tambahkan timestamp untuk mencegah browser melakukan caching pada response lama
                const cacheBuster = new Date().getTime();
                const response = await fetch(`${window.SiteScoreConfig.routes.resolveMapsUrl}?url=${encodeURIComponent(val)}&_t=${cacheBuster}`);
                const data = await response.json();

                if (data.lat && data.lng) {
                    document.getElementById('latitude').value = data.lat;
                    document.getElementById('longitude').value = data.lng;
                    updateMapsPreview();
                    autoFillAlamat(data.lat, data.lng);
                } else {
                    alert('Gagal menemukan titik koordinat dari link tersebut. Mohon isi Latitude & Longitude secara manual.');
                    updateMapsPreview(); // reset view
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan jaringan saat mengekstrak koordinat.');
                updateMapsPreview(); // reset view
            }
        }
    });

    // Setup jam ramai day selector
    document.querySelectorAll('.jam-ramai-day').forEach(el => {
        el.addEventListener('click', function () {
            document.querySelectorAll('.jam-ramai-day').forEach(d => d.classList.remove('active'));
            this.classList.add('active');
            updateJamRamai();
        });
    });

    document.querySelectorAll('[name="tipe_outlet"]').forEach(function (el) {
        el.addEventListener('change', function () {
            setTimeout(hitungLiveScore, 0);
        });
    });
    document.querySelector('[name="harga_kompetitor"]').addEventListener('input', hitungLiveScore);
    hitungLiveScore();

    // --- VIDEO DETECTION AI MODAL ---
    let currentAiJobId = null;
    let aiPollInterval = null;

    window.openAiModal = function () {
        const aiModal = new bootstrap.Modal(document.getElementById('aiDetectionModal'));
        document.getElementById('aiDetectionForm').reset();
        document.getElementById('aiDetectionForm').classList.remove('d-none');
        document.getElementById('aiProgressArea').classList.add('d-none');
        document.getElementById('aiResultArea').classList.add('d-none');
        aiModal.show();
    };


    window.toggleAiSource = function () {
        const type = document.getElementById('aiSourceType').value;
        if (type === 'upload') {
            document.getElementById('aiUploadWrapper').classList.remove('d-none');
            document.getElementById('aiDriveWrapper').classList.add('d-none');
            document.getElementById('aiDriveUrl').value = '';
        } else {
            document.getElementById('aiUploadWrapper').classList.add('d-none');
            document.getElementById('aiDriveWrapper').classList.remove('d-none');
            document.getElementById('aiVideoFile').value = '';
        }
    };


    // FITUR BARU: Auto-detect Hari dan Rentang Jam dari Metadata Video
    const aiVideoFileInput = document.getElementById('aiVideoFile');
    if (aiVideoFileInput) {
        aiVideoFileInput.addEventListener('change', function (e) {
            if (e.target.files && e.target.files.length > 0) {
                const file = e.target.files[0];
                if (file.lastModified) {
                    const dateObj = new Date(file.lastModified);

                    // 1. Set Hari (Weekday/Weekend)
                    const dayOfWeek = dateObj.getDay(); // 0 = Sunday, 6 = Saturday
                    const isWeekend = (dayOfWeek === 0 || dayOfWeek === 6);
                    const dayEl = document.getElementById('aiDayType');
                    if (dayEl) dayEl.value = isWeekend ? 'weekend' : 'weekday';

                    // 2. Set Rentang Jam
                    const hour = dateObj.getHours();
                    const hourEl = document.getElementById('aiHourRange');
                    if (hourEl) hourEl.value = hour.toString();

                    // Optional Feedback
                    const helperId = 'aiAutoDetectHelper';
                    let helperEl = document.getElementById(helperId);
                    const dayName = dateObj.toLocaleDateString('id-ID', { weekday: 'long' });
                    const timeStr = dateObj.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                    const helperText = `<i class="bi bi-magic"></i> Terdeteksi otomatis dari waktu rekaman video (${dayName}, ${timeStr})`;

                    if (!helperEl) {
                        helperEl = document.createElement('small');
                        helperEl.id = helperId;
                        helperEl.className = 'text-success d-block mt-1 fw-bold';
                        helperEl.innerHTML = helperText;
                        hourEl.parentNode.appendChild(helperEl);
                    } else {
                        helperEl.innerHTML = helperText;
                    }
                }
            }
        });
    }

    const aiForm = document.getElementById('aiDetectionForm');
    if (aiForm) {
        aiForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const sourceType = document.getElementById('aiSourceType').value;
            const fileInput = document.getElementById('aiVideoFile');
            const driveUrl = document.getElementById('aiDriveUrl').value;

            if (sourceType === 'upload' && !fileInput.files.length) { alert('Pilih video terlebih dahulu!'); return; }
            if (sourceType === 'google_drive' && !driveUrl) { alert('Masukkan link Google Drive!'); return; }

            const formData = new FormData();
            formData.append('_token', document.querySelector('input[name="_token"]').value);
            formData.append('source_type', sourceType);
            if (sourceType === 'upload') formData.append('video_file', fileInput.files[0]);
            if (sourceType === 'google_drive') formData.append('google_drive_url', driveUrl);
            formData.append('lokasi', 'AI Draft Analysis');

            // Sembunyikan Modal, Munculkan Toast Melayang
            const aiModalEl = document.getElementById('aiDetectionModal');
            const modal = bootstrap.Modal.getInstance(aiModalEl) || new bootstrap.Modal(aiModalEl);
            modal.hide();

            document.getElementById('aiFloatingToast').style.display = 'block';
            document.getElementById('aiToastProgressArea').classList.remove('d-none');
            document.getElementById('aiToastResultArea').classList.add('d-none');
            document.getElementById('aiToastStatus').innerText = 'Sedang Mengirim Data...';

            fetch(window.SiteScoreConfig.routes.videoSubmit, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        currentAiJobId = data.job_id;
                        localStorage.setItem('ai_polling_job_id', currentAiJobId);

                        // BUG FIX: Track active job for background persistence (page refresh/navigation)
                        const dayType = document.getElementById('aiDayType')?.value || 'weekday';
                        const hour = parseInt(document.getElementById('aiHourRange')?.value) || 0;
                        window.addActiveJob(currentAiJobId, dayType, hour);

                        document.getElementById('aiToastStatus').innerText = 'Video sedang dianalisis oleh AI...';
                        startAiPolling();
                    } else {
                        alert('Gagal mengirim video: ' + (data.message || 'Unknown error'));
                        document.getElementById('aiFloatingToast').style.display = 'none';
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Terjadi kesalahan jaringan saat mengirim video.');
                    document.getElementById('aiFloatingToast').style.display = 'none';
                });
        });
    }

    window.lastAiData = null;

    // ========================================================
    // SISTEM KALIBRASI TRAFFIC: Video (Aktual) + Radar (Estimasi)
    // ========================================================
    // videoStore menyimpan base-unit dari setiap video per hari.
    // Beberapa video pada hari yang sama dirata-rata agar makin akurat.
    window.videoStore = {
        weekday: { motorBases: [], pejalanBases: [] },
        weekend: { motorBases: [], pejalanBases: [] },
    };
    window.lockedDays = new Set(); // 'weekday' dan/atau 'weekend'

    // Hourly multiplier & period hours (sudah didefinisikan di bawah, referensi di sini)
    window.calibPeriodHours = {
        pagi: [6, 7],
        siang: [11, 12],
        sore: [17, 18, 19],
    };

    // ========================================================
    // BUG FIX: Multiple Active Jobs Tracking (Background Persistence)
    // ========================================================
    window.activeJobs = []; // Track multiple active analysis jobs

    window.addActiveJob = function (jobId, dayType, hour) {
        const job = { jobId, dayType, hour, uploadedAt: new Date().toISOString() };
        window.activeJobs.push(job);
        localStorage.setItem('ai_jobs_active', JSON.stringify(window.activeJobs));
    };

    window.removeActiveJob = function (jobId) {
        window.activeJobs = window.activeJobs.filter(j => j.jobId !== jobId);
        localStorage.setItem('ai_jobs_active', JSON.stringify(window.activeJobs));
    };

    window.loadActiveJobs = function () {
        const stored = localStorage.getItem('ai_jobs_active');
        if (stored) {
            try {
                window.activeJobs = JSON.parse(stored);
            } catch (e) {
                window.activeJobs = [];
            }
        }
    };

    window.applyDayFromVideo = function (dayType) {
        const store = window.videoStore[dayType];
        if (!store.motorBases.length) return;

        // Rata-rata base unit dari semua video
        const avgMotorBase = store.motorBases.reduce((a, b) => a + b, 0) / store.motorBases.length;
        const avgPejalanBase = store.pejalanBases.reduce((a, b) => a + b, 0) / store.pejalanBases.length;

        const mArr = dayType === 'weekend' ? hourlyMultiplier.weekend : hourlyMultiplier.weekday;
        const sumPeriod = (base, hours) => hours.reduce((t, h) => t + base * (mArr[h] || 0), 0);

        const prefix = dayType;
        const setField = (name, val) => {
            const el = document.querySelector(`input[name="${name}"]`);
            if (el) {
                el.value = Math.round(val);
                el.style.border = '2px solid #1976d2';
                el.title = `Data video aktual (${store.motorBases.length} video)`;
                el.dataset.locked = 'true';
            }
        };

        setField(`motor_${prefix}_pagi`, sumPeriod(avgMotorBase, window.calibPeriodHours.pagi));
        setField(`motor_${prefix}_siang`, sumPeriod(avgMotorBase, window.calibPeriodHours.siang));
        setField(`motor_${prefix}_sore`, sumPeriod(avgMotorBase, window.calibPeriodHours.sore));

        setField(`pejalan_${prefix}_pagi`, sumPeriod(avgPejalanBase, window.calibPeriodHours.pagi));
        setField(`pejalan_${prefix}_siang`, sumPeriod(avgPejalanBase, window.calibPeriodHours.siang));
        setField(`pejalan_${prefix}_sore`, sumPeriod(avgPejalanBase, window.calibPeriodHours.sore));

        window.lockedDays.add(dayType);
        window.updateCalibrationBadges();
        window.saveCalibrationToHidden();

        if (typeof hitungLiveScore === 'function') hitungLiveScore();
        if (typeof updateJamRamai === 'function') updateJamRamai();
    }

    window.updateCalibrationBadges = function () {
        // Tampilkan badge kunci di header traffic jika ada hari terkunci
        let badge = document.getElementById('calibrationBadge');
        if (!badge) {
            const trafficHeader = document.querySelector('.card-header-traffic-motor') || document.querySelector('[data-section="traffic"]');
            if (trafficHeader) {
                badge = document.createElement('span');
                badge.id = 'calibrationBadge';
                badge.className = 'badge bg-info ms-2';
                trafficHeader.appendChild(badge);
            }
        }
        if (badge) {
            const parts = [];
            if (window.lockedDays.has('weekday')) parts.push('Weekday 🔒');
            if (window.lockedDays.has('weekend')) parts.push('Weekend 🔒');
            badge.innerHTML = parts.length ? parts.join(' | ') + ' (Data Video)' : '';
            badge.style.display = parts.length ? 'inline-block' : 'none';
        }
    }

    window.saveCalibrationToHidden = function () {
        const data = {
            videoStore: window.videoStore,
            lockedDays: Array.from(window.lockedDays),
        };
        const el = document.getElementById('trafficCalibrationJson');
        if (el) el.value = JSON.stringify(data);
    }

    window.resetKalibrasiTraffic = function () {
        if (!confirm('Reset kalibrasi? Semua data video akan dihapus dan Radar Fasilitas bisa mengisi ulang traffic.')) return;
        window.videoStore.weekday.motorBases = [];
        window.videoStore.weekday.pejalanBases = [];
        window.videoStore.weekend.motorBases = [];
        window.videoStore.weekend.pejalanBases = [];
        window.lockedDays.clear();

        // Hapus indikator visual
        document.querySelectorAll('input[data-locked="true"]').forEach(el => {
            el.style.border = '';
            el.title = '';
            el.dataset.locked = '';
        });
        window.updateCalibrationBadges();
        window.saveCalibrationToHidden();
        alert('Kalibrasi direset. Anda bisa klik Radar Fasilitas untuk mengisi estimasi baru.');
    };

    document.addEventListener('DOMContentLoaded', function () {
        // BUG FIX: Load active jobs from localStorage for background persistence
        window.loadActiveJobs();

        // BUG FIX: Show toast if there are active jobs (even after page navigation)
        if (window.activeJobs && window.activeJobs.length > 0) {
            const latestJob = window.activeJobs[window.activeJobs.length - 1];
            currentAiJobId = latestJob.jobId;
            localStorage.setItem('ai_polling_job_id', currentAiJobId);

            document.getElementById('aiFloatingToast').style.display = 'block';
            document.getElementById('aiToastProgressArea').classList.remove('d-none');
            document.getElementById('aiToastResultArea').classList.add('d-none');
            document.getElementById('aiToastStatus').innerText = `Melanjutkan analisis AI... (${window.activeJobs.length} job aktif)`;
            startAiPolling();
            return;
        }

        const savedJobId = localStorage.getItem('ai_polling_job_id');
        if (savedJobId) {
            currentAiJobId = savedJobId;
            document.getElementById('aiFloatingToast').style.display = 'block';
            document.getElementById('aiToastProgressArea').classList.remove('d-none');
            document.getElementById('aiToastResultArea').classList.add('d-none');
            document.getElementById('aiToastStatus').innerText = 'Melanjutkan analisis AI...';
            startAiPolling();
        }

        // Pre-submit: pastikan calibration JSON tersimpan
        const mainForm = document.getElementById('siteScoreForm');
        if (mainForm) {
            mainForm.addEventListener('submit', function () {
                window.saveCalibrationToHidden();
            });
        }
    });

    function startAiPolling() {
        if (aiPollInterval) clearInterval(aiPollInterval);

        aiPollInterval = setInterval(() => {
            if (!currentAiJobId) return;

            fetch(`${window.SiteScoreConfig.routes.videoStatus}/${currentAiJobId}`, {
                headers: { 'Accept': 'application/json' }
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'done') {
                        clearInterval(aiPollInterval);
                        document.getElementById('aiFloatingToast').style.display = 'none';
                        window.lastAiData = data;

                        if (typeof applyVideoResults === 'function') {
                            applyVideoResults(data);
                        } else {
                            document.getElementById('aiToastProgressArea').classList.add('d-none');
                            document.getElementById('aiToastResultArea').classList.remove('d-none');
                            document.getElementById('aiToastMotor').innerText = data.counts?.motorcycle || 0;
                            document.getElementById('aiToastPerson').innerText = data.counts?.person || 0;
                        }
                    } else if (data.status === 'failed') {
                        clearInterval(aiPollInterval);
                        alert('Analisis gagal: ' + data.message);
                        document.getElementById('aiFloatingToast').style.display = 'none';
                    }
                })
                .catch(err => {
                    console.error('Polling error:', err);
                    clearInterval(aiPollInterval);
                    alert('Error polling analysis status. Please try uploading again.');
                    document.getElementById('aiFloatingToast').style.display = 'none';
                });
        }, 5000);
    }

    function resetAiModal() {
        // Obsolete
    }

    window.applyAiResult = function () {
        const dayType = document.getElementById('aiDayType').value;
        const hour = parseInt(document.getElementById('aiHourRange').value);
        const motor = parseInt(document.getElementById('resMotor').innerText) || 0;
        const pejalan = parseInt(document.getElementById('resPejalan').innerText) || 0;

        // BUG FIX 1: Validate hour range
        if (isNaN(hour) || hour < 0 || hour > 23) {
            alert('Error: Invalid hour range.');
            return;
        }

        // BUG FIX 2: Validate motor & pejalan counts
        if (motor < 0 || motor > 99999 || pejalan < 0 || pejalan > 99999) {
            alert('Error: Invalid count values (must be 0-99999).');
            return;
        }

        // Hitung base unit menggunakan hourly multiplier (rumus SOP)
        const mArr = dayType === 'weekend' ? hourlyMultiplier.weekend : hourlyMultiplier.weekday;
        const currentMult = mArr[hour] || 0.6;
        const motorBase = currentMult > 0.1 ? (motor / currentMult) : motor;
        const pejalanBase = currentMult > 0.1 ? (pejalan / currentMult) : pejalan;

        // Akumulasi ke videoStore (rata-rata)
        window.videoStore[dayType].motorBases.push(motorBase);
        window.videoStore[dayType].pejalanBases.push(pejalanBase);

        // Terapkan rata-rata ke seluruh hari + kunci
        window.applyDayFromVideo(dayType);

        // BUG FIX 4: Prevent double-click by disabling button
        const applyBtn = document.querySelector('button[onclick="applyAiResult()"]');
        if (applyBtn) applyBtn.disabled = true;

        const aiModalEl = document.getElementById('aiDetectionModal');
        const modal = bootstrap.Modal.getInstance(aiModalEl);
        if (modal) modal.hide();

        // BUG FIX 4b: Re-enable button after modal closes
        if (aiModalEl) {
            aiModalEl.addEventListener('hidden.bs.modal', function () {
                if (applyBtn) applyBtn.disabled = false;
                window.lastAiData = null;
            }, { once: true });
        }

        alert(`Video berhasil dikalibrasi ke ${dayType} (total ${window.videoStore[dayType].motorBases.length} video).`);
    };

    // --- END VIDEO DETECTION AI MODAL ---
});

function getRabVal(id) {
    const el = document.getElementById(id);
    if (!el) return 0;
    const qty = parseFloat(el.value) || 0;
    let price = 0;
    if (el.getAttribute('data-price') === 'custom') {
        const customPriceEl = document.querySelector('.rab-custom-price[data-target="' + id + '"]');
        if (customPriceEl) price = parseFloat(customPriceEl.value) || 0;
    } else {
        price = parseFloat(el.getAttribute('data-price')) || 0;
    }
    return qty * price;
}

function calculateRab() {
    let total = 0;
    document.querySelectorAll('.rab-input').forEach(el => {
        total += getRabVal(el.id);
    });
    const transportSelect = document.getElementById('rab_transport');
    if (transportSelect) total += parseFloat(transportSelect.value) || 0;

    const disp = document.getElementById('rab_grand_total_display');
    if (disp) disp.innerText = 'Rp ' + total.toLocaleString('id-ID');
    return total;
}

function applyRabResult() {
    const total = calculateRab();

    let signage = 0, furniture = 0, listrik = 0, exhaust = 0, air = 0, kitchen = 0, renovasi = 0, transport = 0;

    document.querySelectorAll('.rab-input').forEach(el => {
        let val = getRabVal(el.id);
        let id = el.id;

        // Keep explicit routing for existing hardcoded items to maintain 100% data integrity
        if (['rab_neon_sign', 'rab_billboard', 'rab_banner_baliho', 'rab_stand_banner', 'rab_banner_is_coming', 'rab_stiker_neon_box', 'rab_stiker_billboard'].includes(id)) { signage += val; return; }
        if (['rab_rombong_set', 'rab_rak_meja', 'rab_rombong_teh_kecil', 'rab_rombong_teh_besar', 'rab_stiker_rombong', 'rab_rak_lunchbox'].includes(id)) { furniture += val; return; }
        if (id === 'rab_exhaust') { exhaust += val; return; }
        if (['rab_zink_stainless', 'rab_zink_standart', 'rab_kran_zink'].includes(id)) { kitchen += val; return; }

        // Dynamic items added by Admin from Master BOQ
        let cat = el.getAttribute('data-kategori');
        if (cat === 'Promosi') signage += val;
        else if (cat === 'Listrik') listrik += val;
        else if (cat === 'Sanitasi') air += val;
        else if (cat === 'Partisi' || cat === 'Sipil') renovasi += val;
        else if (cat === 'Transport') transport += val;
        else if (!cat && id === 'rab_transport') transport += val;
    });

    // Add explicit transport field if it exists manually (fallback)
    const manualTransport = document.getElementById('rab_transport');
    if (manualTransport && !manualTransport.classList.contains('rab-input')) {
        transport += (parseFloat(manualTransport.value) || 0);
    }
    renovasi += transport; // Add transport to renovasi as per original logic

    const rTarget = document.querySelector('input[name="rab_renovasi"]'); if (rTarget) rTarget.value = renovasi;
    const kTarget = document.querySelector('input[name="rab_kitchen"]'); if (kTarget) kTarget.value = kitchen;
    const sTarget = document.querySelector('input[name="rab_signage"]'); if (sTarget) sTarget.value = signage;
    const fTarget = document.querySelector('input[name="rab_furniture"]'); if (fTarget) fTarget.value = furniture;
    const lTarget = document.querySelector('input[name="rab_listrik"]'); if (lTarget) lTarget.value = listrik;
    const aTarget = document.querySelector('input[name="rab_air"]'); if (aTarget) aTarget.value = air;
    const eTarget = document.querySelector('input[name="rab_exhaust"]'); if (eTarget) eTarget.value = exhaust;

    if (typeof initFeasibility === 'function') initFeasibility();
}

function setRab(id, val) {
    const el = document.getElementById(id);
    if (el) el.value = val;
}

function calculateLuasBangunan() {
    const lebarStr = document.querySelector('input[name="lebar_depan"]')?.value;
    const panjangStr = document.querySelector('input[name="panjang_bangunan"]')?.value;
    const luasInput = document.querySelector('input[name="luas_bangunan"]');
    if (!luasInput) return;

    if (lebarStr && panjangStr) {
        const lebar = parseFloat(lebarStr);
        const panjang = parseFloat(panjangStr);
        const luas = lebar * panjang;
        luasInput.value = luas.toFixed(2);
        luasInput.style.backgroundColor = '#f1f5f9';

        // AUTOFILL ALL BOQ LOGIC
        setRab('rab_rombong_set', 1);
        setRab('rab_neon_sign', 1);
        setRab('rab_billboard', lebar + 1);
        setRab('rab_banner_baliho', 6);
        setRab('rab_banner_is_coming', 6);
        setRab('rab_stiker_rombong', 1);
        setRab('rab_stiker_neon_box', 1);
        setRab('rab_stiker_billboard', 1);
        setRab('rab_stand_banner', 1);
        setRab('rab_rak_meja', 1);

        const lampu = Math.ceil((luas / 5) * (lebar / 1.5));
        const sk_3lb = luas < 30 ? 1 : 2;
        const sk_1lb = 4;
        const exhaust = 1;
        setRab('rab_lampu_hannochs', lampu);
        setRab('rab_sk_3lb', sk_3lb);
        setRab('rab_steker', 3);
        setRab('rab_exhaust', exhaust);
        setRab('rab_tduz', luas < 30 ? 2 : 4);
        setRab('rab_saklar_single', 5);
        setRab('rab_saklar_double', 2);
        setRab('rab_mcb', 1);
        setRab('rab_sk_1lb', sk_1lb);
        setRab('rab_jasa_listrik', sk_3lb + sk_1lb + lampu + exhaust);

        setRab('rab_partisi_penggorengan', lebar < 4 ? +(1.22 * 1.22).toFixed(2) : +(2.44 * 1.22).toFixed(2));
        setRab('rab_partisi_cucian', +(1.5 * 0.9).toFixed(2));
        setRab('rab_partisi_breading', +(3.5 * 0.9).toFixed(2));
        setRab('rab_partisi_gudang', panjang < 7 ? 0 : (lebar < 4 ? +(1.22 * 2.44).toFixed(2) : +(2.44 * 2.44).toFixed(2)));
        setRab('rab_rak_lunchbox', 1);

        setRab('rab_kran_zink', 1);
        setRab('rab_zink_stainless', 1);
        const airKotor = lebar + panjang;
        const airBersih = lebar + panjang + 3;
        setRab('rab_air_kotor', airKotor);
        setRab('rab_air_bersih', airBersih);
        setRab('rab_keni_3', airKotor < 10 ? 4 : 6);
        setRab('rab_keni_34', airBersih < 15 ? 4 : 6);
        setRab('rab_keni_drat', airBersih < 15 ? 4 : 6);

        const catDinding = panjang < 7 ? (((lebar + panjang + panjang) * 3) + 4) : (9 + panjang + lebar + 4);
        setRab('rab_cat_dinding', catDinding);

        calculateRab();
    } else {
        const allIds = ["rab_rombong_set", "rab_neon_sign", "rab_billboard", "rab_banner_baliho", "rab_stand_banner", "rab_rak_meja", "rab_rombong_teh_kecil", "rab_rombong_teh_besar", "rab_banner_is_coming", "rab_stiker_rombong", "rab_stiker_neon_box", "rab_stiker_billboard", "rab_lampu_hannochs", "rab_kabel", "rab_exhaust", "rab_sk_1lb", "rab_sk_3lb", "rab_steker", "rab_saklar_single", "rab_saklar_double", "rab_tduz", "rab_mcb", "rab_jasa_listrik", "rab_fitting", "rab_meteran_listrik", "rab_tambah_daya_450", "rab_tambah_daya_900", "rab_zink_stainless", "rab_kran_zink", "rab_air_kotor", "rab_air_bersih", "rab_keni_3", "rab_keni_34", "rab_zink_standart", "rab_kran_km", "rab_avour_km", "rab_pintu_km", "rab_septic_tank", "rab_closed_jongkok", "rab_keramik_km", "rab_keni_drat", "rab_keni_t_drat", "rab_keni_1_setengah", "rab_instal_pdam", "rab_pembuatan_km", "rab_pompa_air", "rab_meteran_pembanding", "rab_tambah_meteran_air", "rab_partisi_penggorengan", "rab_partisi_cucian", "rab_partisi_breading", "rab_rak_lunchbox", "rab_partisi_gudang", "rab_sekat_dinding_1", "rab_sekat_dinding_2", "rab_plafon_gypsum", "rab_cat_dinding", "rab_dinding_bata", "rab_plester_acian", "rab_rabat_teras", "rab_cat_folding", "rab_plamir", "rab_canopi", "rab_roda_kecil", "rab_roda_besar", "rab_tiang_neon_box", "rab_support_neon_box", "rab_perbaikan_pintu", "rab_perbaikan_rolling_door", "rab_tambah_pintu_rolling_door"];
        allIds.forEach(id => setRab(id, 0));
        calculateRab();
        luasInput.value = '';
        luasInput.style.backgroundColor = '';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.rab-input, .rab-select, .rab-custom-price').forEach(el => {
        el.addEventListener('input', calculateRab);
        el.addEventListener('change', calculateRab);
    });

    const lInp = document.querySelector('input[name="lebar_depan"]');
    const pInp = document.querySelector('input[name="panjang_bangunan"]');
    if (lInp) lInp.addEventListener('input', calculateLuasBangunan);
    if (pInp) pInp.addEventListener('input', calculateLuasBangunan);

    // Trigger auto-fill on page load in case fields are pre-filled by browser/Laravel
    calculateLuasBangunan();

    if (typeof initFeasibility === 'function') initFeasibility();

    // Attach listeners to manual RAB form inputs
    const formFields = ['harga_sewa', 'rab_renovasi', 'rab_kitchen', 'rab_signage', 'rab_furniture', 'rab_listrik', 'rab_air', 'rab_exhaust', 'rab_ac_kipas', 'rab_perizinan', 'rab_deposit_sewa', 'rab_biaya_opening'];
    formFields.forEach(name => {
        const el = document.querySelector(`input[name="${name}"]`);
        if (el) {
            el.addEventListener('input', () => {
                if (typeof initFeasibility === 'function') initFeasibility();
            });
        }
    });
});

function initFeasibility() {
    const kElem = document.getElementById('kategori_outlet');
    const kategori = kElem ? kElem.value : 'Express';

    const badge = document.getElementById('fs_kategori_badge');
    if (badge) badge.innerText = kategori;

    const panelBadge = document.getElementById('panel_kategori_badge');
    if (panelBadge) panelBadge.innerText = kategori;

    // 1. Get Sewa
    const sewaElem = document.querySelector('input[name="harga_sewa"]');
    const sewa = sewaElem ? (parseFloat(sewaElem.value) || 0) : 0;
    const invSewa = document.getElementById('fs_inv_sewa');
    if (invSewa) invSewa.value = sewa;

    // 2. Get Renovasi
    let boqTotal = 0;
    const formFields = ['rab_renovasi', 'rab_kitchen', 'rab_signage', 'rab_furniture', 'rab_listrik', 'rab_air', 'rab_exhaust', 'rab_ac_kipas', 'rab_perizinan', 'rab_deposit_sewa', 'rab_biaya_opening'];
    formFields.forEach(name => {
        const input = document.querySelector(`input[name="${name}"]`);
        if (input) {
            boqTotal += (parseFloat(input.value) || 0);
        }
    });

    const invRenovasi = document.getElementById('fs_inv_renovasi');
    if (invRenovasi) invRenovasi.value = boqTotal;

    // 3. Set Defaults
    if (kategori === 'Express') {
        document.getElementById('fs_inv_asset').value = 29377800;
        document.getElementById('fs_inv_marketing').value = 7131000;
        document.getElementById('fs_inv_lain').value = 7020000;
        document.getElementById('fs_total_opex').value = 14310647;
    } else {
        document.getElementById('fs_inv_asset').value = 41349500;
        document.getElementById('fs_inv_marketing').value = 10906000;
        document.getElementById('fs_inv_lain').value = 9228700;
        document.getElementById('fs_total_opex').value = 18575625;
    }

    if (!document.fsListenersAdded) {
        document.querySelectorAll('.fs-input').forEach(el => {
            el.addEventListener('input', calculateFeasibility);
            el.addEventListener('change', calculateFeasibility);
        });
        document.fsListenersAdded = true;
    }

    calculateFeasibility();
}

function calculateFeasibility() {
    const sewa = parseFloat(document.getElementById('fs_inv_sewa').value) || 0;
    const renov = parseFloat(document.getElementById('fs_inv_renovasi').value) || 0;
    const asset = parseFloat(document.getElementById('fs_inv_asset').value) || 0;
    const mkt = parseFloat(document.getElementById('fs_inv_marketing').value) || 0;
    const lain = parseFloat(document.getElementById('fs_inv_lain').value) || 0;

    const totalInvestasi = sewa + renov + asset + mkt + lain;
    document.getElementById('fs_total_investasi').innerText = 'Rp ' + totalInvestasi.toLocaleString('id-ID');

    const opex = parseFloat(document.getElementById('fs_total_opex').value) || 0;
    const marginPct = (parseFloat(document.getElementById('fs_margin').value) || 37) / 100;

    const grandOmsetDailyText = document.getElementById('omsetPerhariDisplay') ? document.getElementById('omsetPerhariDisplay').innerText : 'Rp 0';
    const grandOmsetDaily = parseFloat(grandOmsetDailyText.replace(/[^\d]/g, '')) || 0;
    const omsetMonthly = grandOmsetDaily * 30;

    document.getElementById('fs_res_omset').innerText = 'Rp ' + omsetMonthly.toLocaleString('id-ID');

    const bepMonthly = marginPct > 0 ? (opex / marginPct) : 0;
    document.getElementById('fs_res_bep').innerText = 'Rp ' + bepMonthly.toLocaleString('id-ID');

    const netProfit = (omsetMonthly * marginPct) - opex;
    document.getElementById('fs_res_netprofit').innerText = 'Rp ' + netProfit.toLocaleString('id-ID');

    let pp = 0;
    if (netProfit > 0) {
        pp = totalInvestasi / netProfit;
    }
    const resPp = document.getElementById('fs_res_pp');
    if (pp > 0) {
        resPp.innerText = pp.toFixed(1) + ' Bulan';
        resPp.className = 'fw-bold fs-4 text-white';
    } else {
        resPp.innerText = 'Tidak Tercapai';
        resPp.className = 'fw-bold fs-4 text-warning';
    }

    // UPDATE RIGHT PANEL ELEMENTS
    const setElem = (id, val, isMoney = true) => {
        const el = document.getElementById(id);
        if (el) el.innerText = isMoney ? 'Rp ' + val.toLocaleString('id-ID') : val;
    };

    setElem('panel_inv_sewa', sewa);
    setElem('panel_inv_renovasi', renov);
    setElem('panel_inv_asset', asset);
    setElem('panel_inv_marketing', mkt);
    setElem('panel_inv_lain', lain);
    setElem('panel_total_investasi', totalInvestasi);
    setElem('panel_total_opex', opex);
    setElem('panel_margin', (marginPct * 100) + '%', false);
    setElem('panel_res_omset', omsetMonthly);
    setElem('panel_res_netprofit', netProfit);
    setElem('panel_res_bep', bepMonthly);

    const panelPp = document.getElementById('panel_res_pp');
    if (panelPp) {
        if (pp > 0) {
            panelPp.innerText = pp.toFixed(1) + ' Bulan';
            panelPp.className = 'text-warning fw-bold';
        } else {
            panelPp.innerText = 'Tidak Tercapai';
            panelPp.className = 'text-danger fw-bold';
        }
    }
}

// --- KUADRAN CALCULATION ---
window.kalkulasiKuadran = function (q) {
    const luas = parseFloat(document.getElementById('luas_k' + q).value) || 0;
    const mult = parseFloat(document.getElementById('mult_k' + q).value) || 1;
    let hasil = 0;
    if (mult > 0) {
        hasil = (luas * 0.6) / mult * 2;
    }
    const finalHasil = Math.round(hasil);
    document.getElementById('hasil_k' + q).innerText = 'Hasil: ' + finalHasil;

    // Update ke form Rumah Q1-Q4
    const inputRumah = document.querySelector(`input[name="rumah_q${q}"]`);
    if (inputRumah) {
        inputRumah.value = finalHasil;
        if (typeof hitungLiveScore === 'function') hitungLiveScore();
    }

    return finalHasil;
}

window.generateKuadranAI = function () {
    // Generate AI Multiplier untuk simulasi perhitungan (80 - 120)
    for (let i = 1; i <= 4; i++) {
        let aiMult = (Math.random() * (120 - 80) + 80).toFixed(2);
        document.getElementById('mult_k' + i).value = aiMult;
        window.kalkulasiKuadran(i);
    }
}

// --- 24 HOUR EXTRAPOLATION ---
// Hourly Multiplier based on spreadsheet (Weekdays & Weekends mapping)
const hourlyMultiplier = {
    weekday: [0.5, 0.5, 0.5, 0.5, 0.5, 0.5, 0.5, 0.5, 0.5, 0.5, 0.5, 0.6, 0.5, 0.5, 0.5, 0.6, 0.7, 0.8, 0.9, 1.0, 1.0, 0.9, 0.7, 0.5],
    weekend: [0.5, 0.5, 0.5, 0.5, 0.5, 0.5, 0.5, 0.5, 0.5, 0.6, 0.6, 0.6, 0.5, 0.5, 0.6, 0.6, 0.6, 0.7, 0.8, 0.9, 1.0, 1.0, 0.9, 0.7]
};

window.applyAiResult24Jam = function () {
    if (!window.lastAiData) {
        alert('Data AI belum tersedia.');
        return;
    }

    // Ambil input form untuk mengetahui tipe Hari dan Jam
    const dayType = document.getElementById('aiDayType').value || 'weekday';
    const hourRangeStr = document.getElementById('aiHourRange').value;
    let recordedHour = parseInt(hourRangeStr);
    if (isNaN(recordedHour)) recordedHour = new Date().getHours();

    // BUG FIX 1: Validate hour
    if (recordedHour < 0 || recordedHour > 23) {
        alert('Error: Invalid recorded hour.');
        return;
    }

    const motorCount = parseInt(window.lastAiData.counts?.motorcycle) || 0;
    const pejalanCount = parseInt(window.lastAiData.counts?.person) || 0;

    // BUG FIX 2: Validate counts
    if (motorCount < 0 || motorCount > 99999 || pejalanCount < 0 || pejalanCount > 99999) {
        alert('Error: Invalid count values.');
        return;
    }

    const multiplierArr = dayType === 'weekend' ? hourlyMultiplier.weekend : hourlyMultiplier.weekday;
    const currentMultiplier = multiplierArr[recordedHour] || 0.6;

    // SOP Traffic Generator: Base Unit = actual count ÷ bar(jam rekam)
    const baseMotor = currentMultiplier > 0.1 ? (motorCount / currentMultiplier) : motorCount;
    const basePejalan = currentMultiplier > 0.1 ? (pejalanCount / currentMultiplier) : pejalanCount;

    // Akumulasi ke videoStore (rata-rata makin banyak video makin akurat)
    window.videoStore[dayType].motorBases.push(baseMotor);
    window.videoStore[dayType].pejalanBases.push(basePejalan);

    // Terapkan rata-rata ke seluruh hari + kunci
    window.applyDayFromVideo(dayType);

    // BUG FIX 3: Clean state after apply
    window.lastAiData = null;

    document.getElementById('aiFloatingToast').style.display = 'none';
    localStorage.removeItem('ai_polling_job_id');

    alert(`Ekstrapolasi 24 Jam berhasil! ${dayType} dikalibrasi dari ${window.videoStore[dayType].motorBases.length} video.`);
}

// BUG FIX: Enhanced banner update with progress polling
function updateSurveyorBanner() {
    const banner = document.getElementById('surveyorActiveJobsBanner');
    if (!banner) return;

    const stored = localStorage.getItem('ai_jobs_active');
    if (!stored) {
        banner.style.display = 'none';
        return;
    }

    try {
        const activeJobs = JSON.parse(stored);
        if (activeJobs && activeJobs.length > 0) {
            banner.style.display = 'block';
            const latestJob = activeJobs[activeJobs.length - 1];

            // Poll progress endpoint for latest status
            fetch(`${window.SiteScoreConfig.routes.videoProgress}/${latestJob.jobId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.job_id) {
                        // Update job count
                        document.getElementById('surveyorBannerJobCount').innerText =
                            `${activeJobs.length} video${activeJobs.length > 1 ? 's' : ''}`;

                        // Update progress bar
                        const progress = data.progress || 0;
                        const progressBar = document.getElementById('surveyorProgressBar');
                        progressBar.style.width = progress + '%';
                        progressBar.setAttribute('aria-valuenow', progress);
                        document.getElementById('surveyorProgressText').innerText = progress + '%';

                        // Update stage name
                        const stageName = data.stage_name || 'Processing';
                        document.getElementById('surveyorBannerStage').innerText =
                            `${stageName} ${progress}% - ${data.message || ''}`;

                        // Show Action buttons when complete
                        const actionBtns = document.getElementById('surveyorActionButtons');
                        if ((data.stage === 'completed' || data.status === 'done') && progress === 100) {
                            actionBtns.style.display = 'flex';

                            const applyBtn = document.getElementById('surveyorApplyResultsBtn');
                            const discardBtn = document.getElementById('surveyorDiscardResultsBtn');

                            if (applyBtn) applyBtn.onclick = function () { applyVideoResults(data); };
                            if (discardBtn) discardBtn.onclick = function () { discardVideoResults(data); };

                            // Ensure stage text is correct for jobs completed before this update
                            if (data.stage === 'queued') {
                                document.getElementById('surveyorBannerStage').innerText =
                                    `Complete 100% - ${data.message || ''}`;
                            }
                        } else {
                            actionBtns.style.display = 'none';
                        }
                    }
                })
                .catch(err => {
                    console.warn('Error polling progress:', err);
                    // Fallback: just show job count
                    document.getElementById('surveyorBannerJobCount').innerText =
                        `${activeJobs.length} video${activeJobs.length > 1 ? 's' : ''}`;
                });
        } else {
            banner.style.display = 'none';
        }
    } catch (e) {
        console.warn('Error loading active jobs for banner:', e);
    }
}

// BUG FIX: Apply video analysis results to form
function applyVideoResults(progressData) {
    if (!progressData.counts) {
        alert('No detection results available');
        return;
    }

    // Tampilkan AI Insight & Snapshot via SweetAlert
    let insightHtml = '';
    if (progressData.peak_frame_b64) {
        insightHtml += `<img src="data:image/jpeg;base64,${progressData.peak_frame_b64}" class="img-fluid rounded mb-3 shadow-sm" style="max-height:250px; border:2px solid #e2e8f0;">`;
    }
    if (progressData.ai_insight) {
        insightHtml += `<div class="alert alert-info text-start p-3 shadow-sm" style="font-size:14px; border-radius: 10px;">
            <i class="bi bi-robot me-2 text-primary" style="font-size:18px;"></i>
            <strong class="text-primary">Groq AI Insight:</strong><br>
            <span class="text-dark">${progressData.ai_insight}</span>
        </div>`;
    }

    const nextStep = () => {
        // Get stored job info to know which day/hour was analyzed
        const stored = localStorage.getItem('ai_jobs_active');
        let dayType = 'weekday', hour = 6;
        if (stored) {
            try {
                const activeJobs = JSON.parse(stored);
                const job = activeJobs.find(j => j.jobId === progressData.job_id);
                if (job) {
                    dayType = job.dayType || 'weekday';
                    hour = parseInt(job.hour) || 6;
                }
            } catch (e) { }
        }

        // Set global data for extrapolation logic
        window.lastAiData = { counts: progressData.counts };

        // Temporarily set modal inputs so extrapolation logic reads the correct hour/day
        const dayTypeEl = document.getElementById('aiDayType');
        const hourRangeEl = document.getElementById('aiHourRange');

        if (dayTypeEl) dayTypeEl.value = dayType;
        if (hourRangeEl) hourRangeEl.value = hour;

        // Populate counts to modal for compatibility
        const mEl = document.getElementById('resMotor');
        const pEl = document.getElementById('resPejalan');
        if (mEl) mEl.innerText = progressData.counts.motorcycle || 0;
        if (pEl) pEl.innerText = progressData.counts.person || 0;

        // Call original 24 hour extrapolation function
        if (typeof window.applyAiResult24Jam === 'function') {
            window.applyAiResult24Jam();
        } else {
            alert('Extrapolation function not found!');
        }

        // Remove from active tracking
        window.removeActiveJob(progressData.job_id);
        updateSurveyorBanner();
    };

    if (insightHtml && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '<h4 class="fw-bold text-success mb-0"><i class="bi bi-check-circle-fill me-2"></i>Analisis Selesai!</h4>',
            html: insightHtml,
            confirmButtonText: '<i class="bi bi-magic me-1"></i> Terapkan ke Form',
            confirmButtonColor: '#2563eb',
            width: '600px',
            allowOutsideClick: false,
            customClass: { popup: 'rounded-4' }
        }).then((result) => {
            if (result.isConfirmed) nextStep();
        });
    } else {
        nextStep();
    }
}

// BUG FIX: Discard video analysis results
function discardVideoResults(progressData) {
    if (confirm('Apakah Anda yakin ingin membuang hasil analisis ini?')) {
        window.removeActiveJob(progressData.job_id);
        updateSurveyorBanner();
    }
}

// Call on page load
window.addEventListener('load', updateSurveyorBanner);
// Refresh every 2 seconds for responsive progress (BUG FIX: Changed from 5 to 2 seconds)
setInterval(updateSurveyorBanner, 2000);

// --- RADAR INTELLIGENCE REPORT RENDERER ---
function renderRadarReport() {
    const container = document.getElementById('radarReportContainer');
    if (!container) return;

    container.classList.remove('d-none');

    if (!window.radarData) return;

    // --- Cannibalization Check ---
    const alertBox = document.getElementById('cannibalizationAlert');
    const title = document.getElementById('canibTitle');
    const msg = document.getElementById('canibMessage');
    const icon = document.getElementById('canibIcon');

    if (alertBox && window.radarData.internal) {
        alertBox.classList.remove('d-none');
        alertBox.className = 'alert m-3 mb-0';

        let closestDist = 999999;
        let closestName = '';

        window.radarData.internal.forEach(p => {
            if (p.dist < closestDist) {
                closestDist = p.dist;
                closestName = p.name;
            }
        });

        // Reset defaults first (optional, fallback to base values if possible, we'll just manipulate inputs)
        const w1 = document.querySelector('input[name="w_rumah_q1"]');
        const w2 = document.querySelector('input[name="w_rumah_q2"]');
        const w3 = document.querySelector('input[name="w_rumah_q3"]');
        const w4 = document.querySelector('input[name="w_rumah_q4"]');

        if (closestDist < 500) {
            alertBox.classList.add('alert-danger');
            icon.className = 'bi bi-shield-x fs-3 me-3 text-danger';
            title.innerText = 'BAHAYA EKSTRIM: Kanibalisasi Zona Inti (Q1)';
            msg.innerText = `Ditemukan outlet internal (${closestName}) pada jarak ${closestDist}m. Potensi Omset Q1-Q4 dikurangi 80%!`;

            if (w1) w1.value = (w1.value * 0.2).toFixed(2);
            if (w2) w2.value = (w2.value * 0.2).toFixed(2);
            if (w3) w3.value = (w3.value * 0.2).toFixed(2);
            if (w4) w4.value = (w4.value * 0.2).toFixed(2);

        } else if (closestDist < 1500) {
            alertBox.classList.add('alert-warning');
            icon.className = 'bi bi-shield-exclamation fs-3 me-3 text-warning';
            title.innerText = 'BERBAGI PASAR: Kanibalisasi Zona Primer (Q2/Q3)';
            msg.innerText = `Ditemukan outlet internal (${closestName}) pada jarak ${closestDist}m. Bobot skor Rumah Q2 & Q3 dipotong 50%.`;

            if (w2) w2.value = (w2.value * 0.5).toFixed(2);
            if (w3) w3.value = (w3.value * 0.5).toFixed(2);

        } else if (closestDist <= 3000) {
            alertBox.classList.add('alert-info');
            icon.className = 'bi bi-shield-check fs-3 me-3 text-info';
            title.innerText = 'AMAN: Zona Tersier (Q4)';
            msg.innerText = `Outlet terdekat (${closestName}) berada di jarak aman (${closestDist}m). Bobot Rumah Q4 dipotong 10%.`;

            if (w4) w4.value = (w4.value * 0.9).toFixed(2);

        } else {
            alertBox.classList.add('alert-success');
            icon.className = 'bi bi-shield-check fs-3 me-3 text-success';
            title.innerText = 'Status Jaringan Internal: Aman Mutlak';
            msg.innerText = `Tidak ada cabang Geprekin Aja di radius 3 KM. Ruko ini memonopoli area 100%!`;
        }
    }

    const categories = [
        { key: 'pendidikan', el: 'list_pendidikan', countEl: 'count_pendidikan', limit: 5 },
        { key: 'market', el: 'list_market', countEl: 'count_market', limit: 5 },
        { key: 'bank', el: 'list_bank', countEl: 'count_bank', limit: 5 },
        { key: 'kesehatan', el: 'list_kesehatan', countEl: 'count_kesehatan', limit: 3 },
        { key: 'kompetitor', el: 'list_kompetitor', countEl: 'count_kompetitor', limit: 5 }
    ];

    categories.forEach(cat => {
        const data = window.radarData[cat.key] || [];
        const ul = document.getElementById(cat.el);
        const countBadge = document.getElementById(cat.countEl);

        if (countBadge) countBadge.innerText = data.length;
        if (!ul) return;

        // Remove duplicates (same name) and sort by distance
        const uniqueData = Array.from(new Map(data.map(item => [item.name, item])).values());
        uniqueData.sort((a, b) => a.dist - b.dist);

        ul.innerHTML = '';
        if (uniqueData.length === 0) {
            ul.innerHTML = '<li class="list-group-item text-muted text-center py-2" style="font-size:12px;">Tidak ada data ditemukan dalam radius.</li>';
        } else {
            uniqueData.slice(0, cat.limit).forEach(item => {
                const li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between align-items-center bg-transparent px-2 py-1 border-0';
                li.innerHTML = `<span class="text-truncate" style="max-width: 75%;">${item.name}</span><span class="badge bg-light text-dark border">${item.dist} m</span>`;
                ul.appendChild(li);
            });
            if (uniqueData.length > cat.limit) {
                const li = document.createElement('li');
                li.className = 'list-group-item text-center text-primary bg-transparent px-2 py-1 border-0 fw-bold';
                li.style.fontSize = '11px';
                li.innerText = `+ ${uniqueData.length - cat.limit} lokasi lainnya...`;
                ul.appendChild(li);
            }
        }
    });
}
