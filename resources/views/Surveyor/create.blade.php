@section('title', 'Input Worksheet Site Score')
@section('breadcrumb', 'Site Score Outlet / Input Worksheet')
@include('Surveyor.layouts.header')
@include('Surveyor.layouts.site-score-excel-style')

<div class="excel-sheet">
    <div class="excel-titlebar">
        <div>
            <h1>GC - SITE SCORE OUTLET INPUT FORM</h1>
            <p>Format input dibuat seperti worksheet Excel: isi cell putih, subtotal dan keputusan otomatis di sisi kanan.</p>
        </div>
        <a href="{{ route('investor.surveyor.site-score.index') }}" class="btn btn-light btn-excel"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
    </div>
    <div class="excel-toolbar">
        <span class="excel-tab active"><i class="bi bi-pencil-square"></i> Input Data</span>
        <span class="excel-tab"><i class="bi bi-calculator"></i> Auto Score</span>
        <span class="excel-tab"><i class="bi bi-check2-circle"></i> Decision</span>
    </div>

    <form action="{{ route('investor.surveyor.site-score.store') }}" method="POST">
        @csrf
        <div class="p-3">
            <div class="row g-3">
                <div class="col-xl-8">
                    <table class="excel-grid mb-3">
                        <tr><td colspan="4" class="excel-section">1. IDENTITAS LOKASI</td></tr>
                        <tr>
                            <td class="label" style="width:22%">Nama Lokasi</td><td class="input-cell"><input type="text" name="lokasi" required></td>
                            <td class="label" style="width:22%">Kota / Area</td><td class="input-cell"><input type="text" name="kota" required></td>
                        </tr>
                        <tr>
                            <td class="label">Surveyor</td><td class="input-cell"><input type="text" name="surveyor" value="{{ auth()->user()->name ?? '' }}"></td>
                            <td class="label">Tanggal Survey</td><td class="input-cell"><input type="datetime-local" name="tanggal_survey"></td>
                        </tr>
                    </table>

                    <table class="excel-grid mb-3">
                        <tr><td colspan="7" class="excel-section">2. TRAFFIC MOTOR</td></tr>
                        <tr><th>Jenis Hari</th><th>Pagi</th><th>Siang</th><th>Sore</th><th>Total</th><th>Grade</th><th>Bobot</th></tr>
                        <tr>
                            <td class="label">Weekday</td>
                            <td class="input-cell"><input type="number" name="motor_weekday_pagi" class="calc-input motor-input" value="0" min="0"></td>
                            <td class="input-cell"><input type="number" name="motor_weekday_siang" class="calc-input motor-input" value="0" min="0"></td>
                            <td class="input-cell"><input type="number" name="motor_weekday_sore" class="calc-input motor-input" value="0" min="0"></td>
                            <td class="excel-number" id="motorWeekdayTotal">0</td><td id="motorGradeRow" class="excel-number">1</td><td class="excel-number">40%</td>
                        </tr>
                        <tr>
                            <td class="label">Weekend</td>
                            <td class="input-cell"><input type="number" name="motor_weekend_pagi" class="calc-input motor-input" value="0" min="0"></td>
                            <td class="input-cell"><input type="number" name="motor_weekend_siang" class="calc-input motor-input" value="0" min="0"></td>
                            <td class="input-cell"><input type="number" name="motor_weekend_sore" class="calc-input motor-input" value="0" min="0"></td>
                            <td class="excel-number" id="motorWeekendTotal">0</td><td class="excel-number">-</td><td class="excel-number">-</td>
                        </tr>
                    </table>

                    <table class="excel-grid mb-3">
                        <tr><td colspan="7" class="excel-section">3. TRAFFIC PEJALAN KAKI</td></tr>
                        <tr><th>Jenis Hari</th><th>Pagi</th><th>Siang</th><th>Sore</th><th>Total</th><th>Grade</th><th>Bobot</th></tr>
                        <tr>
                            <td class="label">Weekday</td>
                            <td class="input-cell"><input type="number" name="pejalan_weekday_pagi" class="calc-input pejalan-input" value="0" min="0"></td>
                            <td class="input-cell"><input type="number" name="pejalan_weekday_siang" class="calc-input pejalan-input" value="0" min="0"></td>
                            <td class="input-cell"><input type="number" name="pejalan_weekday_sore" class="calc-input pejalan-input" value="0" min="0"></td>
                            <td class="excel-number" id="pejalanWeekdayTotal">0</td><td id="pejalanGradeRow" class="excel-number">1</td><td class="excel-number">10%</td>
                        </tr>
                        <tr>
                            <td class="label">Weekend</td>
                            <td class="input-cell"><input type="number" name="pejalan_weekend_pagi" class="calc-input pejalan-input" value="0" min="0"></td>
                            <td class="input-cell"><input type="number" name="pejalan_weekend_siang" class="calc-input pejalan-input" value="0" min="0"></td>
                            <td class="input-cell"><input type="number" name="pejalan_weekend_sore" class="calc-input pejalan-input" value="0" min="0"></td>
                            <td class="excel-number" id="pejalanWeekendTotal">0</td><td class="excel-number">-</td><td class="excel-number">-</td>
                        </tr>
                    </table>

                    <table class="excel-grid mb-3">
                        <tr><td colspan="4" class="excel-section">4. RUMAH PENDUDUK PER KUADRAN</td></tr>
                        <tr><th>Kuadran 1</th><th>Kuadran 2</th><th>Kuadran 3</th><th>Kuadran 4</th></tr>
                        <tr>
                            <td class="input-cell"><input type="number" name="rumah_q1" class="calc-input plus-input" value="0" min="0"></td>
                            <td class="input-cell"><input type="number" name="rumah_q2" class="calc-input plus-input" value="0" min="0"></td>
                            <td class="input-cell"><input type="number" name="rumah_q3" class="calc-input plus-input" value="0" min="0"></td>
                            <td class="input-cell"><input type="number" name="rumah_q4" class="calc-input plus-input" value="0" min="0"></td>
                        </tr>
                    </table>

                    <table class="excel-grid mb-3">
                        <tr><td colspan="4" class="excel-section">5. FASILITAS UMUM</td></tr>
                        <tr><th>Sekolah</th><th>Market</th><th>Perkantoran</th><th>Fasilitas Kesehatan</th></tr>
                        <tr>
                            <td class="input-cell"><input type="number" name="sekolah" class="calc-input plus-input" value="0" min="0"></td>
                            <td class="input-cell"><input type="number" name="market" class="calc-input plus-input" value="0" min="0"></td>
                            <td class="input-cell"><input type="number" name="perkantoran" class="calc-input plus-input" value="0" min="0"></td>
                            <td class="input-cell"><input type="number" name="kesehatan" class="calc-input plus-input" value="0" min="0"></td>
                        </tr>
                    </table>

                    <table class="excel-grid mb-3">
                        <tr><td colspan="3" class="excel-section">6. KOMPETITOR</td></tr>
                        <tr><th>Geprek / Fried Chicken</th><th>Makanan Lokal</th><th>Harga Kompetitor</th></tr>
                        <tr>
                            <td class="input-cell"><input type="number" name="kompetitor_geprek" class="calc-input minus-input" value="0" min="0"></td>
                            <td class="input-cell"><input type="number" name="kompetitor_lokal" class="calc-input minus-input" value="0" min="0"></td>
                            <td class="input-cell"><input type="number" name="harga_kompetitor" value="0" min="0"></td>
                        </tr>
                    </table>

                    <table class="excel-grid">
                        <tr><td class="excel-section">7. CATATAN SURVEYOR</td></tr>
                        <tr><td class="input-cell"><textarea name="catatan" rows="4" placeholder="Catatan kondisi lokasi, risiko, potensi, dan validasi yang diperlukan..."></textarea></td></tr>
                    </table>
                </div>

                <div class="col-xl-4">
                    <div class="excel-sticky">
                        <div class="decision-box mb-3">
                            <div class="decision-head">LIVE SCORE PREVIEW</div>
                            <div class="decision-body">
                                <table class="excel-grid mb-3">
                                    <tr><td class="label">Total Motor</td><td class="excel-number" id="totalMotor">0</td></tr>
                                    <tr><td class="label">Total Pejalan</td><td class="excel-number" id="totalPejalan">0</td></tr>
                                    <tr><td class="label">Grade Motor</td><td class="excel-number" id="gradeMotor">1</td></tr>
                                    <tr><td class="label">Grade Pejalan</td><td class="excel-number" id="gradePejalan">1</td></tr>
                                    <tr><td class="label excel-success">Total Penambah</td><td class="excel-number excel-success" id="totalPlus">0.00</td></tr>
                                    <tr><td class="label excel-danger">Total Pengurang</td><td class="excel-number excel-danger" id="totalMinus">0.00</td></tr>
                                    <tr><td class="label excel-warning">Final Score</td><td class="excel-number excel-warning fw-bold" id="liveScore">0.00</td></tr>
                                </table>
                                <div class="excel-progress mb-3"><span id="scoreProgress" style="width:0%"></span></div>
                                <div id="recommendationLabel" class="score-cell score-red w-100 mb-2">REJECTED</div>
                                <div class="small-muted" id="recommendationText">Score masih rendah untuk dilanjutkan.</div>
                            </div>
                        </div>

                        <div class="decision-box mb-3">
                            <div class="decision-head">DECISION RULE</div>
                            <div class="decision-body">
                                <table class="excel-grid">
                                    <tr><td class="excel-success fw-bold">APPROVED</td><td>≥ 45</td></tr>
                                    <tr><td class="excel-warning fw-bold">CONSIDERATION</td><td>≥ 30</td></tr>
                                    <tr><td class="excel-danger fw-bold">REJECTED</td><td>&lt; 30</td></tr>
                                </table>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-green btn-excel w-100"><i class="bi bi-save me-1"></i> Simpan Site Score</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function val(name){ const el=document.querySelector('[name='+name+']'); return parseInt(el?.value || 0); }
