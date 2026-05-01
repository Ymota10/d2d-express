<!DOCTYPE html>
<html>
<head>
    <title>Track Shipment</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 py-10">

<div class="max-w-5xl mx-auto">

    {{-- 🔍 SEARCH --}}
    <div class="bg-white p-6 rounded shadow mb-6 text-center">
        <h1 class="text-2xl font-bold mb-4">Track Your Shipment</h1>

        <div class="flex justify-center gap-2">
            <input 
                id="waybill"
                type="text"
                placeholder="Enter tracking number"
                class="px-4 py-2 border rounded w-80"
            >

    <button 
    id="trackBtn"
    onclick="trackOrder()"
    class="px-5 py-2 bg-blue-600 text-white rounded flex items-center justify-center gap-2 transition-all duration-200"
>
    <span id="btnText">Track</span>

    <!-- Loader -->
    <svg id="btnLoader" class="w-4 h-4 animate-spin hidden" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="10" stroke="white" stroke-width="4" fill="none"/>
    </svg>
</button>


        </div>

        <p id="error" class="text-red-500 mt-3 hidden"></p>
    </div>

    {{-- 📦 RESULT --}}
    <div id="result" class="hidden bg-white p-8 rounded shadow">

    <div class="text-center mb-8 space-y-2">

<div>
    <p class="text-gray-500">Waybill Number</p>
    <p id="waybillText" class="text-lg font-semibold"></p>
</div>

<div>
    <p class="text-gray-500">Receiver Name</p>
    <p id="receiverName" class="text-md font-semibold"></p>
</div>

<div>
    <p class="text-gray-500">Mobile</p>
    <p id="receiverMobile" class="text-md font-semibold"></p>
</div>

<div>
    <p class="text-gray-500">Address</p>
    <p id="receiverAddress" class="text-md font-semibold"></p>
</div>

<div>
    <p class="text-gray-500">COD Amount</p>
    <p id="codAmount" class="text-md font-semibold text-green-600"></p>
</div>

</div>

        {{-- PROGRESS BAR --}}
        <div class="relative flex justify-between items-center w-full mb-16">

            <div class="absolute w-full h-1 bg-gray-200"></div>

            <div id="progressBar"
                 class="absolute h-1 bg-green-500 transition-all duration-500">
            </div>

            <div id="stepsContainer" class="flex justify-between w-full"></div>
        </div>

        {{-- STATUS --}}
        <div class="text-center mb-10">
            <span id="statusBadge"
                class="px-4 py-2 rounded bg-green-100 text-green-700 font-semibold">
            </span>
        </div>

        {{-- TIMELINE --}}
        <div class="max-w-md mx-auto">
            <h3 class="text-lg font-semibold mb-4">Timeline</h3>

            <div id="timeline" class="border-l-2 border-gray-200 pl-4 space-y-6"></div>
        </div>

    </div>

</div>

