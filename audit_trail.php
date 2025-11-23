<?php
session_start();
// Require admin session
if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: sign-in.php');
    exit();
}

require_once 'audit_trail_helper.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
  <title>Audit Trail - Barangay 170</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: #DAF1DE;
      min-height: 100vh;
    }

    /* Header */
    .page-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      background: white;
      padding: 0.625rem 1.25rem;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .page-header .logo-section {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .page-header img {
      height: 50px;
      border-radius: 50%;
    }

    .page-header .title-section div:first-child {
      font-weight: 500;
      font-size: 1rem;
    }

    .page-header .title-section div:last-child {
      font-size: 0.875rem;
      color: #666;
    }

    .header-actions {
      display: flex;
      gap: 0.5rem;
      align-items: center;
    }

    .header-actions .btn {
      padding: 0.375rem 1rem;
      font-size: 0.875rem;
      cursor: pointer;
      border: none;
      border-radius: 0.375rem;
      background: #228650;
      color: white;
      transition: opacity 0.2s;
      font-family: 'Poppins', sans-serif;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }

    .header-actions .btn:hover {
      opacity: 0.9;
    }

    /* Main Content */
    .main-content {
      padding: 2rem;
      max-width: 1600px;
      margin: 0 auto;
    }

    /* Page Title */
    .page-title {
      background: white;
      border-radius: 0.75rem;
      padding: 1.5rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      margin-bottom: 1.5rem;
    }

    .page-title h1 {
      font-size: 1.5rem;
      color: #2c5f2d;
      margin-bottom: 0.5rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .page-title p {
      color: #6b7280;
      font-size: 0.875rem;
    }

    /* Filters Section */
    .filters-section {
      background: white;
      border-radius: 0.75rem;
      padding: 1.25rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      margin-bottom: 1.5rem;
    }

    .filter-tabs {
      display: flex;
      gap: 0.5rem;
      flex-wrap: wrap;
    }

    .filter-tab {
      padding: 0.5rem 1rem;
      border: none;
      border-radius: 0.5rem;
      background: #f3f4f6;
      color: #4b5563;
      cursor: pointer;
      font-family: 'Poppins', sans-serif;
      font-size: 0.875rem;
      font-weight: 500;
      transition: all 0.3s;
      display: flex;
      align-items: center;
      gap: 0.375rem;
    }

    .filter-tab.active {
      background: #228650;
      color: white;
    }

    .filter-tab:hover:not(.active) {
      background: #e5e7eb;
    }

    /* Audit Trail List */
    .audit-list {
      background: white;
      border-radius: 0.75rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      overflow: hidden;
    }

    .audit-item {
      padding: 1.25rem;
      border-bottom: 1px solid #f3f4f6;
      transition: background 0.2s;
    }

    .audit-item:hover {
      background: #f9fafb;
    }

    .audit-item:last-child {
      border-bottom: none;
    }

    .audit-header {
      display: flex;
      justify-content: space-between;
      align-items: start;
      margin-bottom: 0.75rem;
    }

    .audit-info {
      flex: 1;
    }

    .audit-action {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin-bottom: 0.375rem;
    }

    .action-icon {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.875rem;
      flex-shrink: 0;
    }

    .action-icon.login {
      background: #d4f8e8;
      color: #16a34a;
    }

    .action-icon.logout {
      background: #fee2e2;
      color: #dc2626;
    }

    .action-icon.update {
      background: #dbeafe;
      color: #2563eb;
    }

    .action-icon.delete {
      background: #fecaca;
      color: #dc2626;
    }

    .action-icon.view {
      background: #e9d5ff;
      color: #9333ea;
    }

    .action-icon.notification {
      background: #fed7aa;
      color: #ea580c;
    }

    .action-text {
      font-weight: 600;
      color: #2c3e50;
      font-size: 0.9375rem;
    }

    .audit-description {
      color: #6b7280;
      font-size: 0.875rem;
      line-height: 1.5;
      margin-bottom: 0.5rem;
    }

    .audit-meta {
      display: flex;
      gap: 1.5rem;
      flex-wrap: wrap;
      font-size: 0.8125rem;
      color: #9ca3af;
    }

    .meta-item {
      display: flex;
      align-items: center;
      gap: 0.375rem;
    }

    .audit-timestamp {
      text-align: right;
      color: #6b7280;
      font-size: 0.8125rem;
    }

    .audit-date {
      font-weight: 600;
      color: #2c3e50;
      margin-bottom: 0.25rem;
    }

    .audit-time {
      color: #9ca3af;
    }

    /* Changes Section */
    .changes-section {
      margin-top: 0.75rem;
      padding: 0.75rem;
      background: #f9fafb;
      border-radius: 0.5rem;
      border-left: 3px solid #228650;
    }

    .changes-title {
      font-size: 0.75rem;
      font-weight: 600;
      color: #6b7280;
      text-transform: uppercase;
      margin-bottom: 0.5rem;
    }

    .change-item {
      font-size: 0.875rem;
      margin-bottom: 0.375rem;
    }

    .change-label {
      color: #6b7280;
      font-weight: 500;
    }

    .change-value {
      color: #2c3e50;
      font-weight: 600;
    }

    .change-arrow {
      color: #228650;
      margin: 0 0.5rem;
    }

    /* Loading State */
    .loading {
      text-align: center;
      padding: 3rem;
      color: #6b7280;
    }

    .spinner {
      border: 3px solid #f3f3f3;
      border-top: 3px solid #228650;
      border-radius: 50%;
      width: 40px;
      height: 40px;
      animation: spin 1s linear infinite;
      margin: 0 auto 1rem;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    /* Empty State */
    .empty-state {
      text-align: center;
      padding: 3rem;
      color: #6b7280;
    }

    .empty-state i {
      font-size: 3rem;
      opacity: 0.3;
      margin-bottom: 1rem;
    }

    /* Pagination */
    .pagination {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 0.5rem;
      padding: 1.5rem;
      background: white;
      border-radius: 0 0 0.75rem 0.75rem;
    }

    .pagination-btn {
      padding: 0.5rem 1rem;
      border: 1px solid #e5e7eb;
      background: white;
      color: #4b5563;
      border-radius: 0.375rem;
      cursor: pointer;
      font-family: 'Poppins', sans-serif;
      font-size: 0.875rem;
      transition: all 0.2s;
    }

    .pagination-btn:hover:not(:disabled) {
      background: #f9fafb;
      border-color: #228650;
    }

    .pagination-btn:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }

    .pagination-info {
      color: #6b7280;
      font-size: 0.875rem;
    }

    @media (max-width: 768px) {
      .main-content {
        padding: 1rem;
      }

      .audit-header {
        flex-direction: column;
        gap: 0.5rem;
      }

      .audit-timestamp {
        text-align: left;
      }

      .audit-meta {
        flex-direction: column;
        gap: 0.5rem;
      }
    }
  </style>
</head>
<body>
  <!-- Header -->
  <div class="page-header">
    <div class="logo-section">
      <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRTDCuh4kIpAtR-QmjA1kTjE_8-HSd8LSt3Gw&s" alt="seal">
      <div class="title-section">
        <div>Barangay 170</div>
        <div>Audit Trail</div>
      </div>
    </div>
    <div class="header-actions">
      <a href="admindashboard.php" class="btn">
        <i class="fas fa-arrow-left"></i>
        Back to Dashboard
      </a>
    </div>
  </div>

  <div class="main-content">
    <!-- Page Title -->
    <div class="page-title">
      <h1>
        <i class="fas fa-history"></i>
        Audit Trail
      </h1>
      <p>Track all administrative actions and system activities</p>
    </div>

    <!-- Filters -->
    <div class="filters-section">
      <div class="filter-tabs">
        <button class="filter-tab active" data-filter="all">
          <i class="fas fa-list"></i>
          All Activities
        </button>
        <button class="filter-tab" data-filter="LOGIN">
          <i class="fas fa-sign-in-alt"></i>
          Login
        </button>
        <button class="filter-tab" data-filter="LOGOUT">
          <i class="fas fa-sign-out-alt"></i>
          Logout
        </button>
        <button class="filter-tab" data-filter="STATUS_CHANGE">
          <i class="fas fa-exchange-alt"></i>
          Status Changes
        </button>
        <button class="filter-tab" data-filter="REQUEST_DELETE">
          <i class="fas fa-trash"></i>
          Deletions
        </button>
        <button class="filter-tab" data-filter="NOTIFICATION_ADD">
          <i class="fas fa-bell"></i>
          Notifications
        </button>
        <button class="filter-tab" data-filter="USER_VIEW">
          <i class="fas fa-eye"></i>
          User Views
        </button>
      </div>
    </div>

    <!-- Audit List -->
    <div class="audit-list">
      <div id="auditContent" class="loading">
        <div class="spinner"></div>
        <p>Loading audit trail...</p>
      </div>
    </div>

    <!-- Pagination -->
    <div class="pagination" id="paginationControls" style="display: none;">
      <button class="pagination-btn" id="prevBtn" onclick="previousPage()">
        <i class="fas fa-chevron-left"></i> Previous
      </button>
      <span class="pagination-info" id="pageInfo">Page 1</span>
      <button class="pagination-btn" id="nextBtn" onclick="nextPage()">
        Next <i class="fas fa-chevron-right"></i>
      </button>
    </div>
  </div>

  <script>
    let currentFilter = 'all';
    let currentPage = 0;
    let itemsPerPage = 50;
    let totalItems = 0;

    // Load audit trail
    async function loadAuditTrail() {
      try {
        const response = await fetch(`api_get_audit_trail.php?filter=${currentFilter}&limit=${itemsPerPage}&offset=${currentPage * itemsPerPage}`, {
          cache: 'no-store'
        });

        if (!response.ok) {
          throw new Error('Failed to fetch audit trail');
        }

        const data = await response.json();
        displayAuditTrail(data.logs);
        totalItems = data.total;
        updatePagination();
      } catch (error) {
        console.error('Error loading audit trail:', error);
        document.getElementById('auditContent').innerHTML = `
          <div class="empty-state">
            <i class="fas fa-exclamation-triangle"></i>
            <p>Failed to load audit trail</p>
          </div>
        `;
      }
    }

    function displayAuditTrail(logs) {
      const container = document.getElementById('auditContent');

      if (logs.length === 0) {
        container.innerHTML = `
          <div class="empty-state">
            <i class="fas fa-history"></i>
            <p>No audit trail records found</p>
          </div>
        `;
        return;
      }

      container.innerHTML = logs.map(log => {
        const icon = getActionIcon(log.actionType);
        const iconClass = getActionIconClass(log.actionType);
        const timestamp = formatTimestamp(log.timestamp);

        let changesHtml = '';
        if (log.oldValue || log.newValue) {
          changesHtml = `
            <div class="changes-section">
              <div class="changes-title">Changes Made</div>
              ${log.oldValue ? `
                <div class="change-item">
                  <span class="change-label">From:</span>
                  <span class="change-value">${log.oldValue}</span>
                </div>
              ` : ''}
              ${log.newValue ? `
                <div class="change-item">
                  <span class="change-label">To:</span>
                  <span class="change-value">${log.newValue}</span>
                </div>
              ` : ''}
            </div>
          `;
        }

        return `
          <div class="audit-item">
            <div class="audit-header">
              <div class="audit-info">
                <div class="audit-action">
                  <div class="action-icon ${iconClass}">
                    <i class="${icon}"></i>
                  </div>
                  <span class="action-text">${formatActionType(log.actionType)}</span>
                </div>
                <div class="audit-description">${log.actionDescription}</div>
                <div class="audit-meta">
                  <div class="meta-item">
                    <i class="fas fa-user"></i>
                    <span>${log.adminEmail}</span>
                  </div>
                  ${log.targetId ? `
                    <div class="meta-item">
                      <i class="fas fa-tag"></i>
                      <span>${log.targetType}: ${log.targetId}</span>
                    </div>
                  ` : ''}
                  <div class="meta-item">
                    <i class="fas fa-network-wired"></i>
                    <span>${log.ipAddress}</span>
                  </div>
                </div>
                ${changesHtml}
              </div>
              <div class="audit-timestamp">
                <div class="audit-date">${timestamp.date}</div>
                <div class="audit-time">${timestamp.time}</div>
              </div>
            </div>
          </div>
        `;
      }).join('');
    }

    function getActionIcon(actionType) {
      const icons = {
        'LOGIN': 'fas fa-sign-in-alt',
        'LOGOUT': 'fas fa-sign-out-alt',
        'REQUEST_UPDATE': 'fas fa-edit',
        'REQUEST_DELETE': 'fas fa-trash-alt',
        'STATUS_CHANGE': 'fas fa-exchange-alt',
        'PRIORITY_CHANGE': 'fas fa-exclamation-triangle',
        'NOTIFICATION_ADD': 'fas fa-bell-plus',
        'NOTIFICATION_DELETE': 'fas fa-bell-slash',
        'USER_VIEW': 'fas fa-eye'
      };
      return icons[actionType] || 'fas fa-circle';
    }

    function getActionIconClass(actionType) {
      if (actionType === 'LOGIN') return 'login';
      if (actionType === 'LOGOUT') return 'logout';
      if (actionType === 'REQUEST_DELETE' || actionType === 'NOTIFICATION_DELETE') return 'delete';
      if (actionType === 'USER_VIEW') return 'view';
      if (actionType.includes('NOTIFICATION')) return 'notification';
      return 'update';
    }

    function formatActionType(actionType) {
      return actionType.replace(/_/g, ' ').toLowerCase()
        .split(' ')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
    }

    function formatTimestamp(timestamp) {
      const date = new Date(timestamp);
      return {
        date: date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }),
        time: date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
      };
    }

    function updatePagination() {
      const paginationControls = document.getElementById('paginationControls');
      const prevBtn = document.getElementById('prevBtn');
      const nextBtn = document.getElementById('nextBtn');
      const pageInfo = document.getElementById('pageInfo');

      const totalPages = Math.ceil(totalItems / itemsPerPage);
      const currentPageNum = currentPage + 1;

      if (totalItems > 0) {
        paginationControls.style.display = 'flex';
        pageInfo.textContent = `Page ${currentPageNum} of ${totalPages} (${totalItems} records)`;
        prevBtn.disabled = currentPage === 0;
        nextBtn.disabled = currentPage >= totalPages - 1;
      } else {
        paginationControls.style.display = 'none';
      }
    }

    function previousPage() {
      if (currentPage > 0) {
        currentPage--;
        loadAuditTrail();
      }
    }

    function nextPage() {
      const totalPages = Math.ceil(totalItems / itemsPerPage);
      if (currentPage < totalPages - 1) {
        currentPage++;
        loadAuditTrail();
      }
    }

    // Filter tabs
    document.querySelectorAll('.filter-tab').forEach(tab => {
      tab.addEventListener('click', () => {
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        currentFilter = tab.dataset.filter;
        currentPage = 0;
        loadAuditTrail();
      });
    });

    // Load on page load
    document.addEventListener('DOMContentLoaded', loadAuditTrail);

    // Auto-refresh every 30 seconds
    setInterval(loadAuditTrail, 30000);
  </script>
</body>
</html>