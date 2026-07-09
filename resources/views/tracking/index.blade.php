<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Shipment</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=IBM+Plex+Mono:wght@400;500;600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        ink: '#0169CC',
                        ink2: '#01549D',
                        paper: '#FAFBFD',
                        amber: '#FFDF00',
                        route: '#2E9E6D',
                        alert: '#D6483F',
                        slate: '#5C7089',
                        line: '#DCE6F2',
                    },
                    fontFamily: {
                        display: ['"Space Grotesk"', 'sans-serif'],
                        mono: ['"IBM Plex Mono"', 'monospace'],
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>

    <style>
        body { background: #FAFBFD; }

        /* ticket card + punched-hole divider, evokes a torn waybill stub */
        .ticket {
            position: relative;
            background: #FFFFFF;
            border: 1px solid #DCE6F2;
            border-radius: 6px;
            box-shadow: 0 1px 2px rgba(18,33,61,0.05), 0 12px 28px -16px rgba(18,33,61,0.25);
        }

        .perf-divider {
            position: relative;
            height: 0;
            border-top: 2px dashed #DCE6F2;
            margin-left: -1.5rem;
            margin-right: -1.5rem;
        }
        .perf-divider::before,
        .perf-divider::after {
            content: '';
            position: absolute;
            top: -9px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #FAFBFD;
            border: 1px solid #DCE6F2;
        }
        .perf-divider::before { left: -9px; }
        .perf-divider::after { right: -9px; }

        /* decorative barcode bars, generated per tracking number */
        .barcode-strip {
            display: flex;
            align-items: stretch;
            height: 26px;
            color: #0169CC;
            opacity: 0.85;
        }
        .barcode-strip span {
            display: inline-block;
            height: 100%;
            background: currentColor;
        }

        /* status stamp, like a rotated ink postmark */
        .stamp {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border: 2px solid currentColor;
            border-radius: 999px;
            font-family: 'IBM Plex Mono', monospace;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.12em;
            transform: rotate(-2deg);
        }

        /* route line + travelling marker */
        .route-track {
            position: relative;
            height: 3px;
            background: #DCE6F2;
            border-radius: 999px;
        }
        .route-fill {
            position: absolute;
            top: 0; left: 0; height: 100%;
            border-radius: 999px;
            transition: width 0.6s ease, background-color 0.3s ease;
        }
        .route-node {
            width: 30px; height: 30px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-family: 'IBM Plex Mono', monospace;
            font-size: 12px;
            font-weight: 600;
            border: 2px solid #DCE6F2;
            background: #FFFFFF;
            color: #5C7089;
            transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
        }
        .route-marker {
            position: absolute;
            top: 50%;
            transform: translate(-50%, -50%);
            transition: left 0.6s ease;
        }

        /* timeline */
        .tl-item { position: relative; padding-left: 1.5rem; }
        .tl-item::before {
            content: '';
            position: absolute;
            left: 0; top: 4px;
            width: 9px; height: 9px;
            border-radius: 50%;
            background: #0169CC;
        }
        .tl-line {
            border-left: 2px dotted #DCE6F2;
        }

        @keyframes riseIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .rise-in { animation: riseIn 0.45s ease both; }

        @media (prefers-reduced-motion: reduce) {
            .rise-in { animation: none; }
            .route-fill, .route-marker, .route-node { transition: none; }
        }
    </style>
</head>

<body class="min-h-screen font-sans text-ink">

<div class="max-w-3xl mx-auto px-4 sm:px-6 py-12 sm:py-16">

    {{-- MARK --}}
    <div class="flex items-center gap-2 mb-10">
        <div class="w-8 h-8 rounded-sm bg-ink flex items-center justify-center">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#FFDF00" stroke-width="2">
                <path d="M3 7l9-4 9 4-9 4-9-4z"/>
                <path d="M3 7v10l9 4 9-4V7"/>
                <path d="M12 11v10"/>
            </svg>
        </div>
        <span class="font-mono text-xs tracking-[0.2em] text-slate uppercase">Shipment Tracking</span>
    </div>

    {{-- HERO / SEARCH --}}
    <div class="mb-4">
        <p class="inline-block font-mono text-xs tracking-[0.2em] text-ink bg-amber px-2.5 py-1 rounded-sm uppercase mb-3">Track · Shipment</p>
        <h1 class="font-display text-3xl sm:text-4xl font-semibold text-ink mb-2">Where's your package right now?</h1>
        <p class="text-slate mb-8">Enter the tracking number printed on your waybill receipt to see live status.</p>
    </div>

    <div class="ticket p-6 sm:p-7 mb-10">
        <div id="barcodeDecor" class="barcode-strip mb-5"></div>

        <label for="waybill" class="block font-mono text-[11px] tracking-[0.15em] text-slate uppercase mb-2">Tracking Number</label>
        <div class="flex flex-col sm:flex-row gap-3">
            <input
                id="waybill"
                type="text"
                value="{{ request('waybill') }}"
                placeholder="e.g. EG10394822"
                autocomplete="off"
                class="flex-1 px-4 py-3 border border-line rounded-sm font-mono text-base tracking-wide text-ink placeholder:text-slate/50 focus:outline-none focus:ring-2 focus:ring-ink/20 focus:border-ink"
                onkeydown="if(event.key==='Enter') trackOrder()"
            >
            <button
                id="trackBtn"
                onclick="trackOrder()"
                class="px-6 py-3 bg-ink text-white rounded-sm font-mono text-sm tracking-[0.1em] uppercase flex items-center justify-center gap-2 transition-all duration-200 hover:bg-ink2"
            >
                <span id="btnText">Track</span>
                <svg id="btnLoader" class="w-4 h-4 animate-spin hidden" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke="white" stroke-width="4" fill="none" stroke-dasharray="50" stroke-dashoffset="15"/>
                </svg>
            </button>
        </div>

        <p id="error" class="hidden mt-4 flex items-start gap-2 text-sm text-alert bg-alert/5 border border-alert/20 rounded-sm px-3 py-2">
            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="9"/><path d="M12 8v5"/><circle cx="12" cy="16" r="0.5" fill="currentColor"/>
            </svg>
            <span id="errorText"></span>
        </p>

        <p class="mt-4 text-xs text-slate">The tracking number is printed on the receipt your courier gave you at pickup.</p>
    </div>

    {{-- RESULT --}}
    <div id="result" class="hidden ticket p-6 sm:p-8 rise-in">

        <div class="flex flex-wrap items-start justify-between gap-4 mb-5">
            <div>
                <p class="font-mono text-[11px] tracking-[0.15em] text-slate uppercase mb-1">Waybill Number</p>
                <div class="flex items-center gap-2">
                    <p id="waybillText" class="font-mono text-xl font-semibold text-ink"></p>
                    <button onclick="copyWaybill()" title="Copy tracking number" class="text-slate hover:text-ink transition-colors">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="9" y="9" width="12" height="12" rx="1"/>
                            <path d="M5 15H4a1 1 0 01-1-1V4a1 1 0 011-1h10a1 1 0 011 1v1"/>
                        </svg>
                    </button>
                </div>
            </div>
            <span id="statusBadge" class="stamp"></span>
        </div>

        <div id="resultBarcode" class="barcode-strip mb-6"></div>

        <div class="perf-divider mb-6"></div>

        {{-- RECEIVER INFO --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-5 mb-10">
            <div>
                <p class="font-mono text-[10px] tracking-[0.12em] text-slate uppercase mb-1">Receiver</p>
                <p id="receiverName" class="text-sm font-medium text-ink"></p>
            </div>
            <div>
                <p class="font-mono text-[10px] tracking-[0.12em] text-slate uppercase mb-1">Mobile</p>
                <p id="receiverMobile" class="text-sm font-medium text-ink font-mono"></p>
            </div>
            <div class="col-span-2 sm:col-span-1">
                <p class="font-mono text-[10px] tracking-[0.12em] text-slate uppercase mb-1">Address</p>
                <p id="receiverAddress" class="text-sm font-medium text-ink"></p>
            </div>
            <div>
                <p class="font-mono text-[10px] tracking-[0.12em] text-slate uppercase mb-1">COD Amount</p>
                <p id="codAmount" class="text-sm font-semibold text-route font-mono"></p>
            </div>
        </div>

        {{-- ROUTE --}}
        <div class="relative mb-14 px-2">
            <div class="route-track">
                <div id="progressBar" class="route-fill bg-route" style="width:0%"></div>
                <div id="routeMarker" class="route-marker" style="left:0%">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#0169CC" stroke-width="1.8">
                        <rect x="1" y="7" width="13" height="9" rx="1"/>
                        <path d="M14 10h4l3 3v3h-7z"/>
                        <circle cx="6" cy="18" r="1.6" fill="#0169CC" stroke="none"/>
                        <circle cx="17" cy="18" r="1.6" fill="#0169CC" stroke="none"/>
                    </svg>
                </div>
            </div>
            <div id="stepsContainer" class="flex justify-between mt-3"></div>
        </div>

        {{-- TIMELINE --}}
        <div>
            <h3 class="font-display text-sm font-semibold text-ink mb-4 tracking-wide">Timeline</h3>
            <div id="timeline" class="tl-line pl-0 space-y-5"></div>
        </div>

    </div>

</div>

<script>
function renderBarcode(containerId, text) {
    const el = document.getElementById(containerId);
    if (!el) return;
    el.innerHTML = '';
    const source = (text && text.length ? text : 'TRACK YOUR SHIPMENT').toUpperCase();
    for (let i = 0; i < source.length; i++) {
        const code = source.charCodeAt(i);
        const width = 2 + (code % 4); // 2-5px
        const bar = document.createElement('span');
        bar.style.width = width + 'px';
        bar.style.marginRight = (i % 3 === 0 ? '4px' : '1.5px');
        el.appendChild(bar);
    }
}
renderBarcode('barcodeDecor', 'TRACK YOUR SHIPMENT');

function copyWaybill() {
    const text = document.getElementById('waybillText').innerText;
    if (text) navigator.clipboard.writeText(text);
}

function trackOrder() {
    let waybill = document.getElementById('waybill').value.trim();
    let error = document.getElementById('error');
    let errorText = document.getElementById('errorText');

    let btn = document.getElementById('trackBtn');
    let loader = document.getElementById('btnLoader');
    let btnText = document.getElementById('btnText');

    if (!waybill) return;

    // START LOADING
    btn.disabled = true;
    loader.classList.remove('hidden');
    btnText.innerText = 'Searching';
    btn.classList.add('opacity-70', 'cursor-not-allowed');

    fetch(`/admin/track/search/${waybill}`)
        .then(res => res.json())
        .then(data => {

            // STOP LOADING
            btn.disabled = false;
            loader.classList.add('hidden');
            btnText.innerText = 'Track';
            btn.classList.remove('opacity-70', 'cursor-not-allowed');

            if (!data.success) {
                errorText.innerText = data.message;
                error.classList.remove('hidden');
                document.getElementById('result').classList.add('hidden');
                return;
            }

            error.classList.add('hidden');

            let order = data.order;
            document.getElementById('waybillText').innerText = order.waybill_number ?? '-';
            document.getElementById('receiverName').innerText = order.receiver_name ?? '-';
            document.getElementById('receiverMobile').innerText = order.receiver_mobile_1 ?? '-';
            document.getElementById('receiverAddress').innerText = order.receiver_address ?? '-';
            document.getElementById('codAmount').innerText =
                (order.cod_amount !== null && order.cod_amount !== undefined)
                    ? order.cod_amount + ' EGP'
                    : '-';

            renderBarcode('resultBarcode', order.waybill_number ?? '');

            // SHOW RESULT
            document.getElementById('result').classList.remove('hidden');

            // STATUS BADGE / STAMP
            let badge = document.getElementById('statusBadge');
            badge.innerText = order.status.replaceAll('_', ' ').toUpperCase();

            let isFailed = [
                'undelivered',
                'returned_to_shipper',
                'returned_and_cost_paid'
            ].includes(order.status);

            let isDelivered = ['success_delivery', 'partial_return'].includes(order.status);

            badge.classList.remove('text-route', 'text-alert', 'text-amber', 'bg-amber', 'text-ink', 'border-transparent');
            if (isFailed) {
                badge.classList.add('text-alert');
            } else if (isDelivered) {
                badge.classList.add('text-route');
            } else {
                // in-progress: filled yellow chip reads better than yellow text on white
                badge.classList.add('bg-amber', 'text-ink', 'border-transparent');
            }

            // STEP DEFINITIONS
            const steps = [
                { key: 'created', label: 'Created' },
                { key: 'picked_up', label: 'Picked Up' },
                { key: 'in_progress', label: 'In Progress' },
                { key: 'out_for_delivery', label: 'Out for Delivery' },
                { key: 'delivered', label: 'Delivered' },
            ];

            function getStepFromStatus(status) {
                switch (status) {
                    case 'pickup_request': return 1;
                    case 'warehouse_received': return 2;
                    case 'time_scheduled': return 3;
                    case 'out_for_delivery': return 4;
                    case 'success_delivery':
                    case 'partial_return': return 5;
                    case 'undelivered':
                    case 'returned_to_shipper':
                    case 'returned_and_cost_paid': return 4; // stopped before delivery
                    default: return 1;
                }
            }

            let currentStep = getStepFromStatus(order.status);

            // ROUTE FILL + MARKER
            let progress = ((currentStep - 1) / (steps.length - 1)) * 100;
            let progressBar = document.getElementById('progressBar');
            let marker = document.getElementById('routeMarker');
            progressBar.style.width = progress + '%';
            marker.style.left = progress + '%';

            progressBar.classList.remove('bg-route', 'bg-alert');
            progressBar.classList.add(isFailed ? 'bg-alert' : 'bg-route');

            // ROUTE NODES
            let stepsHtml = '';
            steps.forEach((step, i) => {
                let index = i + 1;
                let active = index <= currentStep;
                let isCurrent = index === currentStep;

                let nodeClasses = 'route-node';
                if (active) {
                    nodeClasses += (isFailed && isCurrent)
                        ? ' bg-alert border-alert text-white'
                        : ' bg-ink border-ink text-white';
                }

                stepsHtml += `
                    <div class="flex flex-col items-center" style="width:${100 / steps.length}%">
                        <div class="${nodeClasses}">${active ? '&#10003;' : index}</div>
                        <span class="text-[11px] font-mono text-slate mt-2 text-center leading-tight">${step.label}</span>
                    </div>
                `;
            });
            document.getElementById('stepsContainer').innerHTML = stepsHtml;

            // TIMELINE
            let timeline = `
                <div class="tl-item">
                    <p class="text-sm font-medium text-ink">Order created</p>
                    <p class="text-xs font-mono text-slate mt-0.5">${formatDate(order.created_at)}</p>
                </div>
            `;

            if (currentStep >= 2) timeline += `<div class="tl-item"><p class="text-sm font-medium text-ink">Picked up by courier</p></div>`;
            if (currentStep >= 3) timeline += `<div class="tl-item"><p class="text-sm font-medium text-ink">In progress</p></div>`;
            if (currentStep >= 4) timeline += `<div class="tl-item"><p class="text-sm font-medium ${isFailed ? 'text-alert' : 'text-ink'}">${isFailed ? 'Delivery attempt unsuccessful' : 'Out for delivery'}</p></div>`;
            if (currentStep >= 5) timeline += `
                <div class="tl-item">
                    <p class="text-sm font-medium text-route">Delivered successfully</p>
                    <p class="text-xs font-mono text-slate mt-0.5">${formatDate(order.updated_at)}</p>
                </div>
            `;

            document.getElementById('timeline').innerHTML = timeline;
        })
        .catch(() => {
            // HANDLE ERROR
            btn.disabled = false;
            loader.classList.add('hidden');
            btnText.innerText = 'Track';
            btn.classList.remove('opacity-70', 'cursor-not-allowed');

            errorText.innerText = 'Something went wrong. Please try again.';
            error.classList.remove('hidden');
        });
}

function formatDate(dateStr) {
    let d = new Date(dateStr);
    return d.toLocaleString();
}

// Auto-search when arriving with ?waybill=... in the URL
@if (request('waybill'))
    trackOrder();
@endif
</script>

</body>
</html>