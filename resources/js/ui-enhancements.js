// UI Enhancements for Workflow Management System

document.addEventListener("DOMContentLoaded", function () {
    // Initialize all UI enhancements
    initFormEnhancements();
    initDashboardWidgets();
    initApprovalInterface();
    initRequestForms();
    initAccessibilityFeatures();
    initTooltips();
});

// Form enhancements
function initFormEnhancements() {
    // Add date validation for leave and mission requests
    const startDateInputs = document.querySelectorAll(
        'input[type="date"]#start_date'
    );
    const endDateInputs = document.querySelectorAll(
        'input[type="date"]#end_date'
    );

    startDateInputs.forEach((input) => {
        input.addEventListener("change", function () {
            const endDateInput = this.closest("form").querySelector(
                'input[type="date"]#end_date'
            );
            if (
                endDateInput &&
                new Date(endDateInput.value) < new Date(this.value)
            ) {
                endDateInput.value = this.value;
            }
            validateDateRange(this, endDateInput);
        });
    });

    endDateInputs.forEach((input) => {
        input.addEventListener("change", function () {
            const startDateInput = this.closest("form").querySelector(
                'input[type="date"]#start_date'
            );
            if (
                startDateInput &&
                new Date(this.value) < new Date(startDateInput.value)
            ) {
                startDateInput.value = this.value;
            }
            validateDateRange(startDateInput, this);
        });
    });

    // Real-time balance validation for leave requests
    const leaveTypeSelect = document.getElementById("leave_type_id");
    const startDateInput = document.getElementById("start_date");
    const endDateInput = document.getElementById("end_date");

    if (leaveTypeSelect && startDateInput && endDateInput) {
        [leaveTypeSelect, startDateInput, endDateInput].forEach((element) => {
            element.addEventListener("change", function () {
                validateLeaveBalance(
                    leaveTypeSelect.value,
                    startDateInput.value,
                    endDateInput.value
                );
            });
        });
    }

    // File upload enhancements
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach((input) => {
        input.addEventListener("change", function () {
            handleFileUpload(this);
        });
    });
}

// Validate date range
function validateDateRange(startDateInput, endDateInput) {
    if (!startDateInput || !endDateInput) return;

    const startDate = new Date(startDateInput.value);
    const endDate = new Date(endDateInput.value);

    if (startDate > endDate) {
        showError(endDateInput, "End date must be after start date");
        return false;
    } else {
        clearError(endDateInput);
        return true;
    }
}

// Validate leave balance
function validateLeaveBalance(leaveTypeId, startDate, endDate) {
    if (!leaveTypeId || !startDate || !endDate) return;

    // In a real implementation, this would make an AJAX call to check balance
    // For now, we'll just simulate the validation
    const form = document.querySelector("form");
    const balanceInfo = document.getElementById("leave-balance-info");

    if (balanceInfo) {
        // Simulate checking balance
        const daysRequested = calculateDaysBetween(startDate, endDate);
        balanceInfo.innerHTML = `
            <div class="alert alert-info mt-2">
                <strong>Leave Balance Information:</strong>
                You are requesting ${daysRequested} days of leave.
                Your current balance for this leave type is 15 days.
            </div>
        `;
    }
}

// Calculate days between two dates (excluding weekends)
function calculateDaysBetween(startDate, endDate) {
    const start = new Date(startDate);
    const end = new Date(endDate);
    let days = 0;

    for (
        let date = new Date(start);
        date <= end;
        date.setDate(date.getDate() + 1)
    ) {
        // Only count weekdays (Monday-Friday)
        const dayOfWeek = date.getDay();
        if (dayOfWeek !== 0 && dayOfWeek !== 6) {
            days++;
        }
    }

    return days;
}

