<style>
.approvals-center {
    background: #fff;
    border: 1px solid #e1e7f0;
    border-radius: 16px;
    padding: 26px;
    box-shadow: 0 3px 14px rgba(20,40,70,.05);
    color: #1b2d49;
}
.approvals-center * { box-sizing: border-box; }

.approvals-heading h2 {
    margin: 0 0 7px;
    font-size: 23px;
}
.approvals-heading p {
    margin: 0;
    color: #71809a;
    font-size: 14px;
}
.approvals-grid {
    display: grid;
    grid-template-columns: repeat(2,minmax(0,1fr));
    gap: 14px;
    margin-top: 25px;
}
.approvals-section {
    background: #f7f9fc;
    border: 1px solid #e0e7f1;
    border-radius: 12px;
    padding: 22px;
    text-align: left;
    cursor: pointer;
    color: #1b2d49;
    font: inherit;
}
.approvals-section:hover { background: #eef3fa; }
.approvals-section.active {
    background: #eaf1fa;
    border-color: #315b8d;
}
.approvals-section .icon {
    display: block;
    font-size: 27px;
    margin-bottom: 12px;
}
.approvals-section strong {
    display: block;
    font-size: 16px;
    margin-bottom: 7px;
}
.approvals-section small {
    display: block;
    color: #71809a;
    line-height: 1.5;
}
.approvals-details {
    border-top: 1px solid #e1e7f0;
    margin-top: 25px;
    padding-top: 23px;
}
.approvals-details h3 { margin: 0 0 8px; }
.approvals-details p { color: #71809a; }

.approvals-toolbar {
    display: flex;
    gap: 12px;
    margin: 20px 0;
    flex-wrap: wrap;
}
.approvals-toolbar input,
.approvals-toolbar select {
    border: 1px solid #dce4ee;
    border-radius: 9px;
    padding: 11px 13px;
    font: inherit;
    background: white;
    color: #243650;
}
.approvals-toolbar input {
    flex: 1;
    min-width: 180px;
}
.approvals-table-wrap { overflow-x: auto; }
.approvals-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}
.approvals-table th {
    background: #f5f7fb;
    color: #65758f;
    text-align: left;
    padding: 13px;
}
.approvals-table td {
    border-bottom: 1px solid #edf1f6;
    padding: 14px;
}
.approvals-empty {
    text-align: center;
    background: #f7f9fc;
    border-radius: 12px;
    padding: 35px 15px;
    margin-top: 18px;
    color: #71809a;
}
.approvals-empty strong {
    display: block;
    color: #243650;
    margin: 10px 0 6px;
}
.approvals-action {
    background: #193b66;
    color: white;
    border: 0;
    border-radius: 8px;
    padding: 9px 14px;
    cursor: pointer;
}
.approvals-action:disabled {
    opacity: .45;
    cursor: not-allowed;
}
.approvals-status {
    background: #edf2f8;
    border-radius: 20px;
    padding: 5px 10px;
    font-size: 12px;
}
.approvals-hidden { display: none !important; }

@media(max-width:650px) {
    .approvals-grid { grid-template-columns: 1fr; }
    .approvals-center { padding: 17px; }
}
</style>

<div class="approvals-center" id="ashford-approvals">

    <div class="approvals-heading">
        <h2>Approvals Center</h2>
        <p>Review and manage administrative requests.</p>
    </div>

    <div class="approvals-grid">

        <button type="button" class="approvals-section active"
                data-category="grades">
            <span class="icon">🎓</span>
            <strong>Grade Corrections</strong>
            <small>Midterm, final, and overall grade correction requests</small>
        </button>

        <button type="button" class="approvals-section"
                data-category="access">
            <span class="icon">🔐</span>
            <strong>Account Access</strong>
            <small>Locked Student and Faculty accounts and access restoration</small>
        </button>

        <button type="button" class="approvals-section"
                data-category="registration">
            <span class="icon">📅</span>
            <strong>Registration Exceptions</strong>
            <small>Late add/drop, prerequisite, and capacity overrides</small>
        </button>

        <button type="button" class="approvals-section"
                data-category="academic">
            <span class="icon">📚</span>
            <strong>Academic Changes</strong>
            <small>Major, minor, advisor, and academic record changes</small>
        </button>

    </div>

    <div class="approvals-details">
        <h3 id="approvals-title">Grade Corrections</h3>
        <p id="approvals-description">
            Review faculty grade correction requests.
        </p>

        <div class="approvals-toolbar">
            <input type="search" id="approvals-search"
                   placeholder="Search requests..."
                   aria-label="Search requests">

            <select id="approvals-filter" aria-label="Filter request status">
                <option value="all">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>

        <div class="approvals-table-wrap">
            <table class="approvals-table">
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>Requested By</th>
                        <th>Request Type</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="approvals-rows"></tbody>
            </table>
        </div>

        <div class="approvals-empty" id="approvals-empty">
            <div style="font-size:28px">📥</div>
            <strong>No requests to display</strong>
            <span>Requests will appear here once connected to the database.</span>
        </div>

    </div>
</div>

<script>
(function () {
    const root = document.getElementById('ashford-approvals');
    if (!root) return;

    const categories = {
        grades: {
            title: 'Grade Corrections',
            description: 'Review faculty requests for midterm, final, and overall grade corrections.'
        },
        access: {
            title: 'Account Access',
            description: 'Review locked Student and Faculty accounts and authorized unlock requests.'
        },
        registration: {
            title: 'Registration Exceptions',
            description: 'Review late add/drop requests, registration overrides, and prerequisite exceptions.'
        },
        academic: {
            title: 'Academic Changes',
            description: 'Review major, minor, advisor, and academic record changes.'
        }
    };

    const buttons = root.querySelectorAll('[data-category]');
    const search = root.querySelector('#approvals-search');
    const filter = root.querySelector('#approvals-filter');
    const tbody = root.querySelector('#approvals-rows');
    const empty = root.querySelector('#approvals-empty');

    let selectedCategory = 'grades';

    function updateRows() {
        const query = search.value.toLowerCase().trim();
        const status = filter.value;
        let visible = 0;

        tbody.querySelectorAll('tr').forEach(row => {
            const matchesCategory =
                row.dataset.category === selectedCategory;

            const matchesStatus =
                status === 'all' || row.dataset.status === status;

            const matchesSearch =
                row.textContent.toLowerCase().includes(query);

            const show =
                matchesCategory && matchesStatus && matchesSearch;

            row.hidden = !show;
            if (show) visible++;
        });

        empty.classList.toggle('approvals-hidden', visible > 0);
    }

    buttons.forEach(button => {
        button.addEventListener('click', () => {
            selectedCategory = button.dataset.category;

            buttons.forEach(b => {
                b.classList.toggle('active', b === button);
                b.setAttribute('aria-pressed', String(b === button));
            });

            root.querySelector('#approvals-title').textContent =
                categories[selectedCategory].title;

            root.querySelector('#approvals-description').textContent =
                categories[selectedCategory].description;

            search.value = '';
            filter.value = 'all';
            updateRows();
        });
    });

    search.addEventListener('input', updateRows);
    filter.addEventListener('change', updateRows);

    updateRows();
})();
</script>