function sumNames(names){ return names.reduce((t,n)=>t+val(n),0); }
function grade(value,type){
    if(type==='motor'){ if(value<=5000)return 1;if(value<=10000)return 2;if(value<=20000)return 3;if(value<=30000)return 4;return 5; }
    if(type==='pejalan'){ if(value<=250)return 1;if(value<=500)return 2;if(value<=750)return 3;if(value<=1000)return 4;return 5; }
    return value > 0 ? 5 : 0;
}
function calcExcelScore(){
    const motorWeekday=sumNames(['motor_weekday_pagi','motor_weekday_siang','motor_weekday_sore']);
    const motorWeekend=sumNames(['motor_weekend_pagi','motor_weekend_siang','motor_weekend_sore']);
    const pejalanWeekday=sumNames(['pejalan_weekday_pagi','pejalan_weekday_siang','pejalan_weekday_sore']);
    const pejalanWeekend=sumNames(['pejalan_weekend_pagi','pejalan_weekend_siang','pejalan_weekend_sore']);
    const motor=motorWeekday+motorWeekend;
    const pejalan=pejalanWeekday+pejalanWeekend;
    const gMotor=grade(motor,'motor');
    const gPejalan=grade(pejalan,'pejalan');
    let plus=(gMotor/5)*40+(gPejalan/5)*10;
    plus += val('rumah_q1')>0 ? 10 : 0;
    plus += val('rumah_q2')>0 ? 10 : 0;
    plus += val('rumah_q3')>0 ? 7.5 : 0;
    plus += val('rumah_q4')>0 ? 2.5 : 0;
    plus += val('sekolah')>0 ? 5 : 0;
    plus += val('market')>0 ? 5 : 0;
    plus += val('perkantoran')>0 ? 5 : 0;
    plus += val('kesehatan')>0 ? 5 : 0;
    let minus=0;
    minus += val('kompetitor_geprek')>0 ? 12.5 : 0;
    minus += val('kompetitor_lokal')>0 ? 7.5 : 0;
    const final=Math.max(0, plus-minus);
    let label='REJECTED', cls='score-cell score-red w-100 mb-2', text='Score masih rendah untuk dilanjutkan.';
    if(final>=45){ label='APPROVED'; cls='score-cell score-green w-100 mb-2'; text='Lokasi layak masuk validasi lanjutan.'; }
    else if(final>=30){ label='CONSIDERATION'; cls='score-cell score-yellow w-100 mb-2'; text='Lokasi perlu review tambahan.'; }
    document.getElementById('motorWeekdayTotal').innerText=motorWeekday.toLocaleString();
    document.getElementById('motorWeekendTotal').innerText=motorWeekend.toLocaleString();
    document.getElementById('pejalanWeekdayTotal').innerText=pejalanWeekday.toLocaleString();
    document.getElementById('pejalanWeekendTotal').innerText=pejalanWeekend.toLocaleString();
    document.getElementById('totalMotor').innerText=motor.toLocaleString();
    document.getElementById('totalPejalan').innerText=pejalan.toLocaleString();
    document.getElementById('gradeMotor').innerText=gMotor;
    document.getElementById('gradePejalan').innerText=gPejalan;
    document.getElementById('motorGradeRow').innerText=gMotor;
    document.getElementById('pejalanGradeRow').innerText=gPejalan;
    document.getElementById('totalPlus').innerText=plus.toFixed(2);
    document.getElementById('totalMinus').innerText=minus.toFixed(2);
    document.getElementById('liveScore').innerText=final.toFixed(2);
    document.getElementById('scoreProgress').style.width=Math.min(100, final)+'%';
    document.getElementById('recommendationLabel').innerText=label;
    document.getElementById('recommendationLabel').className=cls;
    document.getElementById('recommendationText').innerText=text;
}
document.addEventListener('DOMContentLoaded',()=>{document.querySelectorAll('.calc-input').forEach(e=>e.addEventListener('input',calcExcelScore));calcExcelScore();});
</script>
@endpush

@include('Surveyor.layouts.footer')