// Handle file upload
function handleFileUpload(input) {
    if (input.files.length > 0) {
        const file = input.files[0];
        const maxSize = 2 * 1024 * 1024; // 2MB
        const allowedTypes = [
            "application/pdf",
            "application/msword",
            "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
            "image/jpeg",
            "image/png",
        ];

        if (file.size > maxSize) {
            showError(input, "File size exceeds 2MB limit");
            input.value = "";
            return;
        }

        if (!allowedTypes.includes(file.type)) {
            showError(
                input,
                "File type not allowed. Please upload PDF, DOC, DOCX, JPG, or PNG files."
            );
            input.value = "";
            return;
        }

        clearError(input);

        // Show file preview if it's an image
        if (file.type.startsWith("image/")) {
            showImagePreview(file, input);
        }
    }
}

// Show image preview
function showImagePreview(file, input) {
    const reader = new FileReader();
    reader.onload = function (e) {
        let previewContainer = input.parentNode.querySelector(".file-preview");
        if (!previewContainer) {
            previewContainer = document.createElement("div");
            previewContainer.className = "file-preview mt-2";
            input.parentNode.appendChild(previewContainer);
        }

        previewContainer.innerHTML = `
            <div class="border rounded p-2">
                <p class="text-sm font-medium">File Preview:</p>
                <img src="${e.target.result}" alt="File preview" class="mt-1 max-w-full h-auto rounded" style="max-height: 200px;">
            </div>
        `;
    };
    reader.readAsDataURL(file);
}

// Dashboard widgets
function initDashboardWidgets() {
    // Initialize any dashboard-specific widgets
    initProgressBars();
    initCharts();
    initCalendarWidgets();
}

// Initialize progress bars
function initProgressBars() {
    const progressBars = document.querySelectorAll(".progress-bar");
    progressBars.forEach((bar) => {
        const width = bar.getAttribute("aria-valuenow");
        bar.style.width = width + "%";

        // Add animation
        bar.style.transition = "width 0.5s ease-in-out";
    });
}

// Initialize charts (placeholder for now)
function initCharts() {
    // In a real implementation, this would initialize charting libraries
    // For now, we'll just add some visual enhancements
    const chartContainers = document.querySelectorAll(".chart-container");
    chartContainers.forEach((container) => {
        container.classList.add("bg-white", "rounded", "shadow", "p-4");
    });
}

// Initialize calendar widgets
function initCalendarWidgets() {
    // Add click handlers for calendar navigation
    const prevButtons = document.querySelectorAll(".calendar-prev");
    const nextButtons = document.querySelectorAll(".calendar-next");

    prevButtons.forEach((button) => {
        button.addEventListener("click", function () {
            navigateCalendar(this, -1);
        });
    });

    nextButtons.forEach((button) => {
        button.addEventListener("click", function () {
            navigateCalendar(this, 1);
        });
    });
}

// Navigate calendar
function navigateCalendar(button, direction) {
    // In a real implementation, this would update the calendar view
    // For now, we'll just show a message
    const calendar = button.closest(".calendar-widget");
    if (calendar) {
        const message = document.createElement("div");
        message.className = "alert alert-info mt-2";
        message.textContent = `Calendar navigation: ${
            direction > 0 ? "Next" : "Previous"
        } month`;
        calendar.appendChild(message);

        // Remove message after 2 seconds
        setTimeout(() => {
            message.remove();
        }, 2000);
    }
}

// Approval interface enhancements
function initApprovalInterface() {
    // Add confirmation dialogs for approval actions
    const approveButtons = document.querySelectorAll("button.btn-success");
    const rejectButtons = document.querySelectorAll("button.btn-danger");

    approveButtons.forEach((button) => {
        if (button.textContent.trim() === "Approve") {
            button.addEventListener("click", function (e) {
                if (
                    !confirm("Are you sure you want to approve this request?")
                ) {
                    e.preventDefault();
                }
            });
        }
    });

    rejectButtons.forEach((button) => {
        if (button.textContent.trim() === "Reject") {
            button.addEventListener("click", function (e) {
                const comments = this.closest("form").querySelector(
                    'textarea[name="comments"]'
                );
                if (!comments || !comments.value.trim()) {
                    alert("Please provide a reason for rejection.");
                    e.preventDefault();
                    return;
                }

                if (!confirm("Are you sure you want to reject this request?")) {
                    e.preventDefault();
                }
            });
        }
    });

    // Add comment expansion for approval history
    const commentToggles = document.querySelectorAll(".comment-toggle");
    commentToggles.forEach((toggle) => {
        toggle.addEventListener("click", function () {
            const commentText = this.nextElementSibling;
            if (commentText.style.display === "none") {
                commentText.style.display = "block";
                this.textContent = "Show less";
            } else {
                commentText.style.display = "none";
                this.textContent = "Show more";
            }
        });
    });
}

