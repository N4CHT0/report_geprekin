
                {{-- DATA LOKASI --}}
                <div class="excel-box mb-3">
                    <div class="excel-box-header">
                        <div>
                            <h5>Data Lokasi</h5>
                            <p>Informasi titik yang akan disurvey.</p>
                        </div>
                    </div>
                    <div class="excel-box-body">
                        <div class="field-grid">
                            @if(isset($candidate))
                                <input type="hidden" name="candidate_id" value="{{ $candidate->id }}">
                            @endif
                            <div class="field-col-6">
                                <label class="field-label">Lokasi</label>
                                <input type="text" name="lokasi" class="cell-input yellow-cell" placeholder="Nama jalan / titik lokasi" value="{{ old('lokasi', $score->lokasi ?? ($candidate->nama_lokasi ?? request('lokasi'))) }}" required>
                            </div>
                            <div class="field-col-3">
                                <label class="field-label">Kota / Kab.</label>
                                <input type="text" name="kota" class="cell-input yellow-cell" value="{{ old('kota', $score->kota ?? ($candidate->kota ?? '')) }}" required>
                            </div>
                            <div class="field-col-3">
                                <label class="field-label">Provinsi</label>
                                <input type="text" name="provinsi" class="cell-input" value="{{ old('provinsi', $score->provinsi ?? ($candidate->provinsi ?? 'Jawa Timur')) }}">
                            </div>
                            <div class="field-col-3">
                                <label class="field-label">Latitude</label>
                                <input type="text" name="latitude" id="latitude" class="cell-input yellow-cell" placeholder="-7.xxxxxx" value="{{ old('latitude', $score->latitude ?? ($candidate->latitude ?? request('lat'))) }}">
                            </div>
                            <div class="field-col-3">
                                <label class="field-label">Longitude</label>
                                <input type="text" name="longitude" id="longitude" class="cell-input yellow-cell" placeholder="112.xxxxxx" value="{{ old('longitude', $score->longitude ?? ($candidate->longitude ?? request('lng'))) }}">
                            </div>
                            <div class="field-col-3">
                                <label class="field-label">Surveyor</label>
                                <input type="text" name="surveyor" class="cell-input" value="{{ old('surveyor', $score->surveyor ?? (auth()->user()->name ?? '')) }}">
                            </div>
                            <div class="field-col-3">
                                <label class="field-label">Tanggal</label>
                                <input type="datetime-local" name="tanggal_survey" class="cell-input" value="{{ old('tanggal_survey', isset($score->tanggal_survey) ? date('Y-m-d\TH:i', strtotime($score->tanggal_survey)) : date('Y-m-d\TH:i')) }}">
                            </div>

                            {{-- TIPE OUTLET LDP / BDP --}}
                            <div class="field-col-6">
                                <label class="field-label">Tipe Outlet</label>
                                <div class="tipe-outlet-toggle">
                                    <input type="radio" name="tipe_outlet" id="tipe_ldp" value="LDP" {{ old('tipe_outlet', $score->tipe_outlet ?? 'LDP') == 'LDP' ? 'checked' : '' }}>
                                    <label for="tipe_ldp">
                                        <i class="bi bi-shop"></i> LDP
                                        <div style="font-size:10px;font-weight:700;color:#94a3b8;">Approved ≥ 60%</div>
                                    </label>
                                    <input type="radio" name="tipe_outlet" id="tipe_bdp" value="BDP" {{ old('tipe_outlet', $score->tipe_outlet ?? 'LDP') == 'BDP' ? 'checked' : '' }}>
                                    <label for="tipe_bdp">
                                        <i class="bi bi-building"></i> BDP
                                        <div style="font-size:10px;font-weight:700;color:#94a3b8;">Approved ≥ 100%</div>
                                    </label>
                                </div>
                            </div>

                            <div class="field-col-3">
                                <label class="field-label">Average Check (Rp)</label>
                                <input type="number" name="average_check" class="cell-input yellow-cell calc-input" value="{{ old('average_check', $score->average_check ?? 21000) }}" min="1000">
                            </div>

                            <div class="field-col-3">
                                <label class="field-label">Margin of Error (%)</label>
                                <input type="number" name="margin_of_error" id="margin_of_error" class="cell-input yellow-cell calc-input" placeholder="Auto" min="0" max="100" value="{{ old('margin_of_error', isset($score->margin_of_error) ? $score->margin_of_error * 100 : '') }}" title="Kosongkan untuk otomatis (20%/30%)">
                            </div>
                            <div class="field-col-12">
                                <label class="field-label">Link Maps</label>
                                <input type="text" name="maps_url" id="maps_url" class="cell-input" placeholder="Otomatis dari GPS / paste Google Maps" value="{{ old('maps_url', $score->maps_url ?? request('url')) }}">
                            </div>
                        </div>
                    </div>
                </div>


