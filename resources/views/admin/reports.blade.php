@extends('admin.layout')

@section('title', 'Reports')

@section('content')

    {{-- =========================================================
        ⚠️ TEMPORARY HARDCODED DATA — FOR UI/UX PREVIEW ONLY
        TODO: alisin ito pag kinonekta na natin sa DB / controller.
        Yung $reports array dito ang gagawing $reports mula sa
        controller (paginated) mamaya — drop-in lang ang variable
        name once totoong data na ang gagamitin.
    ========================================================= --}}
    @php
        $reportTypeLabels = [
            'sales_summary'        => 'Sales Summary',
            'commission_breakdown' => 'Commission Breakdown',
            'seller_performance'   => 'Seller Performance',
            'user_growth'          => 'User Growth',
            'disputes_log'         => 'Disputes Log',
        ];

        $typeStyles = [
            'sales_summary'        => ['bg' => 'bg-yellow/20', 'icon' => 'text-yellow-600'],
            'commission_breakdown' => ['bg' => 'bg-mint/15',   'icon' => 'text-mint-dark'],
            'seller_performance'   => ['bg' => 'bg-sky/15',    'icon' => 'text-sky'],
            'user_growth'          => ['bg' => 'bg-sky/15',    'icon' => 'text-sky'],
            'disputes_log'         => ['bg' => 'bg-coral/15',  'icon' => 'text-coral'],
        ];

        $reports = collect([
            (object) [
                'id' => 1,
                'type' => 'sales_summary',
                'title' => 'Monthly Sales Summary — August 2026',
                'generated_label' => 'Generated 3 hours ago',
                'format' => 'PDF',
                'date_from' => '2026-08-01',
                'date_to' => '2026-08-31',
                'requested_by' => 'Admin',
                'file_size' => '1.2 MB',
                'description' => 'Buod ng lahat ng benta, orders, at revenue trends para sa buong buwan.',
            ],
            (object) [
                'id' => 2,
                'type' => 'commission_breakdown',
                'title' => 'Commission Breakdown — July 2026',
                'generated_label' => 'Generated 2 days ago',
                'format' => 'CSV',
                'date_from' => '2026-07-01',
                'date_to' => '2026-07-31',
                'requested_by' => 'Admin',
                'file_size' => '384 KB',
                'description' => 'Detalyadong listahan ng commission fees na siningil sa bawat seller.',
            ],
            (object) [
                'id' => 3,
                'type' => 'seller_performance',
                'title' => 'Seller Performance — Q2 2026',
                'generated_label' => 'Generated 1 week ago',
                'format' => 'PDF',
                'date_from' => '2026-04-01',
                'date_to' => '2026-06-30',
                'requested_by' => 'Admin',
                'file_size' => '2.8 MB',
                'description' => 'Ranking at performance metrics ng mga seller batay sa sales at ratings.',
            ],
            (object) [
                'id' => 4,
                'type' => 'disputes_log',
                'title' => 'Disputes Log — July 2026',
                'generated_label' => 'Generated 2 weeks ago',
                'format' => 'CSV',
                'date_from' => '2026-07-01',
                'date_to' => '2026-07-31',
                'requested_by' => 'Admin',
                'file_size' => '156 KB',
                'description' => 'Listahan ng lahat ng na-file na complaints at disputes kasama ang resolution status.',
            ],
        ]);

        // Flattened version para magamit sa JS (view modal + generate/download)
        $reportsForJs = $reports->map(fn ($r) => [
            'id' => $r->id,
            'type' => $r->type,
            'type_label' => $reportTypeLabels[$r->type] ?? ucfirst($r->type),
            'title' => $r->title,
            'generated_label' => $r->generated_label,
            'format' => $r->format,
            'date_from' => $r->date_from,
            'date_to' => $r->date_to,
            'requested_by' => $r->requested_by,
            'file_size' => $r->file_size,
            'description' => $r->description,
        ])->values();
    @endphp

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-navy">Reports</h1>
        <p class="text-sm text-slate-500 mt-1">Bumuo at tingnan ang mga platform reports.</p>
    </div>

    {{-- ============ GENERATE REPORT PANEL ============ --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-5 mb-6">
        <h2 class="font-semibold text-navy text-sm mb-4">Generate New Report</h2>

        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">

            <div>
                <label for="report_type" class="text-xs font-medium text-slate-500 mb-1 block">Report Type</label>
                <select id="report_type"
                    class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2 focus:border-mint focus:outline-none focus:ring-2 focus:ring-mint/20">
                    @foreach ($reportTypeLabels as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="date_from" class="text-xs font-medium text-slate-500 mb-1 block">Date From</label>
                <input type="date" id="date_from"
                    class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2 focus:border-mint focus:outline-none focus:ring-2 focus:ring-mint/20">
            </div>

            <div>
                <label for="date_to" class="text-xs font-medium text-slate-500 mb-1 block">Date To</label>
                <input type="date" id="date_to"
                    class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2 focus:border-mint focus:outline-none focus:ring-2 focus:ring-mint/20">
            </div>

            <div class="flex items-end">
                <button type="button" id="generateReportBtn"
                    class="w-full px-4 py-2 rounded-xl text-sm font-semibold text-white bg-mint-dark hover:opacity-90 transition">
                    Generate Report
                </button>
            </div>

        </div>

        <p id="generateReportError" class="hidden text-xs text-coral mt-3"></p>
    </div>

    {{-- ============ GENERATED REPORTS LIST ============ --}}
    <div class="bg-white rounded-2xl border border-slate-200">

        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-navy text-sm">Recent Reports</h2>
            <span id="reportsTotalLabel" class="text-xs text-slate-400">{{ $reports->count() }} total</span>
        </div>

        <div id="reportsListBody" class="divide-y divide-slate-100">

            @foreach ($reports as $report)
                @php
                    $style = $typeStyles[$report->type] ?? ['bg' => 'bg-slate-100', 'icon' => 'text-slate-500'];
                @endphp

                <div class="flex items-center justify-between gap-4 px-5 py-4" data-report-row="{{ $report->id }}">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl {{ $style['bg'] }} flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 {{ $style['icon'] }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2a2 2 0 012-2h2a2 2 0 012 2v2m-6 0h6m-6 0H5a2 2 0 01-2-2V5a2 2 0 012-2h10l4 4v8a2 2 0 01-2 2h-2" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 3v4h4" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-navy truncate">{{ $report->title }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $report->generated_label }} · {{ $report->format }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <button type="button" class="report-view-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50" data-report-id="{{ $report->id }}">
                            View
                        </button>
                        <button type="button" class="report-download-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-mint-dark hover:opacity-90" data-report-id="{{ $report->id }}">
                            Download
                        </button>
                    </div>
                </div>
            @endforeach

        </div>

        {{-- ============ PAGINATION (static — one page lang ng hardcoded data) ============ --}}
        <div class="flex items-center justify-between px-5 py-4 border-t border-slate-100">
            <p id="reportsShowingLabel" class="text-xs text-slate-400">Showing 1–{{ $reports->count() }} of {{ $reports->count() }}</p>
            <div class="flex items-center gap-1">
                <button type="button" class="w-8 h-8 rounded-lg text-xs font-semibold border border-slate-200 text-slate-400" disabled>‹</button>
                <button type="button" class="w-8 h-8 rounded-lg text-xs font-semibold bg-navy text-white">1</button>
                <button type="button" class="w-8 h-8 rounded-lg text-xs font-semibold border border-slate-200 text-slate-400" disabled>›</button>
            </div>
        </div>

    </div>


    {{-- =========================================================
        REPORT DETAILS MODAL — pattern galing sa User Accounts page
        (userModalOverlay/Panel), data pinopopulate via JS mula sa
        hardcoded reportsData sa taas.
    ========================================================= --}}
    <div
        id="reportModalOverlay"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-navy/40 backdrop-blur-[2px] px-4"
    >

        <div
            id="reportModalPanel"
            class="relative w-full max-w-lg
                   max-h-[90vh]
                   overflow-y-auto
                   bg-white
                   rounded-2xl
                   border border-slate-200
                   shadow-xl
                   translate-y-2 opacity-0
                   transition
                   duration-150"
        >

            <div class="h-1.5 bg-mint-dark rounded-t-2xl"></div>

            <button
                type="button"
                id="reportModalClose"
                aria-label="Close"
                class="absolute top-4 right-4 z-20
                       w-10 h-10
                       rounded-full
                       bg-slate-100
                       text-navy/45
                       flex items-center justify-center
                       hover:bg-mint/10
                       hover:text-mint-dark
                       focus:outline-none
                       focus:ring-4 focus:ring-mint/15
                       transition"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            {{-- HEADER --}}
            <div class="px-6 pt-9 pb-5 border-b border-slate-100 pr-16">

                <div class="flex items-center gap-3 mb-3">
                    <div id="reportModalIconWrap" class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2a2 2 0 012-2h2a2 2 0 012 2v2m-6 0h6m-6 0H5a2 2 0 01-2-2V5a2 2 0 012-2h10l4 4v8a2 2 0 01-2 2h-2" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 3v4h4" />
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <p class="text-[11px] font-bold tracking-[0.12em] text-mint-dark mb-1">REPORT DETAILS</p>
                        <h2 id="reportModalTitle" class="text-base sm:text-lg font-bold text-navy leading-snug"></h2>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <span id="reportModalTypeBadge" class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-full"></span>
                    <span id="reportModalFormatBadge" class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-500"></span>
                </div>

            </div>

            {{-- BODY --}}
            <div class="px-6 py-5 space-y-4">

                <div class="bg-slate-50 rounded-xl p-4">
                    <dl class="space-y-2.5">
                        <div class="flex justify-between gap-3">
                            <dt class="text-xs text-slate-400 shrink-0">Date Range</dt>
                            <dd id="reportModalDateRange" class="text-xs text-slate-700 text-right"></dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-xs text-slate-400 shrink-0">Requested By</dt>
                            <dd id="reportModalRequestedBy" class="text-xs text-slate-700 text-right"></dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-xs text-slate-400 shrink-0">Generated</dt>
                            <dd id="reportModalGenerated" class="text-xs text-slate-700 text-right"></dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-xs text-slate-400 shrink-0">File Size</dt>
                            <dd id="reportModalFileSize" class="text-xs text-slate-700 text-right"></dd>
                        </div>
                    </dl>
                </div>

                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-1.5">Description</p>
                    <p id="reportModalDescription" class="text-xs text-slate-600 leading-relaxed"></p>
                </div>

            </div>

            {{-- FOOTER --}}
            <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-2">
                <button
                    type="button"
                    id="reportModalCloseBtn2"
                    class="h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50 transition"
                >
                    Close
                </button>
                <button
                    type="button"
                    id="reportModalDownloadBtn"
                    class="h-9 inline-flex items-center gap-1.5 px-4 rounded-full text-xs font-semibold text-white bg-mint-dark hover:opacity-90 transition-all duration-300"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l-4-4m4 4l4-4M4 21h16" />
                    </svg>
                    Download
                </button>
            </div>

        </div>

    </div>


    {{-- =========================================================
        DATA + BEHAVIOR
        TODO: pag naka-DB na, ang generate/download dito ay dapat
        na totoong POST/GET papunta sa controller (form submit /
        fetch()) sa halip na client-side lang na simulation.
    ========================================================= --}}
    <script>
        let reportsData = @json($reportsForJs);
        let nextReportId = Math.max(...reportsData.map(r => r.id)) + 1;

        const reportTypeLabels = @json($reportTypeLabels);

        const typeStyles = {
            sales_summary:        { bg: 'bg-yellow/20', icon: 'text-yellow-600', badge: 'bg-yellow/20 text-yellow-700' },
            commission_breakdown: { bg: 'bg-mint/15',   icon: 'text-mint-dark',  badge: 'bg-mint/15 text-mint-dark' },
            seller_performance:   { bg: 'bg-sky/15',    icon: 'text-sky',        badge: 'bg-sky/15 text-sky' },
            user_growth:          { bg: 'bg-sky/15',    icon: 'text-sky',        badge: 'bg-sky/15 text-sky' },
            disputes_log:         { bg: 'bg-coral/15',  icon: 'text-coral',      badge: 'bg-coral/15 text-coral' },
        };

        const folderIconSvg = '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2a2 2 0 012-2h2a2 2 0 012 2v2m-6 0h6m-6 0H5a2 2 0 01-2-2V5a2 2 0 012-2h10l4 4v8a2 2 0 01-2 2h-2" /><path stroke-linecap="round" stroke-linejoin="round" d="M9 3v4h4" /></svg>';

        function formatDate(dateStr) {
            const d = new Date(dateStr + 'T00:00:00');
            return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        }

        /* -----------------------------------------------------------
           RENDER a single report row (dùng sa Generate Report)
        ----------------------------------------------------------- */
        function renderReportRow(report) {
            const style = typeStyles[report.type] || { bg: 'bg-slate-100', icon: 'text-slate-500' };

            const row = document.createElement('div');
            row.className = 'flex items-center justify-between gap-4 px-5 py-4';
            row.setAttribute('data-report-row', report.id);
            row.innerHTML = `
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl ${style.bg} flex items-center justify-center shrink-0">
                        <span class="${style.icon}">${folderIconSvg}</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-navy truncate">${report.title}</p>
                        <p class="text-xs text-slate-400 mt-0.5">${report.generated_label} · ${report.format}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" class="report-view-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50" data-report-id="${report.id}">
                        View
                    </button>
                    <button type="button" class="report-download-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-mint-dark hover:opacity-90" data-report-id="${report.id}">
                        Download
                    </button>
                </div>
            `;

            row.querySelector('.report-view-btn').addEventListener('click', () => openReportModal(report.id));
            row.querySelector('.report-download-btn').addEventListener('click', () => downloadReport(report.id));

            return row;
        }

        function updateReportsCount() {
            document.getElementById('reportsTotalLabel').textContent = reportsData.length + ' total';
            document.getElementById('reportsShowingLabel').textContent = `Showing 1–${reportsData.length} of ${reportsData.length}`;
        }

        /* -----------------------------------------------------------
           GENERATE REPORT — hardcoded/simulated lang, walang
           totoong backend call. Nagdadagdag lang ng bagong item sa
           itaas ng listahan.
        ----------------------------------------------------------- */
        const generateBtn = document.getElementById('generateReportBtn');
        const generateError = document.getElementById('generateReportError');

        generateBtn.addEventListener('click', () => {
            const typeSelect = document.getElementById('report_type');
            const dateFromInput = document.getElementById('date_from');
            const dateToInput = document.getElementById('date_to');

            const type = typeSelect.value;
            const dateFrom = dateFromInput.value;
            const dateTo = dateToInput.value;

            if (!dateFrom || !dateTo) {
                generateError.textContent = 'Pumili ng Date From at Date To.';
                generateError.classList.remove('hidden');
                return;
            }

            if (new Date(dateFrom) > new Date(dateTo)) {
                generateError.textContent = 'Ang Date From ay hindi puwedeng lagpas sa Date To.';
                generateError.classList.remove('hidden');
                return;
            }

            generateError.classList.add('hidden');

            const typeLabel = reportTypeLabels[type] || type;
            const format = Math.random() > 0.5 ? 'PDF' : 'CSV';

            const newReport = {
                id: nextReportId++,
                type: type,
                type_label: typeLabel,
                title: `${typeLabel} — ${formatDate(dateFrom)} to ${formatDate(dateTo)}`,
                generated_label: 'Generated just now',
                format: format,
                date_from: dateFrom,
                date_to: dateTo,
                requested_by: 'Admin',
                file_size: (Math.random() * 3 + 0.2).toFixed(1) + ' MB',
                description: `Auto-generated ${typeLabel.toLowerCase()} report para sa napiling date range.`,
            };

            reportsData.unshift(newReport);

            const listBody = document.getElementById('reportsListBody');
            listBody.prepend(renderReportRow(newReport));

            updateReportsCount();

            dateFromInput.value = '';
            dateToInput.value = '';
        });

        /* -----------------------------------------------------------
           VIEW MODAL
        ----------------------------------------------------------- */
        const reportOverlay = document.getElementById('reportModalOverlay');
        const reportPanel = document.getElementById('reportModalPanel');
        let currentReport = null;

        function openReportModal(id) {
            const report = reportsData.find(r => r.id === id);
            if (!report) return;

            currentReport = report;
            const style = typeStyles[report.type] || { bg: 'bg-slate-100', icon: 'text-slate-500', badge: 'bg-slate-100 text-slate-500' };

            document.getElementById('reportModalIconWrap').className = 'w-11 h-11 rounded-xl flex items-center justify-center shrink-0 ' + style.bg + ' ' + style.icon;
            document.getElementById('reportModalTitle').textContent = report.title;
            document.getElementById('reportModalTypeBadge').className = 'inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-full ' + style.badge;
            document.getElementById('reportModalTypeBadge').textContent = report.type_label;
            document.getElementById('reportModalFormatBadge').textContent = report.format;
            document.getElementById('reportModalDateRange').textContent = `${formatDate(report.date_from)} – ${formatDate(report.date_to)}`;
            document.getElementById('reportModalRequestedBy').textContent = report.requested_by;
            document.getElementById('reportModalGenerated').textContent = report.generated_label;
            document.getElementById('reportModalFileSize').textContent = report.file_size;
            document.getElementById('reportModalDescription').textContent = report.description;

            reportOverlay.classList.remove('hidden');
            reportOverlay.classList.add('flex');
            requestAnimationFrame(() => {
                reportPanel.classList.remove('translate-y-2', 'opacity-0');
            });
        }

        function closeReportModal() {
            reportPanel.classList.add('translate-y-2', 'opacity-0');
            setTimeout(() => {
                reportOverlay.classList.add('hidden');
                reportOverlay.classList.remove('flex');
            }, 150);
        }

        document.querySelectorAll('.report-view-btn').forEach(btn => {
            btn.addEventListener('click', () => openReportModal(parseInt(btn.dataset.reportId, 10)));
        });

        document.getElementById('reportModalClose').addEventListener('click', closeReportModal);
        document.getElementById('reportModalCloseBtn2').addEventListener('click', closeReportModal);
        reportOverlay.addEventListener('click', (e) => {
            if (e.target === reportOverlay) closeReportModal();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !reportOverlay.classList.contains('hidden')) {
                closeReportModal();
            }
        });

        document.getElementById('reportModalDownloadBtn').addEventListener('click', () => {
            if (currentReport) downloadReport(currentReport.id);
        });

        /* -----------------------------------------------------------
           DOWNLOAD — dahil walang backend/file storage pa, gumagawa
           tayo ng dummy text file client-side lang para totoong
           may na-do-download. Palitan na lang ito ng route papunta
           sa totoong file (Storage::download() atbp.) kapag naka-DB na.
        ----------------------------------------------------------- */
        function downloadReport(id) {
            const report = reportsData.find(r => r.id === id);
            if (!report) return;

            const content =
                `${report.title}\n` +
                `Type: ${report.type_label}\n` +
                `Format: ${report.format}\n` +
                `Date Range: ${formatDate(report.date_from)} - ${formatDate(report.date_to)}\n` +
                `Requested By: ${report.requested_by}\n` +
                `${report.generated_label}\n\n` +
                `${report.description}\n\n` +
                `(This is placeholder content — walang pa totoong report file, ` +
                `hardcoded/preview data lang ito.)`;

            const blob = new Blob([content], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const filename = report.title.toLowerCase().replace(/[^a-z0-9]+/g, '-') + '.txt';

            const link = document.createElement('a');
            link.href = url;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        }

        document.querySelectorAll('.report-download-btn').forEach(btn => {
            btn.addEventListener('click', () => downloadReport(parseInt(btn.dataset.reportId, 10)));
        });
    </script>

@endsection