// Request form enhancements
function initRequestForms() {
    // Add dynamic form fields for mission requests
    const addDestinationButton = document.getElementById("add-destination");
    if (addDestinationButton) {
        addDestinationButton.addEventListener("click", function () {
            addDestinationField();
        });
    }

    // Add budget item functionality
    const addBudgetItemButton = document.getElementById("add-budget-item");
    if (addBudgetItemButton) {
        addBudgetItemButton.addEventListener("click", function () {
            addBudgetItemField();
        });
    }

    // Auto-calculate total budget
    const budgetInputs = document.querySelectorAll("input.budget-amount");
    budgetInputs.forEach((input) => {
        input.addEventListener("input", function () {
            calculateTotalBudget();
        });
    });
}

// Add destination field for mission requests
function addDestinationField() {
    const container = document.getElementById("destinations-container");
    if (!container) return;

    const fieldCount = container.querySelectorAll(".destination-field").length;
    const newField = document.createElement("div");
    newField.className = "destination-field border rounded p-3 mb-3";
    newField.innerHTML = `
        <div class="row mb-2">
            <div class="col-md-6">
                <label for="destination_${fieldCount}" class="form-label">Destination</label>
                <input type="text" class="form-control" id="destination_${fieldCount}" name="destinations[${fieldCount}][name]" required>
            </div>
            <div class="col-md-6">
                <label for="destination_date_${fieldCount}" class="form-label">Date</label>
                <input type="date" class="form-control" id="destination_date_${fieldCount}" name="destinations[${fieldCount}][date]" required>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label for="destination_purpose_${fieldCount}" class="form-label">Purpose</label>
                <textarea class="form-control" id="destination_purpose_${fieldCount}" name="destinations[${fieldCount}][purpose]" rows="2"></textarea>
            </div>
        </div>
        <button type="button" class="btn btn-danger btn-sm mt-2 remove-destination">Remove</button>
    `;

    container.appendChild(newField);

    // Add event listener to remove button
    const removeButton = newField.querySelector(".remove-destination");
    removeButton.addEventListener("click", function () {
        newField.remove();
        calculateTotalBudget();
    });
}

// Add budget item field
function addBudgetItemField() {
    const container = document.getElementById("budget-items-container");
    if (!container) return;

    const fieldCount = container.querySelectorAll(".budget-item").length;
    const newField = document.createElement("div");
    newField.className = "budget-item border rounded p-3 mb-3";
    newField.innerHTML = `
        <div class="row">
            <div class="col-md-4">
                <label for="budget_category_${fieldCount}" class="form-label">Category</label>
                <select class="form-control budget-category" id="budget_category_${fieldCount}" name="budget_items[${fieldCount}][category]" required>
                    <option value="">Select Category</option>
                    <option value="transport">Transport</option>
                    <option value="accommodation">Accommodation</option>
                    <option value="meals">Meals</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="budget_description_${fieldCount}" class="form-label">Description</label>
                <input type="text" class="form-control" id="budget_description_${fieldCount}" name="budget_items[${fieldCount}][description]" required>
            </div>
            <div class="col-md-3">
                <label for="budget_amount_${fieldCount}" class="form-label">Amount</label>
                <input type="number" step="0.01" class="form-control budget-amount" id="budget_amount_${fieldCount}" name="budget_items[${fieldCount}][amount]" required>
            </div>
            <div class="col-md-1">
                <label class="form-label">&nbsp;</label>
                <button type="button" class="btn btn-danger btn-sm remove-budget-item">X</button>
            </div>
        </div>
    `;

    container.appendChild(newField);

    // Add event listener to remove button
    const removeButton = newField.querySelector(".remove-budget-item");
    removeButton.addEventListener("click", function () {
        newField.remove();
        calculateTotalBudget();
    });

    // Add event listener to amount input
    const amountInput = newField.querySelector(".budget-amount");
    amountInput.addEventListener("input", function () {
        calculateTotalBudget();
    });
}

