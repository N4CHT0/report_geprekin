/**
 * Jam Ramai Estimasi - Popular Times Visualization
 * Estimates busy hours based on traffic survey data
 */

function updateJamRamai() {
    const selectedDay = document.querySelector('.jam-ramai-day-btn.active')?.getAttribute('data-day');
    const isWeekday = selectedDay < 5; // 0-4 = Mon-Fri

    // Get traffic data from form
    const mPagi = toNumber(document.querySelector('[name="' + (isWeekday ? 'motor_weekday_pagi' : 'motor_weekend_pagi') + '"]')?.value);
    const mSiang = toNumber(document.querySelector('[name="' + (isWeekday ? 'motor_weekday_siang' : 'motor_weekend_siang') + '"]')?.value);
    const mPetang = toNumber(document.querySelector('[name="' + (isWeekday ? 'motor_weekday_sore' : 'motor_weekend_sore') + '"]')?.value);
    const pPagi = toNumber(document.querySelector('[name="' + (isWeekday ? 'pejalan_weekday_pagi' : 'pejalan_weekend_pagi') + '"]')?.value);
    const pSiang = toNumber(document.querySelector('[name="' + (isWeekday ? 'pejalan_weekday_siang' : 'pejalan_weekend_siang') + '"]')?.value);
    const pPetang = toNumber(document.querySelector('[name="' + (isWeekday ? 'pejalan_weekday_sore' : 'pejalan_weekend_sore') + '"]')?.value);

    // Base values
    const baseMotor = [mPagi, mSiang, mPetang];
    const basePejalan = [pPagi, pSiang, pPetang];

    // Estimate hourly values (06:00-22:00, 17 hours)
    const hours = [];
    for (let h = 6; h <= 22; h++) {
        let motor = 0, pejalan = 0;

        if (h >= 6 && h < 9) {
            // Pagi (06-08) = ramping 06-09
            const ratio = (h - 6) / 3;
            motor = baseMotor[0] * Math.min(1, ratio);
            pejalan = basePejalan[0] * Math.min(1, ratio);
        } else if (h >= 9 && h < 11) {
            // Transition pagi to siang
            const ratio = (h - 9) / 2;
            motor = baseMotor[0] * (1 - ratio) + baseMotor[1] * ratio;
            pejalan = basePejalan[0] * (1 - ratio) + basePejalan[1] * ratio;
        } else if (h >= 11 && h < 14) {
            // Siang (11-13) = ramping 11-14
            const ratio = Math.min(1, (h - 11) / 3);
            motor = baseMotor[1] * ratio;
            pejalan = basePejalan[1] * ratio;
        } else if (h >= 14 && h < 17) {
            // Transition siang to petang
            const ratio = (h - 14) / 3;
            motor = baseMotor[1] * (1 - ratio * 0.5) + baseMotor[2] * ratio;
            pejalan = basePejalan[1] * (1 - ratio * 0.5) + basePejalan[2] * ratio;
        } else if (h >= 17 && h < 21) {
            // Petang (17-20) = peak
            motor = baseMotor[2];
            pejalan = basePejalan[2];
        } else if (h === 21) {
            // Decay after petang
            motor = baseMotor[2] * 0.5;
            pejalan = basePejalan[2] * 0.5;
        } else if (h === 22) {
            // Late evening
            motor = baseMotor[2] * 0.2;
            pejalan = basePejalan[2] * 0.2;
        }

        // Combine motor + pejalan (70% motor + 30% pejalan)
        const combined = (motor * 0.7) + (pejalan * 0.3);
        hours.push(combined);
    }

    // Normalize to 0-100%
    const maxValue = Math.max(...hours, 1);
    const normalized = hours.map(h => (h / maxValue) * 100);

    // Render bars
    const chartEl = document.getElementById('jamRamaiChart');
    const labelsEl = document.getElementById('jamRamaiLabels');
    const infoEl = document.getElementById('jamRamaiInfo');
    const infoText = document.getElementById('jamRamaiInfoText');

    chartEl.innerHTML = '';
    labelsEl.innerHTML = '';

    let peakIdx = -1, peakVal = 0;

    normalized.forEach((val, idx) => {
        const bar = document.createElement('div');
        bar.className = 'jam-ramai-bar';
        bar.style.height = Math.max(val, 2) + '%';

        const hour = 6 + idx;
        const label = String(hour).padStart(2, '0') + ':00';
        bar.innerHTML = '<div class="jam-ramai-tooltip">' + label + '<br>' + Math.round(normalized[idx]) + '%</div>';

        if (val > peakVal) {
            peakVal = val;
            peakIdx = idx;
        }

        chartEl.appendChild(bar);
    });

    // Highlight peak hours
    if (peakIdx >= 0) {
        const bars = chartEl.querySelectorAll('.jam-ramai-bar');
        bars[peakIdx].classList.add('peak');
    }

    // Labels (every 2 hours)
    for (let i = 0; i < 17; i += 2) {
        const h = 6 + i;
        const lbl = document.createElement('div');
        lbl.className = 'jam-ramai-label';
        lbl.textContent = String(h).padStart(2, '0');
        labelsEl.appendChild(lbl);
    }

    // Info section
    const hasData = hours.some(h => h > 0);
    if (hasData) {
        const peakHour = 6 + peakIdx;
        const peakEnd = peakHour + 1;
        infoEl.classList.remove('empty');
        infoText.innerHTML = '&#128308; Paling ramai: <strong>' + String(peakHour).padStart(2, '0') + ':00 - ' + String(peakEnd).padStart(2, '0') + ':00</strong>';
    } else {
        infoEl.classList.add('empty');
        infoText.textContent = 'Isi data traffic untuk melihat estimasi.';
    }
}