<script>
function trackOrder() {
    let waybill = document.getElementById('waybill').value.trim();
    let error = document.getElementById('error');

    let btn = document.getElementById('trackBtn');
    let loader = document.getElementById('btnLoader');
    let btnText = document.getElementById('btnText');

    if (!waybill) return;

    // ✅ START LOADING
    btn.disabled = true;
    loader.classList.remove('hidden');
    btnText.innerText = 'Searching...';
    btn.classList.add('opacity-70', 'cursor-not-allowed');

    fetch(`/admin/track/search/${waybill}`)
        .then(res => res.json())
        .then(data => {

            // ✅ STOP LOADING
            btn.disabled = false;
            loader.classList.add('hidden');
            btnText.innerText = 'Track';
            btn.classList.remove('opacity-70', 'cursor-not-allowed');

            if (!data.success) {
                error.innerText = data.message;
                error.classList.remove('hidden');
                document.getElementById('result').classList.add('hidden');
                return;
            }

            error.classList.add('hidden');

            let order = data.order;
            document.getElementById('waybillText').innerText = order.waybill_number ?? '-';

document.getElementById('receiverName').innerText = order.receiver_name ?? '-';

document.getElementById('receiverMobile').innerText =
    order.receiver_mobile_1 ?? '-';

document.getElementById('receiverAddress').innerText =
    order.receiver_address ?? '-';

document.getElementById('codAmount').innerText =
    (order.cod_amount !== null && order.cod_amount !== undefined)
        ? order.cod_amount + ' EGP'
        : '-';

            // ✅ SHOW RESULT
            document.getElementById('result').classList.remove('hidden');
            document.getElementById('waybillText').innerText = order.waybill_number;

            // ✅ STATUS BADGE
            
        // ✅ STATUS BADGE
let badge = document.getElementById('statusBadge');

badge.innerText = order.status.replaceAll('_',' ').toUpperCase();

// ✅ DEFINE FIRST
let isFailed = [
    'undelivered',
    'returned_to_shipper',
    'returned_and_cost_paid'
].includes(order.status);

// ✅ THEN USE
badge.classList.remove('bg-green-100','text-green-700','bg-red-100','text-red-700');

if (isFailed) {
    badge.classList.add('bg-red-100','text-red-700');
} else {
    badge.classList.add('bg-green-100','text-green-700');
}

           // ✅ STEP DEFINITIONS
const steps = [
    {key:'created', label:'Created'},
    {key:'picked_up', label:'Picked Up'},
    {key:'in_progress', label:'In Progress'},
    {key:'out_for_delivery', label:'Out for Delivery'},
    {key:'delivered', label:'Delivered'},
];

// ✅ STATUS → STEP MAPPING
function getStepFromStatus(status) {
    switch (status) {
        case 'pickup_request':
            return 1;

        case 'warehouse_received':
            return 2;

        case 'time_scheduled':
            return 3;

        case 'out_for_delivery':
            return 4;

        case 'success_delivery':
        case 'partial_return':
            return 5;

        case 'undelivered':
        case 'returned_to_shipper':
        case 'returned_and_cost_paid':
            return 4; // stopped before delivery

        default:
            return 1;
    }
}

let currentStep = getStepFromStatus(order.status);

            // ✅ PROGRESS WIDTH
            let progress = ((currentStep - 1) / (steps.length - 1)) * 100;
            let progressBar = document.getElementById('progressBar');
progressBar.style.width = progress + '%';

// 🔴 Red if failed
progressBar.classList.remove('bg-green-500', 'bg-red-500');
progressBar.classList.add(isFailed ? 'bg-red-500' : 'bg-green-500');

            // ✅ RENDER STEPS
            let stepsHtml = '';
steps.forEach((step, i) => {
    let index = i + 1;
    let active = index <= currentStep;

    stepsHtml += `
        <div class="flex flex-col items-center z-10">
            <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold
                ${active 
                    ? (isFailed && index === currentStep 
                        ? 'bg-red-500 text-white' 
                        : 'bg-green-500 text-white') 
                    : 'bg-gray-200 text-gray-500'}">
                
                ${active ? '✓' : index}
            </div>

            <span class="text-sm mt-2">${step.label}</span>
        </div>
    `;
});

            document.getElementById('stepsContainer').innerHTML = stepsHtml;

            // ✅ TIMELINE
            let timeline = `
                <div>
                    <p class="font-semibold">Order is created</p>
                    <p class="text-xs text-gray-500">${formatDate(order.created_at)}</p>
                </div>
            `;

            if (currentStep >= 2) timeline += `<div><p class="font-semibold">Order picked up</p></div>`;
            if (currentStep >= 3) timeline += `<div><p class="font-semibold">In progress</p></div>`;
            if (currentStep >= 4) timeline += `<div><p class="font-semibold">Out for delivery</p></div>`;
            if (currentStep >= 5) timeline += `
                <div>
                    <p class="font-semibold text-green-600">Delivered successfully</p>
                    <p class="text-xs text-gray-500">${formatDate(order.updated_at)}</p>
                </div>
            `;

            document.getElementById('timeline').innerHTML = timeline;
        })
        .catch(() => {
            // ✅ HANDLE ERROR
            btn.disabled = false;
            loader.classList.add('hidden');
            btnText.innerText = 'Track';
            btn.classList.remove('opacity-70', 'cursor-not-allowed');

            error.innerText = 'Something went wrong';
            error.classList.remove('hidden');
        });
}
// ✅ Format date
function formatDate(dateStr) {
    let d = new Date(dateStr);
    return d.toLocaleString();
}
</script>

</body>
</html>