// Calculate total budget
function calculateTotalBudget() {
    const amountInputs = document.querySelectorAll(".budget-amount");
    let total = 0;

    amountInputs.forEach((input) => {
        const value = parseFloat(input.value) || 0;
        total += value;
    });

    const totalElement = document.getElementById("total-budget");
    if (totalElement) {
        totalElement.textContent = total.toFixed(2);
    }
}

// Accessibility features
function initAccessibilityFeatures() {
    // Add keyboard navigation support for dropdowns
    const dropdowns = document.querySelectorAll(".dropdown");
    dropdowns.forEach((dropdown) => {
        const toggle = dropdown.querySelector('[data-bs-toggle="dropdown"]');
        if (toggle) {
            toggle.addEventListener("keydown", function (e) {
                if (e.key === "Enter" || e.key === " ") {
                    e.preventDefault();
                    const menu = dropdown.querySelector(".dropdown-menu");
                    if (menu) {
                        menu.classList.toggle("show");
                    }
                }
            });
        }
    });

    // Add focus indicators for keyboard users
    const focusableElements = document.querySelectorAll(
        "button, a, input, select, textarea"
    );
    focusableElements.forEach((element) => {
        element.addEventListener("focus", function () {
            this.classList.add("keyboard-focus");
        });

        element.addEventListener("blur", function () {
            this.classList.remove("keyboard-focus");
        });
    });

    // Add skip links for screen readers
    addSkipLinks();
}

// Add skip links
function addSkipLinks() {
    const skipLink = document.createElement("a");
    skipLink.href = "#main-content";
    skipLink.className = "skip-link sr-only";
    skipLink.textContent = "Skip to main content";
    document.body.insertBefore(skipLink, document.body.firstChild);
}

// Initialize tooltips
function initTooltips() {
    // Add tooltip functionality to elements with data-tooltip attribute
    const tooltipElements = document.querySelectorAll("[data-tooltip]");
    tooltipElements.forEach((element) => {
        element.addEventListener("mouseenter", function () {
            showTooltip(this);
        });

        element.addEventListener("mouseleave", function () {
            hideTooltip(this);
        });
    });
}

// Show tooltip
function showTooltip(element) {
    const tooltipText = element.getAttribute("data-tooltip");
    if (!tooltipText) return;

    let tooltip = document.getElementById("custom-tooltip");
    if (!tooltip) {
        tooltip = document.createElement("div");
        tooltip.id = "custom-tooltip";
        tooltip.className = "custom-tooltip";
        document.body.appendChild(tooltip);
    }

    tooltip.textContent = tooltipText;
    tooltip.style.display = "block";

    const rect = element.getBoundingClientRect();
    tooltip.style.left = rect.left + "px";
    tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + "px";
}

// Hide tooltip
function hideTooltip() {
    const tooltip = document.getElementById("custom-tooltip");
    if (tooltip) {
        tooltip.style.display = "none";
    }
}

// Utility functions
function showError(element, message) {
    clearError(element);

    const errorDiv = document.createElement("div");
    errorDiv.className = "text-danger mt-1 error-message";
    errorDiv.textContent = message;
    element.parentNode.appendChild(errorDiv);

    element.classList.add("is-invalid");
}

function clearError(element) {
    const errorDiv = element.parentNode.querySelector(".error-message");
    if (errorDiv) {
        errorDiv.remove();
    }

    element.classList.remove("is-invalid");
}

// Add global error handling
window.addEventListener("error", function (e) {
    console.error("Global error:", e.error);
    // In a real implementation, you might want to send this to a logging service
});
