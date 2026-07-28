(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const config = window.pmMaintenanceConfig || {};
        const tableReady = !!config.tableReady;
        const pageEndpoint = config.pageEndpoint || 'maintenance.php';

        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const priorityFilter = document.getElementById('priorityFilter');
        const tableBody = document.getElementById('maintenanceTableBody');
        const resultCount = document.getElementById('resultCount');
        const alertContainer = document.getElementById('alertContainer');

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/\"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function showAlert(message, type) {
            alertContainer.innerHTML = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
                escapeHtml(message) +
                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        }

        function staffOptions(selectedId) {
            const options = ["<option value=''>Unassigned</option>"];
            document.querySelectorAll('#maintenanceTableBody tr select[data-field="assigned_staff_id"] option').forEach(function (opt) {
                if (opt.value === '') {
                    return;
                }

                const selected = String(opt.value) === String(selectedId) ? 'selected' : '';
                options.push('<option value="' + escapeHtml(opt.value) + '" ' + selected + '>' + escapeHtml(opt.textContent) + '</option>');
            });
            return options.join('');
        }

        function getStatusBadgeClass(status) {
            const normalized = String(status || 'pending').toLowerCase();
            if (normalized === 'resolved') {
                return 'success';
            }
            if (normalized === 'accepted') {
                return 'primary';
            }
            if (normalized === 'in-progress') {
                return 'info';
            }
            if (normalized === 'on-hold') {
                return 'warning text-dark';
            }
            if (normalized === 'cancelled' || normalized === 'rejected') {
                return 'danger';
            }
            return 'secondary';
        }

        function formatStatusLabel(status) {
            return String(status || 'pending')
                .toLowerCase()
                .replace(/[-_]/g, ' ')
                .replace(/\b\w/g, function (char) {
                    return char.toUpperCase();
                });
        }

        function getPriorityBadgeClass(priority) {
            const normalized = String(priority || 'medium').toLowerCase();
            if (normalized === 'urgent') {
                return 'danger';
            }
            if (normalized === 'high') {
                return 'warning text-dark';
            }
            if (normalized === 'medium') {
                return 'primary';
            }
            return 'secondary';
        }

        function formatPriorityLabel(priority) {
            return String(priority || 'medium')
                .toLowerCase()
                .replace(/[-_]/g, ' ')
                .replace(/\b\w/g, function (char) {
                    return char.toUpperCase();
                });
        }

        function findRowByRequestId(requestId) {
            return Array.from(tableBody.querySelectorAll('tr')).find(function (tr) {
                return String(tr.getAttribute('data-request-id')) === String(requestId);
            }) || null;
        }

        function updateRowInPlace(row) {
            if (!row || !row.request_id) {
                return;
            }

            const tr = findRowByRequestId(row.request_id);
            if (!tr) {
                return;
            }

            const status = String(row.status || 'pending').toLowerCase();
            const statusBadge = tr.querySelector('[data-status-label]');
            if (statusBadge) {
                statusBadge.className = 'badge bg-' + getStatusBadgeClass(status);
                statusBadge.textContent = formatStatusLabel(status);
            }

            const staffSelect = tr.querySelector('select[data-field="assigned_staff_id"]');
            if (staffSelect) {
                const assignedId = row.assigned_staff_id == null ? '' : String(row.assigned_staff_id);
                staffSelect.value = assignedId;
            }

            const priority = String(row.priority || 'medium').toLowerCase();
            const priorityBadge = tr.querySelector('[data-priority-label]');
            if (priorityBadge) {
                priorityBadge.className = 'badge bg-' + getPriorityBadgeClass(priority);
                priorityBadge.textContent = formatPriorityLabel(priority);
            }

            const updatedAtCell = tr.querySelector('[data-updated-at]');
            if (updatedAtCell) {
                updatedAtCell.textContent = String(row.updated_at || row.created_at || 'N/A');
            }
        }

        function renderRows(rows) {
            resultCount.textContent = 'Total: ' + rows.length;

            if (!Array.isArray(rows) || rows.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No matching maintenance requests found.</td></tr>';
                return;
            }

            tableBody.innerHTML = rows.map(function (row) {
                const status = String(row.status || 'pending').toLowerCase();
                const priority = String(row.priority || 'medium').toLowerCase();

                return '\n                <tr data-request-id="' + escapeHtml(row.request_id) + '">\n                    <td><strong>#' + escapeHtml(row.request_id) + '</strong></td>\n                    <td>\n                        <div class="fw-semibold">' + escapeHtml(row.title || 'Untitled') + '</div>\n                        <small class="text-muted">' + escapeHtml(row.category || 'N/A') + '</small>\n                    </td>\n                    <td>' + escapeHtml(row.tenant_name || 'Unknown Tenant') + '</td>\n                    <td><span class="badge bg-' + getStatusBadgeClass(status) + '" data-status-label>' + escapeHtml(formatStatusLabel(status)) + '</span></td>\n                    <td><span class="badge bg-' + getPriorityBadgeClass(priority) + '" data-priority-label>' + escapeHtml(formatPriorityLabel(priority)) + '</span></td>\n                    <td>\n                        <select class="form-select form-select-sm update-field" data-request-id="' + escapeHtml(row.request_id) + '" data-field="assigned_staff_id">\n                            ' + staffOptions(row.assigned_staff_id) + '\n                        </select>\n                    </td>\n                    <td data-updated-at>' + escapeHtml(row.updated_at || row.created_at || 'N/A') + '</td>\n                </tr>\n            ';
            }).join('');
        }

        async function loadData() {
            if (!tableReady) {
                return;
            }

            try {
                const params = new URLSearchParams({
                    ajax: 'search',
                    q: searchInput.value.trim(),
                    status: statusFilter.value,
                    priority: priorityFilter.value
                });

                const res = await fetch(pageEndpoint + '?' + params.toString());
                const payload = await res.json();

                if (payload.success) {
                    renderRows(payload.data || []);
                }
            } catch (err) {
                console.error('Maintenance search error:', err);
            }
        }

        if (tableReady) {
            let debounce;
            searchInput.addEventListener('input', function () {
                clearTimeout(debounce);
                debounce = setTimeout(loadData, 300);
            });
            statusFilter.addEventListener('change', loadData);
            priorityFilter.addEventListener('change', loadData);
        }

        document.addEventListener('change', async function (event) {
            const field = event.target.closest('.update-field');
            if (!field || !tableReady) {
                return;
            }

            const requestId = field.getAttribute('data-request-id');
            const fieldName = field.getAttribute('data-field');
            const fieldValue = field.value;

            const formData = new FormData();
            formData.append('action', 'update_request');
            formData.append('request_id', requestId);
            formData.append(fieldName, fieldValue);

            try {
                const res = await fetch(pageEndpoint, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                });
                const payload = await res.json();

                if (payload.success) {
                    showAlert(payload.message, 'success');
                    if (payload.data) {
                        updateRowInPlace(payload.data);
                    }

                    const hasActiveFilters = searchInput.value.trim() !== '' || statusFilter.value !== '' || priorityFilter.value !== '';
                    if (hasActiveFilters) {
                        loadData();
                    }
                } else {
                    showAlert(payload.message || 'Update failed.', 'danger');
                }
            } catch (err) {
                console.error('Maintenance update error:', err);
                showAlert('Failed to update maintenance request.', 'danger');
            }
        });

        if (tableReady) {
            setInterval(loadData, 10000);
        }
    });
})();
